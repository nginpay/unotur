<?php

/**
 * Model_VendaProduto - Tabela 'venda_produto'
 * 
 * @autor Vilmar
 * @version 1.0.0 - 04/12/2012
 */
class Model_VendaProduto extends Zend_Db_Table_Abstract {

	/**
	 * Nome da tabela
	 *
	 * @var String
	 */
	protected $_name = 'venda_produto';
	const name = 'venda_produto';
	
	/**
	 * Mapeamento das Foreing Keys da Tabela
	 *
	 * @var Array
	 */
	protected $_referenceMap = array('Hospedagem'=>array('columns' => 'hospedagem', 'refTableClass' => 'Model_Hospedagem', 'refColumns' => 'codigo'), 
	                                 'Pacote'=>array('columns' => 'pacote', 'refTableClass' => 'Model_Pacote', 'refColumns' => 'codigo'),
	                                 'Transporte'=>array('columns' => 'transporte', 'refTableClass' => 'Model_Transporte', 'refColumns' => 'codigo'),
	                                 'Servico'=>array('columns' => 'servico', 'refTableClass' => 'Model_Servico', 'refColumns' => 'codigo'),
	                                 'Moeda'=>array('columns' => 'moeda', 'refTableClass' => 'Model_Moeda', 'refColumns' => 'codigo'),
	                                 'Passageiro'=>array('columns' => 'passageiro', 'refTableClass' => 'Model_Passageiro', 'refColumns' => 'codigo'),
	                                 'Venda'=>array('columns' => 'venda', 'refTableClass' => 'Model_Venda', 'refColumns' => 'codigo')
	                                );
	
}
