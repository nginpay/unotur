<?php

/**
 * Model_Configuracao - Tabela configuracao
 *  
 * @version 1.0.0 - 12/04/2012
 */
class Model_Configuracao extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'configuracao';
	const name = 'configuracao';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Banco' => array('columns' => 'banco', 'refTableClass' => 'Model_Banco', 'refColumns' => 'codigo'));

}
