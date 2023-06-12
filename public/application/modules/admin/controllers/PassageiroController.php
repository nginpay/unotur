
<?php		
require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @autor Estudio Criar
 * @contato estudiocriar.com.br
 * @versão 1.0.1 - 11/07/2014
 */
class Admin_PassageiroController extends AdminController {
	/**
	 * Model principal do crud
	 */
	private $model = "Model_Passageiro";
	private $ordenacao = "codigo DESC";	
	
	/**
	 * IndexAction -
	 */
	public function indexAction() {
		/* Array com os dados da tabela */		
		$this->view->etiquetas = array('codigo'=>'Código', 'nome'=>'Nome', 'sobrenome'=>'Sobrenome', 'datanascimento'=>'Data de Nascimento');	
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
                $query->orWhere("lower(passaporte) like '%" . $termoBusca . "%'");               
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
						
		$post["nome"] = trim($post["nome"]);		
		if(empty($post["nome"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo nome"));
			return;
		}
		
		$post["sobrenome"] = trim($post["sobrenome"]);		
		if(empty($post["sobrenome"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo sobrenome"));
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
        
        $post["datanascimento"] = trim($post["datanascimento"]);
        if(empty($post["datanascimento"])){
            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo datanascimento"));
            return;
        } else {
            $date = new Zend_Date($post["datanascimento"]);
            $post["datanascimento"] = $date->get("WWW");
        }
        
        if(!empty($post["emissaopassaporte"])){
            $date = new Zend_Date($post["emissaopassaporte"]);
            $post["emissaopassaporte"] = $date->get("WWW");
        } else {
            unset($post["emissaopassaporte"]);
        }
        
        if(!empty($post["vencimentopassaporte"])){
            $date = new Zend_Date($post["vencimentopassaporte"]);
            $post["vencimentopassaporte"] = $date->get("WWW");
        } else {
            unset($post["vencimentopassaporte"]);
        }
        
        $post["nome"] = mb_strtoupper($post["nome"],"UTF-8");
        $post["sobrenome"] = mb_strtoupper($post["sobrenome"],"UTF-8");
        
        //Verificando se o passageiro já está cadastrado
        $querie = "nome = '{$post["nome"]}' AND sobrenome = '{$post["sobrenome"]}'";        
        if(!empty($post["codigo"])){
            $querie .= " AND codigo <> {$post['codigo']}";
        }        
        $result = $tbl->fetchRow($querie);
        if($result){
            echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Já existe um passageiro cadastrado com este nome e sobrenome"));
            return;
        }
                
		if($erro == 0){
			if(empty($post["codigo"])) {
				unset($post["codigo"]);				
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
	
	public function obterPassageirosAction() {
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	     
	    $q = $_REQUEST['data']['q'];	    
	    $tblPassageiro = new Model_Passageiro();
	    $results = array();
	    if(!empty($q)){
	        $querie = "lower(nome) like '%".$q."%'";
	        $querie .= " || lower(sobrenome) like '%".$q."%'";
	        
	        $passageiros = $tblPassageiro->fetchAll($querie);
	        foreach($passageiros as $passageiro){
	            $clienteSobrenome = " ".$passageiro->sobrenome;
	            $results[] = array('id' => $passageiro->codigo, 'text' => $passageiro->nome.$clienteSobrenome);
	        }
	    }
	     
	    echo json_encode(array('q' => $q, 'results' => $results));
	}
	
	
}
