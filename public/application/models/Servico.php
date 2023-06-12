<?php

/**
 * Model_Servico - Tabela Servico
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Servico extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'servico';
	const name = 'servico';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_PacoteServico');	
}
