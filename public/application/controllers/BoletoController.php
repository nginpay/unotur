<?php
/**
 * BoletoController - Controller responsavel por
 *
 * @author Vilmar | Analista de Sistemas
 * @version 1.0.0 - 21/08/2012
 */
class BoletoController extends Zend_Controller_Action {
	
    
    public function indexAction() {    
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $tblConfiguracao = new Model_Configuracao();
        $config = $tblConfiguracao->fetchRow();
        $vendaReceber = $this->_getParam("vendareceber");
        
        switch ($config->boleto):
            case "Bradesco":
                $this->boletoBradesco($vendaReceber);
                break;
            case "Banco do Brasil":
                $this->boletoBB($vendaReceber);
                break;
        endswitch;
    }
    
    private function boletoBradesco($vendaReceber){
        // +----------------------------------------------------------------------+
        // | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
        // | Desenvolvimento Boleto Bradesco: Ramon Soares						            |
        // +----------------------------------------------------------------------+
                
        // ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
        // Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc)	//
        
        
        $tblVendaReceber = new Model_VendaAReceber();        
        $result = $tblVendaReceber->find($vendaReceber)->current();
        
        if(!$result){
            echo "Parâmetros para gerar o boleto inválido #codigo $vendaReceber";
            exit;        
        }
        
        $venda = $result->findParentRow("Model_Venda");
        $cliente = $venda->findParentRow("Model_Cliente");        
        $cidade = $cliente->findParentRow("Model_Cidade");
        $estado = $cidade->findParentRow("Model_Estado");
        
        // DADOS DO BOLETO PARA O SEU CLIENTE
        $dias_de_prazo_para_pagamento = 5;
        $taxa_boleto = 0;
        $data_venc = $this->view->data($result->datavencimento);  // Prazo de X dias OU informe data: "13/04/2006";
        $valor_cobrado = $result->valor; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal        
        $valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');
        
        $dadosboleto["nosso_numero"] = $result->codigo;  // Nosso numero sem o DV - REGRA: Máximo de 11 caracteres!
        $dadosboleto["numero_documento"] = $dadosboleto["nosso_numero"];	// Num do pedido ou do documento = Nosso numero
        $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
        $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
        $dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
        
        // DADOS DO SEU CLIENTE
        $dadosboleto["sacado"] = $cliente->nome;
        $dadosboleto["endereco1"] = $cliente->endereco;
        $dadosboleto["endereco2"] = "$cidade->nome - $estado->nome";
        $dadosboleto["endereco2"] .= (!empty($cliente->cep))?" -  CEP: $cliente->cep":"";
        
        // INFORMACOES PARA O CLIENTE
        $dadosboleto["demonstrativo1"] = "Pagamento de Compra Unotur";
        $dadosboleto["demonstrativo2"] = "Código de recebimento nº {$result->codigo}<br>Taxa bancária - R$ ".number_format($taxa_boleto, 2, ',', '');
        $dadosboleto["demonstrativo3"] = "Unotur - http://www.unotur.com.br";
        $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
        $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
        $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: contato@unotur.com.br";
        $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema UnoTur - www.unotur.com.br";
        
        // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
        $dadosboleto["quantidade"] = "001";
        $dadosboleto["valor_unitario"] = $valor_boleto;
        $dadosboleto["aceite"] = "";
        $dadosboleto["especie"] = "R$";
        $dadosboleto["especie_doc"] = "DS";
        
        
        // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
        $tblConfiguracao = new Model_Configuracao();
        $config = $tblConfiguracao->fetchRow();
        
        // DADOS DA SUA CONTA - Bradesco
        $dadosboleto["agencia"] = $config->agencia; // Num da agencia, sem digito
        $dadosboleto["agencia_dv"] = $config->agencia_dv; // Digito do Num da agencia
        $dadosboleto["conta"] = $config->conta; 	// Num da conta, sem digito
        $dadosboleto["conta_dv"] = $config->conta_dv; 	// Digito do Num da conta
        
        // DADOS PERSONALIZADOS - Bradesco
        $dadosboleto["conta_cedente"] = $config->conta; // ContaCedente do Cliente, sem digito (Somente Números)
        $dadosboleto["conta_cedente_dv"] = $config->conta_dv; // Digito da ContaCedente do Cliente
        $dadosboleto["carteira"] = $config->carteira;  // Código da Carteira: pode ser 06 ou 03
        
        // SEUS DADOS
        $dadosboleto["identificacao"] = $config->nome;
        $dadosboleto["cpf_cnpj"] = $config->cpf_cnpj;
        $dadosboleto["endereco"] = $config->endereco;
        $dadosboleto["cidade_uf"] = $config->cidade." / ".$config->uf;
        $dadosboleto["cedente"] = $config->nome;
        $dadosboleto["logomarca"] = $config->logomarca;
        
        // NÃO ALTERAR!
        include("boletophp-master/include/funcoes_bradesco.php");
        include("boletophp-master/include/layout_bradesco.php");
    }
    
    
     private function boletoBB($vendaReceber) {
                    
        // +----------------------------------------------------------------------+
        // | Equipe Coordenação Projeto BoletoPhp: <boletophp@boletophp.com.br>   |
        // | Desenvolvimento Boleto Bradesco: Ramon Soares						            |
        // +----------------------------------------------------------------------+
                
        // ------------------------- DADOS DINÂMICOS DO SEU CLIENTE PARA A GERAÇÃO DO BOLETO (FIXO OU VIA GET) -------------------- //
        // Os valores abaixo podem ser colocados manualmente ou ajustados p/ formulário c/ POST, GET ou de BD (MySql,Postgre,etc)	//                
        $tblVendaReceber = new Model_VendaAReceber();        
        $result = $tblVendaReceber->find($vendaReceber)->current();
        
        if(!$result){
            echo "Parâmetros para gerar o boleto inválido #codigo $vendaReceber";
            exit;        
        }
        
        $venda = $result->findParentRow("Model_Venda");
        $cliente = $venda->findParentRow("Model_Cliente");        
        $cidade = $cliente->findParentRow("Model_Cidade");
        $estado = $cidade->findParentRow("Model_Estado");
        
        // DADOS DO BOLETO PARA O SEU CLIENTE
        $dias_de_prazo_para_pagamento = 5;
        $taxa_boleto = 0;
        $data_venc = $this->view->data($result->datavencimento);  // Prazo de X dias OU informe data: "13/04/2006";
        $valor_cobrado = $result->valor; // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal        
        $valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');
        
        $dadosboleto["nosso_numero"] = $result->codigo;  // Nosso numero sem o DV - REGRA: Máximo de 11 caracteres!
        $dadosboleto["numero_documento"] = $dadosboleto["nosso_numero"];	// Num do pedido ou do documento = Nosso numero
        $dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
        $dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
        $dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
        $dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula
        
        // DADOS DO SEU CLIENTE
        $dadosboleto["sacado"] = $cliente->nome;
        $dadosboleto["endereco1"] = $cliente->endereco;
        $dadosboleto["endereco2"] = "$cidade->nome - $estado->nome";
        $dadosboleto["endereco2"] .= (!empty($cliente->cep))?" -  CEP: $cliente->cep":"";
        
        // INFORMACOES PARA O CLIENTE
        $dadosboleto["demonstrativo1"] = "Pagamento de Compra Unotur";
        $dadosboleto["demonstrativo2"] = "Código de recebimento nº {$result->codigo}<br>Taxa bancária - R$ ".number_format($taxa_boleto, 2, ',', '');
        $dadosboleto["demonstrativo3"] = "Unotur - http://www.unotur.com.br";
        
        // INSTRUÇÕES PARA O CAIXA
        $dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
        $dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
        $dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: contato@unotur.com.br";
        $dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema UnoTur - www.unotur.com.br";
        
        // DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
        $dadosboleto["quantidade"] = "01";
        $dadosboleto["valor_unitario"] = $valor_boleto;
        $dadosboleto["aceite"] = "";
        $dadosboleto["especie"] = "R$";
        $dadosboleto["especie_doc"] = "DS";
        
        
        // ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //
        $tblConfiguracao = new Model_Configuracao();
        $config = $tblConfiguracao->fetchRow();
        
        // DADOS DA SUA CONTA - BANCO DO BRASIL
        $dadosboleto["agencia"] = $config->agencia; // Num da agencia, sem digito        
        $dadosboleto["conta"] = $config->conta; 	// Num da conta, sem digito        
        
        // DADOS PERSONALIZADOS - BANCO DO BRASIL
        $dadosboleto["convenio"] = $config->convenio;  // Num do convênio - REGRA: 6 ou 7 ou 8 dígitos
        $dadosboleto["contrato"] = $config->contrato; // Num do seu contrato
        $dadosboleto["carteira"] = $config->carteira;
        $dadosboleto["variacao_carteira"] = "-019";  // Código da Carteira: pode ser 06 ou 03
        
        // TIPO DO BOLETO
        $dadosboleto["formatacao_convenio"] = "7"; // REGRA: 8 p/ Convênio c/ 8 dígitos, 7 p/ Convênio c/ 7 dígitos, ou 6 se Convênio c/ 6 dígitos
        $dadosboleto["formatacao_nosso_numero"] = "2"; // REGRA: Usado apenas p/ Convênio c/ 6 dígitos: informe 1 se for NossoNúmero de até 5 dígitos ou 2 para opção de até 17 dígitos
        
        // SEUS DADOS
        $dadosboleto["identificacao"] = $config->nome;
        $dadosboleto["cpf_cnpj"] = $config->cpf_cnpj;
        $dadosboleto["endereco"] = $config->endereco;
        $dadosboleto["cidade_uf"] = $config->cidade." / ".$config->uf;
        $dadosboleto["cedente"] = $config->nome;
        $dadosboleto["logomarca"] = $config->logomarca;
        
        // NÃO ALTERAR!
        include("boletophp-master/include/funcoes_bb.php");
        include("boletophp-master/include/layout_bb.php");
        
     }
	
}
