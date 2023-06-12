
<?php		
require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @autor Estudio Criar
 * @contato estudiocriar.com.br
 * @versão 1.0.1 - 11/07/2014
 */
class Admin_ClienteController extends AdminController {
	/**
	 * Model principal do crud
	 */
	private $model = "Model_Cliente";
	private $ordenacao = "codigo DESC";	
	
	/**
	 * IndexAction -
	 */
	public function indexAction() {
		/* Array com os dados da tabela */		
		$this->view->etiquetas = array('codigo'=>'Código', 'nome'=>'Nome', 'email'=>'Email', 'telefonefixo'=>'Telefone Fixo', 'celular'=>'Celular', 'status'=>'Status');	
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
                $query->orWhere("lower(nome) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(email) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(cpf) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(cnpj) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(rg) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datanascimento) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(sexo) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(cep) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(endereco) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(bairro) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(cidade) like '%" . $termoBusca . "%'");                                               
                $query->orWhere("lower(datacadastro) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(status) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(observacoes) like '%" . $termoBusca . "%'");
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
		
		//Lista de categorias
		$tblPais = new Model_Pais();
		$this->view->paises = $tblPais->fetchAll(null,"nome ASC");

		//Carregando os estados para o formulário
		$tblEstado = new Model_Estado();
		$this->view->estados = $tblEstado->fetchAll(null,"sigla ASC");
		
		$iframe = $this->_getParam("iframe",false);
		if($iframe){
		    $this->_helper->layout()->setLayout('admin-form');
		}
		$this->view->iframe = $iframe;
		
		//Plugin galeria
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/core/gallery.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/js/admin/plugins/prettyphoto/css/prettyPhoto.css'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/prettyphoto/js/jquery.prettyPhoto-min.js'));
		
		//Plugin pluploader
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/plupload/plupload.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/plupload/plupload.flash.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/plupload/plupload.html4.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/plupload/plupload.html5.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/plupload/jquery.plupload.queue/jquery.plupload.queue.js'));
		$this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/plupload/i18n/pt-br.js'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/js/admin/plugins/plupload/jquery.plupload.queue.css'));
		
	}
				
	public function salvarCadastroAction() {
	
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = "";
	
		$post = $this->getRequest()->getPost();
	
		$tbl = new $this->model();
		$tbl->getAdapter()->beginTransaction();

		if (!empty($post['senha'])) {
		    $post['senha'] = new Zend_Db_Expr('password("'.$post['senha'].'")');
		    $cliente["senha"] = $post["senha"];
		} else {
		    unset($post['senha']);
		}
		
		if($post["tipopessoa"] == "F"){
		    $post["cpf"] = trim($post["cpf"]);
		    if(empty($post["cpf"])){
		        echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo cpf"));
		        return;
		    } else {
		        //Validando o cpf do cliente
		        if(!$this->validaCPF($post["cpf"]) && $post["cpf"] != "999.999.999-99"){
		            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"O CPF informado para este cliente é inválido"));
		            return;
		        }
		        
		        //Verificando se o cpf do cliente já está cadastrado
		        $querie = "cpf = '{$post["cpf"]}'";
		        if(!empty($post["codigo"])){
		            $querie = "cpf = '{$post["cpf"]}' AND codigo <> {$post['codigo']}";
		        }
		        $querie.= " AND cpf <> '999.999.999-99'";
		        $result = $tbl->fetchRow($querie);
		        if($result){
		            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"O Cpf informado já se encontra cadastrado em nossa base de dados para o cliente {$result->nome}"));
		            return;
		        }
		        $post["cnpj"] = null;
		        $post["razaosocial"] = null;
		    }    
		} else {
		    $post["razaosocial"] = trim($post["razaosocial"]);
		    if(empty($post["razaosocial"])){
		        echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo razão social"));
		        return;
		    }
		    		    
		    $post["cnpj"] = trim($post["cnpj"]);
		    if(empty($post["cnpj"])){
		        echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo cnpj"));
		        return;
		    } else {
		        
		        //Validando o cpf do cliente
		        if(!$this->validaCNPJ($post["cnpj"]) && $post["cnpj"] != "99.999.999/9999-99"){
		            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"O CNPJ informado para este cliente é inválido"));
		            return;
		        }
		        
		        //Verificando se o cnpj do cliente já está cadastrado
		        $querie = "cnpj = '{$post["cnpj"]}'";
		        if(!empty($post["codigo"])){
		            $querie = "cnpj = '{$post["cnpj"]}' AND codigo <> {$post['codigo']}";
		        }
		        $querie.= " AND cnpj <> '99.999.999/9999-99'";
		        $result = $tbl->fetchRow($querie);
		        if($result){
		            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"O Cnpj informado já se encontra cadastrado em nossa base de dados para o cliente {$result->nome}"));
		            return;
		        }
		        $post["cpf"] = null;
		    }    
		}
		
		if(isset($post["usuario"])){
		    $post["usuario"] = mb_strtolower(str_replace(" ", "", $post["usuario"]),"UTF-8");
		
		    //Verificando se já existe o usuário	    
		    $query = "usuario = '{$post["usuario"]}'";
		    		    
		    $result = $tbl->fetchRow($query);
		
		    if($result){
		        echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Este usuário já se encontra cadastrado em nossa base de dados, por favor tente outro"));
			    return;
		    } else {
		        $tblUsuario = new Model_Usuario();
		        
		        //Verificando se já existe o usuário
		        $query = "usuario = '{$post["usuario"]}'";		        
		        $result = $tblUsuario->fetchRow($query);
		        
		        if($result){
		            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Este usuário já se encontra cadastrado em nossa base de dados, por favor tente outro"));
		            return;
		        }
		    }
		}
		
		$post["nome"] = trim($post["nome"]);		
		if(empty($post["nome"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo nome"));
			return;
		}
		
		if(empty($post["telefonefixo"]) && empty($post["celular"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Por favor preencha ao menos um telefone para contato"));
			return;
		}

	    $post["email"] = strtolower(trim($post["email"]));
        if(empty($post["email"])){
            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Por favor preencha o campo email"));
            return;
        } else {
            if(filter_var($post["email"], FILTER_VALIDATE_EMAIL) === FALSE) {
                echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Por favor preencha um email válido"));
                return;
            } else {
                //Verificando se o email do cliente já está cadastrado
                $querie = "email = '{$post["email"]}'";
                if(!empty($post["codigo"])){
                    $querie .= " AND codigo <> {$post['codigo']}";
                }
                $querie .= " AND email NOT IN (SELECT email FROM usuario)";
                $result = $tbl->fetchRow($querie);
                if($result){
                    echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"O Email informado já se encontra cadastrado em nossa base de dados para o cliente/usuário {$result->nome}"));
                    return;
                }    
            }
        }
			 
		$post["datanascimento"] = trim($post["datanascimento"]);		
		if(empty($post["datanascimento"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo datanascimento"));
			return;
		} else {
		    $date = new Zend_Date($post["datanascimento"]);
		    $post["datanascimento"] = $date->get("WWW");
		}
							 
		$post["endereco"] = trim($post["endereco"]);		
		if(empty($post["endereco"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo endereco"));
			return;
		}
			 
		$post["bairro"] = trim($post["bairro"]);		
		if(empty($post["bairro"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo bairro"));
			return;
		}
			 
		$post["cidade"] = trim($post["cidade"]);		
		if(empty($post["cidade"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo cidade"));
			return;
		}
							 
		$post["status"] = trim($post["status"]);		
		if(empty($post["status"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo status"));
			return;
		}
						
		$codigo = null;
		$adicionado = false;
		unset($post["uploader_count"]);
        unset($post["confirmar-senha"]);
        unset($post["usuarioedit"]);
        
        //Removendo vars de upload obsoletas
        foreach($post as $key=>$var):        
            if(substr($key, 0, 8) == "uploader") {
                unset($post["$key"]);           
            }
        endforeach;
                
		if($erro == 0){
			if(empty($post["codigo"])) {
				unset($post["codigo"]);
				$date = new Zend_Date();
				$post["datacadastro"] = $date->get("WWW");
				$codigo = $tbl->insert($post);
				$adicionado = true;				
			} else {
				$codigo = $post["codigo"];
				unset($post["codigo"]);														
				$tbl->update($post,"codigo = ".$codigo);
			}
		}	
		$tbl->getAdapter()->commit();
		
		//Option
		$option = "<option value='{$codigo}'>{$post["nome"]}</option>";
	
		echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg"=>$msg, "codigo" => $codigo, "nome"=>$post["nome"], "option"=>$option, "adicionado"=>$adicionado));
	}

	public function obterClientesAction() {	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	    
	    $q = $_REQUEST['data']['q'];
	    $liderPacote = $this->_getParam("liderpacote");
	    $tblCliente = new Model_Cliente();
	    $results = array();
	    if(!empty($q)){
	        $querie = "lower(nome) like '%".$q."%'";
	        if(!empty($liderPacote)){
	            $querie.= " AND liderpacote = 1";    
	        }
	        $clientes = $tblCliente->fetchAll($querie);
	        foreach($clientes as $cliente){	
	            $cpfCnpj = ($cliente["tipopessoa"] == "F")?" CPF - ".$cliente["cpf"]:" CNPJ - ".$cliente["cnpj"];
	            $results[] = array('id' => $cliente->codigo, 'text' => $cliente->nome.$cpfCnpj);	           
	        }    
	    }
	    
	    echo json_encode(array('q' => $q, 'results' => $results));
	}
	
	public function excluirAction() {
	
		Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = "";
	
		$post = $this->getRequest()->getPost();
	
		$itens = explode(",", $post["itens"]);
	
		$tbl = new $this->model();
		$tblClienteMidia = new Model_ClienteMidia();
		$tblVenda = new Model_Venda();
		if(count($itens)>0):	
			foreach($itens as $codigo):		
				if(!empty($codigo) && is_numeric($codigo)) {
        		    //Verificando se este cliente faz parte de uma venda
        		    $result = $tblVenda->fetchRow("cliente = $codigo");
        		    if($result){
        		        echo Zend_Json_Encoder::encode(array("msg"=> "Este cliente se encontra localizado na venda $result->codigo e não pode ser excluído", "erro"=> 1));
        		        return;
        		    }
		    
        		    $midias = $tblClienteMidia->fetchAll("cliente = $codigo");
        		    
        		    foreach($midias as $midia):
            		    // Excluindo imagem
            		    $filename = getcwd().$midia['foto'];
            		    if(file_exists($filename) && !empty($midia['foto'])) {
            		        unlink($filename);
            		    }
            		    $filename = getcwd().$midia['thumb'];
            		    if(file_exists($filename) && !empty($midia['thumb'])) {
            		        unlink($filename);
            		    }
        		    endforeach;
					$tbl->delete("codigo = ".$codigo);
				}		
			endforeach;
		endif;
	
		echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));	
	}
	
}
