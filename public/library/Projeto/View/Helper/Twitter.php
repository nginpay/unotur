<?php
/**
 *
 * @version
 *
 *
 */
require_once 'Zend/View/Interface.php';

/**
 * Twitter helper
 *
 * @uses viewHelper Projeto_View_Helper
 */
class Projeto_View_Helper_Twitter {

	/**
	 *
	 * @var Zend_View_Interface
	 */
	public $view;

	/**
	 */
	public function twitter() {

		return $this;
	
	}
	
	public function getLastTwittes(){
		
		$twitterSearch = new Zend_Service_Twitter_Search('json');
		
		$searchResults = $twitterSearch->search('amppropaganda', array('lang' => 'pt'));

		return $searchResults["results"];		
	}

	/**
	 * Sets the view field
	 *
	 * @param $view Zend_View_Interface        	
	 */
	public function setView(Zend_View_Interface $view) {

		$this->view = $view;
	
	}

}
