<?php

/**
 * Utils Action Helper - Action Helper responsável por algumas funcionalidades úteis no portal
 *
 * @uses actionHelper Projeto_Controller_Action_Helper
 * @version 1.0.0 - 26/04/2012 
 */
class Projeto_Controller_Action_Helper_Utils extends Zend_Controller_Action_Helper_Abstract {
	
	/**
	 * Instancia do Plugin Loader
	 *
	 * @var Zend_Loader_PluginLoader
	 */
	public $pluginLoader;

	/**
	 * Constructor: initialize plugin loader
	 *
	 * @return void
	 */
	public function __construct() {
		$this->pluginLoader = new Zend_Loader_PluginLoader();	
	}

	/**
	 * Strategy pattern: call helper as broker method
	 */
	public function direct() {
		return $this;	
	}

	/**
	 * Metodo que adicona os arquivos CSS/JS básicos para a requisição.
	 *
	 * @param Zend_View $view        	
	 */
	public function addBasicFilesOfRequestToView(&$view, $inibeCache = false) {
				
		// Paths básicos para os arquivos de JS e CSS
		$pathStylesheet = SITE_PATH.'/css';
		$pathScript = SITE_PATH.'/js';
		
		
		// Incrementa cada path com a expressao
		// <path>/<module>/<controller>/<action>.(css,js)
		$pathStylesheet .= '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$this->getRequest()->getActionName().'.css';		
		$pathScript .= '/'.$this->getRequest()->getModuleName().'/'.$this->getRequest()->getControllerName().'/'.$this->getRequest()->getActionName().'.js';

		// Verifica se o path indicado é realmente um arquivo de estilos válido
		if (is_file ($pathStylesheet) && is_readable ($pathStylesheet)) {
			
			// Recupera o caminho relativo do arquivo
			$relativeStylesheet = str_replace (SITE_PATH, $view->baseUrl(), $pathStylesheet);
			
			// Adiciona o arquivo a lista de styles do site
			$view->headLink ()->appendStylesheet ($relativeStylesheet.(($inibeCache) ? '?'.mt_rand (0, 99999) : ''));
		}
		
		// Verifica se o path indicado é realmente um arquivo de scripts válido
		if (is_file ($pathScript) && is_readable ($pathScript)) {
			
			// Recupera o caminho relativo do arquivo
			$relativeScript = str_replace (SITE_PATH, $view->baseUrl(), $pathScript);
			
			// Adiciona o arquivo a lista de styles do site
			$view->headScript()->appendFile ($relativeScript.(($inibeCache) ? '?'.mt_rand (0, 99999) : ''));
		}	
	}
	
	/**
	 * Retorna o total a receber de acordo com a venda
	 * 
	 */
	public function totalAReceber($venda){	   
        $tblVendaReceber = new Model_VendaAReceber();
	    $result =  $tblVendaReceber->fetchAll("venda = $venda");
	    
	    $totalAReceber = 0;	    
	    if(count($result) > 0){
	        foreach($result as $dado):
	            $totalAReceber += $dado["valor"];    	               
	        endforeach;    
	    }
	    	    
	    return $totalAReceber;
	}
	
	/**
	 * Retorna o total recebido com a venda
	 * 
	 */
	public function totalRecebido($venda){	   
        $tblVendaReceber = new Model_VendaAReceber();
	    $result =  $tblVendaReceber->fetchAll("venda = $venda");
	    
	    $totalRecebido = 0;
	    if(count($result) > 0){
	        foreach($result as $dado):
	            $totalRecebido += $dado["valorpago"];    
	        endforeach;    
	    }
	    
	    return $totalRecebido;
	}

}
