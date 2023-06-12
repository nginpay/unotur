
<?php		
require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @autor Estudio Criar
 * @contato estudiocriar.com.br
 * @versão 1.0.1 - 30/10/2014
 */
class Admin_ConfiguracaoController extends AdminController {
	/**
	 * Model principal do crud
	 */
	private $model = "Model_Configuracao";
	private $ordenacao = "codigo DESC";	
	
	/**
	 * IndexAction -
	 */
	public function indexAction() {
		/* Array com os dados da tabela */		
		$this->view->etiquetas = array('nome'=>'Nome', 'cpf_cnpj'=>'Cpf/Cnpj');	
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
				
                $query->orWhere("lower(logomarca) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(nome) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(cpf_cnpj) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(endereco) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(cidade) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(uf) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(banco) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(agencia) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(agencia_dv) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(conta) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(conta_dv) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(carteira) like '%" . $termoBusca . "%'");	

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
		$codigo = $this->_getParam('codigo');
	
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
				
		 
		$post["nome"] = trim($post["nome"]);		
		if(empty($post["nome"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo nome"));
			return;
		}
			 
		$post["cpf_cnpj"] = trim($post["cpf_cnpj"]);		
		if(empty($post["cpf_cnpj"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo cpf/cnpj"));
			return;
		}
			 
		$post["endereco"] = trim($post["endereco"]);		
		if(empty($post["endereco"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo endereço"));
			return;
		}
			 
		$post["cidade"] = trim($post["cidade"]);		
		if(empty($post["cidade"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo cidade"));
			return;
		}
			 
		$post["uf"] = trim($post["uf"]);		
		if(empty($post["uf"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo uf"));
			return;
		}
					 
		$post["agencia"] = trim($post["agencia"]);		
		if(empty($post["agencia"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo agência"));
			return;
		}
			 
		$post["agencia_dv"] = trim($post["agencia_dv"]);		
		if(empty($post["agencia_dv"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo agência dv"));
			return;
		}
			 
		$post["conta"] = trim($post["conta"]);		
		if(empty($post["conta"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo conta"));
			return;
		}
			 
		$post["conta_dv"] = trim($post["conta_dv"]);		
		if(empty($post["conta_dv"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo conta dv"));
			return;
		}
			 
		$post["carteira"] = trim($post["carteira"]);		
		if(empty($post["carteira"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo carteira"));
			return;
		}
			
	
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
				    					
					$tbl->delete("codigo = ".$codigo);
				}		
			endforeach;
		endif;
	
		echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));	
	}
	
	function uploadAction(){
	
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	
	    $this->view->headMeta()->appendName( 'Expires', 'Mon, 26 Jul 1997 05:00:00 GMT' );
	    $this->view->headMeta()->appendName( 'Last-Modified', gmdate("D, d M Y H:i:s") . " GMT");
	    $this->view->headMeta()->appendName( 'Cache-Control', 'no-store, no-cache, must-revalidate' );
	    $this->view->headMeta()->appendName( 'Pragma', 'no-cache' );
	
	    // Parâmetros
	    //$targetDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "images". DIRECTORY_SEPARATOR ."convite";
	    $targetDir = getcwd()."/images/default/tmp";
	
	    $cleanupTargetDir = true; // Removendo velhos arquivos
	    $maxFileAge = 5 * 3600; // Tempo máximo de execução em segundos
	
	    // 5 minutes execution time
	    @set_time_limit(5 * 60);
	
	    // Recupera parâmetros
	    $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
	    $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
	    $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
	
	    // Limpa o filename para reações seguras
	    $fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
	
	    // Marca se o filename é unico somente se chunking estiver desabilitado
	    if($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
	        $ext = strrpos($fileName, '.');
	        $fileName_a = substr($fileName, 0, $ext);
	        $fileName_b = substr($fileName, $ext);
	
	        $count = 1;
	        while(file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
	            $count++;
	
	        $fileName = $fileName_a . '_' . $count . $fileName_b;
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
	            if(preg_match('/\.part$/', $file) &&(filemtime($tmpfilePath) < time() - $maxFileAge) &&($tmpfilePath != "{$filePath}.part")) {
	                @unlink($tmpfilePath);
	            }
	        }
	
	        closedir($dir);
	    } else
	        die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Falha para abrir o diretório temporário"}, "id" : "id"}');
	
	
	    // Look for the content type header
	    if(isset($_SERVER["HTTP_CONTENT_TYPE"]))
	        $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
	
	    if(isset($_SERVER["CONTENT_TYPE"]))
	        $contentType = $_SERVER["CONTENT_TYPE"];
	
	    // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
	    if(strpos($contentType, "multipart") !== false) {
	        if(isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
	            // Open temp file
	            $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
	            if($out) {
	                // Read binary input stream and append it to temp file
	                $in = fopen($_FILES['file']['tmp_name'], "rb");
	
	                if($in) {
	                    while($buff = fread($in, 4096))
	                        fwrite($out, $buff);
	                } else
	                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
	                fclose($in);
	                fclose($out);
	                @unlink($_FILES['file']['tmp_name']);
	
	            } else
	                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	        } else
	            die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
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
	                die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
	
	            fclose($in);
	            fclose($out);
	        } else
	            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
	    }
	
	    // Check if file has been uploaded
	    if(!$chunks || $chunk == $chunks - 1) {
	        // Strip the temp .part suffix off
	        rename("{$filePath}.part", $filePath);
	
	    }
	
	    // Return JSON-RPC response
	    die('{"jsonrpc" : "2.0", "link":'.$fileName.', "result" : '.$filePath.', "id" : "id"}');
	}
	
	public function excluirLogomarcaAction(){
	    $this->_helper->viewRenderer->setNoRender();
	
	    $codigo = $this->_getParam('codigo');
	    $logomarca = $this->_getParam('logomarca');
	
	    $retorno = new stdClass();
	
	    $erro = 0;
	    $mensagem = '';
	
	    if(!empty($logomarca)):
	
    	    $filename = getcwd().$logomarca;
    	    if(file_exists($filename)) {
    	        unlink($filename);
    	    }
    	
    	    if(!empty($logomarca) && is_numeric($codigo) && $erro == 0){
    	        //Buscando as midias que será deletada
    	        $tblConfiguracao = new Model_Configuracao();
    	        $tblConfiguracao->update(array("logomarca"=>""),"codigo = ".$codigo);
    	    }
	
	    endif;
	
	    $retorno->erro = $erro;
	    $retorno->mensagem = $mensagem;
	    header( 'Content-Type: application/json; charset=utf-8' );
	    echo json_encode($retorno);
	}
	
	
	
}
