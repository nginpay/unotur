<?php

/**
 * Model_Cotacao - Tabela Cotação
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Cotacao extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'cotacao';
	const name = 'cotacao';

	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Moeda' => array('columns' => 'moeda', 'refTableClass' => 'Model_Moeda', 'refColumns' => 'codigo'));
}
