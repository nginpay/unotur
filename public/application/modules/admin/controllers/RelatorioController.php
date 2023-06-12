<?php
require_once 'AdminController.php';

/**
 * Admin_IndexController - Controller responsavel por
 *
 * @version 1.0.0 - 28/06/2012
*/

class Admin_RelatorioController extends AdminController {

    public function init() {
        parent::init();
        
        $this->view->headScript()->appendFile($this->view->baseUrl('/js/admin/relatorio/index.js'));
    }
         
     public function cotacaoAction() {
         /* Array com os dados da tabela */
         $this->view->etiquetas = array('moeda'=>'Moeda','data'=>'Data','valor'=>'Valor', 'usuario'=>'Usuário'); 

         //Moedas cadastradas
         $tblMoeda = new Model_Moeda();
         $this->view->moedas = $tblMoeda->fetchAll("codigo in (SELECT moeda FROM cotacao)");
     }
      
     public function cotacaoDadosAction() {
          
         $this->_helper->layout()->disableLayout();
          
         $tbl = new Model_Cotacao();
          
         $query = $tbl->select()->setIntegrityCheck(false);
          
         if(!empty($this->query))
             $query->where($this->query);
         
         if($this->getRequest()->isPost()) {
              
             //Dados da busca
             $post = $this->getRequest()->getPost();
              
             //Termo da ordenação
             $ordem = $this->_getParam("ordenacao", "data DESC");
             $ordenacao = !empty($ordem)?$ordem:$this->ordenacao;
              
            /* Buscas */             
            $dataInicial = $this->_getParam("dataInicial");
            if(!empty($dataInicial)) {
                $date = new Zend_Date($dataInicial);
                $dataInicial = $date->get("WWW");
                $query->where("data >= '$dataInicial'");
            }
            
            $dataFinal = $this->_getParam("dataFinal");
            if(!empty($dataFinal)) {
                $date = new Zend_Date($dataFinal);
                $dataFinal = $date->get("WWW");
                $query->where("data <= '$dataFinal'");
            }
            
            $moeda = $this->_getParam("moeda");
            if(!empty($moeda)) {                
                $query->where("moeda = '$moeda'");
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
     
     public function passageiroAction() {
         /* Array com os dados da tabela */
         $this->view->etiquetas = array('nome'=>'Passageiro', 'datanascimento'=>'Nascimento', 'passaporte'=>'Passaporte', 'emissaopassaporte'=>'Emissão', 'emissorpassaporte'=>'Emissor', 'vencimentopassaporte'=>'Vencimento');
                  
         //Lista dos pacotes disponíveis
         $tblPacote = new Model_Pacote();
         $this->view->pacotes = $tblPacote->fetchAll(null,"descricao ASC");
     }
      
     public function passageiroDadosAction() {
          
         $this->_helper->layout()->disableLayout();
          
         $tbl = new Model_VendaProduto();
          
         $query = $tbl->select()->setIntegrityCheck(false);
         $query->where("pacote IS NOT NULL AND passageiro IS NOT NULL AND (venda NOT IN (SELECT venda FROM venda_areceber) OR venda IN (SELECT venda FROM venda_areceber WHERE status <> 'remanejado'))");
                    
         if($this->getRequest()->isPost()) {              
             //Dados da busca
             $post = $this->getRequest()->getPost();
                           
            /* Buscas */             
            $pacote = $this->_getParam("pacote");
            if(!empty($pacote)) {
                $query->where("pacote = $pacote");
            }

            $pacote = $this->_getParam("statuspacote");
            if(!empty($pacote)) {
            	if($pacote == "aberto"){
            		$query->where("venda in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida >= CURRENT_DATE()))");
            	} else {
            		$query->where("venda in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida < CURRENT_DATE()))");
            	}
            }
            
		    $liderpacote = $this->_getParam("liderpacote");	
			if(!empty($liderpacote)) {				                
                $query->where("pacote IN (SELECT codigo FROM pacote WHERE liderpacote IN (SELECT codigo FROM cliente WHERE lower(nome) like '%".$liderpacote."%'))");                                
			}
			
		    $cliente = $this->_getParam("cliente");	
			if(!empty($cliente)) {				                
                $query->where("venda IN (SELECT codigo FROM venda WHERE cliente IN (SELECT codigo FROM cliente WHERE lower(nome) like '%".$cliente."%'))");                               
			}
			
		    $passageiro = $this->_getParam("passageiro");	
			if(!empty($passageiro)) {				                
                $query->where("passageiro IN (SELECT codigo FROM passageiro WHERE lower(nome) like '%".$passageiro."%' || lower(sobrenome) like '%".$passageiro."%')");                               
			}									         
         }
          
         $query->order("codigo DESC");
          
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
     
     public function relatorioPassageiroAction() {
         Zend_Layout::getMvcInstance()->disableLayout();
         $this->_helper->viewRenderer->setNoRender();
     
         $msg = "";
         $erro = 0;
          
         #gerar matriz de dados
         $dados = array();
          
         $tbl = new Model_VendaProduto();
          
         $query = $tbl->select()->setIntegrityCheck(false);         
         $query->where("pacote IS NOT NULL AND passageiro IS NOT NULL AND (venda NOT IN (SELECT venda FROM venda_areceber) OR venda IN (SELECT venda FROM venda_areceber WHERE status <> 'remanejado'))");
                    
         if($this->getRequest()->isPost()) {              
             //Dados da busca
             $post = $this->getRequest()->getPost();
                           
            /* Buscas */             
            $pacote = $this->_getParam("pacote");
            if(!empty($pacote)) {
                $query->where("pacote = $pacote");
            }

            $pacote = $this->_getParam("statuspacote");
            if(!empty($pacote)) {
            	if($pacote == "aberto"){
            		$query->where("venda in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida >= CURRENT_DATE()))");
            	} else {
            		$query->where("venda in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida < CURRENT_DATE()))");
            	}
            }
            
		    $liderpacote = $this->_getParam("liderpacote");	
			if(!empty($liderpacote)) {				                
                $query->where("pacote IN (SELECT codigo FROM pacote WHERE liderpacote IN (SELECT codigo FROM cliente WHERE lower(nome) like '%".$liderpacote."%'))");                                
			}
			
		    $cliente = $this->_getParam("cliente");	
			if(!empty($cliente)) {				                
                $query->where("venda IN (SELECT codigo FROM venda WHERE cliente IN (SELECT codigo FROM cliente WHERE lower(nome) like '%".$cliente."%'))");                               
			}
			
		    $passageiro = $this->_getParam("passageiro");	
			if(!empty($passageiro)) {				                
                $query->where("passageiro IN (SELECT codigo FROM passageiro WHERE lower(nome) like '%".$passageiro."%' || lower(sobrenome) like '%".$passageiro."%')");                               
			}									         
         }
     
         $query->order("codigo DESC");
              
         $dados = $tbl->fetchAll($query);
     
         #seta as propriedades
         $this->separador = ";";
         $this->path = getcwd()."/images/default/tmp/";
         $this->arquivo = "rel_passageiros_".date('h_i_s');
     
         #gera cabeçalho
         $cabecalho = array("Passageiro", "Nascimento", "Passaporte", "Emissão", "Emissor", "Vencimento", "Contratante", "Pacote", "Líder");
                  
         #gera string de cabeçalho
         $colunas = "";
         foreach($cabecalho as $coluna){
             $coluna = utf8_decode($coluna);
         if ($colunas == ""){
             $colunas .= $coluna;
         } else {
             $colunas .= $this->separador.$coluna;         
         }
     }
     
     $saida[] = $colunas;
      
     #gera string do corpo do arquivo
     foreach($dados as $vendaPassageiro){
         $passageiro = $vendaPassageiro->findParentRow("Model_Passageiro");       
         $venda = $vendaPassageiro->findParentRow("Model_Venda");
         $contratante = $venda->findParentRow("Model_Cliente");
         $vendaProduto = $venda->findDependentRowSet("Model_VendaProduto", null, $venda->select()->where("pacote IS NOT NULL"))->current();
         $pacote = $vendaProduto->findParentRow("Model_Pacote");
         $lider = $pacote->findParentRow("Model_Cliente");
         
         $nome = $passageiro->nome." ".$passageiro->sobrenome;
         $colunaValues["nome"] = (!empty($nome))?$nome:" ";
         $colunaValues["datanascimento"] = (!empty($passageiro->datanascimento))?$this->view->data($passageiro->datanascimento):" ";
         $colunaValues["passaporte"] = (!empty($passageiro->passaporte))?$passageiro->passaporte:" ";
         $colunaValues["emissaopassaporte"] = (!empty($passageiro->emissaopassaporte))?$this->view->data($passageiro->emissaopassaporte):" ";
         $colunaValues["emissorpassaporte"] = (!empty($passageiro->emissorpassaporte))?$passageiro->emissorpassaporte:" ";
         $colunaValues["vencimentopassaporte"] = (!empty($passageiro->vencimentopassaporte))?$this->view->data($passageiro->vencimentopassaporte):" ";
         $colunaValues["contratante"] = (!empty($contratante->nome))?$contratante->nome:" ";
         $colunaValues["pacote"] = (!empty($pacote->descricao))?$pacote->descricao:" ";
         $lider = isset($lider->nome)?$lider->nome:null;
         $colunaValues["lider"] = (!empty($lider))?$lider:" ";
      
         #pega as variaveis do array
         $colunasDados = "";
         foreach($colunaValues as $coluna){
             $colunasDados .= utf8_decode($coluna).$this->separador;
         }
         $saida[] = $colunasDados;
    }
          
    $arquivoRetorno = null;
      
    #verifica se alguma linha foi inserida
    if(count($saida)>1){
        #monta o corpo do CSV
        $corpo = implode("\n", $saida);
        $corpo = str_replace(";;", ";", $corpo);
     
       #abre um arquivo para escrita, se o arquivo não existir ele tenta criar
       $fp = fopen ($this->path.$this->arquivo.".csv", "w");
       if($fp <> NULL){
         #escreve no arquivo
         fwrite($fp, $corpo);
         #fecha o arquivo
         fclose($fp);
         $arquivoRetorno = "/images/default/tmp/".$this->arquivo.".csv";
      }
    } else {
         $msg = 'Nenhum registro encontrado';
         $erro = 1;
    }
      
    echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "arquivo"=>$arquivoRetorno));
      
   }
   
   public function contratanteAction() {
       /* Array com os dados da tabela */
       $this->view->etiquetas = array('venda'=>'Venda', 'contratante'=>'Contratante', 'passageiros'=>'Passageiros', 'totalprodutos'=>'Total', 'valorpago'=>'Pago', 'saldo'=>'Receber', 'moeda'=>'Moeda', 'pacote'=>'Pacote', 'telefone'=>'Telefone', 'celular'=>'Celular');
       
       //Lista dos pacotes disponíveis
       $tblPacote = new Model_Pacote();
       $this->view->pacotes = $tblPacote->fetchAll(null,"descricao ASC");
   }
   
   public function contratanteDadosAction() {
        
       $this->_helper->layout()->disableLayout();
        
       $tbl = new Model_Venda();
        
       $query = $tbl->select()->setIntegrityCheck(false);
        
       $dados = null;
        
       if($this->getRequest()->isPost()) {
           //Dados da busca
           $post = $this->getRequest()->getPost();
   
           /* Buscas */                      
           $contratante = $this->_getParam("cliente");
           if(!empty($contratante)) {
               $query->where("cliente in (SELECT codigo FROM cliente WHERE lower(nome) like '%".$contratante."%')");
           }
           
           $pacote = $this->_getParam("pacote");
           if(!empty($pacote)) {
               $query->where("codigo in (SELECT venda FROM venda_produto WHERE pacote = $pacote)");
           }
           
           $pacote = $this->_getParam("statuspacote");
           if(!empty($pacote)) {
           	if($pacote == "aberto"){
           		$query->where("codigo in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida >= CURRENT_DATE()))");
           	} else {
           		$query->where("codigo in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida < CURRENT_DATE()))");
           	}
           }
           
           $tipo = $this->_getParam("tipo");
           if(empty($tipo)) {
               //Não mostrar os que estão com status de remanejado
               $query->where("codigo NOT IN (SELECT venda FROM venda_areceber WHERE status = 'remanejado')");
           } else {
               $query->where("codigo IN (SELECT venda FROM venda_areceber WHERE status = 'remanejado')");
           }

           $query->order("codigo DESC");
           //echo $query; exit;
           $dados = $tbl->fetchAll($query);
       }
        
        
       $paginator = Zend_Paginator::factory($dados);
       $paginator->setCurrentPageNumber($this->_getParam("pagina", 1));
        
       $porPagina = $this->_getParam("por-pagina");
        
       //numero de itens por pagina
       $paginator->setItemCountPerPage($porPagina);
        
       //numero de indices de paginas que serão exibidos
       $paginator->setPageRange(6);
        
       $this->view->paginacao = $paginator;
   }
   
   public function relatorioContratanteAction() {
       Zend_Layout::getMvcInstance()->disableLayout();
       $this->_helper->viewRenderer->setNoRender();
        
       $msg = "";
       $erro = 0;
   
       #gerar matriz de dados
       $dados = array();
   
       $tblVenda = new Model_Venda();
       $query = $tblVenda->select()->setIntegrityCheck(false);
       $query->from("venda",array("*"));
        
       if($this->getRequest()->isPost()) {
           //Dados da busca
           $post = $this->getRequest()->getPost();
   
           /* Buscas */                      
           $contratante = $this->_getParam("cliente");
           if(!empty($contratante)) {
               $query->where("cliente in (SELECT codigo FROM cliente WHERE lower(nome) like '%".$contratante."%')");
           }
           
           $pacote = $this->_getParam("pacote");
           if(!empty($pacote)) {
               $query->where("codigo in (SELECT venda FROM venda_produto WHERE pacote = $pacote)");
           }
           
           $pacote = $this->_getParam("statuspacote");
           if(!empty($pacote)) {
           	if($pacote == "aberto"){
           		$query->where("codigo in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida >= CURRENT_DATE()))");
           	} else {
           		$query->where("codigo in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida < CURRENT_DATE()))");
           	}
           }
           
           $tipo = $this->_getParam("tipo");
           if(empty($tipo)) {
               //Não mostrar os que estão com status de remanejado
               $query->where("codigo NOT IN (SELECT venda FROM venda_areceber WHERE status = 'remanejado')");
           } else {
               $query->where("codigo IN (SELECT venda FROM venda_areceber WHERE status = 'remanejado')");
           }
                        
           $query->order("codigo DESC");
           $dados = $tblVenda->fetchAll($query);
       }
    
       $query->order("codigo DESC");
    
       $dados = $tblVenda->fetchAll($query);
        
       #seta as propriedades
       $this->separador = ";";
       $this->path = getcwd()."/images/default/tmp/";
       $this->arquivo = "rel_contas_".date('h_i_s');
        
       #gera cabeçalho
       $cabecalho = array("Venda", "Contratante", "Passageiros", "Total", "Pago", "Receber", "Moeda", "Pacote", "Telefone", "Celular");

       #gera string de cabeçalho
       $colunas = "";
       foreach($cabecalho as $coluna){
           $coluna = utf8_decode($coluna);
           if ($colunas == ""){
               $colunas .= $coluna;
           } else {
               $colunas .= $this->separador.$coluna;
          }
       }
    
       $saida[] = $colunas;
   
       #gera string do corpo do arquivo
       foreach($dados as $venda){
           $contratante = $venda->findParentRow("Model_Cliente");
           $descricaoPacote = null;
           $telefone = null;
           $celular = null;
           $vendaProduto = $venda->findDependentRowSet("Model_VendaProduto")->current();
           $moedaNome = null;
           if($vendaProduto){
               $pacote = $vendaProduto->findParentRow("Model_Pacote");
               if($pacote){
                   $descricaoPacote = $pacote->descricao;
               }
               $moeda = $vendaProduto->findParentRow("Model_Moeda");
               $moedaNome = $moeda->sigla;
            }
           $cliente = $venda->findParentRow("Model_Cliente");               
           $passageiros = $venda->findDependentRowSet("Model_VendaProduto",null,$venda->select()->where("passageiro IS NOT NULL"));
           $vendaRecebida = $this->view->totalVendaRecebido($venda["codigo"]);
           $divida = $venda["valortotal"] - $vendaRecebida;
           $totalPassageiros =  count($passageiros);
           
           $colunaValues["venda"] = (!empty($venda->codigo))?$venda->codigo:" ";
           $colunaValues["contratante"] = (!empty($cliente->nome))?$cliente->nome:" ";
           $colunaValues["passageiros"] = (!empty($totalPassageiros))?$totalPassageiros:" ";
           $colunaValues["total"] = (!empty($venda->valortotal))?$venda->valortotal:" ";
           $colunaValues["pago"] = (!empty($vendaRecebida))?$vendaRecebida:" ";
           $colunaValues["receber"] = (!empty($divida))?$divida:" ";
           $colunaValues["moeda"] = (!empty($moedaNome))?$moedaNome:" ";
           $colunaValues["pacote"] = (!empty($descricaoPacote))?$descricaoPacote:" ";
           $colunaValues["telefone"] = (!empty($cliente->telefonefixo))?$cliente->telefonefixo:" ";
           $colunaValues["celular"] = (!empty($cliente->celular))?$cliente->celular:" ";
                      
           #pega as variaveis do array
           $colunasDados = "";
           foreach($colunaValues as $coluna){
               $colunasDados .= utf8_decode($coluna).$this->separador;
           }
           $saida[] = $colunasDados;
        }
   
       $arquivoRetorno = null;
       
       #verifica se alguma linha foi inserida
       if(count($saida)>1){
           #monta o corpo do CSV
           $corpo = implode("\n", $saida);
           $corpo = str_replace(";;", ";", $corpo);
        
           #abre um arquivo para escrita, se o arquivo não existir ele tenta criar
           $fp = fopen ($this->path.$this->arquivo.".csv", "w");
           if($fp <> NULL){
               #escreve no arquivo
               fwrite($fp, $corpo);
               #fecha o arquivo
               fclose($fp);
               $arquivoRetorno = "/images/default/tmp/".$this->arquivo.".csv";
           }
       } else {
           $msg = 'Nenhum registro encontrado';
           $erro = 1;
       }
   
       echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "arquivo"=>$arquivoRetorno));
   
   }   
   
