<?php

/**
 * Model_Cidade - Tabela cidade
 *  
 * @version 1.0.0 - 12/04/2012
 */
class Model_Cidade extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'cidade';
	const name = 'cidade';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_CorretorImovel', 'Model_Corretor');

	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Estado' => array('columns' => 'estado', 'refTableClass' => 'Model_Estado', 'refColumns' => 'codigo'));

}
