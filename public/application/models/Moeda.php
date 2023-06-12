<?php

/**
 * Model_Moeda - Tabela Hospedagem
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Moeda extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'moeda';
	const name = 'moeda';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_Pacote', 'Model_Cotacao');
}
