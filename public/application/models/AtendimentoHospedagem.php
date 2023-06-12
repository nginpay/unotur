<?php

/**
 * Model_AtendimentoHospedagem - Tabela 'atendimento_hospedagem'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_AtendimentoHospedagem extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'atendimento_hospedagem';
	const name = 'atendimento_hospedagem';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('TipoHospedagem'=>array('columns' => 'tipo_hospedagem', 'refTableClass' => 'Model_TipoHospedagem', 'refColumns' => 'codigo'), 'Atendimento'=>array('columns' => 'atendimento', 'refTableClass' => 'Model_Atendimento', 'refColumns' => 'codigo'));
	
}
