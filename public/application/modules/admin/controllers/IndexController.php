<?php
require_once 'AdminController.php';

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @version 1.0.0 - 28/06/2012
*/

class Admin_IndexController extends AdminController {
    
	/**
	 * IndexAction -
	 */
	public function indexAction() {
		$host = $_SERVER["SERVER_NAME"];
		$aux = explode(".", $host);
		$dbName = str_replace("http://", "", $aux[0]);
		if($dbName == "tropicalturismo"){
			$this->_redirect("/admin/atendimento");
		}		
		
	    $this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/flot/jquery.flot.js'));
	    $this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/flot/jquery.flot.pie.min.js'));
	    $this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/flot/jquery.flot.stack.min.js'));
	    $this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/flot/jquery.flot.resize.min.js'));	    
	    $this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/plugins/flot/jquery.flot.categories.js'));
	    
	    //Informações do servidor
	    $this->view->totaldiskspace = disk_total_space ('/') / 1073741824;
	    $this->view->freediskspace = disk_free_space ('/') / 1073741824;
	    $this->view->totalusagesize = $this->view->totaldiskspace - $this->view->freediskspace;
	    
	    //Total de atendimentos
	    $tblAtendimento = new Model_Atendimento();
	    $select = $tblAtendimento->select();
	    $querie = $select->from("atendimento",array("total"=>"COUNT(*)"));
	    $this->view->totalAtendimentos = $tblAtendimento->fetchRow($querie);
	    
	    //Total de vendas
	    $tblVenda = new Model_Venda();
	    $select = $tblVenda->select();
	    $querie = $select->from("venda",array("total"=>"COUNT(*)"));
	    $this->view->totalVendas = $tblVenda->fetchRow($querie);
	    
	    //Total de clientes
	    $tblCliente = new Model_Cliente();
	    $select = $tblCliente->select();
	    $querie = $select->from("cliente",array("total"=>"COUNT(*)"));
	    $this->view->totalClientes = $tblCliente->fetchRow($querie);
	    
	    //Total de pacotes
	    $tblPacote = new Model_Pacote();
	    $select = $tblPacote->select();
	    $querie = $select->from("pacote",array("total"=>"COUNT(*)"));
	    $this->view->totalPacotes = $tblPacote->fetchRow($querie);
	    
	    //Atendimentos para o gráfico
	    $tblViewAtendimentos = new Model_ViewAtendimentos();
	    $this->view->atendimentos = $tblViewAtendimentos->fetchAll(null, array('ano ASC', 'data ASC'));
	    
	    //Vendas para o gráfico
	    $tblViewVendas = new Model_ViewVendas();
	    $this->view->vendas = $tblViewVendas->fetchAll(null, array('ano ASC', 'data ASC'));

	    //Buscando moedas para a cotação
	    $tblMoeda = new Model_Moeda();
	    $this->view->moedas = $tblMoeda->fetchAll("codigo <> 1");
	    
	    $data = date('Y-m-d');
	    $tblCotacao = new Model_Cotacao();
	    $result = $tblCotacao->fetchRow("moeda = 2 AND data = '$data'");
	    if($result){
	        $this->view->dolar = $result->valor;    
	    }
	    $result = $tblCotacao->fetchRow("moeda = 3 AND data = '$data'");
	    if($result){
	        $this->view->euro = $result->valor;    
	    }
	}
	
	/**
	 * Retorna dados da localidade do endereço
	 */
	public function buscaEnderecoAction() {
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout()->disableLayout();
	
	    $post = $this->getRequest()->getPost();
	
	    $localidade = array();
	    $cidade = null;
	    if(!empty($post["cep"])){
	        $tblLocalidade = new Model_Localidade();
	        $result = $tblLocalidade->fetchRow("cep = '{$post["cep"]}'");
	
	        if($result){
	            $localidade = $result->toArray();
	        } else {
	            $localidade = $this->buscaCep($post["cep"]);
	            if($localidade["resultado"]){
	                unset($localidade["resultado"]);
	                unset($localidade["resultado_txt"]);
	                $localidade["cep"] = $post["cep"];
	                $localidade["cidade"] = utf8_encode($localidade["cidade"]);
	                $localidade["bairro"] = utf8_encode($localidade["bairro"]);
	                $localidade["tipo_logradouro"] = utf8_encode($localidade["tipo_logradouro"]);
	                $localidade["logradouro"] = utf8_encode($localidade["logradouro"]);
	                $tblLocalidade->insert($localidade);
	            }
	        }
	
	        if(!empty($localidade["cidade"])){
	            $tblCidade = new Model_Cidade;
	            $localidade["cidade"] = mb_strtolower($localidade["cidade"],"UTF-8");
	            $result = $tblCidade->fetchRow("lower(nome) = '{$localidade["cidade"]}'");
	            if($result){
	                $cidade = $result->codigo;
	            }
	        }
	
	    }
	
	    echo Zend_Json_Encoder::encode(array('localidade'=>$localidade, 'cidade'=>$cidade));
	}
	
