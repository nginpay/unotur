<?php

/**
 * Model_Resource - Tabela Resource
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Resource extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */	
	protected $_name = 'resource';
	const name = 'resource';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_UsuarioPermissao');
}
