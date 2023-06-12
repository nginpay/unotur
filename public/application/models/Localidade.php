<?php

/**
 * Model_Localidade - Tabela localidade
 *  
 * @version 1.0.0 - 12/04/2012
 */
class Model_Localidade extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'localidade';
	const name = 'localidade';

	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Estado' => array('columns' => 'estado', 'refTableClass' => 'Model_Estado', 'refColumns' => 'codigo'));

}
