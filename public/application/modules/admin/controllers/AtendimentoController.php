<?php

require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @versão 1.0.1 - 13/08/2014
 */
class Admin_AtendimentoController extends AdminController {

    /**
     * Model principal do crud
     */
    private $model = "Model_Atendimento";
    private $ordenacao = "dataretorno ASC";

    /**
     * IndexAction -
     */
    public function indexAction() {
        /* Array com os dados da tabela */
        $this->view->etiquetas = array('cliente' => 'Cliente', 'dataatendimento' => 'Último atendimento', 'dataretorno' => 'Data de retorno', 'statusatendimento' => 'Status');

        //Lista os atendentes
        $tblUsuario = new Model_Usuario();
        $this->view->usuarios = $tblUsuario->fetchAll(null, "nome ASC");
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
            $ordenacao = !empty($ordem) ? $ordem : $this->ordenacao;

            $cliente = $this->_getParam("cliente");
            if(!empty($cliente)) {
                $query->where("cliente IN(SELECT codigo FROM cliente WHERE lower(nome) like '%" . $cliente . "%')");
                $this->view->cliente = $cliente;
                $limit = false;
            }

            $usuario = $this->_getParam("usuario");
            if(!empty($usuario)) {
                $query->where("usuario = '$usuario' OR usuario IN (SELECT usuario FROM atendimento_historico WHERE usuario = '$usuario')");
                $this->view->usuario = $usuario;
                $limit = false;
            }

            $dataAtendimentoInicio = $this->_getParam("dataAtendimentoInicio");
            if(!empty($dataAtendimentoInicio)) {
                $date = new Zend_Date($dataAtendimentoInicio);
                $data = $date->get("WWW");
                $query->where("DATE(dataatendimento) >= '$data'");
                $this->view->dataAtendimentoInicio = $dataAtendimentoInicio;
            }

            $dataAtendimentoFim = $this->_getParam("dataAtendimentoFim");
            if(!empty($dataAtendimentoFim)) {
                $date = new Zend_Date($dataAtendimentoFim);
                $data = $date->get("WWW");
                $query->where("DATE(dataatendimento) <= '$data'");
                $this->view->dataAtendimentoFim = $dataAtendimentoFim;
            }
            
            if(empty($dataAtendimentoInicio) && empty($dataAtendimentoFim)){
            	$query->where("DATE(dataretorno) >= CURRENT_DATE()");
            }            
        }
        $query->order($ordenacao);

        if($limit):
            $query->limit(1000);
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

        $tblCliente = new Model_Cliente();
        if(is_numeric($codigo)) {
            $this->view->registro = $tbl->find($codigo)->current();
            $this->view->clientes = $tblCliente->fetchAll("codigo = {$this->view->registro->cliente}", "nome ASC", 1000);
        } else {
            $this->view->clientes = $tblCliente->fetchAll(null, "nome ASC", 1000);
        }


        $iframe = $this->_getParam("iframe", false);
        if($iframe) {
            $this->_helper->layout()->setLayout('admin-form');
        }
        $this->view->iframe = $iframe;

        $conta = $this->_getParam("conta", false);
        if($conta) {
            $this->view->registro = array();
            $this->view->registro["codigo"] = null;
            $this->view->registro["dataretorno"] = null;
            $this->view->registro["observacoes"] = null;
            $this->view->registro["cliente"] = null;
            $this->view->registro["statusatendimento"] = 1;
            $this->view->registro["venda_areceber"] = $conta;
        }

        $tblStatus = new Model_StatusAtendimento();
        $this->view->status = $tblStatus->fetchAll();

        $tblTipoHospedagem = new Model_TipoHospedagem();
        $this->view->tiposHospedagens = $tblTipoHospedagem->fetchAll(null, "nome ASC");
        
        $tblTipoPacote = new Model_TipoPacote();        
        $this->view->tiposPacotes = $tblTipoPacote->fetchAll(null, "nome ASC");
        
