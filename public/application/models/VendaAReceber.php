<?php

/**
 * Model_VendaAReceber - Tabela 'venda_areceber'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_VendaAReceber extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'venda_areceber';
	const name = 'venda_areceber';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Venda'=>array('columns' => 'venda', 'refTableClass' => 'Model_Venda', 'refColumns' => 'codigo'));
	
}
