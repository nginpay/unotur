<?php

/**
 * Model_StatusAtendimento - Tabela Estado
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_StatusAtendimento extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'status_atendimento';
	const name = 'status_atendimento';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_Atendimento');
}
