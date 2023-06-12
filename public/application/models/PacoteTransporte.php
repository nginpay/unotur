<?php

/**
 * Model_PacoteTransporte - Tabela 'pacote_transporte'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_PacoteTransporte extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'pacote_transporte';
	const name = 'pacote_transporte';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Transporte'=>array('columns' => 'transporte', 'refTableClass' => 'Model_Transporte', 'refColumns' => 'codigo'), 'Pacote'=>array('columns' => 'pacote', 'refTableClass' => 'Model_Pacote', 'refColumns' => 'codigo'));
	
}
