<?php
require_once 'Zend/Loader/PluginLoader.php';
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/**
 * Acesso Action Helper
 *
 */
class Projeto_Controller_Action_Helper_Acesso extends Zend_Controller_Action_Helper_Abstract {

	/**
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

		$this->pluginLoader = new Zend_Loader_PluginLoader ();
	}

	/**
	 * Strategy pattern: call helper as broker method
	 */
	public function direct() {

		return $this;
	}

	public function verificarLogin($request, &$view) {

		$s = new Zend_Session_Namespace("USUARIO");
		
		$cName = $request->getControllerName ();
		$aName = $request->getActionName ();
		$mName = $request->getModuleName ();
		
		$livre = array();
		
		if ($mName != "default") {
			
			if (!in_array($mName . "-" . $cName . "-" . $aName, $livre)) {
				
				$s = new Zend_Session_Namespace("USUARIO");
				$usr = $s->usuario;
				
				if (empty($usr)) {					
					$redirector = new Zend_Controller_Action_Helper_Redirector();
					$redirector->gotoUrlAndExit($view->url(array('module' => 'admin', 'controller' => 'index', 'action' => 'login')));				
				} else {
					$view->usuario = $usr;
				}
			
			}
		}
			
	}

}
