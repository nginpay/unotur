<?php
$keys = 'array(';
$colunas = 'array(';

$validacoes = '';
$excluirImagem = '';
$upload = '';
$querie = '';
$count = 0;
foreach($this->metadados as $metadata=>$key){
	
	if(!$key["PRIMARY_POSITION"]){
		
		//Validando os campos nulos
		if(!$key["NULLABLE"]){
			$validacoes.= ' 
		$post["'.$key["COLUMN_NAME"].'"] = trim($post["'.$key["COLUMN_NAME"].'"]);		
		if(empty($post["'.$key["COLUMN_NAME"].'"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo '.$key["COLUMN_NAME"].'"));
			return;
		}
			';
		}

		//Tratando os campos decimais
		if($key["DATA_TYPE"] == "decimal" || $key["DATA_TYPE"] == "numeric"){
			$validacoes.= '
		if(!empty($post["'.$key["COLUMN_NAME"].'"])){
			$post["'.$key["COLUMN_NAME"].'"] = $this->moedaParaNumero($post["'.$key["COLUMN_NAME"].'"]);
		} else {
			unset($post["'.$key["COLUMN_NAME"].'"]);
		}
			';
		}
				
		//Tratando os campos datetime e timestamp
		if($key["DATA_TYPE"] == "date" || $key["DATA_TYPE"] == "datetime"){
			$validacoes.= '
		if(!empty($post["'.$key["COLUMN_NAME"].'"])){
			$date = new Zend_Date($post["'.$key["COLUMN_NAME"].'"]);
			$post["'.$key["COLUMN_NAME"].'"] = $date->get("WWW");
		} else {
			$post["'.$key["COLUMN_NAME"].'"] = null;	
		}
			';
		}
		
		//Tratando os campos de busca
		//$query->where("lower(nome) like \'%" . $termoBusca . "%\'");
		$querie .= PHP_EOL.'                $query->orWhere("lower('.$key["COLUMN_NAME"].') like \'%" . $termoBusca . "%\'");';
		
		if($key["COLUMN_NAME"] == "foto" || $key["COLUMN_NAME"] == "imagem"){
						
    $upload = '
	function uploadAction(){		
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	
		$this->view->headMeta()->appendName("Expires", "Mon, 26 Jul 1997 05:00:00 GMT");
		$this->view->headMeta()->appendName("Last-Modified", gmdate("D, d M Y H:i:s") . " GMT");
		$this->view->headMeta()->appendName("Cache-Control", "no-store, no-cache, must-revalidate");
		$this->view->headMeta()->appendName("Pragma", "no-cache");
	
		// Parâmetros		
		$targetDir = getcwd()."/images/default/tmp";
	
		$cleanupTargetDir = true; // Removendo velhos arquivos
		$maxFileAge = 5 * 3600; // Tempo máximo de execução em segundos
	
		// 5 minutes execution time
		@set_time_limit(5 * 60);
		
		// Recupera parâmetros
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		$fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : "";
	
		// Limpa o filename para reações seguras
		$fileName = preg_replace(\'/[^\w\._]+/\', "_", $fileName);
	
		// Marca se o filename é unico somente se chunking estiver desabilitado
		if($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
			$ext = strrpos($fileName, ".");
			$fileName_a = substr($fileName, 0, $ext);
			$fileName_b = substr($fileName, $ext);
	
			$count = 1;
			while(file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . "_" . $count . $fileName_b))
				$count++;
	
			$fileName = $fileName_a . "_" . $count . $fileName_b;
		}
	
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
	
		// Cria um target dir
		if(!file_exists($targetDir))
			@mkdir($targetDir);
	
		// Remove arquivos temporários
		if($cleanupTargetDir && is_dir($targetDir) &&($dir = opendir($targetDir))) {
			while(($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
	
				// Remove temp file if it is older than the max age and is not the current file
				if(preg_match(\'/\.part$/\', $file) &&(filemtime($tmpfilePath) < time() - $maxFileAge) &&($tmpfilePath != "{$filePath}.part")) {
					@unlink($tmpfilePath);
				}
			}
	
			closedir($dir);
		} else
			die(\'{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Falha para abrir o diretório temporário"}, "id" : "id"}\');
	
	
		// Look for the content type header
		if(isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	
		if(isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];
	
		// Handle non multipart uploads older WebKit versions didn"t support multipart in HTML5
		if(strpos($contentType, "multipart") !== false) {
			if(isset($_FILES["file"]["tmp_name"]) && is_uploaded_file($_FILES["file"]["tmp_name"])) {
				// Open temp file
				$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES["file"]["tmp_name"], "rb");
	
					if($in) {
						while($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die(\'{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}\');
					fclose($in);
					fclose($out);
					@unlink($_FILES["file"]["tmp_name"]);
						
				} else
					die(\'{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}\');
			} else
				die(\'{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}\');
		} else {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");
	
				if($in) {
					while($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die(\'{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}\');
	
				fclose($in);
				fclose($out);
			} else
				die(\'{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}\');
		}
	
		// Check if file has been uploaded
		if(!$chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename("{$filePath}.part", $filePath);
			
		}
	
		// Return JSON-RPC response
		die(\'{"jsonrpc" : "2.0", "link":\'.$fileName.\', "result" : \'.$filePath.\', "id" : "id"}\');
	}
    
	public function excluirFotoAction(){
		$this->_helper->viewRenderer->setNoRender();
	
		$codigo = $this->_getParam("codigo");		
		$foto = $this->_getParam("foto");		
	
		$retorno = new stdClass();
	
		$erro = 0;
		$mensagem = "";
		
		if(!empty($foto)):        					
			$this->unlink($codigo, $foto);        		
		endif;
	
		$retorno->erro = $erro;
		$retorno->mensagem = $mensagem;
		header("Content-Type: application/json; charset=utf-8");
		echo json_encode($retorno);
	}
	
	private function unlink($codigo=null, $foto){
	    if(!empty($foto)):
	    	
    	    $filename = getcwd().$foto;
    	    if(file_exists($filename)) {
    	        unlink($filename);
    	    }
    	    
    	    if(!empty($codigo) && is_numeric($codigo)){
    	        //Buscando as midias que será deletada
    	        $tbl = new $this->model();
    	        $tbl->update(array("'.$key["COLUMN_NAME"].'"=>""),"codigo = ".$codigo);
    	    }
	    
	    endif;
	}
	
	';
		
    	$excluirImagem = '
            	    $dados = $tbl->find($codigo)->current();
            
            	    // Excluindo imagem grande do diretório
            		if(!empty($dados["'.$key["COLUMN_NAME"].'"])){
            		    $this->unlink($codigo,$dados["'.$key["COLUMN_NAME"].'"]);    
            		}
    	';
    	}
		
		$coluna = $metadata;
		$metadata = str_replace("_", " ", $metadata);
		$metadata = ucwords($metadata);
		$keys .= "'{$coluna}'=>'{$metadata}', ";	
		$colunas .= "'{$coluna}', ";
	}
	$count++;
}
$colunas = substr($colunas, 0, strlen($colunas)-2);
$keys = substr($keys, 0, strlen($keys)-2);
$keys .= ')';
$colunas .= ')';

echo '
<?php		
require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @autor Estudio Criar
 * @contato estudiocriar.com.br
 * @versão 1.0.1 - '.date('d/m/Y').'
 */
class Admin_'.$this->controller.'Controller extends AdminController {
	/**
	 * Model principal do crud
	 */
	private $model = "'.$this->model.'";
	private $ordenacao = "codigo DESC";	
	
	/**
	 * IndexAction -
	 */
	public function indexAction() {
		/* Array com os dados da tabela */		
		$this->view->etiquetas = '.$keys.';	
	}
	
	public function indexDadosAction() {
		
		$this->_helper->layout()->disableLayout();
			
		$tbl = new $this->model();
		
		$query = $tbl->select()->setIntegrityCheck(false);
		
		if(!empty($this->query))
			$query->where($this->query);
	    
		$limit = true;
		if($this->getRequest()->isPost()) {

			//Dados da busca
			$post = $this->getRequest()->getPost();

			//Termo da ordenação
			$ordem = $this->_getParam("ordenacao");					
			$ordenacao = !empty($ordem)?$ordem:$this->ordenacao;
			
			$termoBusca = $this->_getParam("busca");	
			if(!empty($termoBusca)) {
				'.$querie.'	

				$limit = false;
			}
						
		}
		
		$query->order($ordenacao);
		if($limit):
		    $query->limit(10000);
		endif;
		$dados = $tbl->fetchAll($query);
	
		$paginator = Zend_Paginator::factory($dados);
		$paginator->setCurrentPageNumber($this->_getParam("pagina", 1));
	
		$porPagina = $this->_getParam("por-pagina");
				
		//numero de itens por pagina
		$paginator->setItemCountPerPage($porPagina);
	
		//numero de indices de paginas que serão exibidos
		$paginator->setPageRange(6);
	
		$this->view->paginacao = $paginator;	
	}
				
	public function cadastroAction() {	
		$codigo = $this->_getParam(\'codigo\');
	
		$tbl = new $this->model();
		if(is_numeric($codigo)) {			
			$this->view->registro = $tbl->find($codigo)->current();
		}

		//Plugin plupload
		$this->view->headScript()->appendFile($this->view->baseUrl("/js/admin/plugins/plupload/plupload.full.js"));
		
		//Plugin tinymce
		$this->view->headScript()->appendFile($this->view->baseUrl("/js/admin/plugins/tiny_mce/jquery.tinymce.js"));
		
		//Plugin galeria
		$this->view->headLink()->appendStylesheet($this->view->baseUrl("/css/admin/core/gallery.css"));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl("/js/admin/plugins/prettyphoto/css/prettyPhoto.css"));
		$this->view->headScript()->appendFile($this->view->baseUrl("/js/admin/plugins/prettyphoto/js/jquery.prettyPhoto-min.js"));
	}
				
	public function salvarCadastroAction() {
	
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = "";
	
		$post = $this->getRequest()->getPost();
	
		$tbl = new $this->model();
		$tbl->getAdapter()->beginTransaction();
				
		'.$validacoes.'
	
		$codigo = null;
	
		if($erro == 0){
			if(empty($post["codigo"])) {
				unset($post["codigo"]);
				$codigo = $tbl->insert($post);				
			} else {
				$codigo = $post["codigo"];
				unset($post["codigo"]);					
				$tbl->update($post,"codigo = ".$codigo);
			}
		}	
		$tbl->getAdapter()->commit();
	
		echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg"=>$msg, "codigo" => $codigo));
	}
				
	public function excluirAction() {
	
		Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = "";
	
		$post = $this->getRequest()->getPost();
	
		$itens = explode(",", $post["itens"]);
	
		$tbl = new $this->model();
		if(count($itens)>0):	
			foreach($itens as $codigo):		
				if(!empty($codigo) && is_numeric($codigo)) {
				    '.$excluirImagem.'					
					$tbl->delete("codigo = ".$codigo);
				}		
			endforeach;
		endif;
	
		echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));	
	}
	
	'.$upload.'
	
}
';