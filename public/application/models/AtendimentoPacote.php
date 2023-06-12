<?php

/**
 * Model_AtendimentoPacote - Tabela 'atendimento_pacote'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_AtendimentoPacote extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'atendimento_pacote';
	const name = 'atendimento_pacote';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('TipoPacote'=>array('columns' => 'tipo_pacote', 'refTableClass' => 'Model_TipoPacote', 'refColumns' => 'codigo'), 'Atendimento'=>array('columns' => 'atendimento', 'refTableClass' => 'Model_Atendimento', 'refColumns' => 'codigo'));
	
}
