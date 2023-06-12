
<?php		
require_once "AdminController.php";

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @autor Estudio Criar
 * @contato estudiocriar.com.br
 * @versão 1.0.1 - 20/08/2014
 */
class Admin_VendaController extends AdminController {
	
        /**
	 * Model principal do crud
	 */
	private $model = "Model_Venda";
	private $ordenacao = "codigo DESC";	
	
	/**
	 * IndexAction -
	 */
	public function indexAction() {
		/* Array com os dados da tabela */		
		$this->view->etiquetas = array('codigo'=>'Cód.', 'cliente'=>'Contratante', 'usuario'=>'Responsável', 'datavenda'=>'Data da Venda', 'valortotal'=>'Valor Total');	
	}
	
	public function indexDadosAction() {
		
		$this->_helper->layout()->disableLayout();
			
		$tbl = new $this->model();
		
		$query = $tbl->select()->setIntegrityCheck(false);
		
		if(!empty($this->query))
			$query->where($this->query);
	
		if($this->getRequest()->isPost()) {

			//Dados da busca
			$post = $this->getRequest()->getPost();

			//Termo da ordenação
			$ordem = $this->_getParam("ordenacao");					
			$ordenacao = !empty($ordem)?$ordem:$this->ordenacao;
			
			/* Buscas */
		    $codigo = $this->_getParam("codigo");	
			if(!empty($codigo)) {				                
                $query->where("codigo = $codigo");                               
			}
						
			$vendaReceber = $this->_getParam("vendaReceber");
			if(!empty($vendaReceber)) {
			    $query->where("codigo in (SELECT venda FROM venda_areceber WHERE codigo = $vendaReceber)");
			}
			
		    $cliente = $this->_getParam("cliente");	
			if(!empty($cliente)) {				                
                $query->where("cliente in (SELECT codigo FROM cliente WHERE lower(nome) like '%".$cliente."%')");
                $this->view->cliente = $cliente;                
			}
			
		    $passageiro = $this->_getParam("passageiro");	
			if(!empty($passageiro)) {				                
                $query->where("codigo in (SELECT venda FROM venda_passageiro WHERE passageiro in (SELECT codigo FROM passageiro WHERE lower(nome) like '%".$passageiro."%' || lower(sobrenome) like '%".$passageiro."%'))");                                
			}
			
						
		}
		
		$query->order($ordenacao);
		
		$dados = $tbl->fetchAll($query);
	
		$paginator = Zend_Paginator::factory($dados);
		$paginator->setCurrentPageNumber($this->_getParam("pagina", 1));
	
		$porPagina = $this->_getParam("por-pagina");
				
		//numero de itens por pagina
		$paginator->setItemCountPerPage($porPagina);
	
		//numero de indices de paginas que serão exibidos
		$paginator->setPageRange(6);
	
		$this->view->paginacao = $paginator;	
	}
				
	public function cadastroAction() {	
		$codigo = $this->_getParam('codigo');
		
		//Carregando as moedas
		$tblMoeda = new Model_Moeda();
		$this->view->moedas = $tblMoeda->fetchAll();
	
		$tbl = new $this->model();
		if(is_numeric($codigo)) {			
			$this->view->registro = $tbl->find($codigo)->current();			
			$this->view->totalAReceber = $this->_helper->utils()->totalAReceber($codigo);
			
			$tblCliente = new Model_Cliente();
			$this->view->clientes = $tblCliente->fetchAll("codigo = {$this->view->registro->cliente}","nome ASC",10000);
		}
		
		$iframe = $this->_getParam("iframe",false);
		if($iframe){
		    $this->_helper->layout()->setLayout('admin-form');
		}
		$this->view->iframe = $iframe;
		
		$tblPacote = new Model_Pacote();
		$this->view->pacotes = $tblPacote->fetchAll(null,"descricao ASC");
	}
				
