<?php
require_once 'AdminController.php';

/**
 * Admin_ClienteDocumentoController - Controller responsável por
 *
 * @version 1.0.0 - 28/06/2012
 */
class Admin_ClienteDocumentoController extends AdminController {
	
	function uploadGaleriaAction(){
		
		$this->_helper->layout()->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);
	
		$this->view->headMeta()->appendName('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
		$this->view->headMeta()->appendName('Last-Modified', gmdate("D, d M Y H:i:s") . " GMT");
		$this->view->headMeta()->appendName('Cache-Control', 'no-store, no-cache, must-revalidate');
		$this->view->headMeta()->appendName('Pragma', 'no-cache');
	
		// Parâmetros		
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
			
			$aux = explode(".", $fileName);
			$ext = $aux[1];
			$transparencia =($ext=="png")?1:0;
			
			$galeriaFoto["midia"] = '/images/default/tmp/'.$fileName;
						
			$codigo = $this->_getParam("codigo");
				
			if(!empty($codigo)){
			    $galeriaFoto["cliente"] = $codigo;
			     
			    //Retirando as imagens antigas e postando as novas imagens
			    $tblGaleriaFoto = new Model_ClienteMidia();
			    	
			    //Gravando as novas imagens
			    $tblGaleriaFoto->insert($galeriaFoto);
			}						
		}
	
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "link":'.$fileName.', "result" : '.$filePath.', "id" : "id"}');
	}
	
	public function midiasAction(){
	    $this->_helper->layout()->disableLayout();
	    $cliente = $this->_getParam('codigo');
	    if(is_numeric($cliente)){
	        $this->view->cliente = $cliente;
	        $tblClienteDocumentoFoto = new Model_ClienteMidia();
	        $this->view->midias = $tblClienteDocumentoFoto->fetchAll("cliente = {$cliente}", "ordem asc");
	    } else {
	        $midias = $this->_getParam('midias');
	        if(!empty($midias)){
	            $midias = explode(";", $midias);
	            $array = array();
	            if(count($midias) > 0){
	                $key = 0;
	                foreach($midias as $midia):
	                    if(!empty($midia)):
    	                    $array[$key]["codigo"] = $key;
    	                    $array[$key]["legenda"] = null;
    	                    $array[$key]["ordem"] = null;
    	                    $array[$key]["midia"] = $midia;
    	                    $array[$key]["cliente"] = null;
    	                    $array[$key]["thumb"] = null;
    	                    $key++;
	                    endif;
	                endforeach;	               
	            }   
                $this->view->midias = $array;
	        }
	    }
	}
	
	public function excluirFotoGaleriaAction(){
	    $this->_helper->viewRenderer->setNoRender();
	
	    $codigo = $this->_getParam('codigo');
	
	    $retorno = new stdClass();
	
	    $erro = 0;
	    $mensagem = '';
	
	    if(!empty($codigo)):
	        $tblClienteDocumentoFoto = new Model_ClienteMidia();
	        $foto = $tblClienteDocumentoFoto->find($codigo)->current();
	        
	        if($foto){
	            $tblClienteDocumentoFoto->delete("codigo = $codigo");
        	    $filename = getcwd().$foto->midia;
        	    if(file_exists($filename)) {
        	        unlink($filename);
        	    }	            
	        }	
	    endif;
	
	    $retorno->erro = $erro;
	    $retorno->mensagem = $mensagem;
	    header( 'Content-Type: application/json; charset=utf-8' );
	    echo json_encode($retorno);
	}
	
    public function buscarFotoGaleriaAction(){
		$this->_helper->viewRenderer->setNoRender ();
		$this->_helper->layout ()->disableLayout();
		$post = $this->getRequest()->getPost();
		$retorno = new stdClass ();
		$erro = 1;
		$mensagem = 'Mídia não encontrada';		
		if(!empty($post["codigo"])){
			$tblFoto = new Model_ClienteMidia();
			$result = $tblFoto->find($post["codigo"])->current();			
			$erro = 0;
			$mensagem = 'Mídia encontrada com sucesso';
			$retorno->legenda = $result->legenda;					
			$retorno->ordem = $result->ordem;		
		}
		$retorno->erro = $erro;
		$retorno->mensagem = $mensagem;
		header ( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode ( $retorno );
	}
	
	public function salvarFotoGaleriaAction(){
	    $this->_helper->viewRenderer->setNoRender();
	    $post = $this->getRequest()->getPost();
	    $retorno = new stdClass();
	    $erro = 0;
	    $mensagem = "";
	    if(!empty($post["codigo"])){
	        $tblFoto = new Model_ClienteMidia();
	        $foto["ordem"] = $post["ordem"];
	        $foto["legenda"] = $post["legenda"];	       	        
	        $tblFoto->update($foto, "codigo = {$post['codigo']}");	        
	    }
	    
	    $retorno->erro = $erro;
	    $retorno->mensagem = $mensagem;
	    header ("Content-Type: application/json; charset=utf-8");
	    echo json_encode($retorno);
	}
		
	public function salvarCadastroAction() {
	
		Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$erro = 0;
		$msg = '';
		
		$post = $this->getRequest()->getPost();
		
		$tbl = new $this->model();
		$tbl->getAdapter()->beginTransaction();
                         
        $post["titulo"] = trim($post["titulo"]);
        if(empty($post["titulo"])){
            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Por favor preencha o campo Título"));
            return;
        }
                         		
		$session = new Zend_Session_Namespace("CORRETOR");		
		$imovel["corretor"] = $session->corretor;
		$imovel["tipo"] = $post["tipo"];
		$imovel["disponibilidade"] = $post["disponibilidade"];
		$imovel["cep"] = $post["cep"];
		$imovel["endereco"] = $post["endereco"];
		$imovel["bairro"] = $post["bairro"];
		$imovel["cidade"] = $post["cidade"];
		$imovel["latitude"] = $post["latitude"];
		$imovel["longitude"] = $post["longitude"];		
		$imovel["valor"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valor"])));
		$imovel["financiamento"] = $post["financiamento"];
		$imovel["condominiofechado"] = $post["condominiofechado"];
		$imovel["titulo"] = $post["titulo"];
		$imovel["quartos"] = $post["quartos"];
		$imovel["banheiros"] = $post["banheiros"];
		$imovel["suites"] = $post["suites"];
		$imovel["garagens"] = $post["garagens"];
		$imovel["cozinhas"] = $post["cozinhas"];
		$imovel["areasdeservico"] = $post["areasdeservico"];
		$imovel["elevadores"] = $post["elevadores"];
		$imovel["andares"] = $post["andares"];
		$imovel["apsporandar"] = $post["apsporandar"];
		$imovel["numeroandar"] = $post["numeroandar"];
		$imovel["areaconstruida"] = floatval(str_replace(',', '.', str_replace('.', '', $post["areaconstruida"])));
		$imovel["valoriptu"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valoriptu"])));
		
		if($imovel["valor"] <= 0){
		    echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Por favor preencha o campo Valor"));
		    return;
		} elseif($imovel["valor"] > 99000000) {
		    echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Para vossa senhoria, nós podemos fazer um aplicativo completo e exclusivo, por favor entre em contato conosco"));
		    return;
		}
		
		//Verificando se o corretor já tem mais de 6 imóveis cadastrados
		if(empty($post["codigo"])):
    		$result = $tbl->fetchAll("corretor = {$imovel['corretor']}");
    		if(count($result)>6){
    		    echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"A sua cota máxima de 6 anúncios foi atingida, agradecemos sua preferência"));
    		    return;
    		}
		endif;
		
		$update = false;
		if(empty($post['codigo'])) {
			unset($post['codigo']);											
			$codigo = $tbl->insert($imovel);
		} else {			
			$codigo = $post['codigo'];
			unset($post ['codigo']);
			$update = true;	
			$tbl->update($imovel,'codigo = '.$codigo);
		}
		
		$tbl->getAdapter()->commit();
					
		echo Zend_Json_Encoder::encode(array('erro' => $erro, 'msg'=>$msg, 'update'=>$update, 'codigo' => $codigo));
	}
	
	public function downloadAction() {
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	    	
	    $tblArquivo = new Model_ClienteMidia();
	    $arquivo = $tblArquivo->find($this->_getParam('codigo', 0))->current();
	    
	    $erro = 0;
	    $msg = 0;
	    if($arquivo == null) {
	        die('O arquivo solicitado para download não existe ou não está; disponível');
	    }
	    
	    $linklocal = getcwd().$arquivo->midia;
	    if(! file_exists($linklocal) || ! is_readable($linklocal)) {
	        die('O arquivo solicitado para download não pode ser acessado no momento');
	    }
	     
	    $arquivo = $linklocal;
	    $pathinfo = pathinfo($arquivo);		       
	    $filename = "{$pathinfo["filename"]}.{$pathinfo["extension"]}";
	    header("Content-Length: ".filesize($arquivo));
	    header("Content-Disposition: attachment; filename=".$filename);
	    readfile($arquivo);
	    exit;
	}
	
}
