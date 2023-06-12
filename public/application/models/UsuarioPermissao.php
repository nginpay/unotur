<?php

/**
 * Model_UsuarioPermissao - Tabela Permissao
 *  
 * @version 1.0.0 - 12/04/2012
 */
class Model_UsuarioPermissao extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'usuario_permissao';
	const name = 'usuario_permissao';

	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array ('Usuario' => array ('columns' => 'usuario', 'refTableClass' => 'Model_Usuario', 'refColumns' => 'usuario' ), 'Resource' => array ('columns' => 'resource', 'refTableClass' => 'Model_Resource', 'refColumns' => 'resource' ) );

}
