<?php

/**
 * Model_Hospedagem - Tabela Transporte
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Hospedagem extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'hospedagem';
	const name = 'hospedagem';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_PacoteHospedagem');

	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Pais' => array('columns' => 'pais', 'refTableClass' => 'Model_Pais', 'refColumns' => 'iso'));
}