        //Notificações
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/jgrowl/jquery.jgrowl.js'));
        $this->view->headLink()->appendStylesheet($this->view->baseUrl('/js/admin/plugins/jgrowl/jquery.jgrowl.css'));
    }

    public function salvarCadastroAction() {

        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";

        $post = $this->getRequest()->getPost();

        $tbl = new $this->model();
        $tbl->getAdapter()->beginTransaction();


        $post["statusatendimento"] = trim($post["statusatendimento"]);
        if(empty($post["statusatendimento"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo Tipo de Atendimento"));
            return;
        }

        $post["observacoes"] = trim($post["observacoes"]);
        if(empty($post["observacoes"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo Observação"));
            return;
        }
        
        $post["telefone"] = trim($post["telefone"]);
        if(empty($post["telefone"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo Telefone"));
            return;
        }
        
        if($post["statusatendimento"] == 1) {

            if(empty($post["venda_areceber"])) {
                echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Para realizar uma cobrança é necessário informar o código da conta a receber"));
                return;
            }

            //Verificando se o valor informado no conta a receber corresponde a uma conta
            $tblVendaReceber = new Model_VendaAReceber();
            $result = $tblVendaReceber->find($post["venda_areceber"])->current();

            if($result) {
                $venda = $result->findParentRow("Model_Venda");
                $cliente = $venda->findParentRow("Model_Cliente");
                $post["cliente"] = $cliente->nome;
            } else {
                echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "O código da conta a receber é inválido"));
                return;
            }
        }

        $post["usuario"] = $this->view->usuario["usuario"];
        if(empty($post["usuario"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo usuario"));
            return;
        }

        $date = new Zend_Date();
        $post["dataatendimento"] = $date->get("WWW");

        if(!empty($post["dataretorno"])) {
            $date = new Zend_Date($post["dataretorno"]);
            $post["dataretorno"] = $date->get("WWW");
        } else {
            $post["dataretorno"] = null;
        }

        $codigo = null;

        if(empty($post["venda_areceber"])) {
            $post["venda_areceber"] = null;
        }

        $adicionou = false;
        if($erro == 0) {
        	
            if(empty($post["codigo"])) {
                $post["cliente"] = trim($post["cliente"]);
                
                //Verificando se já existe cadastro para este cliente
                $result = $tbl->fetchRow("cliente = {$post["cliente"]}");
                if($result){
                    echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Já existe um tramite de atendimento para este cliente por favor utilize o filtro de pesquisa"));
                    return;
                }
                                
                if(empty($post["cliente"])) {
                    echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo cliente"));
                    return;
                }
                
                unset($post["codigo"]);
                $codigo = $tbl->insert($post);
                $adicionou = true;
            } else {            	
                $codigo = $post["codigo"];
                                
                unset($post["codigo"]);
                unset($post["cliente"]);
                $tbl->update($post, "codigo = " . $codigo);
            }
        }
        $tbl->getAdapter()->commit();

        echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg" => $msg, "codigo" => $codigo, "adicionou" => $adicionou));
    }

    public function excluirAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";

        $post = $this->getRequest()->getPost();

        $itens = explode(",", $post["itens"]);

        $tbl = new $this->model();
        if(count($itens) > 0):
            foreach($itens as $codigo):
                if(!empty($codigo) && is_numeric($codigo)) {

                    $tbl->delete("codigo = " . $codigo);
                }
            endforeach;
        endif;

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro));
    }

    public function buscaContaAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $tipo = null;
        $parcela = null;
        $vencimento = null;
        $valor = null;
        $nome = null;

        $post = $this->getRequest()->getPost();
        $tblVendaReceber = new Model_VendaAReceber();
        if(!empty($post["codigo"])) {
            $result = $tblVendaReceber->find($post["codigo"])->current();
            if($result) {
                $tipo = $result->tipo;
                $parcela = $result->parcela;
                $vencimento = $this->view->data($result->datavencimento);
                $valor = "R$ " . $this->view->NumeroParaMoeda($result->valor);

                $venda = $result->findParentRow("Model_Venda");
                $cliente = $venda->findParentRow("Model_Cliente");
                $nome = $cliente->nome;
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "tipo" => $tipo, "parcela" => $parcela, "vencimento" => $vencimento, "valor" => $valor, "nome" => $nome));
    }

    public function salvarCategoriaAction() {

        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "Categoria adicionada com sucesso!";

        $post = $this->getRequest()->getPost();

        $tbl = new Model_StatusAtendimento();
        $tbl->getAdapter()->beginTransaction();


        $post["nome"] = trim($post["nome"]);
        if(empty($post["nome"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo nome"));
            return;
        }

        //Verificando se o filtro já foi cadastrado
        $query = "nome = '{$post["nome"]}'";
        if(!empty($post["codigo"])) {
            $query = " AND codigo <> {$post["codigo"]}";
        }
        $result = $tbl->fetchRow($query);
        if($result) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Este status de atendimento já se encontra cadastrado em nossa base de dados"));
            return;
        }

        $codigo = null;

        if($erro == 0) {
            if(empty($post["codigo"])) {
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

        echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg" => $msg, "codigo" => $codigo, "option" => $option));
    }

    public function salvarClienteAction() {

        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "Contratante adicionado com sucesso!";

        $post = $this->getRequest()->getPost();

        $tbl = new Model_Cliente();
        $tbl->getAdapter()->beginTransaction();


        $post["nome"] = trim($post["nome"]);
        if(empty($post["nome"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo nome"));
            return;
        }

        //Verificando se o filtro já foi cadastrado
        $query = "nome = '{$post["nome"]}'";
        if(!empty($post["codigo"])) {
            $query = " AND codigo <> {$post["codigo"]}";
        }
        $result = $tbl->fetchRow($query);
        if($result) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Este nome já se encontra cadastrado em nossa base de dados"));
            return;
        }

        $codigo = null;

        if($erro == 0) {
            if(empty($post["codigo"])) {
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

        echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg" => $msg, "codigo" => $codigo, "option" => $option));
    }

    public function buscaTransporteAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;
        $categoriaTransporte = null;

        $post = $this->getRequest()->getPost();

        if(!empty($post["codigo"])) {
            $tblAtendimentoTransporte = new Model_AtendimentoTransporte();
            $result = $tblAtendimentoTransporte->find($post["codigo"])->current();
            if($result) {
                $transporte = $result->findParentRow("Model_Transporte");
                $categoriaTransporte = $transporte->categoriatransporte;

                $html = $result->toArray();
                if(!empty($html["datasaida"])) {
                    $html["datasaida"] = $this->view->data($html["datasaida"]);
                }
                if(!empty($html["datachegada"])) {
                    $html["datachegada"] = $this->view->data($html["datachegada"]);
                }

                $html["valor"] = $this->view->NumeroParaMoeda($html["valor"]);
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html, "categoriaTransporte" => $categoriaTransporte));
    }

    public function salvarTipoHospedagemAction() {

        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "Tipo de hospedagem adicionada com sucesso!";

        $post = $this->getRequest()->getPost();

        $tbl = new Model_TipoHospedagem();
        $tbl->getAdapter()->beginTransaction();


        $post["nome"] = trim($post["nome"]);
        if(empty($post["nome"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo nome"));
            return;
        }

        //Verificando se o filtro já foi cadastrado
        $query = "nome = '{$post["nome"]}'";
        if(!empty($post["codigo"])) {
            $query = " AND codigo <> {$post["codigo"]}";
        }
        $result = $tbl->fetchRow($query);
        if($result) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Este tipo de hospedagem já se encontra cadastrado em nossa base de dados"));
            return;
        }

        $codigo = null;

        if($erro == 0) {
            if(empty($post["codigo"])) {
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

        echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg" => $msg, "codigo" => $codigo, "option" => $option));
    }

    public function buscaHospedagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;
        $pais = null;

        $post = $this->getRequest()->getPost();

        if(!empty($post["codigo"])) {
            $tblAtendimentoHospedagem = new Model_AtendimentoHospedagem();
            $result = $tblAtendimentoHospedagem->find($post["codigo"])->current();
            if($result) {
                $tipoHospedagem = $result->findParentRow("Model_TipoHospedagem");
                $tipo = $tipoHospedagem->nome;

                $html = $result->toArray();
                if(!empty($html["datasaida"])) {
                    $html["datasaida"] = $this->view->data($html["datasaida"]);
                }
                if(!empty($html["datachegada"])) {
                    $html["datachegada"] = $this->view->data($html["datachegada"]);
                }
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html, "tipo" => $tipo));
    }

    public function addHospedagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["tipo_hospedagem"]) || empty($post["datasaida"]) || empty($post["datachegada"]) || empty($post["local"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Todos campos são obrigatórios", "erro" => 1));
            return;
        }

        //Inserindo o transporte atendimento
        $tblAtendimentoHospedagem = new Model_AtendimentoHospedagem();

        if(!empty($post["datasaida"])):
            $data = new Zend_Date($post["datasaida"]);
            $post["datasaida"] = $data->get("WWW");
        else:
            unset($post["datasaida"]);
        endif;

        if(!empty($post["datachegada"])):
            $data = new Zend_Date($post["datachegada"]);
            $post["datachegada"] = $data->get("WWW");
        else:
            unset($post["datachegada"]);
        endif;

        if(empty($post["codigo"])) {
            $tblAtendimentoHospedagem->insert($post);
        } else {
            $tblAtendimentoHospedagem->update($post, "codigo = {$post["codigo"]}");
        }

        $html = $this->htmlHospedagem($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    private function htmlHospedagem($atendimento) {

        $html = null;

        $tblAtendimentoHospedagem = new Model_AtendimentoHospedagem();
        $hospedagens = $tblAtendimentoHospedagem->fetchAll("atendimento = $atendimento", "codigo DESC");

        if(count($hospedagens) > 0):
            $aux = 0;
            foreach($hospedagens as $atendimentoHospedagem):
                $tipoHospedagem = $atendimentoHospedagem->findParentRow("Model_TipoHospedagem");

                $class =($aux % 2 == 0) ? "even" : "odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$tipoHospedagem->nome}</td>";
                $html .= "<td>{$this->view->data($atendimentoHospedagem->datasaida)}</td>";
                $html .= "<td>{$this->view->data($atendimentoHospedagem->datachegada)}</td>";
                $html .= "<td>{$atendimentoHospedagem->local}</td>";
                $html .= "<td>";
                $html .= "<a data-codigo='{$atendimentoHospedagem->codigo}' class='mws-ic-16 ic-edit editRowHospedagem' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$atendimentoHospedagem->codigo}' class='mws-ic-16 ic-cross deleteRowHospedagem' title=''>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='5' style='text-align:center;'>";
            $html.= "Nenhuma hospedagem adicionada no atendimento até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;

        return $html;
    }

    public function gridHospedagemAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $post = $this->getRequest()->getPost();

        $html = $this->htmlHospedagem($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("html" => $html));
    }

    public function delHospedagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["atendimento"]) || empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Parâmetros inválidos", "erro" => 1));
            return;
        }

        //Deletando a hospedagem do atendimento
        $tblAtendimentoHospedagem = new Model_AtendimentoHospedagem();
        $tblAtendimentoHospedagem->delete("codigo = {$post["codigo"]}");
        $html = $this->htmlHospedagem($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function buscaPassagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;
        $pais = null;

        $post = $this->getRequest()->getPost();

        if(!empty($post["codigo"])) {
            $tblAtendimentoPassagem = new Model_AtendimentoPassagem();
            $result = $tblAtendimentoPassagem->find($post["codigo"])->current();
            if($result) {

                $html = $result->toArray();
                if(!empty($html["data"])) {
                    $html["data"] = $this->view->data($html["data"]);
                }
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function addPassagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["atendimento"]) || empty($post["origem"]) || empty($post["destino"]) || empty($post["data"]) || empty($post["qtdadulto"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Verifique os campos obrigatórios(*)", "erro" => 1));
            return;
        }

        //Inserindo o transporte atendimento
        $tblAtendimentoPassagem = new Model_AtendimentoPassagem();

        if(!empty($post["data"])):
            $data = new Zend_Date($post["data"]);
            $post["data"] = $data->get("WWW");
        else:
            unset($post["data"]);
        endif;

        if(empty($post["codigo"])) {
            $tblAtendimentoPassagem->insert($post);
        } else {
            $tblAtendimentoPassagem->update($post, "codigo = {$post["codigo"]}");
        }

        $html = $this->htmlPassagem($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    private function htmlPassagem($atendimento) {

        $html = null;

        $tblAtendimentoPassagem = new Model_AtendimentoPassagem();
        $hospedagens = $tblAtendimentoPassagem->fetchAll("atendimento = $atendimento", "codigo DESC");

        if(count($hospedagens) > 0):
            $aux = 0;
            foreach($hospedagens as $atendimentoPassagem):

                $class =($aux % 2 == 0) ? "even" : "odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$atendimentoPassagem->origem}</td>";
                $html .= "<td>{$atendimentoPassagem->destino}</td>";
                $html .= "<td>{$this->view->data($atendimentoPassagem->data)}</td>";
                $html .= "<td>{$atendimentoPassagem->qtdadulto}</td>";
                $html .= "<td>{$atendimentoPassagem->qtdcrianca}</td>";
                $html .= "<td>";
                $html .= "<a data-codigo='{$atendimentoPassagem->codigo}' class='mws-ic-16 ic-edit editRowPassagem' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$atendimentoPassagem->codigo}' class='mws-ic-16 ic-cross deleteRowPassagem' title=''>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='6' style='text-align:center;'>";
            $html.= "Nenhuma passagem adicionada no atendimento até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;

        return $html;
    }

    public function gridPassagemAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $post = $this->getRequest()->getPost();

        $html = $this->htmlPassagem($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("html" => $html));
    }

    public function delPassagemAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["atendimento"]) || empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Parâmetros inválidos", "erro" => 1));
            return;
        }

        //Deletando a passagem do atendimento
        $tblAtendimentoPassagem = new Model_AtendimentoPassagem();
        $tblAtendimentoPassagem->delete("codigo = {$post["codigo"]}");
        $html = $this->htmlPassagem($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function buscaSeguroAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;
        $pais = null;

        $post = $this->getRequest()->getPost();

        if(!empty($post["codigo"])) {
            $tblAtendimentoSeguro = new Model_AtendimentoSeguro();
            $result = $tblAtendimentoSeguro->find($post["codigo"])->current();
            if($result) {

                $html = $result->toArray();
                if(!empty($html["datainicio"])) {
                    $html["datainicio"] = $this->view->data($html["datainicio"]);
                }
                if(!empty($html["datafim"])) {
                    $html["datafim"] = $this->view->data($html["datafim"]);
                }
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function addSeguroAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["datainicio"]) || empty($post["datafim"]) || empty($post["descricao"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Verifique os campos obrigatórios(*)", "erro" => 1));
            return;
        }

        //Inserindo o transporte atendimento
        $tblAtendimentoSeguro = new Model_AtendimentoSeguro();

        if(!empty($post["datainicio"])):
            $data = new Zend_Date($post["datainicio"]);
            $post["datainicio"] = $data->get("WWW");
        else:
            unset($post["datainicio"]);
        endif;

        if(!empty($post["datafim"])):
            $data = new Zend_Date($post["datafim"]);
            $post["datafim"] = $data->get("WWW");
        else:
            unset($post["datafim"]);
        endif;

        if(empty($post["codigo"])) {
            $tblAtendimentoSeguro->insert($post);
        } else {
            $tblAtendimentoSeguro->update($post, "codigo = {$post["codigo"]}");
        }

        $html = $this->htmlSeguro($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    private function htmlSeguro($atendimento) {

        $html = null;

        $tblAtendimentoSeguro = new Model_AtendimentoSeguro();
        $seguros = $tblAtendimentoSeguro->fetchAll("atendimento = $atendimento", "codigo DESC");

        if(count($seguros) > 0):
            $aux = 0;
            foreach($seguros as $atendimentoSeguro):

                $class =($aux % 2 == 0) ? "even" : "odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$this->view->data($atendimentoSeguro->datainicio)}</td>";
                $html .= "<td>{$this->view->data($atendimentoSeguro->datafim)}</td>";
                $html .= "<td>{$atendimentoSeguro->descricao}</td>";
                $html .= "<td>";
                $html .= "<a data-codigo='{$atendimentoSeguro->codigo}' class='mws-ic-16 ic-edit editRowSeguro' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$atendimentoSeguro->codigo}' class='mws-ic-16 ic-cross deleteRowSeguro' title=''>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='4' style='text-align:center;'>";
            $html.= "Nenhum seguro adicionado no atendimento até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;

        return $html;
    }

    public function gridSeguroAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $post = $this->getRequest()->getPost();

        $html = $this->htmlSeguro($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("html" => $html));
    }

    public function delSeguroAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["atendimento"]) || empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Parâmetros inválidos", "erro" => 1));
            return;
        }

        //Deletando a seguro do atendimento
        $tblAtendimentoSeguro = new Model_AtendimentoSeguro();
        $tblAtendimentoSeguro->delete("codigo = {$post["codigo"]}");
        $html = $this->htmlSeguro($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    public function salvarTipoPacoteAction() {

        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "Tipo de pacote adicionada com sucesso!";

        $post = $this->getRequest()->getPost();

        $tbl = new Model_TipoPacote();
        $tbl->getAdapter()->beginTransaction();


        $post["nome"] = trim($post["nome"]);
        if(empty($post["nome"])) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Preencha o campo nome"));
            return;
        }

        //Verificando se o filtro já foi cadastrado
        $query = "nome = '{$post["nome"]}'";
        if(!empty($post["codigo"])) {
            $query = " AND codigo <> {$post["codigo"]}";
        }
        $result = $tbl->fetchRow($query);
        if($result) {
            echo Zend_Json_Encoder::encode(array("erro" => 1, "msg" => "Este tipo de pacote já se encontra cadastrado em nossa base de dados"));
            return;
        }

        $codigo = null;

        if($erro == 0) {
            if(empty($post["codigo"])) {
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

        echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg" => $msg, "codigo" => $codigo, "option" => $option));
    }

    public function buscaPacoteAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;
        $pais = null;

        $post = $this->getRequest()->getPost();

        if(!empty($post["codigo"])) {
            $tblAtendimentoPacote = new Model_AtendimentoPacote();
            $result = $tblAtendimentoPacote->find($post["codigo"])->current();
            if($result) {
                $tipoPacote = $result->findParentRow("Model_TipoPacote");
                $tipo = $tipoPacote->nome;

                $html = $result->toArray();
                if(!empty($html["data"])) {
                    $html["data"] = $this->view->data($html["data"]);
                }                
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html, "tipo" => $tipo));
    }

    public function addPacoteAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["tipo_pacote"]) || empty($post["data"]) || empty($post["destino"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Todos campos são obrigatórios", "erro" => 1));
            return;
        }

        //Inserindo o transporte atendimento
        $tblAtendimentoPacote = new Model_AtendimentoPacote();

        if(!empty($post["data"])):
            $data = new Zend_Date($post["data"]);
            $post["data"] = $data->get("WWW");
        else:
            unset($post["data"]);
        endif;

        if(empty($post["codigo"])) {
            $tblAtendimentoPacote->insert($post);
        } else {
            $tblAtendimentoPacote->update($post, "codigo = {$post["codigo"]}");
        }

        $html = $this->htmlPacote($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    private function htmlPacote($atendimento) {

        $html = null;

        $tblAtendimentoPacote = new Model_AtendimentoPacote();
        $pacotes = $tblAtendimentoPacote->fetchAll("atendimento = $atendimento", "codigo DESC");

        if(count($pacotes) > 0):
            $aux = 0;
            foreach($pacotes as $atendimentoPacote):
                $tipoPacote = $atendimentoPacote->findParentRow("Model_TipoPacote");

                $class =($aux % 2 == 0) ? "even" : "odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$tipoPacote->nome}</td>";                
                $html .= "<td>{$this->view->data($atendimentoPacote->data)}</td>";
                $html .= "<td>{$atendimentoPacote->destino}</td>";
                $html .= "<td>";
                $html .= "<a data-codigo='{$atendimentoPacote->codigo}' class='mws-ic-16 ic-edit editRowPacote' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$atendimentoPacote->codigo}' class='mws-ic-16 ic-cross deleteRowPacote' title=''>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='4' style='text-align:center;'>";
            $html.= "Nenhuma pacote adicionada no atendimento até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;

        return $html;
    }

    public function gridPacoteAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $post = $this->getRequest()->getPost();

        $html = $this->htmlPacote($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("html" => $html));
    }

    public function delPacoteAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["atendimento"]) || empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Parâmetros inválidos", "erro" => 1));
            return;
        }

        //Deletando a pacote do atendimento
        $tblAtendimentoPacote = new Model_AtendimentoPacote();
        $tblAtendimentoPacote->delete("codigo = {$post["codigo"]}");
        $html = $this->htmlPacote($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    
    
    
    public function buscaHistoricoAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;
        $pais = null;

        $post = $this->getRequest()->getPost();

        if(!empty($post["codigo"])) {
            $tblAtendimentoHistorico = new Model_AtendimentoHistorico();
            $result = $tblAtendimentoHistorico->find($post["codigo"])->current();
            if($result) {

                $html = $result->toArray();
                if(!empty($html["dataretorno"])) {
                    $html["dataretorno"] = $this->view->data($html["dataretorno"]);
                }               
            }
        }

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }

    private function htmlHistorico($atendimento) {

        $html = null;

        $tblAtendimentoHistorico = new Model_AtendimentoHistorico();
        $Historicos = $tblAtendimentoHistorico->fetchAll("atendimento = $atendimento", "dataretorno DESC");

        if(count($Historicos) > 0):
            $aux = 0;
            foreach($Historicos as $atendimentoHistorico):

                $class =($aux % 2 == 0) ? "even" : "odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$atendimentoHistorico->usuario}</td>";
                $html .= "<td>{$atendimentoHistorico->observacao}</td>";
                $html .= "<td>{$this->view->data($atendimentoHistorico->dataretorno)}</td>";
                $html .= "<td>{$this->view->data($atendimentoHistorico->data, "comHorario")}</td>";
                $html .= "<td>";
                $html .= "<a data-codigo='{$atendimentoHistorico->codigo}' class='mws-ic-16 ic-edit editRowHistorico' title=''>&nbsp;</a>";
                $html .= "<a data-codigo='{$atendimentoHistorico->codigo}' class='mws-ic-16 ic-cross deleteRowHistorico' title=''>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='5' style='text-align:center;'>";
            $html.= "Nenhum histórico adicionado até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;

        return $html;
    }

    public function gridHistoricoAction() {
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $post = $this->getRequest()->getPost();

        $html = $this->htmlHistorico($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("html" => $html));
    }
    
    
    public function addHistoricoAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["dataretorno"]) || empty($post["observacao"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Verifique os campos obrigatórios(*)", "erro" => 1));
            return;
        }

        $tblAtendimentoHistorico = new Model_AtendimentoHistorico();

        if(!empty($post["dataretorno"])):
            $data = new Zend_Date($post["dataretorno"]);
            $post["dataretorno"] = $data->get("WWW");
        else:
            unset($post["dataretorno"]);
        endif;


        if(empty($post["codigo"])) {
            $tblAtendimentoHistorico->insert($post);
        } else {
            $tblAtendimentoHistorico->update($post, "codigo = {$post["codigo"]}");
        }

        $html = $this->htmlHistorico($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }
    
     public function delHistoricoAction() {

        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $erro = 0;
        $msg = "";
        $html = null;

        $post = $this->getRequest()->getPost();

        if(empty($post["atendimento"]) || empty($post["codigo"])) {
            echo Zend_Json_Encoder::encode(array("msg" => "Parâmetros inválidos", "erro" => 1));
            return;
        }

        //Deletando a seguro do atendimento
        $tblAtendimentoHistorico = new Model_AtendimentoHistorico();
        $tblAtendimentoHistorico->delete("codigo = {$post["codigo"]}");
        $html = $this->htmlHistorico($post["atendimento"]);

        echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "html" => $html));
    }
    
    public function relatorioAction() {
		Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$msg = "";
		$erro = 0;
		
		// erar matriz de dados
		$dados = array();
		
		$tbl = new Model_AtendimentoHistorico();
		
		$query = $tbl->select()->setIntegrityCheck(false);
		
        if($this->getRequest()->isPost()) {

            //Dados da busca
            $post = $this->getRequest()->getPost();

            //Termo da ordenação
            $ordem = $this->_getParam("ordenacao");
            $ordenacao = !empty($ordem) ? $ordem : $this->ordenacao;

            $cliente = $this->_getParam("cliente");
            if(!empty($cliente)) {
                $query->where("atendimento IN (SELECT codigo FROM atendimento WHERE cliente IN (SELECT codigo FROM cliente WHERE lower(nome) like '%" . $cliente . "%'))");                
                $limit = false;
            }

            $usuario = $this->_getParam("usuario");
            if(!empty($usuario)) {
                $query->where("usuario = '$usuario' OR usuario IN (SELECT usuario FROM atendimento WHERE usuario = '$usuario')");
                $this->view->usuario = $usuario;
                $limit = false;
            }

            $dataAtendimentoInicio = $this->_getParam("dataAtendimentoInicio");
            if(!empty($dataAtendimentoInicio)) {
                $date = new Zend_Date($dataAtendimentoInicio);
                $data = $date->get("WWW");
                $query->where("DATE(data) >= '$data'");                
            }

            $dataAtendimentoFim = $this->_getParam("dataAtendimentoFim");
            if(!empty($dataAtendimentoFim)) {
                $date = new Zend_Date($dataAtendimentoFim);
                $data = $date->get("WWW");
                $query->where("DATE(data) <= '$data'");                
            }                       
        }
		        
		$query->order("codigo ASC");
		
		$dados = $tbl->fetchAll($query);
		
		// seta as propriedades
		$this->separador = ";";
		$this->path = getcwd() . "/images/default/tmp/";
		$this->arquivo = "atendimentos_".date('h_i_s');
		
		// gera cabeçalho
		$cabecalho = array("Cliente", "Atendente", "Data", "Data Retorno", "Status", "Observação");
		
		// gera string de cabeçalho
		$colunas = "";
		foreach($cabecalho as $coluna) {
			$coluna = utf8_decode($coluna);
			if($colunas == "") {
				$colunas .= $coluna;
			} else {
				$colunas .= $this->separador . $coluna;
			}
		}
		
		$saida[] = $colunas;
		
		// gera string do corpo do arquivo
		foreach($dados as $historico) {

			$statusAtendimento = $historico->findParentRow("Model_StatusAtendimento");
			$atendimento = $historico->findParentRow("Model_Atendimento");
			$cliente = $atendimento->findParentRow("Model_Cliente");
			
			$colunaValues["cliente"] = $cliente->nome;
			$colunaValues["atendente"] = $historico->usuario;
			$colunaValues["data"] = $historico->data;
			$colunaValues["dataretorno"] = $historico->dataretorno;
			$colunaValues["status"] = $statusAtendimento->nome;
			$colunaValues["observacao"] = $historico->observacao;
			$colunaValues["observacao"] = str_replace("\r", " ", $colunaValues["observacao"]);
			$colunaValues["observacao"] = str_replace("\n", " ", $colunaValues["observacao"]);
												
			// gega as variaveis do array
			$colunasDados = "";
			foreach($colunaValues as $coluna) {				
				$colunasDados .= utf8_decode($coluna).$this->separador;
			}
			$saida[] = $colunasDados;
		}
				
		$arquivoRetorno = null;
		
		// verifica se alguma linha foi inserida
		if(count($saida) > 1) {
			// conta o corpo do CSV
			$corpo = implode("\n", $saida);
			$corpo = str_replace(";;", ";", $corpo);
			
			// abre um arquivo para escrita, se o arquivo não existir ele tenta
			// criar
			$fp = fopen($this->path . $this->arquivo . ".csv", "w");
			if($fp != NULL) {
				// escreve no arquivo
				fwrite($fp, $corpo);
				// fecha o arquivo
				fclose($fp);
				$arquivoRetorno = "/images/default/tmp/" . $this->arquivo . ".csv";
			}
		} else {
			$msg = 'Nenhum registro encontrado';
			$erro = 1;
		}
		
		echo Zend_Json_Encoder::encode(array("msg" => $msg, "erro" => $erro, "arquivo" => $arquivoRetorno));
	}

   
}
