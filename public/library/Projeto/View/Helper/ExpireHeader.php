<?php

class Projeto_View_Helper_ExpireHeader extends Zend_View_Helper_Abstract {
	
	/**
	 * TimeStamp de expiracao
	 * @var DateTime
	 */
	public $timestamp;
	
	/**
	 * View Padrao
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 * Constructor do Helper. Utilizado para setar o quando deve-se adicionar ao timestamp da data atual.
	 * @param $dias
	 * @param $meses
	 * @param $anos
	 */
	public function expireHeader($dias = 10, $meses = 0, $anos = 0) {
		$this->timestamp = mktime(date('H'), date('i'), date('s'), date('m') + $meses, date('d') + $dias, date('Y') + $anos);
		$this->view->headMeta()->appendName('expires', date('r', $this->timestamp));
		$this->view->headMeta()->appendHttpEquiv('Expires', date('r', $this->timestamp));
		return null;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}

}
