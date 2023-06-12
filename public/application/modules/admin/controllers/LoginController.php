<?php
require_once 'AdminController.php';

/**
 * Admin_LoginController - Controller responsavel por
 *
 * @version 1.0.0 - 02/07/2012
 */
class Admin_LoginController extends AdminController {
	
	public function indexAction() {
		$erros = $this->_getParam ('erro', null);
		$logado = $this->_getParam ('logado', null);
		
		if (!empty ($logado) && empty ($erros)) {
			$this->_redirect ('/');
			return;
		}
		
		$this->view->login = isset($_COOKIE['usuario'])?$_COOKIE['usuario']:"";
		
		$this->view->erro = $erros;	
				
		$this->_helper->layout()->setLayout('login');
		
	}

	/**
	 * LogarAction - Acao de Logar no sistemas
	 */
	public function logarAction() {

		$this->_helper->layout ()->disableLayout ();
		$this->_helper->viewRenderer->setNoRender (true);
		$erro = 0;
		$msg = 'Logado com sucesso';
		
		$user = $this->_getParam ('usuario', '');
		$pass = $this->_getParam ('senha', '');
		
		if (empty ($user) || empty ($pass)) {
			$msg = "Usuário ou senha inválidos";
			$erro = 1;
		}
		
		$tblUsuario = new Model_Usuario();
		$authAdapter = new Zend_Auth_Adapter_DbTable($tblUsuario->getAdapter(), 'usuario', 'usuario', 'senha', 'PASSWORD(?) AND `status` = "ativo"');
		
		$authAdapter->setIdentity($this->_getParam ('usuario', 'xxxxxx'));
		$authAdapter->setCredential($this->_getParam('senha', 'zzzzzz'));
		
		$auth = Zend_Auth::getInstance();
		$authResult = $auth->authenticate($authAdapter);
		
		if ($authResult->isValid () && $erro == 0) {
			
			$gravar = $this->_getParam('gravar');			
			if($gravar == "sim"){				
				setcookie('usuario', $this->_getParam('usuario'), time() + (60 * 3000 * 24), '/');
			} else {				
				setcookie("usuario", "",time() - 3600, '/');
			}
			
			$usuario = $tblUsuario->find($this->_getParam('usuario', 'xxxxxx'))->current();
			
			$authStorage = $auth->getStorage ();
			$usuario = $usuario->toArray();			
			$role = array ('role' => $usuario['usuario']);
			$usuario = array_merge($usuario, $role);
			$authStorage->write ($usuario);	

		} else {
			$msg = "Usuário ou senha inválidos ";
			$erro = 1;			
		}
		
		echo Zend_Json_Encoder::encode (array('msg'=>$msg, 'erro'=>$erro));	
	}

	/**
	 * LogoutAction - Pagina de Logout do sistema
	 */
	public function logoutAction() {

		$this->_helper->layout ()->disableLayout ();
		$this->_helper->viewRenderer->setNoRender (true);
		
		$auth = Zend_Auth::getInstance ();
		
		$auth->clearIdentity();
		
		$this->_redirect ('/admin');
		return;
	
	}
	
	public function salvarSenhaAction() {
	    $this->_helper->layout()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender(true);
	
	    $senha = $this->_getParam("senha");
	    $confirmarSenha = $this->_getParam("confirmar-senha");
	
	    $erro = 0;
	    $msg = "";
	
	    if($senha != $confirmarSenha){
	        $msg = 	"A senha inserida não coincide com a senha de confirmação";
	        $erro = 1;
	    }
	
	    if(empty($senha) || empty($confirmarSenha) || empty($this->view->usuario["usuario"])){
	        $msg = 	"Preencha todos campos para alterar a senha";
	        $erro = 1;
	    }
	
	    if(!$erro){
	        $tblUsuario = new Model_Usuario();
	        $data["senha"] = new Zend_Db_Expr('password("'.$senha.'")');
	        $tblUsuario->update($data, "usuario = '{$this->view->usuario["usuario"]}'");
	    }
	
	    echo Zend_Json_Encoder::encode(array('msg'=>$msg, 'erro'=>$erro));
	}

}