	public function salvarCadastroAction() {
	
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = "";
	
		$post = $this->getRequest()->getPost();
	
		$tbl = new $this->model();
		$tbl->getAdapter()->beginTransaction();
				
		 
		$post["cliente"] = trim($post["cliente"]);		
		if(empty($post["cliente"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo cliente"));
			return;
		}
			 
		$post["usuario"] = $this->view->usuario["usuario"];		
		if(empty($post["usuario"])){
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo usuario"));
			return;
		}
			 
		$post["datavenda"] = trim($post["datavenda"]);						
		if(!empty($post["datavenda"])){
			$date = new Zend_Date($post["datavenda"]);
			$post["datavenda"] = $date->get("WWW");
		} else {
			echo Zend_Json_Encoder::encode(array("erro"=>1, "msg"=>"Preencha o campo datavenda"));
			return;	
		}
								
		if(!empty($post["valortotal"])) {
		    $post["valortotal"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valortotal"])));
		}
		
		$adicionou = false;
				
		if($erro == 0){
			if(empty($post["codigo"])) {
				unset($post["codigo"]);
				$venda = $tbl->insert($post);
				$adicionou = true;
			} else {
				$venda = $post["codigo"];
				unset($post["codigo"]);					
				$tbl->update($post,"codigo = ".$venda);
			}
		}	
		$tbl->getAdapter()->commit();
		
		if($adicionou){
		    //Verificando se o cliente possui parcelas de crédito para serem inseridas
		    $tblClienteCredito = new Model_ClienteCredito();		    
		    $creditos = $tblClienteCredito->fetchAll("cliente = {$post["cliente"]} AND venda_areceber IS NULL");
		    
		    $tblVendaReceber = new Model_VendaAReceber();
		    
		    if(count($creditos)>0){
		        foreach($creditos as $credito){
		            $vendaReceber["tipo"] = "À Vista";        
		            $vendaReceber["parcela"] = 0;        
		            $vendaReceber["datavencimento"] = $credito->datapagamento;        
		            $vendaReceber["valor"] = 0;        
		            $vendaReceber["venda"] = $venda;
		            $vendaReceber["datapagamento"] = $credito->datapagamento;
		            $vendaReceber["valorpago"] = $credito->valor;
		            $vendaReceber["valorcambio"] = $credito->valorcambio;
		            $vendaReceber["valorreceber"] = $credito->valor;
		            $vendaReceber["venda_areceber_fornecedora"] = $credito->venda_areceber_fornecedora;
		            $vendaReceber["status"] = "crédito";
		            $venda_areceber = $tblVendaReceber->insert($vendaReceber);
		            $tblClienteCredito->update(array("venda_areceber"=>$venda_areceber), "codigo = $credito->codigo");
		        }    
		    }
		}
	
		echo Zend_Json_Encoder::encode(array("erro" => $erro, "msg"=>$msg, "codigo" => $venda, "adicionou"=>$adicionou));
	}
				
	public function excluirAction() {
	
		Zend_Layout::getMvcInstance()->disableLayout();
		$this->_helper->viewRenderer->setNoRender();
	
		$erro = 0;
		$msg = "";
	
		$post = $this->getRequest()->getPost();
	
		$itens = explode(",", $post["itens"]);
	
		$tbl = new $this->model();
		
		$tblVendaReceber = new Model_VendaAReceber();
		$tblClienteCredito = new Model_ClienteCredito();
		if(count($itens)>0):	
			foreach($itens as $codigo):		
				if(!empty($codigo) && is_numeric($codigo)) {
                                    //Buscando as parcelas a pagar
                                    $parcelas = $tblVendaReceber->fetchAll("venda = $codigo");

                                    if(count($parcelas)>0){
                                        foreach($parcelas as $parcela):
                                            if($parcela->status == "remanejado"):
                                                echo Zend_Json_Encoder::encode(array("erro" => 1, "msg"=>"Esta venda não pode ser excluída pois possui uma ou mais parcelas remanejadas"));
                                                return;
                                            endif;

                                            if($parcela->status == "crédito"):
                                                echo Zend_Json_Encoder::encode(array("erro" => 1, "msg"=>"Esta venda não pode ser excluída pois possui uma ou mais parcelas com status de crédito"));
                                                return;
                                            endif;
                                            
                                            if($parcela->valorpago > 0):
                                                echo Zend_Json_Encoder::encode(array("erro" => 1, "msg"=>"Esta venda não pode ser excluída pois possui uma ou mais parcelas pagas"));
                                                return;
                                            endif;
                                        endforeach;
                                    }

                                    $tbl->delete("codigo = ".$codigo);
				}		
			endforeach;
		endif;
	
		echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));	
	}
	
	//Lista produtos
	public function obterProdutosAction() {
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $tipo = $this->_getParam('tipo');
	    $produtoSelecionado = $this->_getParam('produto', 0);
	    $res = "<option value=''>Selecione um produto</option>";;
	    if(!empty($tipo)){
	        $dados = null;
	        switch ($tipo):
	            case "pacote":
	                $tblPacote = new Model_Pacote();
	                $dados = $tblPacote->fetchAll("datafimvenda > CURRENT_DATE()");
	            break;
	            case "hospedagem":
	                $tblHospedagem = new Model_Hospedagem();
	                $dados = $tblHospedagem->fetchAll();
	            break;
	            case "transporte":
	                $tblTransporte = new Model_Transporte();
	                $dados = $tblTransporte->fetchAll();
	            break;
	            case "servico":
	                $tblServico = new Model_Servico();
	                $dados = $tblServico->fetchAll();
	            break;
	        endswitch;
            	        
	        if(count($dados)>0):
	            foreach($dados as $produto):
	                $valor = null;
	                $moeda = null;	                
	                if(isset($produto->valor)){
	                    $valor = $this->view->NumeroParaMoeda($produto->valorvendaindividual);
	                }
	                
	                if(isset($produto->moeda)){
	                    $moeda = $produto->moeda;
	                }
	                
	                if($tipo == "pacote"):
	                    $nome = $produto["descricao"];
	                else:
	                    $nome = $produto["nome"];
	                endif;
	                
	                $checked = ($produto['codigo'] == $produtoSelecionado)?"selected='selected'":null;
	                
	                $res.= "<option $checked data-moeda='".$moeda."' data-valor='".$valor."' value='".$produto['codigo']."'>".$nome."</option>";
	            endforeach;
	        endif;
	        
	    }
	    	
	    echo $res;	
	}
				
	public function addProdutoAction() {
	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $erro = 0;
	    $msg = "";
	    $html = null;
	
	    $post = $this->getRequest()->getPost();
	     
	    if($post["adicionado"] == "true"){
	        echo Zend_Json_Encoder::encode(array("msg"=> "Este produto já se encontra cadastrado neste produto", "erro"=> 1));
	        return;
	    }
	
	    if(empty($post["produto"]) || empty($post["venda"]) || empty($post["passageiro"])){
	        echo Zend_Json_Encoder::encode(array("msg"=> "Por favor preencha os campos obrigatórios (*)", "erro"=> 1));
	        return;
	    }
	    
	    //Cadastrando produtos
	    $tblVendaProduto = new Model_VendaProduto();
           	   
        switch ($post["tipo"]):
	        case "hospedagem":
	            $post["hospedagem"] = $post["produto"];	            
	        break;
	        case "pacote":
	            $post["pacote"] = $post["produto"];
	        break;
	        case "servico":
	            $post["servico"] = $post["produto"];
	        break;
	        case "transporte":
	            $post["transporte"] = $post["produto"];
	        break;
         endswitch;
         unset($post["produto"]);
         unset($post["adicionado"]);
         
         $post["valor"] = $this->view->MoedaParaNumero($post["valor"]);
         
         if(empty($post["codigo"])){
             $tblVendaProduto->insert($post);
         } else {
             $tblVendaProduto->update($post, "codigo = {$post["codigo"]}");
         }
                  
	     $dados = $this->htmlProduto($post["venda"]);
	     $html = $dados["html"]; 

	     $valorTotal = (isset($dados["valorTotal"]))?$this->view->NumeroParaMoeda($dados["valorTotal"]):'0,00';
	     $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):'0,00';
	     $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):'0,00';
	     