   public function pagamentoAction() {
       /* Array com os dados da tabela */
       $this->view->etiquetas = array('venda'=>'Venda', 'codigo'=>'Código', 'contratante'=>'Contratante', 'tipo'=>'Forma', 'parcela'=>'Parc', 'datavencimento'=>'Vencimento', 'valor'=>'Valor (R$)', 'pacote'=>'Pacote', 'datasaida'=>'Data Saída', 'telefone'=>'Telefone', 'celular'=>'Celular');

       //Lista dos pacotes disponíveis
       $tblPacote = new Model_Pacote();
       $this->view->pacotes = $tblPacote->fetchAll(null,"descricao ASC");
   }
   
   public function pagamentoDadosAction() {
   
       $this->_helper->layout()->disableLayout();
   
       $tbl = new Model_VendaAReceber();
   
       $query = $tbl->select()->setIntegrityCheck(false);
       
       $dados = null;
       
       $query->where("valor > 0");
       
       if($this->getRequest()->isPost()) {   
           //Dados da busca
           $post = $this->getRequest()->getPost();
            
           /* Buscas */           
           $vencimentoInicial = $this->_getParam("vencimentoInicial");
           if(!empty($vencimentoInicial)) {
               $date = new Zend_Date($vencimentoInicial);
               $vencimentoInicial = $date->get("WWW");
               $query->where("datavencimento >= '$vencimentoInicial'");
           }
           
           $vencimentoFinal = $this->_getParam("vencimentoFinal");
           if(!empty($vencimentoFinal)) {
               $date = new Zend_Date($vencimentoFinal);
               $vencimentoFinal = $date->get("WWW");
               $query->where("datavencimento <= '$vencimentoFinal'");
           }
           
           $contratante = $this->_getParam("cliente");
           if(!empty($contratante)) {               
               $query->where("venda in (SELECT codigo FROM venda WHERE cliente in (SELECT codigo FROM cliente WHERE lower(nome) like '%".$contratante."%'))");
           } 
           
           $pacote = $this->_getParam("pacote");
           if(!empty($pacote)) {
           	   if($pacote == "aberto"){
           	   	$query->where("venda in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida >= CURRENT_DATE()))");
           	   } else {
           	   	$query->where("venda in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida < CURRENT_DATE()))");
           	   }                              
           } 
           
           $pacoteId = $this->_getParam("pacoteId");
           if(!empty($pacoteId)) {
           	   $query->where("venda in (SELECT venda FROM venda_produto WHERE pacote = {$pacoteId})");                              
           } 
                         
           $tipo = $this->_getParam("tipo");
           if(!empty($tipo)) {
               $query->where("tipo = '$tipo'");
           } 
                         
           $conta = $this->_getParam("conta");
           if(!empty($conta)) {
               if($conta == "apagar"){
                   $query->where("datapagamento IS NULL");                   
               } elseif ($conta == "vencidas") {
                   $query->where("datavencimento < CURRENT_DATE()");               
                   $query->where("valorpago IS NULL");               
               } else {
                   $query->where("datapagamento IS NOT NULL");
               }
           } 
                         
           $query->order(array("venda DESC", "parcela ASC"));                  
           $dados = $tbl->fetchAll($query);
       }
   
   
       $paginator = Zend_Paginator::factory($dados);
       $paginator->setCurrentPageNumber($this->_getParam("pagina", 1));
   
       $porPagina = $this->_getParam("por-pagina");
   
       //numero de itens por pagina
       $paginator->setItemCountPerPage($porPagina);
   
       //numero de indices de paginas que serão exibidos
       $paginator->setPageRange(6);
   
       $this->view->paginacao = $paginator;
   }
   
