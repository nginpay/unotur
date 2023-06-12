<?php

/**
 * Model_Banco - Tabela Banco
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Banco extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'banco';
	const name = 'banco';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_Configuracao');
}
