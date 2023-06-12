<?php

require_once 'AdminController.php';

/**
 * Admin_CrudController - Controller responsavel por
 *
 * @author Vilmar
 * @version 1.0.0 - 28/06/2012
 */
class Admin_CrudController extends AdminController {
	
	/**
	 * IndexAction -
	 */
	public function indexAction() {
	   
	}
	
	public function salvarCadastroAction() {
	
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = '';
	
		$post = $this->getRequest()->getPost();
		
		if(!empty($post["nome"])){
			$model = $post["nome"];
			$tbl = new $model();
			$name = $tbl::name;
			
			//Estrutura da tabela
			$metadados = $tbl->select()->getAdapter()->describeTable($name);
			$aux = explode("_", $name);
			if(isset($aux[1])){
				$controller = ucwords($aux[0]).ucwords($aux[1]);				
			} else {
				$controller = ucwords($name);
			}			
						
			################################################################################################################################
			################################################################################################################################			
			//Gerando o controller		
			$arquivo = getcwd()."/application/modules/admin/controllers/{$controller}Controller.php";
			
			if(file_exists($arquivo)){
				unlink($arquivo);
			}
			$fp = fopen($arquivo, "a");
			
			//Conteúdo do controller
			$conteudo = $this->view->partial('partial/crud/controller.php', array('metadados'=>$metadados, 'model'=>$model, 'controller'=>$controller));
						
			//Escrevendo o conteúdo do controller
			fwrite($fp, $conteudo);
			
			//Fechando o controller 
			fclose($fp);
			
			################################################################################################################################
			################################################################################################################################
			
			//Gerando o index.phtml 
			$name = str_replace("_", "-", $name); 
			$caminho = getcwd()."/application/modules/admin/views/scripts/{$name}/";			
			
			if (!file_exists($caminho)) {
				mkdir($caminho, 0777);
			}
			
			$arquivo = $caminho."index.phtml";
			
			if(file_exists($arquivo)){
				unlink($arquivo);
			}
			
			$fp = fopen($arquivo, "a");
				
			//Conteúdo do index
			$conteudo = $this->view->partial('partial/crud/index.php', array('url'=>$this->view->url, 'controller'=>$name));
			
			//Escrevendo o conteúdo do index
			fwrite($fp, $conteudo);
				
			//Fechando o index.phtml
			fclose($fp);
			
			################################################################################################################################
			################################################################################################################################
				
			//Gerando o index-dados.phtml
			$name = str_replace("_", "-", $name);
			$caminho = getcwd()."/application/modules/admin/views/scripts/{$name}/";
				
			if (!file_exists($caminho)) {
			mkdir($caminho, 0777);
			}
				
			$arquivo = $caminho."index-dados.phtml";
				
			if(file_exists($arquivo)){
			    unlink($arquivo);
			}
			    	
		    $fp = fopen($arquivo, "a");
		
		    //Conteúdo do index-dados
		    $conteudo = $this->view->partial('partial/crud/index-dados.php', array('metadados'=>$metadados));
		    	
		    //Escrevendo o conteúdo do index-dados
		    fwrite($fp, $conteudo);
		
		    //Fechando o index-dados.phtml
		    fclose($fp);
			
			################################################################################################################################
			################################################################################################################################
			
			//Gerando o cadastro.phtml
			$caminho = getcwd()."/application/modules/admin/views/scripts/{$name}/";
				
			if (!file_exists($caminho)) {
				mkdir($caminho, 0777);
			}
				
			$arquivo = $caminho."cadastro.phtml";
		
			if(file_exists($arquivo)){
				unlink($arquivo);
			}
				
			$fp = fopen($arquivo, "a");
			
			//Conteúdo do cadastro
			$conteudo = $this->view->partial('partial/crud/cadastro.php', array('url'=>$this->view->url, 'controller'=>$name, 'metadados'=>$metadados));
				
			//Escrevendo o conteúdo do cadastro
			fwrite($fp, $conteudo);
			
			//Fechando o cadastro.phtml
			fclose($fp);
			
			################################################################################################################################
			################################################################################################################################
			
			//Gerando o cadastro.js
			$caminho = getcwd()."/js/admin/{$name}/";
			
			if (!file_exists($caminho)) {
				mkdir($caminho, 0777);
			}
			
			$arquivo = $caminho."cadastro.js";
			
			if(file_exists($arquivo)){
				unlink($arquivo);
			}
			
			$fp = fopen($arquivo, "a");
				
			//Conteúdo do cadastro
			$conteudo = $this->view->partial('partial/crud/js.php', array('url'=>$this->view->url, 'controller'=>$name, 'metadados'=>$metadados));
			
			//Escrevendo o conteúdo do cadastro
			fwrite($fp, $conteudo);
				
			//Fechando o cadastro.js
			fclose($fp);
			
			################################################################################################################################
			################################################################################################################################
			
			//Inserindo os resources									
			$tblResource = new Model_Resource();
			$tblResource->delete("controller = '$name'");
			
			$resource["resource"] = "admin:{$name}";
			$resource["nome"] = ucfirst("{$name}s");
			$resource["controller"] = "{$name}";
			$resource["icone"] = "i-create";
			$tblResource->insert($resource);
			
			$resource["resource"] = "admin:{$name}:cadastro";
			$resource["nome"] = "Cadastro de {$name}";
			$resource["controller"] = "{$name}";
			$resource["icone"] = null;
			$resource["resourcepai"] = "admin:{$name}";
			$tblResource->insert($resource);
			
			$resource["resource"] = "admin:{$name}:excluir";
			$resource["nome"] = "Excluir {$name}";
			$resource["controller"] = "{$name}";
			$resource["icone"] = null;
			$resource["resourcepai"] = "admin:{$name}";
			$tblResource->insert($resource);
			
		}
		
		echo Zend_Json_Encoder::encode(array('erro' => $erro, 'msg'=>$msg));
		
	}
		

}