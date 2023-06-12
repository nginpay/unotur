<?php

/**
 * Model_Pacote - Tabela 'pacote'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Pacote extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'pacote';
	const name = 'pacote';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_PacoteTransporte', 'Model_PacoteHospedagem', 'Model_PacoteServico');
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Moeda' => array('columns' => 'moeda', 'refTableClass' => 'Model_Moeda', 'refColumns' => 'codigo'), 
                                      'Lider' => array('columns' => 'liderpacote', 'refTableClass' => 'Model_Cliente', 'refColumns' => 'codigo'));
	
}
