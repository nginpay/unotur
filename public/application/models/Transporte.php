<?php

/**
 * Model_Transporte - Tabela Transporte
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Transporte extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'transporte';
	const name = 'transporte';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_PacoteTransporte');
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('CategoriaTransporte' => array('columns' => 'categoriatransporte', 'refTableClass' => 'Model_CategoriaTransporte', 'refColumns' => 'codigo'));
}
