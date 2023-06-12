<?php

/**
 * Model_PacoteHospedagem - Tabela 'pacote_hospedagem'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_PacoteHospedagem extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'pacote_hospedagem';
	const name = 'pacote_hospedagem';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Hospedagem'=>array('columns' => 'hospedagem', 'refTableClass' => 'Model_Hospedagem', 'refColumns' => 'codigo'), 'Pacote'=>array('columns' => 'pacote', 'refTableClass' => 'Model_Pacote', 'refColumns' => 'codigo'));
	
}
