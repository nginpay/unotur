<?php
require_once 'SacController.php';

/**
 * Sac_IndexController - Controller responsavel por
 *
 * @version 1.0.0 - 28/06/2012
*/

class Sac_IndexController extends SacController {
    
	/**
	 * IndexAction -
	 */
	public function indexAction() {
	    
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

