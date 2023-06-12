<?php

/**
 * Blocos helper contem todas os renders de blocos que sao utilizados no site
 *
 * @uses viewHelper Zend_View_Helper
 * @version 1.0.0 - 21/10/2010
 */
class Projeto_View_Helper_Blocos extends Zend_View_Helper_Abstract {
	
	/**
	 * View Padrao
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 * Constructor do Helper
	 */
	public function blocos() {		
		return $this;
	}
	
	public function produto($vendaProduto) {
	    $produto = null;
	    if(!empty($vendaProduto)):
	        if($vendaProduto->hospedagem > 0):
	            $produto = $vendaProduto->findParentRow("Model_Hospedagem");	            
	        elseif ($vendaProduto->pacote > 0):
	            $produto = $vendaProduto->findParentRow("Model_Pacote");	            
	        elseif ($vendaProduto->servico > 0):
	            $produto = $vendaProduto->findParentRow("Model_Servico");
	        elseif ($vendaProduto->transporte > 0):
	            $produto = $vendaProduto->findParentRow("Model_Transporte");
	        endif;	        
	    endif;
		return $produto;	
	}
	
}
