<?php

/**
 * Model_Usuario - Tabela 'usuario'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Usuario extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */    
	protected $_name = 'usuario';
	const name = 'usuario';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Cliente' => array ('columns' => 'cliente', 'refTableClass' => 'Model_Cliente', 'refColumns' => 'codigo'));
	
}
