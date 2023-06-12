<?php
class Acl_Global extends Zend_Acl {

	public function __construct($dbAdapter) {
       	    
	    //Acesso de usuários
		$tblUsuario = new Model_Usuario($dbAdapter);
		$usuarios = $tblUsuario->fetchAll('`status` = "ativo" AND `administrador` = 0');
		
		$tblResource = new Model_Resource($dbAdapter);
		$resources = $tblResource->fetchAll();
		
		foreach($resources as $resource) {			
			if(strpos($resource->resource, ':') !== false) {
				
				list($module, $controller) = explode(':', $resource->resource);
				
				if(!$this->has($module)) {
					$this->addResource(new Zend_Acl_Resource($module));
				} else {
					continue;
				}
			    
			} else {
				$this->addResource(new Zend_Acl_Resource($resource->resource));
			}
		
		}
		
		foreach($usuarios as $usuario) {			
			$this->addRole(new Zend_Acl_Role($usuario->usuario));
			if(!empty($usuario->cliente)){
			    $this->deny($usuario->usuario);
			}
			
			$permissoes = $usuario->findDependentRowset('Model_UsuarioPermissao');
			
			foreach($permissoes as $permissao) {						
				if(strpos($permissao->resource, ':') !== false) {
					list($module, $controller) = explode(':', $permissao->resource);
					$this->allow($usuario->usuario, $module, $controller);
				} else {
					$this->allow($usuario->usuario, $permissao->resource);
				}			
			}
			$this->allow($usuario->usuario, 'admin', 'cliente-documento');
		}
		
		$admins = $tblUsuario->fetchAll('status = "ativo" AND `administrador` = 1');		
		foreach($admins as $admin) {
			$this->addRole(new Zend_Acl_Role($admin->usuario));
			$this->allow($admin->usuario);
		}
		
		//Acesso de clientes
		$this->addResource(new Zend_Acl_Resource("sac"));
		$tblCliente = new Model_Cliente();
		$clientes = $tblCliente->fetchAll('`status` = "ativo" AND usuario IS NOT NULL AND usuario <> ""');
		foreach($clientes as $cliente) {		    
		    $this->addRole(new Zend_Acl_Role($cliente->usuario));
		    $this->allow($cliente->usuario, "sac");
		}
		
		$this->addRole(new Zend_Acl_Role('visitante'));
		
		// Todo mundo tem acesso a...
		$this->allow(null, 'admin', 'login');
		$this->allow(null, 'sac', 'login');

		// Todo mundo tem acesso ao default do projeto
		$this->allow(null, 'default');
	
	}

}

?>