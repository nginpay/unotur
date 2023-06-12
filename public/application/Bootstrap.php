<?php
/**
* Bootstrap Padrao
*
* @version v1-09.11.2011
*/
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {
	
    protected function _initCache() {
		$cache = Zend_Cache::factory('Core', 'File', array(
				'lifetime' => 7200,
				'automatic_serialization' => true
		), array(
				'cache_dir' => getcwd() . "/data/cache"
		));
		Zend_Db_Table::setDefaultMetadataCache($cache);
		Zend_Date::setOptions(array(
		'cache' => $cache
		));	
	}
    
	private function getDb(){
	    $host = $_SERVER["SERVER_NAME"];
	    $aux = explode(".unotur.com.br", $host);
	    $dbName = str_replace("http://", "", $aux[0]);
	    return $dbName;
	}
	
	protected function _initDb() {       
        $options = array(
                Zend_Db::ALLOW_SERIALIZATION => false
        );
                
        $params = array(
                'host'           => 'localhost',
                'username'       => 'root',
                'password'       => 'Estu@3095',
                'dbname'         => $this->getDb(),
                'adapter'        => 'Pdo_Mysql',
                'driver_options' => array(
                        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'
                ),
                'options'        => $options
        );
                
        try {
            $db = Zend_Db::factory('Pdo_Mysql', $params);
            $db->getConnection();            
        } catch (Zend_Db_Adapter_Exception $e) {
        
        }
        Zend_Db_Table::setDefaultAdapter($db);
                
    }
    	
	protected function _initAutoload() {
	    // Inicializa o Resource do DB
		$this->bootstrap('db');
		
		// Recupera o Resource do DB
		$dbAdapter = $this->getResource('db');
				
		// Adicionar as configuracoes de Resource para a Aplicacao
		$resourceLoader = new Zend_Loader_Autoloader_Resource(array('basePath' => APPLICATION_PATH, 'namespace' => ''));
		$resourceLoader->addResourceType('model', 'models/', 'Model');
		$resourceLoader->addResourceType('acl', 'acls/', 'Acl');
		$resourceLoader->addResourceType('plugin', 'plugins/', 'Plugin');
				
		// Registra o plugin de autenticação
		$frontController = Zend_Controller_Front::getInstance();
		$frontController->registerPlugin(new Plugin_Autenticacao($dbAdapter));
		
		return $resourceLoader;	
			
	}
	
	/**
	 * Método de Inicialização da View
	 */
	protected function _initView() {
		// Verifica/CarregaoResourceLayoutdoBootstrap
		$this->bootstrap('layout');
		
		/**
		 * Resource:Layout
		 *
		 * @var Zend_Config
		 */
		$layout = $this->getResource('layout');
		
		/**
		 * View em execucao
		 *
		 * @var Zend_View
		 */
		$view = $layout->getView();
		
		// Define o Doctype da Página
		$view->doctype('HTML5');
		
		// Define o Título Padrão
		$view->headTitle('UnoTur - Sistema de Turismo');
		
		// Adiciona a library de View Helpers Projeto
		$view->addHelperPath('Projeto/View/Helper', 'Projeto_View_Helper');
		
		// Codificação gzip
		ob_start("ob_gzhandler");
		
		// Adiciona o header de expiração
		$view->expireHeader(10);
		
		// Adiciona o Header de Codificação da Página
		$view->headMeta()->appendHttpEquiv('Content-Type', 'text/html; charset=utf-8');
		
		// Adiciona o Favicon
		$view->headLink(array('rel' => 'shortcut icon', 'type' => 'image/x-icon', 'href' => $view->baseUrl('/images/admin/favicon.png')), 'PREPEND');
		
		// Retorna a view
		return $view;	
	}
	
	/**
	 * Método de inicialização da navegação
	 */
	protected function _initNavigation() {
		/**
		 * Zend_Config contendo as informações de navegação
		 * @var Zend_Config_Xml
		 */
		$config = new Zend_Config_Xml(APPLICATION_PATH . '/configs/navegacao.xml', 'nav');
		
		/**
		 * Instancia do Zend_Navigation
		 * @var Zend_Navigation
		*/
		$container = new Zend_Navigation($config);
		
		// Adiciona as configurações carregadas de navegação ao Zend_Navigation
		Zend_Registry::set('Zend_Navigation', $container);	
	}
	
	
}