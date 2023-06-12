<?php
// Controller Default
class ErrorController extends Zend_Controller_Action {
	public function errorAction() {
		$errors = $this->_getParam('error_handler');
		if(! $errors || ! $errors instanceof ArrayObject) {
			$this->_redirect('/');
			return;
		}
		switch($errors->type) {
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE :
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER :
			case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION :
				// 404 error -- controller or action not found
				$this->getResponse()->setHttpResponseCode(404);
				$priority = Zend_Log::NOTICE;
				$this->view->message = 'A página solicitada não existe.';
				break;
			default :
				// application error
				$this->getResponse()->setHttpResponseCode(500);
				$priority = Zend_Log::CRIT;
				$this->view->message = 'O site se comportou de forma inexperada';
				break;
		}
		// Log exception, if logger available
		$log = $this->getLog();
		if($log) {
			$log->log('Erro Detectado ==> ' . $errors->exception, $priority);
			$log->log('Parametros ======> ' . Zend_Json::encode($errors->request->getParams()), $priority);
		}
		// Conditionally display exceptions
		if($this->getInvokeArg('displayExceptions') == true) {
			$this->view->exception = $errors->exception;
		}
		$this->view->request = $errors->request;
		
		$this->_helper->layout()->setLayout('admin-form');
	}
	public function getLog() {
		$bootstrap = $this->getInvokeArg('bootstrap');
		if(! $bootstrap->hasResource('Log')) {
			return false;
		}
		$log = $bootstrap->getResource('Log');
		return $log;
	}
}