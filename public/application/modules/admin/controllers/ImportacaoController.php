<?php

require_once 'AdminController.php';

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @author Vilmar
 * @version 1.0.0 - 28/06/2012
 */
class Admin_ImportacaoController extends AdminController {

	/**
	 * IndexAction -
	 */
	public function indexAction() {
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	    
	    //$this->importarClientes();	    
	    $this->importarFornecedores();	    
	}
		
	private function importarFornecedores() {
	    	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    require_once 'PhpExcel/PHPExcel.php';
	    set_time_limit(0);
	    	
	    $objReader = new PHPExcel_Reader_Excel5();
	    $objReader->setReadDataOnly(true);
	    $arquivo = getcwd()."/images/default/planilha.xls";
	    $objPHPExcel = $objReader->load($arquivo);
	    $objPHPExcel->setActiveSheetIndex(0);
	    	
	    //$tblHospedagem = new Model_Hospedagem();
	    $tblServico = new Model_Servico();
	    
	    	
	    $cliente = array();
	    	
	    $insert = 0;
	    $update = 0;
	    	
	    // navegar na linha
	    for($linha=2; $linha<=2000; $linha++){
	        // navegar nas colunas da respectiva linha
	        for($coluna=0; $coluna<=16; $coluna++){
	             
	            //Clientes
	            switch ($coluna):    	            
    	            case 1:
    	                $cliente["nome"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                $cliente["nome"] = str_replace("'", "", $cliente["nome"]);
    	                break;    	          
    	            case 2:
    	                $cliente["razaosocial"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                $cliente["razaosocial"] = str_replace("'", "", $cliente["razaosocial"]);
    	                break;    	              	             	          
    	            case 3:
    	                $cliente["tipo"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                
    	                break;    	              	             	          
    	            case 7:
    	                $cliente["cpf"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                $cliente["cnpj"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                
    	                break;
	                case 8:
	                    $tipo = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
	                    if(!empty($tipo)):
	                        $cliente["tipopessoa"] = strtoupper(substr($tipo, 0, 1));
	                    endif;
                        break;
	                case 9:
	                    $data = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue();
	                    if(!empty($data)):
	                        $date = new Zend_Date($data);
	                        $cliente["datacadastro"] = $date->get("WWW");
	                    endif;
                        break;
                    case 11:
                        $cliente["cidade"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
//                         if(!empty($cidade)):
//                            $cliente["cidade"] = $this->buscaCidade($cidade);
//                         endif;                        
                        break;
                    case 12:
                        $cliente["telefonefixo"] = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());                                             
                        break;
                    case 16:
                        $cliente["celular"] = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());                                             
                        break;
                    
	             endswitch;
	             
	        }

	        $cliente["status"] = "ativo";

	        if(!empty($cliente["nome"])):
    	        //Cadastrando $cliente
    	        $tblServico->insert($cliente);
    	            $insert++;
	        endif;
		
	    }
	    	
	    echo $insert." registros inseridos <br/>";
	    echo $update." registros atualizados";
	    	
	    exit;
	
	}
	private function importarClientes() {
	    	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    require_once 'PhpExcel/PHPExcel.php';
	    set_time_limit(0);
	    	
	    $objReader = new PHPExcel_Reader_Excel5();
	    $objReader->setReadDataOnly(true);
	    $arquivo = getcwd()."/images/default/planilha.xls";
	    $objPHPExcel = $objReader->load($arquivo);
	    $objPHPExcel->setActiveSheetIndex(0);
	    	
	    $tblCliente = new Model_Cliente();
	    	
	    $cliente = array();
	    	
	    $insert = 0;
	    $update = 0;
	    	
	    // navegar na linha
	    for($linha=2; $linha<=2000; $linha++){
	        // navegar nas colunas da respectiva linha
	        for($coluna=0; $coluna<=18; $coluna++){
	             
	            //Clientes
	            switch ($coluna):
    	            case 0:
    	                $cliente["codigo"] = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());	                    
    	                break;
    	            case 1:
    	                $cliente["nome"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                $cliente["nome"] = str_replace("'", "", $cliente["nome"]);
    	                break;    	          
    	            case 2:
    	                $cliente["razaosocial"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                $cliente["razaosocial"] = str_replace("'", "", $cliente["razaosocial"]);
    	                break;    	              	             	          
    	            case 7:
    	                $cliente["cpf"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                $cliente["cnpj"] = ($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
    	                
    	                break;
	                case 8:
	                    $tipo = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
	                    if(!empty($tipo)):
	                        $cliente["tipopessoa"] = strtoupper(substr($tipo, 0, 1));
	                    endif;
                        break;
	                case 9:
	                    $data = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue();
	                    if(!empty($data)):
	                        $date = new Zend_Date($data);
	                        $cliente["datacadastro"] = $date->get("WWW");
	                    endif;
                        break;
                    case 11:
                        $cidade = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
                        if(!empty($cidade)):
                           $cliente["cidade"] = $this->buscaCidade($cidade);
                        endif;                        
                        break;
                    case 12:
                        $cliente["telefonefixo"] = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());                                             
                        break;
                    case 14:
                        $rg = utf8_decode($objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue());
                        $cliente["rg"] = preg_replace("/[^0-9]/", "", $rg);
                        break;
                    case 17:
                        $data = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue();
                        if(!empty($data)):
                            $date = new Zend_Date($data);
                            $cliente["datanascimento"] = $date->get("WWW");
                        endif;
                        break;
                    case 18:
                        $cliente["email"] = $objPHPExcel->getActiveSheet()->getCellByColumnAndRow($coluna, $linha)->getValue();                       
                        break;
	             endswitch;
	             
	        }

	        $cliente["status"] = "ativo";

	        if(!empty($cliente["nome"])):
    	        //Cadastrando $cliente
    	        $result = $tblCliente->fetchRow("codigo = '{$cliente["codigo"]}'");
    	        if(!$result && count($cliente)>0){
    	            $tblCliente->insert($cliente);
    	            $insert++;
    	        } else {
    	            $tblCliente->update($cliente, "codigo = '{$cliente["codigo"]}'");
    	            $update++;
    	        }
	        endif;
		
	    }
	    	
	    echo $insert." registros inseridos <br/>";
	    echo $update." registros atualizados";
	    	
	    exit;
	
	}
	
	private function buscaCidade($nome){
	    $tblCidade = new Model_Cidade();
	    $querie = "`nome` = '".$nome."'";
	    $cidade = $tblCidade->fetchRow($querie);
	     
	    if($cidade){
	        return $cidade->codigo;
	    }
	}
		
	
}
