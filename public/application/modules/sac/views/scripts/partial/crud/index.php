<?php
$controller = strtolower($this->controller); 
echo '
<div id="grid" class="mws-panel grid_8">
	<div class="mws-panel-header">
    	<span class="mws-i-24 i-table-1">Lista de Registros</span>
    </div>
    
    <div class="mws-panel-body">
    <div class="dataTables_wrapper">
    	<div class="dataTables_length">
			<label>
				Mostrar 
				<select id="select-por-pagina" size="1">
					<option value="5" selected="selected">5</option>
					<option value="10">10</option>
					<option value="25">25</option>					
				</select> registros
    		</label>
    	</div>
    	<div class="mws-panel-toolbar top clearfix">
			<ul>
				<li><a id="novo" class="mws-ic-16 ic-accept edit" href="<?php echo $this->url(array("controller"=>$this->controllerName, "action"=>"cadastro", "module"=>"admin"));?>" title="Novo">Novo</a></li>
				<li><a class="mws-ic-16 ic-arrow-refresh" href="javascript://" title="Atualizar registros">Atualizar</a></li>				
				<li><a class="mws-ic-16 ic-cross delete" href="<?php echo $this->url(array("controller"=>$this->controllerName, "action"=>"excluir", "module"=>"admin"));?>" title="Excluir">Excluir</a></li>				
		    </ul>
		</div>
    	<div class="dataTables_filter">
    		<form id="form_consulta" action="<?php echo $this->url(array("controller"=>$this->controllerName, "action"=>"index-dados", "module"=>"admin"));?>" method="post" enctype="multipart/form-data">
	    		<input name="pagina" type="hidden" id="pagina">
				<input name="por-pagina" type="hidden" id="porpagina" value="5">
				<input name="ordenacao" type="hidden" id="ordenacao">
	    		<label>
	    		    <span class="blockLeft">Buscar:</span> 
	    		    <input size="18" class="blockLeft" type="text" name="busca">
	    		    <input class="blockLeft" type="submit" class="mws-search-submit" value="Buscar dados"> 
	    		</label>
    		</form>
    	</div>	        	
        <table class="mws-datatable-fn mws-table">
            <thead>
                <tr>
                	<th title=""><input id="selectAll" type="checkbox"></th>                	
<?php 
	if(count($this->etiquetas)>0):
	foreach($this->etiquetas as $chave => $etiqueta):		
?> 	
					<th char="<?php echo $chave;?>" class="sorting"><?php echo $etiqueta;?></th>
<?php
	endforeach; 
	endif;
?>	                
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                                                
            </tbody>
        </table>
        <!-- Paginação --> 
        <div id="paginacao"></div>       	        
    </div>
    </div>
</div>
<!-- Panels End -->

<!-- Box cadastro -->
<div id="cadastro"></div>
';