<?php

/**
 * Model_Atendimento - Tabela 'atendimento'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Atendimento extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'atendimento';
	const name = 'atendimento';
        
        /**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_AtendimentoHospedagem', 'Model_AtendimentoHistorico');
		
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Usuario' => array('columns' => 'usuario', 'refTableClass' => 'Model_Usuario', 'refColumns' => 'usuario'),
	                                  'Cliente' => array('columns' => 'cliente', 'refTableClass' => 'Model_Cliente', 'refColumns' => 'codigo'),
	                                  'Status' => array('columns' => 'statusatendimento', 'refTableClass' => 'Model_StatusAtendimento', 'refColumns' => 'codigo'));
	
}
