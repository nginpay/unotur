<?php
$controller = strtolower($this->controller);
$count = 0;
$campos = '';
foreach($this->metadados as $metadata=>$key){
    if(!$key["PRIMARY_POSITION"]):
        
        if($key["DATA_TYPE"] == "date" || $key["DATA_TYPE"] == "datetime"){
            $campos.= PHP_EOL.'    <td class="center"><?php echo $this->data($dados["'.$key["COLUMN_NAME"].'"]);?></td>';
        }
        elseif($key["COLUMN_NAME"] == "foto" || $key["COLUMN_NAME"] == "imagem"){
            $campos.= PHP_EOL.'    <td width="64"><img src="<?php echo (!empty($dados["'.$key["COLUMN_NAME"].'"]))?$this->baseUrl($dados["'.$key["COLUMN_NAME"].'"]):"http://dummyimage.com/250x200/d6d6d6/686a82.gif&text=Sem+'.$key["COLUMN_NAME"].'";?>" height="55" /></td>';
        }        
        else {
            $campos.= PHP_EOL.'    <td class="center"><?php echo stripslashes($dados["'.$key["COLUMN_NAME"].'"]);?></td>';
        }
    endif;
    $count++;    
}

echo '
<!-- Paginação do crud -->
<tr id="paginacao-temp" style="display: none;">
	<td>
		<?php echo $this->paginationControl($this->paginacao, "Sliding", "/partial/paginacao.phtml"); ?>
	</td>
</tr>

<?php
if(count($this->paginacao)>0):

$aux = 0;
foreach($this->paginacao as $dados):
$class = ($aux%2 == 0)?"even":"odd";
?>

<tr class="gradeX <?php echo $class;?>">
    <td width="20"><input class="checkCodigo" name="codigo[]" value="<?php echo $dados["codigo"];?>" type="checkbox"></td>        
    '.$campos.'    
    <td width="180">
		<a class="mws-ic-16 ic-edit edit" href="<?php echo $this->url(array("controller"=>$this->controllerName, "action"=>"cadastro", "module"=>"admin", "codigo"=>$dados["codigo"]),null,true);?>" title="Editar">&nbsp;</a>
		<a class="mws-ic-16 ic-cross delete deleteRow" href="<?php echo $this->url(array("controller"=>$this->controllerName, "action"=>"excluir", "module"=>"admin"));?>" title="Excluir">&nbsp;</a>		
    </td>
</tr>	

<?php
$aux++;
endforeach;

else :
	echo \'<tr class="gradeX even"> <td style="text-align:center;" class="center" colspan="'.($count+2).'">Nenhum registro encontrado</td>\';
endif;
?>
';