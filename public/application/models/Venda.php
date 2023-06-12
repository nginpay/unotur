<?php

/**
 * Model_Venda - Tabela 'venda'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Venda extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'venda';
	const name = 'venda';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_VendaProduto');
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Cliente' => array('columns' => 'cliente', 'refTableClass' => 'Model_Cliente', 'refColumns' => 'codigo'));
	
}
