<?php

/**
 * Model_AtendimentoPassagem - Tabela 'atendimento_passagem'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_AtendimentoPassagem extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'atendimento_passagem';
	const name = 'atendimento_passagem';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Atendimento'=>array('columns' => 'atendimento', 'refTableClass' => 'Model_Atendimento', 'refColumns' => 'codigo'));
	
}
