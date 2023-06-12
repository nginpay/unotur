<?php
/**
 * IndexController - Controller responsavel por
 *
 * @author Vilmar | Analista de Sistemas
 * @version 1.0.0 - 21/08/2012
 */
class IndexController extends Zend_Controller_Action {
	public function indexAction() {
	    $this->_redirect("/admin/");
	}
}
