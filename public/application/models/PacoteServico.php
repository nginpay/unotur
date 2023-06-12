<?php

/**
 * Model_PacoteServico - Tabela 'pacote_servico'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_PacoteServico extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'pacote_servico';
	const name = 'pacote_servico';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Servico'=>array('columns' => 'servico', 'refTableClass' => 'Model_Servico', 'refColumns' => 'codigo'), 'Pacote'=>array('columns' => 'pacote', 'refTableClass' => 'Model_Pacote', 'refColumns' => 'codigo'));
	
}