   public function excluirParcelaAction() {
   
   		Zend_Layout::getMvcInstance()->disableLayout();
   		$this->_helper->viewRenderer->setNoRender();
   
   		$erro = 0;
   		$msg = "";
   
   		$post = $this->getRequest()->getPost();
   		$tblVendaReceber = new Model_VendaAReceber();
   		
   		if(!empty($post["codigo"])){
   			$tblVendaReceber->delete("codigo = {$post["codigo"]}");
   		}
      	
   		echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));
   }
   
   public function excluirVendaAction() {
   
   		Zend_Layout::getMvcInstance()->disableLayout();
   		$this->_helper->viewRenderer->setNoRender();
   
   		$erro = 0;
   		$msg = "";
   
   		$post = $this->getRequest()->getPost();
   		$tblVenda = new Model_Venda();
   		
   		if(!empty($post["codigo"])){
   			$tblVenda->delete("codigo = {$post["codigo"]}");
   		}
      	
   		echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro));
   }
   
    public function relatorioPagamentoAction() {
         Zend_Layout::getMvcInstance()->disableLayout();
         $this->_helper->viewRenderer->setNoRender();
     
         $msg = "";
         $erro = 0;
          
         #gerar matriz de dados
         $dados = array();
          
         $tblVendaReceber = new Model_VendaAReceber();
         $query = $tblVendaReceber->select()->setIntegrityCheck(false);
         $query->from("venda_areceber",array("*"));

         $query->where("valor > 0");
         
         if($this->getRequest()->isPost()) {   
           //Dados da busca
           $post = $this->getRequest()->getPost();
            
           /* Buscas */           
           $vencimentoInicial = $this->_getParam("vencimentoInicial");
           if(!empty($vencimentoInicial)) {
               $date = new Zend_Date($vencimentoInicial);
               $vencimentoInicial = $date->get("WWW");
               $query->where("datavencimento >= '$vencimentoInicial'");
           }
           
           $vencimentoFinal = $this->_getParam("vencimentoFinal");
           if(!empty($vencimentoFinal)) {
               $date = new Zend_Date($vencimentoFinal);
               $vencimentoFinal = $date->get("WWW");
               $query->where("datavencimento <= '$vencimentoFinal'");
           }
           
           $contratante = $this->_getParam("cliente");
           if(!empty($contratante)) {               
               $query->where("venda in (SELECT codigo FROM venda WHERE cliente in (SELECT codigo FROM cliente WHERE lower(nome) like '%".$contratante."%'))");
           } 
                         
           $tipo = $this->_getParam("tipo");
           if(!empty($tipo)) {
               $query->where("tipo = '$tipo'");
           } 
			
           $pacote = $this->_getParam("pacote");
           if(!empty($pacote)) {
           	if($pacote == "aberto"){
           		$query->where("venda in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida >= CURRENT_DATE()))");
           	} else {
           		$query->where("venda in (SELECT venda FROM venda_produto WHERE pacote in (SELECT codigo FROM pacote WHERE datasaida < CURRENT_DATE()))");
           	}
           }
           
           $conta = $this->_getParam("conta");
           if(!empty($conta)) {
               if($conta == "apagar"){
                   $query->where("datapagamento IS NULL");                   
               } elseif ($conta == "vencidas") {
                   $query->where("datavencimento < CURRENT_DATE()");               
                   $query->where("valorpago IS NULL");               
               } else {
                   $query->where("datapagamento IS NOT NULL");
               }
           }    
           
         }
     
         $query->order(array("venda DESC", "parcela ASC"));       
                     
         $dados = $tblVendaReceber->fetchAll($query);
     
         #seta as propriedades
         $this->separador = ";";
         $this->path = getcwd()."/images/default/tmp/";
         $this->arquivo = "rel_contas_".date('h_i_s');
     
         #gera cabeçalho
         $cabecalho = array("Venda", "Código", "Contratante", "Forma", "Parcela", "Vencimento", "Valor (R$)", "Pacote", "Data Saída", "Telefone", "Celular");
                  
         #gera string de cabeçalho
         $colunas = "";
         foreach($cabecalho as $coluna){
             $coluna = utf8_decode($coluna);
         if ($colunas == ""){
             $colunas .= $coluna;
         } else {
             $colunas .= $this->separador.$coluna;         
         }
     }
     
     $saida[] = $colunas;
      
     #gera string do corpo do arquivo
     foreach($dados as $vendaReceber){
         $venda = $vendaReceber->findParentRow("Model_Venda");
         $contratante = $venda->findParentRow("Model_Cliente");
         $descricaoPacote = null;
         $dataSaida = null;
         $telefone = null;
         $celular = null;
         $vendaProduto = $venda->findDependentRowSet("Model_VendaProduto")->current();
         if($vendaProduto){
             $pacote = $vendaProduto->findParentRow("Model_Pacote");
             if($pacote){
                 $descricaoPacote = $pacote->descricao;
                 $dataSaida = $pacote->datasaida;
             }
         }         
         $cliente = $venda->findParentRow("Model_Cliente");
           
         $colunaValues["venda"] = (!empty($vendaReceber->venda))?$vendaReceber->venda:" ";
         $colunaValues["codigo"] = (!empty($vendaReceber->codigo))?$vendaReceber->codigo:" ";
         $colunaValues["contratante"] = (!empty($contratante->nome))?$contratante->nome:" ";
         $colunaValues["forma"] = (!empty($vendaReceber->tipo))?$vendaReceber->tipo:" ";
         $colunaValues["parcela"] = (!empty($vendaReceber->parcela))?$vendaReceber->parcela:" ";
         $colunaValues["datavencimento"] = (!empty($vendaReceber->datavencimento))?$this->view->data($vendaReceber->datavencimento):" ";
         $colunaValues["valor"] = (!empty($vendaReceber->valor))?$vendaReceber->valor:" ";
         $colunaValues["pacote"] = (!empty($descricaoPacote))?$descricaoPacote:" ";
         $colunaValues["datasaida"] = (!empty($dataSaida))?$dataSaida:" ";
         $colunaValues["telefone"] = (!empty($cliente->telefonefixo))?$cliente->telefonefixo:" ";
         $colunaValues["celular"] = (!empty($cliente->celular))?$cliente->celular:" ";
         
         
      
         #pega as variaveis do array
         $colunasDados = "";
         foreach($colunaValues as $coluna){
             $colunasDados .= utf8_decode($coluna).$this->separador;
         }
         $saida[] = $colunasDados;
    }
          
    $arquivoRetorno = null;
      
    #verifica se alguma linha foi inserida
    if(count($saida)>1){
        #monta o corpo do CSV
        $corpo = implode("\n", $saida);
        $corpo = str_replace(";;", ";", $corpo);
     
       #abre um arquivo para escrita, se o arquivo não existir ele tenta criar
       $fp = fopen ($this->path.$this->arquivo.".csv", "w");
       if($fp <> NULL){
         #escreve no arquivo
         fwrite($fp, $corpo);
         #fecha o arquivo
         fclose($fp);
         $arquivoRetorno = "/images/default/tmp/".$this->arquivo.".csv";
      }
    } else {
         $msg = 'Nenhum registro encontrado';
         $erro = 1;
    }
      
    echo Zend_Json_Encoder::encode(array("msg"=> $msg, "erro"=> $erro, "arquivo"=>$arquivoRetorno));
      
   }

}

