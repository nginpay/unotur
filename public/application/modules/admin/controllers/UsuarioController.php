<?php
require_once 'AdminController.php';

/**
 * Admin_UsuarioController - Controller responsável por
 *
 * @version 1.0.0 - 28/06/2012
 */
class Admin_UsuarioController extends AdminController {
	
	/**
	 * Model principal do crud
	 */
	private $model = 'Model_Usuario';
	
	/**
	 * Query pre-requisito de busca
	 */	
	private $query = '';
			
	/**
	 * Ordenação inicial
	 */	
	private $ordenacao = 'usuario asc';
		
	/**
	 * IndexAction -
	 */
	public function indexAction() {
		/* Array com os dados da tabela */
		$etiquetas = array('nome'=>'Nome');
		$this->view->etiquetas = $etiquetas;								
	}
		
	public function indexDadosAction() {
		
		$this->_helper->layout()->disableLayout();
			
		$tbl = new $this->model();
		
		$query = $tbl->select()->setIntegrityCheck(false);
		
		if(!empty($this->query))
			$query->where($this->query);
	
		if($this->getRequest()->isPost()) {

			//Dados da busca
			$post = $this->getRequest()->getPost();

			//Termo da ordenação
			$ordem = $this->_getParam('ordenacao');					
			$ordenacao = !empty($ordem)?$ordem:$this->ordenacao;
			
			$termoBusca = $this->_getParam('busca');	
			if(!empty($termoBusca)) {
				$query->where("lower(nome) like '%" . $termoBusca . "%'");																								
			}
						
		}
		
		$query->order($ordenacao);
		
		$dados = $tbl->fetchAll($query);
	
		$paginator = Zend_Paginator::factory($dados->toArray());
		$paginator->setCurrentPageNumber($this->_getParam('pagina', 1));
	
		$porPagina = $this->_getParam('por-pagina');
				
		//numero de itens por pagina
		$paginator->setItemCountPerPage($porPagina);
	
		//numero de indices de paginas que serão exibidos
		$paginator->setPageRange(6);
	
		$this->view->paginacao = $paginator;
	
	}
				
	public function cadastroAction() {	
		$usuario = $this->_getParam('usuario');
	
		$tbl = new $this->model();
		if(!empty($usuario)) {			
			$this->view->registro = $tbl->find($usuario)->current();
		}
				
		//Instancia a classe canvas
		$this->view->canvas = $this->_helper->canvas();

		//Lista de permissões
		$tblResource = new Model_Resource();
		$this->view->resources = $tblResource->fetchAll("resourcepai IS NULL AND resource NOT IN ('default','admin','admin:index')");
		
		//Plugin plupload
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/plupload/plupload.full.js'));
		
		//Plugin dualbox
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/duallistbox/jQuery.dualListBox-1.3.js'));
		
		//Plugin galeria
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/core/gallery.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/js/admin/plugins/prettyphoto/css/prettyPhoto.css'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/prettyphoto/js/jquery.prettyPhoto-min.js'));
		
		$iframe = $this->_getParam("iframe",false);
		if($iframe){
		    $this->_helper->layout()->setLayout('admin-form');
		}
		$this->view->iframe = $iframe;
	}
				
	public function salvarCadastroAction() {
	    Zend_Layout::getMvcInstance ()->disableLayout ();
	    $this->_helper->viewRenderer->setNoRender ();
	    
	    $erro = 0;
	    $msg = '';
	    
	    $post = $this->getRequest()->getPost();
	    
	    $tbl = new $this->model();
	    
	    $permissoes = (isset($post["permissoes"]))?$post["permissoes"]:null;
	    	    
	    unset($post["permissoes"]);	    
	    unset($post["confirmar-senha"]);	    
	    unset($post["codigo"]);	    
	    if (!empty($post['senha'])) {
	        $post['senha'] = new Zend_Db_Expr('password("'.$post['senha'].'")');
	    } else {
	        unset($post['senha']);
	    }
	    
	    if(!empty($post["email"])){
	        $post["email"] = strtolower($post["email"]);    
	    }
	    
	    if(isset($post["usuario"])){
	        $post["usuario"] = mb_strtolower(str_replace(" ", "", $post["usuario"]),"UTF-8");
	        	
	        //Verificando se já existe um usuário com este nome...
	        $query = "usuario in (SELECT usuario FROM usuario WHERE usuario = '{$post["usuario"]}')";
	        $result = $tbl->fetchRow($query);
	        	
	        if($result){
	            $erro = 1;
	            $msg = "Este usuário já está cadastrado na base de dados";
	        }
	    }
	    	    
	    $adicionou = false;
	    $codigo = null;
	    
	    if($erro == 0){
	        if (!isset($post['usuarioedit'])) {
	            unset($post['usuarioedit']);	    	    
	            $usuario = $tbl->insert($post);	            
	        } else {
	            $usuario = $post['usuarioedit'];
	            unset($post['usuarioedit']);	                        	
	            $tbl->update($post,"usuario = '{$usuario}'");
	        }
	    }
	    	    
	    //Inserindo as permissões	    
        $tblUsuarioPermissao = new Model_UsuarioPermissao();
        $tblUsuarioPermissao->delete("usuario = '$usuario'");
        	    
        $permissao["usuario"] = $usuario;
        $permissao["resource"] = "admin:index";
        $tblUsuarioPermissao->insert($permissao);        
        if(count($permissoes)>0):            	       
            foreach($permissoes as $item):
                $permissao["resource"] = $item;
                $tblUsuarioPermissao->insert($permissao);
            endforeach;
        endif;
       	    
	    //Option
	    $option = "<option value='{$usuario}'>{$post["nome"]}</option>";
	    
	    echo Zend_Json_Encoder::encode (array('erro' => $erro, 'msg'=>$msg, 'option'=>$option));
	}
				
	public function excluirAction() {
	
		Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = "";
	
		$post = $this->getRequest()->getPost();
		
		$usuarioLogado = $this->view->usuario["usuario"];
	
		$itens = explode(",", $post["itens"]);
	
		$tbl = new $this->model();
		if(count($itens)>0):	
			foreach($itens as $usuario):		
				if(!empty($usuario) && $usuario != $usuarioLogado) {					        			
        			$tbl->delete("usuario = '$usuario' AND usuario <> '$usuarioLogado'");
				} else {
				    die(Zend_Json_Encoder::encode(array("msg"=> "Você não pode excluir a você mesmo", "erro"=> "1")));
				}		
			endforeach;
		endif;
	
		echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));	
	}
	
	
	public function excluirFotoAction(){
	    $this->_helper->viewRenderer->setNoRender();
	
	    $usuario = $this->_getParam('usuario');
	    $foto = $this->_getParam('foto');
	
	    $retorno = new stdClass();
	
	    $erro = 0;
	    $mensagem = '';
	
	    if(!empty($foto)):
	    	
	    $filename = getcwd().$foto;
	    if(file_exists($filename)) {
	        unlink($filename);
	    }
	
	    if(!empty($usuario) && is_numeric($usuario) && $erro == 0){
	        //Buscando as midias que será deletada
	        $tblUsuario = new Model_Usuario();
	        $tblUsuario->update(array("foto"=>""),"usuario = ".$usuario);
	    }
	
	    endif;
	
	    $retorno->erro = $erro;
	    $retorno->mensagem = $mensagem;
	    header( 'Content-Type: application/json; charset=utf-8' );
	    echo json_encode($retorno);
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
			
			
	
}