	//Lista e envia as cidades
	public function obterCidadesAction() {
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	    	    
	    $uf = $this->_getParam('estado');
	    $selecionar = $this->_getParam('selecionar');
	    if(!empty($uf)) {
	        $tblCidade = new Model_Cidade();
	        $querie = "estado in (SELECT codigo FROM estado WHERE sigla = '{$uf}')";
	        $dLocs = $tblCidade->fetchAll($querie,"nome asc");
	
	        $res = "";
	        if($dLocs->count() > 0) {
	            foreach($dLocs as $d) {
	                $s = "";
	                if($d['codigo'] == $selecionar) {
	                    $s = "selected='selected'";
	                } else if(empty($selecionar) && $d['codigo'] == 2174) {
	                    $s = "selected='selected'";
	                } else {
	                    $s = "";
	                }
	                $res.= "<option ".$s." value='".$d['codigo']."'>".$d['nome']."</option>";
	            }
	        }
	    }
	    else {
	        $res = "<option value=''>Selecione um estado</option>";
	    }
	
	    echo $res;
	
	}
	
	//Lista e envia os transportes
	public function obterTransportesAction() {
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $categoriaTransporte = $this->_getParam('categoria');
	    $selecionar = $this->_getParam('selecionar');
	    if(!empty($categoriaTransporte)) {
	        $tblTransporte = new Model_Transporte();
	        $querie = "categoriatransporte = '{$categoriaTransporte}'";
	        $dLocs = $tblTransporte->fetchAll($querie,"nome asc");
	
	        $res = "";
	        if($dLocs->count() > 0) {
	            foreach($dLocs as $d) {
	                $s = "";
	                if($d['codigo'] == $selecionar) {
	                    $s = "selected='selected'";	               
	                } else {
	                    $s = "";
	                }
	                $res.= "<option ".$s." value='".$d['codigo']."'>".$d['nome']."</option>";
	            }
	        }
	    }
	    else {
	        $res = "<option value=''>Transporte</option>";
	    }
	
	    echo $res;	
	}
	
	//Lista e envia as hospedagens
	public function obterHospedagensAction() {
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $pais = $this->_getParam('pais');
	    $selecionar = $this->_getParam('selecionar');
	    if(!empty($pais)) {
	        $tblHospedagem = new Model_Hospedagem();
	        $querie = "pais = '{$pais}'";
	        $dLocs = $tblHospedagem->fetchAll($querie,"nome asc");
	
	        $res = "<option value=''>Selecione</option>";
	        if($dLocs->count() > 0) {
	            foreach($dLocs as $d) {
	                $s = "";
	                if($d['codigo'] == $selecionar) {
	                    $s = "selected='selected'";
	                } else {
	                    $s = "";
	                }
	                $res.= "<option ".$s." value='".$d['codigo']."'>".$d['nome']."</option>";
	            }
	        }
	    }
	    else {
	        $res = "<option value=''>Selecione um país</option>";
	    }
	
	    echo $res;
	
	}
	
	//Lista e envia os pacotes
	public function obterPacotesAction() {
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $statusPacote = $this->_getParam('statuspacote');
	    $selecionar = $this->_getParam('selecionar');
	    
	    $tblPacote = new Model_Pacote();
	    $querie = "codigo IS NOT NULL";
	    if(!empty($statusPacote)){
	    	if($statusPacote == "aberto"){
	    		$querie.= " AND datasaida >= CURRENT_DATE()";	
	    	} else {
	    		$querie.= " AND datasaida <= CURRENT_DATE()";
	    	}
	    }	    
	    $dLocs = $tblPacote->fetchAll($querie,"descricao asc");
	    
	    $res = "<option value=''>Selecione</option>";
	    if($dLocs->count() > 0) {
	    	foreach($dLocs as $d) {
	    		$s = "";
	    		if($d['codigo'] == $selecionar) {
	    			$s = "selected='selected'";
	    		} else {
	    			$s = "";
	    		}
	    		$res.= "<option ".$s." value='".$d['codigo']."'>".$d['descricao']."</option>";
	    	}
	    }	
	    echo $res;	
	}
	
	public function salvarCotacaoAction() {
	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $erro = 0;
	    $msg = "";
	    $html = null;
	     
	    $post = $this->getRequest()->getPost();	    
	    $moedas = $post["moeda"];
	    
	    $tblCotacao = new Model_Cotacao();
	    
	    $date = new Zend_Date($post["data"]);
	    $data = $date->get("WWW");
	    	    
	    foreach($moedas as $key=>$moeda){
	        $cotacao["moeda"] = $moeda;    
	        $cotacao["valor"] = $this->view->MoedaParaNumero($post["valor"][$key]);  
	        $cotacao["data"] = $data;
	        $cotacao["usuario"] = $this->view->usuario["usuario"];

	        //Verificando se a data já está cadastrada na data de hoje
	        $result = $tblCotacao->fetchRow("data = '$data' AND moeda = $moeda");
	        if($result){
	            $tblCotacao->update($cotacao, "codigo = $result->codigo");
	        } else {
	            $tblCotacao->insert($cotacao);
	        }
	    }
	     	
	    echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));
	}
	
	
}

