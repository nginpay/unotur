<?php

/**
 * Model_TipoPacote - Tabela TipoPacote
 *  
 * @author Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_TipoPacote extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'tipo_pacote';
	const name = 'tipo_pacote';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_AtendimentoPacote');
}
