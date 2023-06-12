<?php
$campos = '';
$inputImagem = '';
foreach($this->metadados as $metadata=>$key){
	$label = $metadata;	
	$label = str_replace("_", " ", $label);
	$label = ucwords($label);
	$obrigatorio = (!$key["NULLABLE"])?"*":"";
	
	$class = "";
	if($key["DATA_TYPE"] == "decimal" || $key["DATA_TYPE"] == "numeric"){
		$class = "dinheiro";	
	} elseif(strpos($label,'Telefone') !== false){
		$class = "telefone";
	} elseif(strpos($label,'Cep') !== false){
		$class = "cep";	
	} elseif(strpos($label,'Cpf') !== false){
		$class = "cpf";
	} elseif(strpos($label,'Cnpj') !== false){
		$class = "cnpj";	
	} elseif(strpos($label,'Data') !== false){
		$class = "data";	
	} elseif($key["DATA_TYPE"] == "int"){
		$class = "numero";
	}
	
	$maxlength = (empty($class))?"maxlength='{$key["LENGTH"]}'":"";
	if(!$key["PRIMARY_POSITION"]){
		if($key["COLUMN_NAME"] == "foto" || $key["COLUMN_NAME"] == "imagem"){
		    $inputImagem = '<input class="hidden" type="text" id="foto" name="'.$key["COLUMN_NAME"].'" value="<?php echo $this->registro["'.$key["COLUMN_NAME"].'"];?>" />';
			$campos.= '
        			<div class="mws-form-col-8-8 omega">
        				<input id="uploader" type="button" class="mws-button orange mws-i-24 i-camera-2 large" value="Imagem">
        				<span>(Tamanho XXxXX)</span>
        				<div id="filelist"></div>
        				<br/>
        				<?php 
        				    $foto = (empty($this->registro["'.$key["COLUMN_NAME"].'"]))?"http://dummyimage.com/300x300/d6d6d6/686a82.gif&text=Imagem":$this->baseUrl($this->registro["'.$key["COLUMN_NAME"].'"]);
        				?>
        				<a id="pretty" rel="prettyPhoto" href="<?php echo $foto;?>" title="">
        					<img  id="imagem" src="<?php echo $foto;?>" />
        				</a>
        				<div id="edicao" style="<?php echo ($this->registro["'.$key["COLUMN_NAME"].'"] != "")?"display:block":"display:none"; ?>">																					
        					<a style="text-indent:10000px;margin-top:5px;<?php echo (!empty($this->registro["'.$key["COLUMN_NAME"].'"]))?"display:block":"display:none"; ?>" title="Excluir foto" class="mws-ic-16 ic-cancel excluir-foto" rel="<?php echo $this->registro["codigo"];?>" href="javascript://">Excluir</a>					
        				</div>						
        			</div>		
			';		
		 
		} elseif ($key["DATA_TYPE"] == "text"){
			$campos.= '
        			<div class="mws-form-col-8-8 omega">
        				<label>'.$label.'</label>
        				<div class="mws-form-item large">
        					<textarea cols="100%" rows="100%" id="texto" name="'.$key["COLUMN_NAME"].'"><?php echo stripslashes($this->registro["'.$key["COLUMN_NAME"].'"]); ?></textarea>					
        				</div>				
        			</div>
			';
		} elseif ($key["DATA_TYPE"] == "date" || $key["DATA_TYPE"] == "datetime"){
			$campos.= '
        			<div class="mws-form-col-2-8 omega">
        			    <label>'.$label.'</label>
        			    <div class="mws-form-item large">
        					<input value="<?php echo (!empty($this->registro["'.$key["COLUMN_NAME"].'"]))?$this->data($this->registro["'.$key["COLUMN_NAME"].'"]):""; ?>" type="text" class="mws-textinput data" name="'.$key["COLUMN_NAME"].'">
        			    </div>
        			</div>
			';
		} else {
			$campos.= '
        			<div class="mws-form-col-2-8 alpha">
        			    <label>'.$label.'</label>
        			    <div class="mws-form-item large">
        					<input value="<?php echo stripslashes($this->registro["'.$key["COLUMN_NAME"].'"]); ?>" type="text" class="mws-textinput" name="'.$key["COLUMN_NAME"].'" maxlength="100">
        			    </div>
        			</div>
			';
		}
	}
}

echo '
<div class="mws-panel grid_8">
	<div class="mws-panel-header">
	    <span class="mws-i-24 i-pencil">Editor de Registro</span>
	</div>
	<div class="mws-panel-body">	    
    	<form action="<?php echo $this->url(array("controller"=>$this->controllerName, "action"=>"salvar-cadastro", "module"=>"admin"));?>" class="mws-form" id="formCadastro">		
    		'.$inputImagem.'		
    		<input id="codigo" type="hidden" name="codigo" value="<?php echo $this->registro["codigo"]; ?>" />
    		<div class="mws-form-inline">                                
			    <div class="mws-form-cols clearfix">				
    			    '.$campos.'        		
        	    </div>
        	</div>
        	<div class="mws-button-row">
    			<input type="submit" class="mws-button red" value="Enviar" />
    			<input id="voltar" type="reset" class="mws-button gray" value="Voltar" onclick="javascript:history.back(-1)" />
    		</div>	
    	</form>    	
	</div>
</div>	
';