<?php
/**
 * Plugin de Autenticação - Plugin responsável pela autenticação dos usuários que acessam o portal
 * 
 * @version 1.0.0 - 23/03/2012
 *
 */
class Plugin_Autenticacao extends Zend_Controller_Plugin_Abstract {

	/**
	 * Acls Globais
	 *
	 * @var Zend_Acl
	 */
	private $acl = null;

	/**
	 * Sessao de Autenticacao
	 *
	 * @var Zend_Auth
	 */
	private $auth = null;

	/**
	 * Construtor do Plugin
	 *
	 * @param $acl Zend_Acl        	
	 * @param $auth Zend_Auth        	
	 */
	public function __construct($dbAdapter) {
			
		// Carrega todas as ACl's
		$this->acl = new Acl_Global($dbAdapter);
		
		// Recupera a informacao de autenticacao
		$this->auth = Zend_Auth::getInstance();
		
		// Adiciona o role padrao de visitante
		if(! $this->auth->hasIdentity()) {
			$authStorage = $this->auth->getStorage();
			$authStorage->write(array('usuario' => 'visitante', 'role' => 'visitante'));
		}
	
	}

	/**
	 *(non-PHPdoc)
	 *
	 * @see Zend_Controller_Plugin_Abstract::preDispatch()
	 */
	public function preDispatch(Zend_Controller_Request_Abstract $request) {

		/**
		 * Recupera a identidade do usuario logado
		 *
		 * @var Array
		 */
		$role = $this->auth->getIdentity();
		
		/**
		 * Recursos que se deseja acesso
		 *
		 * @var String
		 */
		$resource = $this->getRequest()->getModuleName();
		
		/**
		 * Ação permitida dentro de um resource
		 *
		 * @var String
		 */
		$action = ($this->getRequest()->getModuleName() != 'admin' && $this->getRequest()->getModuleName() != 'sac') ? null : $this->getRequest()->getControllerName();
		
		// Verificação condicional para os controllers e actions de upload
		if(!($request->getActionName() == 'upload' || $request->getControllerName() == 'upload')) {			
			// Verifica se ha lixo na autenticacao
			if(!is_array($role)) {
				
				// Parametros
				$params = array();
				
				// Destroi qualquer instancia de autenticacao
				$this->auth->clearIdentity();				
				// Altera a rota de destino
				$request->setModuleName('admin')->setControllerName('login')->setActionName('index');
				return;
			
			}
            
			// Verifica se o recurso existe e se o usuario logado tem acesso			
			if(!$this->acl->has($resource) || !$this->acl->isAllowed($role['usuario'], $resource, $action)) {
				
				// Parametros
				$params = array();
				
				// Redireciona para o controller de login
				if($role['usuario'] != 'visitante') {
					$params['erro'] = 'Você não possui permissão de acesso a este recurso.';
					$request->setModuleName('admin')->setControllerName('index')->setActionName('index')->setParams($params);
				} else {
				    if($this->getRequest()->getModuleName() == "sac"){
				        $request->setModuleName('sac')->setControllerName('login')->setActionName('index')->setParams($params);
				    } else {
				        $request->setModuleName('admin')->setControllerName('login')->setActionName('index')->setParams($params);
				    }
				    
				}
	            
				
								
				return;
			
			}
		
		}
	
	}

}

?>