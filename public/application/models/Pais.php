<?php

/**
 * Model_Pais - Tabela Hospedagem
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Pais extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'paises';
	const name = 'paises';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_Hospedagem');
}
