<?php

/**
 * Model_Passageiro - Tabela 'passageiro'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_Passageiro extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'passageiro';
	const name = 'passageiro';
	
	/**
	 * Lista das tabelas que mantem referencia a esta tabela
	 *
	 * @var Array
	 */
	protected $_dependentTables = array ('Model_Passageiro', 'Model_VendaProduto');
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Naturalidade' => array('columns' => 'cidade', 'refTableClass' => 'Model_Cidade', 'refColumns' => 'codigo'),
                        	          'Nacionalidade' => array('columns' => 'nacionalidade', 'refTableClass' => 'Model_Nacionalidade', 'refColumns' => 'iso'),
                        	          'PaisPassaporte' => array('columns' => 'paispassaporte', 'refTableClass' => 'Model_Nacionalidade', 'refColumns' => 'iso')
                        	         );
	
}
