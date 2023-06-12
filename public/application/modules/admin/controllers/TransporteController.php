
<?php		
require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @autor Estudio Criar
 * @contato estudiocriar.com.br
 * @versão 1.0.1 - 08/08/2014
 */
class Admin_TransporteController extends AdminController {
	/**
	 * Model principal do crud
	 */
	private $model = "Model_Transporte";
	private $ordenacao = "codigo DESC";	
	
	/**
	 * IndexAction -
	 */
	public function indexAction() {
		/* Array com os dados da tabela */		
		$this->view->etiquetas = array('nome'=>'Nome', 'categoriatransporte'=>'Categoria');	
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
			$ordem = $this->_getParam("ordenacao");					
			$ordenacao = !empty($ordem)?$ordem:$this->ordenacao;
			
			$termoBusca = $this->_getParam("busca");	
			if(!empty($termoBusca)) {
				
                $query->orWhere("lower(nome) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(categoriatransporte) like '%" . $termoBusca . "%'");																								
			}
						
		}
		
		$query->order($ordenacao);
		
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
		
		$iframe = $this->_getParam("iframe",false);
		if($iframe){
		    $this->_helper->layout()->setLayout('admin-form');
		}
		$this->view->iframe = $iframe;
		
		//Lista de categorias
		$tblCategoriaTransporte = new Model_CategoriaTransporte();
		$this->view->categorias = $tblCategoriaTransporte->fetchAll(null,"nome ASC");

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
		
		
		if($post["tipopessoa"] == "F"){
		    $post["cpf"] = trim($post["cpf"]);
		    if(empty($post["cpf"])){
		        echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo cpf"));
		        return;
		    } else {
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
		        //Verificando se o cpf do cliente já está cadastrado
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

		if(!empty($post["site"])){
		     $post["site"] = (strpos($post["site"], "http://") !== false || strpos($post["site"], "https://") !== false)?$post["site"]:"http://".$post["site"];    
		}
		
		$post["categoriatransporte"] = trim($post["categoriatransporte"]);
		if(empty($post["categoriatransporte"])){
		    echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo categoriatransporte"));
		    return;
		}
		
		$post["nome"] = trim($post["nome"]);		
		if(empty($post["nome"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo nome"));
			return;
		} else {
		    $querie = "nome = '{$post["nome"]}' AND categoriatransporte = '{$post["categoriatransporte"]}'";
		    if(!empty($post["codigo"])){
		        $querie.= " AND codigo <> {$post["codigo"]}";    
		    }
		    $result = $tbl->fetchRow($querie);
		    if($result){
		        echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Já existe um transporte cadastrado com este nome e esta categoria em nossa base de dados"));
		        return;
		    }
		}
						
		$codigo = null;
	
		if($erro == 0){
			if(empty($post["codigo"])) {
				unset($post["codigo"]);
				$date = new Zend_Date();
				$post["datacadastro"] = $date->get("WWW");
				$codigo = $tbl->insert($post);				
			} else {
				$codigo = $post["codigo"];
				unset($post["codigo"]);					
				$tbl->update($post,"codigo = ".$codigo);
			}
		}	
		$tbl->getAdapter()->commit();
			
		echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg"=>$msg, "codigo" => $codigo, "nome"=>$post["nome"], "categoriatransporte"=>$post["categoriatransporte"]));
	}
				
	public function excluirAction() {
	
		Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = "";
	
		$post = $this->getRequest()->getPost();
	
		$itens = explode(",", $post["itens"]);
	
		$tbl = new $this->model();
		$tblVendaProduto = new Model_VendaProduto();
		if(count($itens)>0):	
			foreach($itens as $codigo):			
				if(!empty($codigo) && is_numeric($codigo)) {
        		    //Verificando se este produto faz parte de uma venda
        		    $result = $tblVendaProduto->fetchRow("transporte = $codigo");
        		    if($result){
        		        echo Zend_Json_Encoder::encode(array("msg"=> "Este produto se encontra localizado na venda $result->venda e não pode ser excluído", "erro"=> 1));
        		        return;
        		    }				    					
					$tbl->delete("codigo = ".$codigo);
				}		
			endforeach;
		endif;
	
		echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));	
	}
	
	
	
}
