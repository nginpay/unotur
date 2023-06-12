<?php

/**
 * Model_Cliente - Tabela 'cliente'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Cliente extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'cliente';
	const name = 'cliente';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_Venda');
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Cidade' => array('columns' => 'cidade', 'refTableClass' => 'Model_Cidade', 'refColumns' => 'codigo'));
	
}
