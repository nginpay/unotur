<?php

/**
 * Model_TipoHospedagem - Tabela TipoHospedagem
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_TipoHospedagem extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'tipo_hospedagem';
	const name = 'tipo_hospedagem';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_AtendimentoHospedagem','Model_Hospedagem');
}
