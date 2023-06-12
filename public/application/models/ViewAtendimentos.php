<?php

/**
 * Model_ViewAtendimentos - Tabela view_atendimentos_ano
 * 
 */
class Model_ViewAtendimentos extends Zend_Db_Table_Abstract {
	
	/**
	 * Nome da Tabela
	 * @var String
	 */
	protected $_name = 'view_atendimentos';
	const name = 'view_atendimentos';

	/**
	 * Chave Primaria
	 * @var String
	 */	
	protected $_primary = array('data');

}


