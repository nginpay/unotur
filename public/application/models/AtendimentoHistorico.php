<?php

/**
 * Model_AtendimentoHistorico - Tabela 'atendimento_historico'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_AtendimentoHistorico extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'atendimento_historico';
	const name = 'atendimento_historico';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Atendimento'=>array('columns' => 'atendimento', 'refTableClass' => 'Model_Atendimento', 'refColumns' => 'codigo'),
									 'StatusAtendimento'=>array('columns' => 'statusatendimento', 'refTableClass' => 'Model_StatusAtendimento', 'refColumns' => 'codigo'));
	
}
