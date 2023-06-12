<?php

/**
 * Model_CategoriaTransporte - Tabela CategoriaTransporte
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_CategoriaTransporte extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'categoria_transporte';
	const name = 'categoria_transporte';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_Transporte');
}