	     echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "html"=>$html, "valorTotal"=>$valorTotal, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));	    	     	    
	}
	
	public function buscaProdutoAction() {
	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $erro = 0;
	    $msg = "";
	    $html = null;
	    $option = null;
	    
	    $post = $this->getRequest()->getPost();
	
	    if(!empty($post["codigo"])){
	        $tblVendaProduto = new Model_VendaProduto();
	        $result = $tblVendaProduto->find($post["codigo"])->current();
	        if($result){
	            
	            if(!empty($result->passageiro)){
	                $passageiro = $result->findParentRow("Model_Passageiro");
	                $html = $result->toArray();
	            
	                //Option
	                $nome = $passageiro->nome." ".$passageiro->sobrenome;
	                $option = "<option selected='selected' value='{$html["passageiro"]}'>{$nome}</option>";
	            }
	            
	            $html = $result->toArray();
	            if(!empty($html["hospedagem"])){
	                $html["produto"] = $html["hospedagem"];    
	            } else if (!empty($html["pacote"])){
	                $html["produto"] = $html["pacote"];
	            } else if (!empty($html["servico"])){
	                $html["produto"] = $html["servico"];
	            } else {
	                $html["produto"] = $html["transporte"];
	            }
	            
	            $html["valor"] = $this->view->NumeroParaMoeda($html["valor"]);
	        }
	    }
	
	    echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "html"=>$html, "option"=>$option));
	}
	
	public function addPagamentoAction() {
	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $erro = 0;
	    $msg = "";
	    $html = null;
	
	    $post = $this->getRequest()->getPost();
			    
	    if(empty($post["tipo"]) || empty($post["datavencimento"]) || empty($post["venda"])){
	        echo Zend_Json_Encoder::encode(array("msg"=> "Por favor preencha os campos obrigatórios (*)", "erro"=> 1));
	        return;
	    }
	    	    
	    $tblVenda = new Model_Venda();
	    $venda = $tblVenda->find($post["venda"])->current();
	    $totalVenda = $venda->valortotal;
	    
	    $tblVendaAReceber = new Model_VendaAReceber();
	    
	    if(empty($post["valor"])){
	        echo Zend_Json_Encoder::encode(array("msg"=> "Preencha o valor da parcela", "erro"=> 1));
	        return;
	    } else {
	        $post["valor"] = $this->view->MoedaParaNumero($post["valor"]);	        
	    }
	    
	    $post["valorreceber"] = $post["valor"];
	    if(empty($post["valorcambio"]) || $post["valorcambio"] == "0,00"){
	        $post["valorcambio"] = 0;
	    } else {	        
	        $post["valorcambio"] = $this->view->MoedaParaNumero($post["valorcambio"]);	        
	        $post["valorreceber"] = bcdiv($post["valor"], $post["valorcambio"], 2);
	    }

	    $vendaReceber["tipo"] = $post["tipo"];
	    
	    //Recuperando a próxima parcela
	    if(empty($post["parcela"])):
    	    $result = $tblVendaAReceber->fetchRow("venda = {$post['venda']}","parcela DESC");
    	    $proxParcela = 1;
    	    if(count($result)>0){
    	        $proxParcela += $result->parcela;	        
    	    }
    	    $vendaReceber["parcela"] = $proxParcela;
	    else:
	        $querie = "venda = {$post['venda']} AND parcela = {$post["parcela"]}";
	        
	        if(!empty($post["codigo"])){
	            $querie .= " AND codigo <> '{$post["codigo"]}'";
	        }
	    
	        $result = $tblVendaAReceber->fetchRow($querie);
	        if($result){
	            echo Zend_Json_Encoder::encode(array("msg"=> "A parcela que você deseja inserir já se encontra cadastrada para esta venda", "erro"=> 1));
	            return;
	        } else {
	            $vendaReceber["parcela"] = $post["parcela"];
	        }
	    endif;
	    
	    $date = new Zend_Date($post["datavencimento"]);	    
	    $vendaReceber["datavencimento"] = $date->get("WWW");
	     	     
	    $vendaReceber["valor"] = $post["valor"];
	    $vendaReceber["venda"] = $post["venda"];
	    $vendaReceber["valorcambio"] = $post["valorcambio"];
	    $vendaReceber["valorreceber"] = $post["valorreceber"];
	    
	    if(!empty($post["codigo"])){
	        $tblVendaAReceber->update($vendaReceber,"codigo = {$post['codigo']}");
	    } else {
	        $tblVendaAReceber->insert($vendaReceber);
	    }
	    
	    	    
	    //Gerando o retorno
	    $dados = $this->htmlPagamento($post["venda"]);
	    $html = $dados["html"];
	    
	    $valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
        $valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
        
        echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));	    		   
	}

	public function addPagamentoAutomaticoAction() {
	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $erro = 0;
	    $msg = "";
	    $html = null;
	
	    $post = $this->getRequest()->getPost();
	     
	    if(empty($post["tipo"]) || empty($post["datavencimento"]) || empty($post["parcela"]) || empty($post["venda"])){
	        echo Zend_Json_Encoder::encode(array("msg"=> "Por favor preencha os campos obrigatórios (*)", "erro"=> 1));
	        return;
	    }
	
	    $tblVenda = new Model_Venda();
	    $venda = $tblVenda->find($post["venda"])->current();
	    $totalVenda = $venda->valortotal;
	     
	    $tblVendaAReceber = new Model_VendaAReceber();
	     
	    if(empty($post["valor"])){
	        echo Zend_Json_Encoder::encode(array("msg"=> "Preencha o valor da parcela", "erro"=> 1));
	        return;
	    } else {
	        $post["valor"] = $this->view->MoedaParaNumero($post["valor"]);
	    }
	    
	    $post["valorreceber"] = $post["valor"];
	    if(empty($post["valorcambio"]) || $post["valorcambio"] == "0,00"){
	        $post["valorcambio"] = 0;
	    } else {
	        $post["valorcambio"] = $this->view->MoedaParaNumero($post["valorcambio"]);	        
	        $post["valorreceber"] = bcdiv($post["valor"], $post["valorcambio"], 2);
	    
	    }
	    	    
	    $vendaReceber["tipo"] = $post["tipo"];
        	    
	    $dataVigente = null;
	    for($x=1; $x<=$post["parcela"]; $x++):
    	    //Recuperando a próxima parcela	    
    	    $result = $tblVendaAReceber->fetchRow("venda = {$post['venda']}","parcela DESC");
    	    $proxParcela = 1;
    	    if(count($result)>0){
    	        $proxParcela += $result->parcela;
    	    }	    	    
    	    $vendaReceber["parcela"] = $proxParcela;
    	   
    	    if(empty($dataVigente)){
    	        $date = new Zend_Date($post["datavencimento"]);
    	        $dataVencimento = $date->get("WWW");    	       
    	    } else {    	        
    	        $datemonth = strtotime(date('Y-m-d', strtotime($result->datavencimento)) . '+1 month');
    	        $dataVencimento = date('Y-m-d', $datemonth);    	           	        
    	    }
    	     
    	    
    	    $vendaReceber["datavencimento"] = $dataVencimento;
    	    $dataVigente = $dataVencimento;
    	
    	    $vendaReceber["valor"] = $post["valor"];
    	    $vendaReceber["venda"] = $post["venda"];
    	    $vendaReceber["valorcambio"] = $post["valorcambio"];
    	    $vendaReceber["valorreceber"] = $post["valorreceber"];
    	    $codigo = $tblVendaAReceber->insert($vendaReceber);
	    endfor;
	
	    //Gerando o retorno
	    $dados = $this->htmlPagamento($post["venda"]);
	    $html = $dados["html"];
	     
	    $valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
        $valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
        
        echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));
	}
	
	public function delPagamentoAction() {
	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $erro = 0;
	    $msg = "";
	    $html = null;
	    
	    $post = $this->getRequest()->getPost();
	    
	    $tblVendaAReceber = new Model_VendaAReceber();
	    $tblVendaAReceber->delete("codigo = {$post['codigo']}");
	    
	    //Gerando o retorno
	    $dados = $this->htmlPagamento($post["venda"]);
	    $html = $dados["html"];
	    
	    $valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
        $valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
        
        echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));
    }
    
    public function buscaPagamentoAction() {
         
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
         
        $erro = 0;
        $msg = "";
        $html = null;
         
        $post = $this->getRequest()->getPost();
         
        if(!empty($post["codigo"])){
            $tblVendaReceber = new Model_VendaAReceber();
            $result = $tblVendaReceber->find($post["codigo"])->current();
            if($result){               
                $html = $result->toArray();
                $html["valor"] = $this->view->NumeroParaMoeda($html["valor"]);
                $html["valorcambio"] = $this->view->NumeroParaMoeda($html["valorcambio"]);
                $html["valorreceber"] = $this->view->NumeroParaMoeda($html["valorreceber"]);
                $html["datavencimento"] = $this->view->data($html["datavencimento"]);    
            }
        }
         
        echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "html"=>$html));
    }

    
	public function estornarPagamentoAction() {
	
	    Zend_Layout::getMvcInstance()->disableLayout();
	    $this->_helper->viewRenderer->setNoRender();
	
	    $erro = 0;
	    $msg = "";
	    $html = null;
	    
	    $post = $this->getRequest()->getPost();
	    
	    $tblVendaAReceber = new Model_VendaAReceber();
	    $data["valorpago"] = 0;	    	    
	    $data["datapagamento"] = null;
	    $tblVendaAReceber->update($data, "codigo = {$post['codigo']}");
	    
	    //Gerando o retorno
	    $dados = $this->htmlPagamento($post["venda"]);
	    $html = $dados["html"];
	    
	    $valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
        $valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
        
        echo Zend_Json_Encoder::encode(array("html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));
	    
    }
    
    public function salvarPagamentoAction() {
    
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
        $erro = 0;
        $msg = "";
        $html = null;
         
        $post = $this->getRequest()->getPost();
         
        $tblVendaAReceber = new Model_VendaAReceber();
        $data["datapagamento"] = $post["datapagamento"];               
        $data["valorpago"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valorpago"])));
        $data["valorcambio"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valorcambioreceber"])));
        $valorReceber = floatval(str_replace(',', '.', str_replace('.', '', $post["valorreceber"])));
        unset($post["valorreceber"]);
        
        if($data["valorpago"] < $valorReceber){
            echo Zend_Json_Encoder::encode(array("msg"=> "Por favor preencha um valor recebido maior ou igual o valor a receber", "erro"=> 1));
            return;
        }
        
        $data["valorpago"] = ($data["valorpago"] > $valorReceber)?$valorReceber:$data["valorpago"];
        
        if(empty($data["datapagamento"])){
            echo Zend_Json_Encoder::encode(array("msg"=> "Por favor preencha a data de pagamento", "erro"=> 1));
            return;
        } else {
            $date = new Zend_Date($data["datapagamento"]);
            $data["datapagamento"] = $date->get("WWW");
        }
        $tblVendaAReceber->update($data, "codigo = {$post['codigo']}");
         
        //Gerando o retorno
        $dados = $this->htmlPagamento($post["venda"]);
        $html = $dados["html"];
                
        $valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
        $valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
        
        echo Zend_Json_Encoder::encode(array("html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));       
    }
    
    public function salvarReembolsoAction() {
    
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
        $erro = 0;
        $msg = "";
        $html = null;
         
        $post = $this->getRequest()->getPost();
                
        if($post["destino"] == "Contratante" && empty($post["cliente"])){
            echo Zend_Json_Encoder::encode(array("msg"=> "Por favor preencha o contratante que irá receber o reembolso", "erro"=> 1));
            return;
        }
                
        if($post["destino"] == "Venda" && empty($post["venda_destino"])){
            echo Zend_Json_Encoder::encode(array("msg"=> "Por favor preencha o número da venda de destino", "erro"=> 1));
            return;
        }
        
        //Verificando se a venda a receber está com o status diferente de remanejado
        $tblVendaReceber = new Model_VendaAReceber();
        $result = $tblVendaReceber->fetchRow("status = 'remanejado' AND codigo = {$post["codigo"]}");
        if($result){
            echo Zend_Json_Encoder::encode(array("msg"=> "Este item foi remanejado por outro usuário por favor clique em Atualizar Página no seu navegador", "erro"=> 1));
            return;
        }
        
        $post["valor_reembolso"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valor_reembolso"])));
        $post["valor_credito"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valor_credito"])));
        $post["valorcambio"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valorcambio"])));
        $post["valorpago"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valorpago"])));
        
        $valorReembolsoTotal = $post["valor_reembolso"]+$post["valor_credito"];
                
        if($valorReembolsoTotal > $post["valorpago"]){
            echo Zend_Json_Encoder::encode(array("msg"=> "O valor de reembolso + o valor de crédito ultrapassam o valor pago", "erro"=> 1));
            return;
        }
        
        if($valorReembolsoTotal < $post["valorpago"]){
            echo Zend_Json_Encoder::encode(array("msg"=> "O valor de reembolso + o valor de crédito é menor que o valor pago", "erro"=> 1));
            return;
        }
        
        if($post["destino"] == "Venda" && !empty($post["venda_destino"])){
                        
            if($post["venda"] == $post["venda_destino"]){
                echo Zend_Json_Encoder::encode(array("msg"=> "A venda de destino não pode ser a mesma que está em vigor", "erro"=> 1));
                return;
            }
            
            $tblVenda = new Model_Venda();
            $result = $tblVenda->find($post["venda_destino"])->current();
            if(!$result){
                echo Zend_Json_Encoder::encode(array("msg"=> "O código da venda de destino não existe em nossa base de dados", "erro"=> 1));
                return;
            } else {
                //Inserindo a parcela de crédito
                $data["tipo"] = 'À Vista';       
                $data["parcela"] = 0;
                $date = new Zend_Date();       
                $data["datavencimento"] = $date->get("WWW");       
                $data["valor"] = 0;       
                $data["venda"] = $post["venda_destino"]; 
                $data["datapagamento"] = $date->get("WWW");
                $data["valorpago"] = $post["valor_credito"];
                $data["valorcambio"] = $post["valorcambio"];
                $data["valorreceber"] = $post["valor_credito"];
                $data["status"] = "crédito";
                $data["venda_areceber_fornecedora"] = $post["codigo"];
                                
                $tblVendaReceber->insert($data);
                                
                //Atualizando a parcela fornecedora
                $tblVendaReceber->update(array("status"=>"remanejado", "vendadestino"=>$post["venda_destino"], "valorreembolsado"=>$post["valor_reembolso"]),"codigo = {$post["codigo"]}");
                unset($data);
            }
        }
        
        if($post["destino"] == "Contratante" && !empty($post["cliente"])){
            $tblClienteCredito = new Model_ClienteCredito();
            $result = $tblClienteCredito->fetchRow("venda_areceber = {$post["codigo"]}");
            if($result){
                echo Zend_Json_Encoder::encode(array("msg"=> "Essa parcela já foi creditada para o contratante selecionado", "erro"=> 1));
                return;
            } else {                
                //Inserindo a parcela de crédito para o contratante
                $data["valor"] = $post["valor_credito"]; 
                $date = new Zend_Date();
                $data["datapagamento"] = $date->get("WWW");
                $data["valorcambio"] = $post["valorcambio"];
                $data["cliente"] = $post["cliente"];
                $data["venda_fornecedora"] = $post["venda"];
                $data["venda_areceber_fornecedora"] = $post["codigo"];                           
                $tblClienteCredito->insert($data);
                
                
                //Atualizando a parcela fornecedora
                $tblVendaReceber->update(array("status"=>"remanejado", "valorreembolsado"=>$post["valor_reembolso"]), "codigo = {$post["codigo"]}");
                unset($data);
            }
        }
                                
        //Gerando o retorno
        $dados = $this->htmlPagamento($post["venda"]);
        $html = $dados["html"];
    
        $valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
        $valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
    
        echo Zend_Json_Encoder::encode(array("erro"=>0, "html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));
    }
    
    public function estornarReembolsoAction() {
    
    	Zend_Layout::getMvcInstance()->disableLayout();
    	$this->_helper->viewRenderer->setNoRender();
    
    	$erro = 0;
    	$msg = "";
    	$html = null;
    	 
    	$post = $this->getRequest()->getPost();
    	
    	$post["valor_reembolso"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valor_reembolso"])));
    	$post["valor_credito"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valor_credito"])));
    	$post["valorcambio"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valorcambio"])));
    	$post["valorpago"] = floatval(str_replace(',', '.', str_replace('.', '', $post["valorpago"])));    
    	$valorReembolsoTotal = $post["valor_reembolso"]+$post["valor_credito"];
        
    	$tblVendaReceber = new Model_VendaAReceber();
    	if($post["destino"] == "Venda" && !empty($post["venda_destino"])){
    
    		if($post["venda"] == $post["venda_destino"]){
    			echo Zend_Json_Encoder::encode(array("msg"=> "A venda de destino não pode ser a mesma que está em vigor", "erro"=> 1));
    			return;
    		}
    
    		$tblVenda = new Model_Venda();
    		$result = $tblVenda->find($post["venda_destino"])->current();
    		if(!$result){
    			echo Zend_Json_Encoder::encode(array("msg"=> "O código da venda de destino não existe em nossa base de dados", "erro"=> 1));
    			return;
    		} else {    			
    			//Retirando o crédito da venda que foi repassado para outra venda e retirando o status de remanejado
    			$tblVendaReceber->delete("venda_areceber_fornecedora = '{$post["codigo"]}' AND venda = '{$post["venda_destino"]}'");    			
    			$tblVendaReceber->update(array("status"=>"ativo", "vendadestino"=>NULL, "valorreembolsado"=>NULL),"codigo = {$post["codigo"]}");    			
    		}
    	}
    
    	if($post["destino"] == "Contratante" && !empty($post["cliente"])){
    		$tblClienteCredito = new Model_ClienteCredito();
    		$result = $tblClienteCredito->fetchRow("venda_areceber IS NULL AND valor > 0 AND venda_areceber_fornecedora = {$post["codigo"]}");
    		if($result){
    			//Removendo parcela de crédito para o outro cliente...
    			$tblClienteCredito->delete("venda_fornecedora = {$post["venda"]} AND venda_areceber_fornecedora = {$post["codigo"]}");
    			$tblVendaReceber->update(array("status"=>"ativo", "valorreembolsado"=>NULL), "codigo = {$post["codigo"]}");
    		} else {
    			echo Zend_Json_Encoder::encode(array("msg"=> "Não é possível fazer o estorno pois o cliente de destino já utilizou o crédito na venda {}", "erro"=> 1));
    			return;    			
    		}
    	}
    
    	//Gerando o retorno
    	$dados = $this->htmlPagamento($post["venda"]);
    	$html = $dados["html"];
    
    	$valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
    	$valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";
    	$saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";
    	$saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
    
    	echo Zend_Json_Encoder::encode(array("erro"=>0, "html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));
    }
    
    public function gridPagamentoAction(){
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
        $post = $this->getRequest()->getPost();
    
        $dados = $this->htmlPagamento($post["venda"]);
        $html = $dados["html"];
        
        $valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
        $valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";        
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";        
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
        
        echo Zend_Json_Encoder::encode(array("html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));
    }
    
    private function htmlPagamento($venda){
        //Gerando o retorno
        $tblVendaAReceber = new Model_VendaAReceber();
        $result = $tblVendaAReceber->fetchAll("venda = {$venda} AND (valor > 0 OR valorpago > 0)","parcela ASC");
    
        $html = null;
        $classRecebimento = null;
        $titleRecebimento = null;
        $totalValor = 0;
        $totalCambio = 0;
        $totalPagamento = 0;            
        $totalPagamentoCambiado = 0;
        if(count($result)>0):
            
            $aux = 0;
            foreach($result as $pagamento):
            
                if(empty($pagamento->datapagamento)){
                    $classRecebimento = "ic-money pagarRow";
                    $titleRecebimento = "Receber";                    
                } elseif (empty($pagamento->venda_areceber_fornecedora)) {
                    $classRecebimento = "ic-zone-money estornarRow";
                    $titleRecebimento = "Estornar";                    
                }
                
                $class = ($aux%2 == 0)?"even":"odd";
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$pagamento->codigo}</td>";
                $html .= "<td>{$pagamento->tipo}</td>";
                $html .= "<td>{$pagamento->parcela}</td>";
                $dataVencimento = $this->view->data($pagamento->datavencimento);
                $html .= "<td>{$dataVencimento}</td>";

                $totalValor += $pagamento->valor;
                $valor = $this->view->NumeroParaMoeda($pagamento->valor);
                $html .= "<td>{$valor}</td>";
                                
                $totalPagamento += $pagamento->valorpago;
                $valorPago = $this->view->NumeroParaMoeda($pagamento->valorpago);
                $html .= "<td class='valorPago'>{$valorPago}</td>";
                
                $totalCambio += $pagamento->valorcambio;
                $valorCambio = $this->view->NumeroParaMoeda($pagamento->valorcambio);
                $html .= "<td>{$valorCambio}</td>";
                
                $pagamentoCambiado = ($pagamento->valorcambio > 0)?bcdiv($pagamento->valorpago, $pagamento->valorcambio, 2):$pagamento->valorpago;
                $totalPagamentoCambiado += $pagamentoCambiado;
                $pagamentoCambiado = $this->view->NumeroParaMoeda($pagamentoCambiado);
                $html .= "<td class='valorGrid'>{$pagamentoCambiado}</td>";
                               
                
                $dataPagamento = $this->view->data($pagamento->datapagamento);
                $html .= "<td>{$dataPagamento}</td>";
                                
                $html .= "<td width='210'>";
                 
                if($pagamento->status != "remanejado"):               
                    if(empty($pagamento->venda_areceber_fornecedora)) $html .= "<a data-valor='{$valor}' data-cambio='{$valorCambio}' data-pagamento='{$pagamento->codigo}' class='mws-ic-16 {$classRecebimento}' title='&nbsp;'>&nbsp;</a>";
                    
                    if($pagamento->tipo == "Boleto" && $pagamento->valor > 0){
                        $url = $this->view->url(array("action"=>"boleto","module"=>"admin","controller"=>"venda", "vendareceber"=>$pagamento->codigo),null,true);
                        $html .= "<a target='blank' href='$url' class='mws-ic-16 ic-doc-shred boletoRowPagamento' title='&nbsp;'>&nbsp;</a>";    
                    }
                    
                    if($pagamento->tipo == "Boleto" && ($pagamento->valorpago <= 0)){ 
                        $dataNotificacao = null;
                        if(!empty($pagamento->datanotificacao)){                            
                            $dataNotificacao = $this->view->data($pagamento->datanotificacao, "comHorario");
                        } 
                        $tblVenda = $pagamento->findParentRow("Model_Venda");
                        $tblContratante = $tblVenda->findParentRow("Model_Cliente");                      
                        $html .= "<a data-email='{$tblContratante->email}' data-notificacao='{$dataNotificacao}' data-pagamento='{$pagamento->codigo}' class='mws-ic-16 ic-envelope emailRowPagamento' title='&nbsp;'>&nbsp;</a>";
                    }
                                    
                    if(empty($pagamento->venda_areceber_fornecedora) && !($pagamento->datapagamento > 0)) $html .= "<a data-codigo='{$pagamento->codigo}' class='mws-ic-16 ic-edit editRowPagamento' title='&nbsp;'>&nbsp;</a>";
                    if($pagamento->datapagamento > 0){
                        $html .= "<a data-valorpago='{$valorPago}' data-cambio='{$valorCambio}' data-codigo='{$pagamento->codigo}' class='mws-ic-16 ic-arrow-refresh reembolsoRow' title='Reembolso'>&nbsp;</a>";
                    } else {
                        $html .= "<a data-pagamento='{$pagamento->codigo}' class='mws-ic-16 ic-cross deleteRowPagamento' title='&nbsp;'>&nbsp;</a>";
                    }
                else:
                    $html .= "<a data-status='{$pagamento->status}' data-codigo='{$pagamento->codigo}' class='mws-ic-16 ic-arrow-refresh reembolsoRow' title='Remanejado'>&nbsp;</a>";
                endif;
                
                if(!empty($pagamento->venda_areceber_fornecedora)):
                    //Buscando o código da venda
                    $result = $tblVendaAReceber->find($pagamento->venda_areceber_fornecedora)->current();
                    if($result):
                        $link = $this->view->baseUrl."/admin/venda/cadastro/codigo/{$result->venda}";                    
                        $html .= "<a href='{$link}' target='_blank' class='mws-ic-16 ic-arrow-undo' title='Créd. Conta {$pagamento->venda_areceber_fornecedora}'>&nbsp;</a>";
                    endif;
                endif;
                
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;                                
            endforeach;
                
            //Totalizadores
            $html .= '<tr class="gradeX even elem">';
            $html .= '<td><strong>Total</strong></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= "<td><strong>{$this->view->NumeroParaMoeda($totalValor)}</strong></td>";
            $html .= "<td><strong>{$this->view->NumeroParaMoeda($totalPagamento)}</strong></td>";
            $html .= "<td><strong>Média: {$this->view->NumeroParaMoeda($totalCambio/$aux)}</strong></td>";
            $html .= "<td><strong>{$this->view->NumeroParaMoeda($totalPagamentoCambiado)}</strong></td>";
            $html .= '<td></td>';
            
            $html .= '<td></td>';
            $html .= "</tr>";
        else:
            $html = '<tr class="gradeX even zero"><td colspan="10" style="text-align:center;">Nenhuma forma de pagamento até o momento</td></tr>';
        endif;

        $dados["html"] = $html;
        $dados["valorReceber"] = $totalValor;
        $dados["valorPago"] = $totalPagamentoCambiado;
        
        //Buscando o total de produtos para fazer a conta.        
        $produtos = $this->htmlProduto($venda);        

        
        $saldo = $produtos["valorTotal"] - $totalPagamentoCambiado;        
        $dados["saldoDevedor"] = $saldo;
        
        $saldo = $totalValor - $totalPagamento;        
        $dados["saldoDevedorReal"] = $saldo;
        
        return $dados;
    }
    
    public function gridProdutoAction(){
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
        $post = $this->getRequest()->getPost();
        
        $sigla = "U$";
        $dados = $this->htmlProduto($post["venda"]);
        $html = $dados["html"];
        
        //Pegando a sigla do último produto cadastrado
        $tblProduto = new Model_VendaProduto();
        $vendaProduto = $tblProduto->fetchRow("venda = {$post["venda"]}", "codigo DESC");
        
        $cambio = 1;
        if($vendaProduto):
            $moeda = $vendaProduto->findParentRow("Model_Moeda");
            $sigla = $moeda->sigla;
            
            //Buscando o câmbio do dia
            $data = date('Y-m-d');
            $cotacao = $moeda->findDependentRowset("Model_Cotacao", null, $moeda->select()->where("moeda = $moeda->codigo AND data = '$data'"))->current();
            $cambio = (isset($cotacao->valor))?$this->view->NumeroParaMoeda($cotacao->valor):"0,00";
        endif;
        
        $valorTotal = (isset($dados["valorTotal"]))?$this->view->NumeroParaMoeda($dados["valorTotal"]):'0,00';
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):'0,00';
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):'0,00';
        
        echo Zend_Json_Encoder::encode(array("html"=>$html, "sigla"=>$sigla, "cambio"=>$cambio, "valorTotal"=>$valorTotal, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));
    }
    
    public function getReembolsoAction(){
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        $erro = 0;
        $msg = "";
        $post = $this->getRequest()->getPost();
        $vendaReceber = $post["codigo"];
        
        $destino = NULL;
        $venda_destino = NULL;
        $cliente = NULL;
        $valor_credito = NULL;
        $valorcambio = NULL;
        $valor_reembolso = NULL;
        $optionCliente = NULL;
        
        if(!empty($vendaReceber)){
            //Verificando se o reembolso foi feito para um contratante
            $tblClienteCredito = new Model_ClienteCredito();
            $tblVendaAReceber = new Model_VendaAReceber();
            $result = $tblClienteCredito->fetchRow("venda_areceber_fornecedora = $vendaReceber");
            if($result) {
                $destino = "Contratante";
                $cliente = $result->cliente;
                $valor_credito = $this->view->NumeroParaMoeda($result->valor);
                
                //Populando option
                $tblCliente = new Model_Cliente();
                $res = $tblCliente->find($cliente)->current();
                $cpfCnpj = ($res->tipopessoa == "F")?" CPF - ".$res->cpf:" CNPJ - ".$res->cnpj;
                $nome = $res->nome.$cpfCnpj;
                $optionCliente = "<option selected='selected' value='$res->codigo'>$nome</option>"; 
            } else {                
                $result = $tblVendaAReceber->fetchRow("status = 'crédito' AND venda_areceber_fornecedora = $vendaReceber");
                if($result){ 
                    $destino = "Venda";
                    $venda_destino = $result->venda;
                    $valor_credito = $this->view->NumeroParaMoeda($result->valor);
                }
            }

            if(count($result) > 0){                                
                $valorcambio = $this->view->NumeroParaMoeda($result->valorcambio);
                
                //Buscando o valor reembolsado
                $result = $tblVendaAReceber->find($vendaReceber)->current();
                if($result) $valor_reembolso = $this->view->NumeroParaMoeda($result->valorreembolsado);
                
            }
            
        }
    
        echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "destino"=>$destino, "venda_destino"=>$venda_destino, "cliente"=>$cliente, "valor_credito"=>$valor_credito, "valor_reembolso"=>$valor_reembolso, "valorcambio"=>$valorcambio, "optionCliente"=>$optionCliente));
    }
    
    private function htmlProduto($venda){
         
        $html = null;
         
        $tblVendaProduto = new Model_VendaProduto();
        $produtos = $tblVendaProduto->fetchAll("venda = $venda","codigo DESC");
         
        $totalValor = 0;
        if(count($produtos)>0):
            $aux = 0;
            foreach($produtos as $vendaProduto):
                $moeda = $vendaProduto->moeda;
                $tblMoeda = $vendaProduto->findParentRow("Model_Moeda");                      
                $produto = $this->view->blocos()->produto($vendaProduto);                             
                $class = ($aux%2 == 0)?"even":"odd";
                $valor = $this->view->NumeroParaMoeda($vendaProduto->valor);
                $totalValor+= $vendaProduto->valor;
                
                $produtoNome = ($vendaProduto->tipo == "pacote")?$produto->descricao:$produto->nome;
                $tipo = ucwords($vendaProduto->tipo);
                
                $passageiroNome = null;
                $passageiro = $vendaProduto->findParentRow("Model_Passageiro");
                if($passageiro){
                    $passageiroNome = $passageiro->nome." ".$passageiro->sobrenome;    
                }
                
                $html .= "<tr class='gradeX {$class} elem'>";
                $html .= "<td>{$passageiroNome}</td>";
                $html .= "<td>{$tipo}</td>";
                $html .= "<td>{$produtoNome}</td>";
                $html .= "<td>{$vendaProduto->descricao}</td>";
                $html .= "<td class='valorGrid'>{$this->view->NumeroParaMoeda($vendaProduto->valor)}</td>";
               
                $html .= "<td>";                
                $html .= "<a data-codigo='{$vendaProduto->codigo}' class='mws-ic-16 ic-edit editRowProduto' title='Editar'>&nbsp;</a>";
                $html .= "<a data-codigo='{$vendaProduto->codigo}' class='mws-ic-16 ic-cross deleteRowProduto' title='Excluir'>&nbsp;</a>";
                $html .= "</td>";
                $html .= "</tr>";
                $aux++;
            endforeach;
            
             //Totalizadores
            $html .= '<tr class="gradeX even elem">';
            $html .= '<td></td>';
            $html .= '<td></td>';
            $html .= '<td></td>';       
            $html .= '<td></td>';       
            $html .= "<td><strong>{$this->view->NumeroParaMoeda($totalValor)}</strong></td>";        
            $html .= '<td></td>';
            $html .= '</tr>';
        else:
            $html = "<tr class='gradeX even elem'>";
            $html.= "<td colspan='6' style='text-align:center;'>";
            $html.= "Nenhum produto adicionado até o momento";
            $html.= "</td>";
            $html.= "</tr>";
        endif;
        
        $dados["html"] = $html;
        $dados["valorTotal"] = $totalValor;
        
        //Atualizando o valor da venda
        $tblVenda = new Model_Venda();
        $tblVenda->update(array("valortotal"=>$totalValor), "codigo = $venda");
        
        //Buscando o total de valor que já foi pago
        $tblVendaReceber = new Model_VendaAReceber();
        $sql = $tblVendaReceber->select()->from('venda_areceber', array('SUM(valor) as totalValor', 'SUM(valorpago) as totalPago', 'SUM(valorcambio) as totalCambio'));
        $sql->where("venda = $venda");
                
        $vendaReceber = $tblVendaReceber->fetchRow($sql);
        $totalPagoCambiado = 0;
        
        $saldoDevedorReal = $totalValor;        
        if($vendaReceber){
            $totalPagoCambiado = $vendaReceber->totalPago;             
            $saldoDevedorReal = $vendaReceber->totalValor - $vendaReceber->totalPago; 
            if($vendaReceber["totalCambio"] > 0):
                $totalPagoCambiado = bcdiv($totalPagoCambiado, $vendaReceber["totalCambio"], 2);
            endif;
            
        }
        $saldo = $totalValor - $totalPagoCambiado;
        $dados["saldoDevedor"] = $saldo;
        $dados["saldoDevedorReal"] = $saldoDevedorReal;
                
        return $dados;         
    }
    
    public function delProdutoAction() {
    
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
        $erro = 0;
        $msg = "";
        $html = null;
    
        $post = $this->getRequest()->getPost();
    
        if(empty($post["venda"]) || empty($post["codigo"])){
            echo Zend_Json_Encoder::encode(array("msg"=> "Parâmetros inválidos", "erro"=> 1));
            return;
        }
    
        //Deletando o produto da venda
        $tblVendaProduto = new Model_VendaProduto();
        $tblVendaProduto->delete("codigo = {$post["codigo"]}");
        $dados = $this->htmlProduto($post["venda"]);
        $html = $dados["html"];
        
        $valorTotal = (isset($dados["valorTotal"]))?$this->view->NumeroParaMoeda($dados["valorTotal"]):'0,00';
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):'0,00';
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):'0,00';
        
        echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "html"=>$html, "valorTotal"=>$valorTotal, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));        
    }
    
    public function boletoAction() {    
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
         
     public function salvarReplicaAction() {
     
         Zend_Layout::getMvcInstance()->disableLayout();
         $this->_helper->viewRenderer->setNoRender();
     
         $erro = 0;
         $msg = "";
         $html = null;
          
         $post = $this->getRequest()->getPost();
          
         if(empty($post["cliente"]) || empty($post["venda"])){
             echo Zend_Json_Encoder::encode(array("msg"=> "Por favor preencha os campos obrigatórios (*)", "erro"=> 1));
             return;
         }
         
         //Replicando a venda
         $tblVenda = new Model_Venda();
         $venda = $tblVenda->find($post["venda"])->current();
         if($venda){
             $venda->cliente = $post["cliente"];
             $venda->passaporte = $post["passaporte"]; 

             if(!empty($post["emissaopassaporte"])):
                 $date = new Zend_Date($post["emissaopassaporte"]);
                 $venda->emissaopassaporte = $date->get("WWW");
             else:
                 $venda->emissaopassaporte = null;
             endif;
             
             $venda->emissorpassaporte = $post["emissorpassaporte"];
             
             if(!empty($post["vencimentopassaporte"])):
                 $date = new Zend_Date($post["vencimentopassaporte"]);
                 $venda->vencimentopassaporte = $date->get("WWW");
             else:
                 $venda->vencimentopassaporte = null;
             endif;
             
             $arrayVenda = $venda->toArray();
             unset($arrayVenda["codigo"]);
             
             $codigoVenda = $tblVenda->insert($arrayVenda);  

             //Replicando o venda a receber
             $tblVendaReceber = new Model_VendaAReceber();
             $recebimentos = $tblVendaReceber->fetchAll("venda = {$post['venda']}");
             if(count($recebimentos)>0) {
                 foreach($recebimentos as $recebimento){
                     $recebimentoReplica = $recebimento->toArray();
                     $recebimentoReplica["venda"] = $codigoVenda;                     
                     $recebimentoReplica["codigo"] = null;                                          
                     $tblVendaReceber->insert($recebimentoReplica);
                 }    
             }
                         
             //Replicando o venda produto
             $tblVendaProduto = new Model_VendaProduto();
             $produtos = $tblVendaProduto->fetchAll("venda = {$post['venda']}");
             if(count($produtos)>0) {
                 foreach($produtos as $produto){
                     $produtoReplica = $produto->toArray();
                     $produtoReplica["venda"] = $codigoVenda;                     
                     $produtoReplica["codigo"] = null;                                          
                     $tblVendaProduto->insert($produtoReplica);
                 }    
             }
             
             
         }
     
         echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));
     }
     
     
     public function pacoteAction() {
         /* Array com os dados da tabela */
         $this->view->etiquetas = array('codigo'=>'Cód.', 'descricao'=>'Descrição', 'datasaida'=>'Saída', 'liderpacote'=>'Líder','valorvendaindividual'=>'Valor', 'qtdparticipantes'=>'Participantes');
         
         $iframe = $this->_getParam("iframe",false);
         if($iframe){
             $this->_helper->layout()->setLayout('admin-form');
         }
     }
     
     public function pacoteDadosAction() {
     
         $this->_helper->layout()->disableLayout();
     
         $tbl = new Model_Pacote();
     
         $query = $tbl->select()->setIntegrityCheck(false);
     
         if(!empty($this->query))
             $query->where($this->query);
     
         if($this->getRequest()->isPost()) {
     
             //Dados da busca
             $post = $this->getRequest()->getPost();
     
             //Termo da ordenação
             $ordem = $this->_getParam("ordenacao");
             $ordenacao = !empty($ordem)?$ordem:$this->ordenacao;
     
             $termoBusca = $this->_getParam("busca");	
			 if(!empty($termoBusca)) {				
                $query->orWhere("lower(descricao) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datainiciovenda) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datafimvenda) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datasaida) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(datachegada) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(horasaida) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(horachegada) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(localsaida) like '%" . $termoBusca . "%'");
                $query->orWhere("lower(qtdparticipantes) like '%" . $termoBusca . "%'");																								
			}
     
         }
     
         $query->order($ordenacao);
     
         $dados = $tbl->fetchAll($query);
     
         $paginator = Zend_Paginator::factory($dados);
         $paginator->setCurrentPageNumber($this->_getParam("pagina", 1));
     
         $porPagina = $this->_getParam("por-pagina");
     
         //numero de itens por pagina
         $paginator->setItemCountPerPage($porPagina);
     
         //numero de indices de paginas que serão exibidos
         $paginator->setPageRange(6);
     
         $this->view->paginacao = $paginator;
     }
     
    public function mailCobrancaAction() {
    
        Zend_Layout::getMvcInstance()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    
        $erro = 0;
        $msg = "";
        $html = null;
        
        $post = $this->getRequest()->getPost();
        
        if(empty($post["venda"]) || empty($post["codigo"]) || empty($post["email"])){
            echo Zend_Json_Encoder::encode(array("msg"=> "Parâmetros inválidos", "erro"=> 1));
            return;
        }
        
        //Marcado a venda a receber para notificar
        $tblVendaReceber = new Model_VendaAReceber();
        $data["notificar"] = '1';
        $data["emailnotificacao"] = trim(strtolower($post["email"]));
        $tblVendaReceber->update($data, "codigo = {$post["codigo"]}");
        
        //Gerando o retorno
        $dados = $this->htmlPagamento($post["venda"]);
        $html = $dados["html"];
        
        $valorReceber = (isset($dados["valorReceber"]))?$this->view->NumeroParaMoeda($dados["valorReceber"]):"0,00";
        $valorPago = (isset($dados["valorPago"]))?$this->view->NumeroParaMoeda($dados["valorPago"]):"0,00";
        $saldoDevedor = (isset($dados["saldoDevedor"]))?$this->view->NumeroParaMoeda($dados["saldoDevedor"]):"0,00";
        $saldoDevedorReal = (isset($dados["saldoDevedorReal"]))?$this->view->NumeroParaMoeda($dados["saldoDevedorReal"]):"0,00";
        
        echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "html"=>$html, "valorReceber"=>$valorReceber, "valorPago"=>$valorPago, "saldoDevedor"=>$saldoDevedor, "saldoDevedorReal"=>$saldoDevedorReal));
    }
     	
	
}
