
<?php

require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @autor Estudio Criar
 * @contato estudiocriar.com.br
 * @versão 1.0.1 - 27/04/2015
 */
class Admin_StatusAtendimentoController extends AdminController {

    /**
     * Model principal do crud
     */
    private $model = "Model_StatusAtendimento";
    private $ordenacao = "codigo DESC";

    /**
     * IndexAction -
     */
    public function indexAction() {
        /* Array com os dados da tabela */
        $this->view->etiquetas = array('nome' => 'Nome');
    }

    public function indexDadosAction() {

        $this->_helper->layout()->disableLayout();

        $tbl = new $this->model();

        $query = $tbl->select()->setIntegrityCheck(false);

        if (!empty($this->query))
            $query->where($this->query);

        $limit = true;
        if ($this->getRequest()->isPost()) {

            //Dados da busca
            $post = $this->getRequest()->getPost();

            //Termo da ordenação
            $ordem = $this->_getParam("ordenacao");
            $ordenacao = !empty($ordem) ? $ordem : $this->ordenacao;

            $termoBusca = $this->_getParam("busca");
            if (!empty($termoBusca)) {

                $query->orWhere("lower(nome) like '%" . $termoBusca . "%'");

                $limit = false;
            }
        }

        $query->order($ordenacao);
        if ($limit):
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
        if (is_numeric($codigo)) {
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
        $msg = "Filtro adicionado com sucesso!";

        $post = $this->getRequest()->getPost();

        $tbl = new $this->model();
        $tbl->getAdapter()->beginTransaction();


        $post["nome"] = trim($post["nome"]);
        if (empty($post["nome"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo nome"));
            return;
        }
        
        //Verificando se o filtro já foi cadastrado
        $query = "nome = '{$post["nome"]}'";
        if (!empty($post["codigo"])) {
            $query = " AND codigo <> {$post["codigo"]}";
        }
        $result = $tbl->fetchRow($query);
        if($result){
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Este status já se encontra cadastrado em nossa base de dados"));
            return;
        }

        $codigo = null;

        if ($erro == 0) {
            if (empty($post["codigo"])) {
                unset($post["codigo"]);
                $codigo = $tbl->insert($post);
            } else {
                $codigo = $post["codigo"];
                unset($post["codigo"]);
                $tbl->update($post, "codigo = " . $codigo);
            }
        }
        $tbl->getAdapter()->commit();
        
        $option = "<option selected='selected' value='$codigo'>{$post["nome"]}</option>";
        
        echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg" => $msg, "codigo" => $codigo, "option"=>$option));
    }

    public function excluirAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";

        $post = $this->getRequest()->getPost();

        $itens = explode(",", $post["itens"]);

        $tbl = new $this->model();
        if (count($itens) > 0):
            foreach ($itens as $codigo):
                if (!empty($codigo) && is_numeric($codigo)) {                    
                    $tbl->delete("codigo = " . $codigo);
                }
            endforeach;
        endif;

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro));
    }

}
