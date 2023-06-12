<?php

/**
 * Model_ViewVendas - Tabela view_vendas_ano
 * 
 */
class Model_ViewVendas extends Zend_Db_Table_Abstract {
	
	/**
	 * Nome da Tabela
	 * @var String
	 */
	protected $_name = 'view_vendas';
	const name = 'view_vendas';

	/**
	 * Chave Primaria
	 * @var String
	 */	
	protected $_primary = array('data');

}


