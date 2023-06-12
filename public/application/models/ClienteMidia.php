<?php
/**
 * Model_ClienteMidia - Tabela ImovelCorretor
 *  
 * @version 1.0.0 - 12/04/2012
 */
class Model_ClienteMidia extends Zend_Db_Table_Abstract {
	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'cliente_midia';
	const name = 'cliente_midia';

	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Cliente'=> array('columns'=>'cliente', 'refTableClass'=>'Model_Cliente', 'refColumns'=>'codigo'));
}
