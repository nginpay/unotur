<?php

/**
 * Admin_CronController - Controller responsavel por
 *
 * @version 1.0.0 - 28/06/2012
*/

class CronController extends Zend_Controller_Action {

	/**
	 * Comandos cronológicos
	 */
	public function indexAction() {
	    $this->_helper->viewRenderer->setNoRender();
	    $this->_helper->layout()->disableLayout();
	    
	    $this->contatoAtendimento();
	    $this->enviarEmailCobranca();
	}
	
	private function contatoAtendimento(){
		Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
		
		$host = $_SERVER["SERVER_NAME"];
		$aux = explode(".unotur.com.br", $host);
		$dbName = str_replace("http://", "", $aux[0]);
		if($dbName == "tropicalturismo"){			
			$data = date('Y-m-d');
			
			$url = "http://tropicaltur.com.br/webservice?data={$data}";
			$curlObj = curl_init();
			curl_setopt($curlObj, CURLOPT_URL, $url);
			curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, 'POST');
			curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);			
			curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, false);
			$response = curl_exec($curlObj);
			curl_close($curlObj);
			
			if(!$response){
				//echo get_current_user(); exit;
				echo "sem retorno"; exit;
			} else {
				$resposta = json_decode($response);				
				if(isset($resposta->post)){
					if(count($resposta->post)>0){
						$data = array();
						foreach($resposta->post as $post){
							if(!empty($post->email)):							
								//Verificando se já não foi cadastrado
								$tblAtendimento = new Model_Atendimento();
								$observacao = "Contato recebido pelo site, email [{$post->email}], com a mensagem: {$post->mensagem}";
								
								$data["usuario"] = "admin";							
								$data["observacoes"] = $observacao;
								$date = new Zend_Date();
								$data["datacadastro"] = $date->get("WWW");
								$data["dataretorno"] = $date->get("WWW");
								$data["statusatendimento"] = 16;
								$data["telefone"] = "(00) 0000-0000";
															
								//Cadastrando o cliente na tabela de clientes
								$cliente["nome"] = $post->nome;
								$cliente["observacoes"] = "Cliente cadastrado através do contato no site";
								
								$tblCliente = new Model_Cliente();
								
								$resultCliente = $tblCliente->fetchRow("observacoes = '{$cliente["observacoes"]}' AND nome = '{$cliente["nome"]}'");								
								if(count($resultCliente)>0){								
									$clienteCodigo = $resultCliente->codigo;	
								} else {
									$clienteCodigo = $tblCliente->insert($cliente);
								}															
								
								//$observacao = utf8_decode($observacao);
								$hoje = date('Y-m-d');
								$result = $tblAtendimento->fetchRow("cliente = '$clienteCodigo' AND statusatendimento = '16' AND observacoes = '$observacao' AND DATE(datacadastro) = '{$hoje}'");
								
								$data["cliente"] = $clienteCodigo;
								if(!count($result)>0){									
									$tblAtendimento->insert($data);
								} else {
									echo "Atendimento já cadastrado <br/>";
								}
							endif;								
						}	
					}	
				}
				
			}
			
		}
	}
	
	private function enviarEmailCobranca(){
	    $tblVendaAReceber = new Model_VendaAReceber();
	    $recebimentos = $tblVendaAReceber->fetchAll("notificar = '1' AND emailnotificacao IS NOT NULL");
	    
	    $tblConfiguracao = new Model_Configuracao();
	    $config = $tblConfiguracao->fetchRow();
	     
	    $registro = array();
	    if(count($recebimentos) > 0){
	        foreach($recebimentos as $recebimento){
	            $venda = $recebimento->findParentRow("Model_Venda");
	            $cliente = $venda->findParentRow("Model_Cliente");
	            $corpo = "<p>";
	            $corpo .= "<strong>Olá $cliente->nome, seus dados de acesso no Konnectt foram atualizados:</strong><br/><br/>";
	            
	
	            $corpo.="</p>";
                
	            $registro = $recebimento->toArray();
	            $registro["valor"] = $this->view->NumeroParaMoeda($recebimento->valor);
	            $registro["datavencimento"] = $this->view->data($recebimento->datavencimento);
	            $registro["cliente"] = $cliente->nome;
	            $registro["emissor"] = $config->nome;
	            	            
	            try {
	                $mail = new Zend_Mail('utf-8');
	                $mensagem = $this->view->partial('/partial/email/cobranca.phtml', array('registro'=>$registro, 'baseUrl'=>$_SERVER["HTTP_HOST"]));
	                $mail->setBodyHtml($mensagem,'utf-8');	                	                	               
	                $mail->setFrom("naoresponda@unotur.com.br", $config->nome);
	                $mail->setReplyTo($cliente->email);
	                $mail->addTo($registro["emailnotificacao"]);
	                $mail->setSubject('Boleto de Cobrança - '.$config->nome.' '.date("d/m/Y H:i:s"));
	                $mail->send();
	                $date = new Zend_Date();
	                $dataNotificacao = $date->get("WWW");
	                $tblVendaAReceber->update(array("notificar"=>0, "datanotificacao"=>$dataNotificacao), "codigo = $recebimento->codigo");
	            } catch ( Zend_Mail_Exception $e ) {
	                $erro = 1;
	                $msg = $e->getMessage();
	            }
	        }
	    }
	}
	
}

