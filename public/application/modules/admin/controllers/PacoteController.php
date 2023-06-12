
<?php

require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @autor Estudio Criar
 * @contato estudiocriar.com.br
 * @versão 1.0.1 - 08/08/2014
 */
class Admin_PacoteController extends AdminController {

    /**
     * Model principal do crud
     */
    private $model = "Model_Pacote";
    private $ordenacao = "codigo DESC";

    /**
     * IndexAction -
     */
    public function indexAction() {
        /* Array com os dados da tabela */
        $this->view->etiquetas = array('descricao' => 'Descrição', 'qtdparticipantes' => 'Qtd Participantes', 'valor' => 'Gasto do Pacote', 'valorpart' => 'Valor de Venda');
    }

    public function indexDadosAction() {

        $this->_helper->layout()->disableLayout();

        $tbl = new $this->model();

        $query = $tbl->select()->setIntegrityCheck(false);

        if (!empty($this->query))
            $query->where($this->query);

        if ($this->getRequest()->isPost()) {

            //Dados da busca
            $post = $this->getRequest()->getPost();

            //Termo da ordenação
            $ordem = $this->_getParam("ordenacao");
            $ordenacao = !empty($ordem) ? $ordem : $this->ordenacao;

            $termoBusca = $this->_getParam("busca");
            if (!empty($termoBusca)) {

                $query->orWhere("lower(descricao) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datainiciovenda) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datafimvenda) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datasaida) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datachegada) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(horasaida) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(horachegada) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(localsaida) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(qtdparticipantes) like '%" . $termoBusca . "%'");
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

        //Carregando as moedas
        $tblMoeda = new Model_Moeda();
        $this->view->moedas = $tblMoeda->fetchAll();

        $tbl = new $this->model();
        if (is_numeric($codigo)) {
            $this->view->registro = $tbl->find($codigo)->current();

            //Lista de clientes
            $tblCliente = new Model_Cliente();
            $this->view->clientes = $tblCliente->fetchAll("liderpacote = 1", "nome ASC");
        }


        //Lista de categorias de transporte
        $tblCategoriaTransporte = new Model_CategoriaTransporte();
        $this->view->categorias = $tblCategoriaTransporte->fetchAll(null, "nome ASC");

        //Paises de hospedagem
        $tblPais = new Model_Pais();
        $this->view->paises = $tblPais->fetchAll("iso in (SELECT pais FROM hospedagem)", "nome ASC");

        //Serviços
        $tblServico = new Model_Servico();
        $this->view->servicos = $tblServico->fetchAll(null, "nome ASC");

        $iframe = $this->_getParam("iframe", false);
        if ($iframe) {
            $this->_helper->layout()->setLayout('admin-form');
        }
        $this->view->iframe = $iframe;

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

        unset($post["categoriatransporte"]);
        unset($post["transporte"]);
        unset($post["pais"]);
        unset($post["hospedagem"]);
        unset($post["servico"]);
        $tbl = new $this->model();
        $tbl->getAdapter()->beginTransaction();

        if (isset($post["lucroesperado"])) {
            $post["lucroesperado"] = floatval(str_replace(',', '.', str_replace('.', '', $post["lucroesperado"])));
        }

        if (!empty($post["valorvendaindividual"])):
            $post["valorvendaindividual"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valorvendaindividual"])));
        else:
            $post["valorvendaindividual"] = 0;
        endif;

        $post["descricao"] = trim($post["descricao"]);
        if (empty($post["descricao"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo Descrição em [Dados Cadastrais]"));
            return;
        }

        if (!empty($post["datainiciovenda"])) {
            $date = new Zend_Date($post["datainiciovenda"]);
            $post["datainiciovenda"] = $date->get("WWW");
        } else {
            $post["datainiciovenda"] = null;
        }

        if (!empty($post["datafimvenda"])) {
            $date = new Zend_Date($post["datafimvenda"]);
            $post["datafimvenda"] = $date->get("WWW");
        } else {
            $post["datafimvenda"] = null;
        }

        if (!empty($post["datasaida"])) {
            $date = new Zend_Date($post["datasaida"]);
            $post["datasaida"] = $date->get("WWW");
        } else {
            $post["datasaida"] = null;
        }

        if (!empty($post["datachegada"])) {
            $date = new Zend_Date($post["datachegada"]);
            $post["datachegada"] = $date->get("WWW");
        } else {
            $post["datachegada"] = null;
        }

        $post["qtdparticipantes"] = trim($post["qtdparticipantes"]);
        if (empty($post["qtdparticipantes"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo Quantidade de Vagas em [Dados Cadastrais]"));
            return;
        }

        $codigo = null;

        if (empty($post["liderpacote"])) {
            $post["liderpacote"] = null;
        }

        $novo = false;

// 		echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>$post["valor"]));
// 		return;

        if ($erro == 0) {
            if (empty($post["codigo"])) {
                unset($post["codigo"]);
                $codigo = $tbl->insert($post);
                $novo = true;
            } else {
                $codigo = $post["codigo"];
                unset($post["codigo"]);
                $tbl->update($post, "codigo = " . $codigo);
            }
        }
        $tbl->getAdapter()->commit();

        echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg" => $msg, "codigo" => $codigo, "novo" => $novo));
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

        if (count($itens) > 0):
            foreach ($itens as $codigo):
                
                if (!empty($codigo) && is_numeric($codigo)) {
                    //Verificando se este pacote faz parte de uma venda
                    $result = $tblVendaProduto->fetchRow("pacote = $codigo");
                    if ($result) {
                        echo Zend_Json_Encoder::encode(array("msg" => "Este produto se encontra localizado na venda $result->venda e não pode ser excluído", "erro" => 1));
                        return;
                    }

                    $tbl->delete("codigo = " . $codigo);
                }
            endforeach;
        endif;

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro));
    }

    public function addTransporteAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if (empty($post["transporte"]) || empty($post["origem"]) || empty($post["destino"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Por favor preencha os campos obrigatórios (*)", "erro" => 1));
            return;
        }

        //Inserindo o transporte pacote
        $tblPacoteTransporte = new Model_PacoteTransporte();

        unset($post["adicionado"]);

        $post["valor"] = $this->view->MoedaParaNumero($post["valor"]);

        if (!empty($post["datasaida"])):
            $data = new Zend_Date($post["datasaida"]);
            $post["datasaida"] = $data->get("WWW");
        else:
            unset($post["datasaida"]);
        endif;

        if (!empty($post["datachegada"])):
            $data = new Zend_Date($post["datachegada"]);
            $post["datachegada"] = $data->get("WWW");
        else:
            unset($post["datachegada"]);
        endif;

        if (empty($post["codigo"])) {
            $tblPacoteTransporte->insert($post);
        } else {
            $tblPacoteTransporte->update($post, "codigo = {$post["codigo"]}");
        }

        $html = $this->htmlTransporte($post["pacote"]);


        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function delTransporteAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if (empty($post["pacote"]) || empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Parâmetros inválidos", "erro" => 1));
            return;
        }

        //Deletando o transporte pacote
        $tblPacoteTransporte = new Model_PacoteTransporte();
        $tblPacoteTransporte->delete("codigo = {$post["codigo"]}");
        $html = $this->htmlTransporte($post["pacote"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    private function htmlTransporte($pacote) {
        $html = null;

        $tblPacoteTransporte = new Model_PacoteTransporte();
        $transportes = $tblPacoteTransporte->fetchAll("pacote = $pacote", "codigo DESC");

        if (count($transportes) > 0):
            $aux = 0;
            foreach ($transportes as $pacoteTransporte):
                $transporte = $pacoteTransporte->findParentRow("Model_Transporte");

                $class = ($aux % 2 == 0) ? "even" : "odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$transporte->nome}</td>";
                $html .= "<td>{$pacoteTransporte->origem}</td>";
                $html .= "<td>{$pacoteTransporte->destino}</td>";
                $html .= "<td>{$this->view->data($pacoteTransporte->datasaida)}</td>";
                $html .= "<td>{$pacoteTransporte->horasaida}</td>";
                $html .= "<td>{$this->view->data($pacoteTransporte->datachegada)}</td>";
                $html .= "<td>{$pacoteTransporte->horachegada}</td>";
                $html .= "<td class='valorGrid'>{$this->view->NumeroParaMoeda($pacoteTransporte->valor)}</td>";
                $html .= "<td>";
                $html .= "<a data-transporte='{$pacoteTransporte->transporte}' class='mws-ic-16 ic-magnifier viewTransporte' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$pacoteTransporte->codigo}' class='mws-ic-16 ic-edit editRow' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$pacoteTransporte->codigo}' class='mws-ic-16 ic-cross deleteRow' title=''>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='9' style='text-align:center;'>";
            $html.= "Nenhum transporte adicionado no pacote até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;

        return $html;
    }

    public function gridTransporteAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $post = $this->getRequest()->getPost();

        $html = $this->htmlTransporte($post["pacote"]);

        echo Zend_Json_Encoder::encode(array("html" => $html));
    }

    public function gridHospedagemAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $post = $this->getRequest()->getPost();

        $html = $this->htmlHospedagem($post["pacote"]);

        echo Zend_Json_Encoder::encode(array("html" => $html));
    }

    public function delHospedagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if (empty($post["pacote"]) || empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Parâmetros inválidos", "erro" => 1));
            return;
        }

        //Deletando a hospedagem do pacote
        $tblPacoteHospedagem = new Model_PacoteHospedagem();
        $tblPacoteHospedagem->delete("codigo = {$post["codigo"]}");
        $html = $this->htmlHospedagem($post["pacote"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function buscaTransporteAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;
        $categoriaTransporte = null;

        $post = $this->getRequest()->getPost();

        if (!empty($post["codigo"])) {
            $tblPacoteTransporte = new Model_PacoteTransporte();
            $result = $tblPacoteTransporte->find($post["codigo"])->current();
            if ($result) {
                $transporte = $result->findParentRow("Model_Transporte");
                $categoriaTransporte = $transporte->categoriatransporte;

                $html = $result->toArray();
                if (!empty($html["datasaida"])) {
                    $html["datasaida"] = $this->view->data($html["datasaida"]);
                }
                if (!empty($html["datachegada"])) {
                    $html["datachegada"] = $this->view->data($html["datachegada"]);
                }

                $html["valor"] = $this->view->NumeroParaMoeda($html["valor"]);
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html, "categoriaTransporte" => $categoriaTransporte));
    }

    public function buscaHospedagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;
        $pais = null;

        $post = $this->getRequest()->getPost();

        if (!empty($post["codigo"])) {
            $tblPacoteHospedagem = new Model_PacoteHospedagem();
            $result = $tblPacoteHospedagem->find($post["codigo"])->current();
            if ($result) {
                $hospedagem = $result->findParentRow("Model_Hospedagem");
                $pais = $hospedagem->pais;

                $html = $result->toArray();
                if (!empty($html["datasaida"])) {
                    $html["datasaida"] = $this->view->data($html["datasaida"]);
                }
                if (!empty($html["datachegada"])) {
                    $html["datachegada"] = $this->view->data($html["datachegada"]);
                }

                $html["valor"] = $this->view->NumeroParaMoeda($html["valor"]);
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html, "pais" => $pais));
    }

    public function buscaServicoAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if (!empty($post["codigo"])) {
            $tblPacoteServico = new Model_PacoteServico();
            $result = $tblPacoteServico->find($post["codigo"])->current();
            if ($result) {
                $html = $result->toArray();
                $html["valor"] = $this->view->NumeroParaMoeda($html["valor"]);
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function addHospedagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if ($post["adicionado"] == "true" && empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Esta hospedagem já se encontra cadastrada neste pacote", "erro" => 1));
            return;
        }

        if (empty($post["hospedagem"]) || empty($post["valor"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Por favor preencha a hospedagem e/ou valor", "erro" => 1));
            return;
        }

        //Inserindo o transporte pacote
        $tblPacoteHospedagem = new Model_PacoteHospedagem();

        unset($post["adicionado"]);

        $post["valor"] = $this->view->MoedaParaNumero($post["valor"]);

        if (!empty($post["datasaida"])):
            $data = new Zend_Date($post["datasaida"]);
            $post["datasaida"] = $data->get("WWW");
        else:
            unset($post["datasaida"]);
        endif;

        if (!empty($post["datachegada"])):
            $data = new Zend_Date($post["datachegada"]);
            $post["datachegada"] = $data->get("WWW");
        else:
            unset($post["datachegada"]);
        endif;

        if (empty($post["codigo"])) {
            $tblPacoteHospedagem->insert($post);
        } else {
            //print_r($post); exit;
            $tblPacoteHospedagem->update($post, "codigo = {$post["codigo"]}");
        }

        $html = $this->htmlHospedagem($post["pacote"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    private function htmlHospedagem($pacote) {

        $html = null;

        $tblPacoteHospedagem = new Model_PacoteHospedagem();
        $hospedagens = $tblPacoteHospedagem->fetchAll("pacote = $pacote", "codigo DESC");

        if (count($hospedagens) > 0):
            $aux = 0;
            foreach ($hospedagens as $pacoteHospedagem):
                $hospedagem = $pacoteHospedagem->findParentRow("Model_Hospedagem");

                $class = ($aux % 2 == 0) ? "even" : "odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$hospedagem->nome}</td>";
                $html .= "<td>{$this->view->data($pacoteHospedagem->datachegada)}</td>";
                $html .= "<td>{$pacoteHospedagem->horachegada}</td>";
                $html .= "<td>{$this->view->data($pacoteHospedagem->datasaida)}</td>";
                $html .= "<td>{$pacoteHospedagem->horasaida}</td>";
                $html .= "<td class='valorGrid'>{$this->view->NumeroParaMoeda($pacoteHospedagem->valor)}</td>";
                $html .= "<td>";
                $html .= "<a data-hospedagem='{$pacoteHospedagem->hospedagem}' class='mws-ic-16 ic-magnifier viewHospedagem' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$pacoteHospedagem->codigo}' class='mws-ic-16 ic-edit editRowHospedagem' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$pacoteHospedagem->codigo}' class='mws-ic-16 ic-cross deleteRowHospedagem' title=''>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='7' style='text-align:center;'>";
            $html.= "Nenhuma hospedagem adicionada no pacote até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;

        return $html;
    }

    public function delServicoAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if (empty($post["pacote"]) || empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Parâmetros inválidos", "erro" => 1));
            return;
        }

        //Deletando o serviço do pacote
        $tblPacoteServico = new Model_PacoteServico();
        $tblPacoteServico->delete("codigo = {$post["codigo"]}");
        $html = $this->htmlServico($post["pacote"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function gridServicoAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $post = $this->getRequest()->getPost();

        $html = $this->htmlServico($post["pacote"]);

        echo Zend_Json_Encoder::encode(array("html" => $html));
    }

    private function htmlServico($pacote) {

        $html = null;

        $tblPacoteServico = new Model_PacoteServico();
        $servicos = $tblPacoteServico->fetchAll("pacote = $pacote", "codigo DESC");

        if (count($servicos) > 0):
            $aux = 0;
            foreach ($servicos as $pacoteServico):
                $servico = $pacoteServico->findParentRow("Model_Servico");

                $class = ($aux % 2 == 0) ? "even" : "odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$servico->nome}</td>";
                $html .= "<td class='valorGrid'>{$this->view->NumeroParaMoeda($pacoteServico->valor)}</td>";
                $html .= "<td>";
                $html .= "<a data-servico='{$pacoteServico->servico}' class='mws-ic-16 ic-magnifier viewServico' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$pacoteServico->codigo}' class='mws-ic-16 ic-edit editRowServico' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$pacoteServico->codigo}' class='mws-ic-16 ic-cross deleteRowServico' title=''>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='7' style='text-align:center;'>";
            $html.= "Nenhum serviço adicionado no pacote até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;

        return $html;
    }

    public function addServicoAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if (empty($post["servico"]) || empty($post["valor"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Por favor preencha o serviço e/ou valor", "erro" => 1));
            return;
        }

        //Inserindo o transporte pacote
        $tblPacoteServico = new Model_PacoteServico();

        unset($post["adicionado"]);

        $post["valor"] = $this->view->MoedaParaNumero($post["valor"]);

        if (empty($post["codigo"])) {
            $tblPacoteServico->insert($post);
        } else {
            $tblPacoteServico->update($post, "codigo = {$post["codigo"]}");
        }

        $html = $this->htmlServico($post["pacote"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    private function valorRecebido($venda) {
        $tblVendaReceber = new Model_VendaAReceber();
        if ($venda > 0) {
            $recebimentos = $tblVendaReceber->fetchAll("venda = $venda");
            $valorRecebido = 0;
            foreach ($recebimentos as $receber) {
                $valorRecebido+= $receber->valorpago;
            }

            return $valorRecebido;
        } else {
            return 0;
        }
    }

    function uploadAction() {

        $this->_helper->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);

        $this->view->headMeta()->appendName('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $this->view->headMeta()->appendName('Last-Modified', gmdate("D, d M Y H:i:s") . " GMT");
        $this->view->headMeta()->appendName('Cache-Control', 'no-store, no-cache, must-revalidate');
        $this->view->headMeta()->appendName('Pragma', 'no-cache');

        // Parâmetros
        //$targetDir = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "images". DIRECTORY_SEPARATOR ."convite";
        $targetDir = getcwd() . "/images/default/tmp";

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
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);

            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;

            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }

        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

        // Cria um target dir
        if (!file_exists($targetDir))
            @mkdir($targetDir);

        // Remove arquivos temporários
        if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
            while (($file = readdir($dir)) !== false) {
                $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

                // Remove temp file if it is older than the max age and is not the current file
                if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                    @unlink($tmpfilePath);
                }
            }

            closedir($dir);
        } else
            die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Falha para abrir o diretório temporário"}, "id" : "id"}');


        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];

        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];

        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = fopen($_FILES['file']['tmp_name'], "rb");

                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                    fclose($in);
                    fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Falha para subir o arquivo ' . $_FILES['file']['tmp_name'] . '"}, "id" : "id"}');
        } else {
            // Open temp file
            $out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = fopen("php://input", "rb");

                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

                fclose($in);
                fclose($out);
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }

        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off
            rename("{$filePath}.part", $filePath);
        }

        // Return JSON-RPC response
        die('{"jsonrpc" : "2.0", "link":' . $fileName . ', "result" : ' . $filePath . ', "id" : "id"}');
    }

    public function excluirRoteiroAction() {
        $this->_helper->viewRenderer->setNoRender();

        $codigo = $this->_getParam('codigo');
        $roteiro = $this->_getParam('roteiro');

        $retorno = new stdClass();

        $erro = 0;
        $mensagem = '';

        if (!empty($roteiro)):

            $filename = getcwd() . $roteiro;
            if (file_exists($filename)) {
                unlink($filename);
            }

            if (!empty($codigo) && is_numeric($codigo) && $erro == 0) {
                //Buscando as midias que será deletada
                $tblPacote = new Model_Pacote();
                $tblPacote->update(array("roteiro" => ""), "codigo = " . $codigo);
            }

        endif;

        $retorno->erro = $erro;
        $retorno->mensagem = $mensagem;
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($retorno);
    }

}
