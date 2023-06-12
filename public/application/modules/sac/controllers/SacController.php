<?php

/**
 * AdminController - Controller principal de todo a admin.
 * Neste Controller deve ser descritas todos os metodos que serão replicados para cada Controller adjacente.
 * 
 * @author Vilmar
 * @version 1.0.0 - 28/06/2012
 */
class SacController extends Zend_Controller_Action {

	/**
	 * Init principal dos Controllers
	 */
	public function init() {

		//Forçar a sessão
		Zend_Session::start();
		
		// Adiciona o Header de Codificacao da Página
		$this->view->headMeta()->setHttpEquiv('Content-Type', 'text/html; charset=utf-8');
		
		//Especificações para Iphone, iPad e Android		
		$this->view->headMeta()->appendName('apple-mobile-web-app-capable', 'no');
		$this->view->headMeta()->appendName('apple-mobile-web-app-status-bar-style', 'black');
		$this->view->headMeta()->appendName('viewport','width=device-width,initial-scale=1,user-scalable=no,maximum-scale=1');
					
		// Adiciona os arquivos CSS básicos	
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/reset.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/text.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/fonts/ptsans/stylesheet.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/fluid.css'));
		
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/mws.style.css?v'.uniqid()));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/icons/16x16.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/icons/24x24.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/icons/32x32.css'));
		
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/demo.css'));
			
		
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/jui/jquery.ui.css'));
				
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/mws.theme.css'));
						
		//Icones
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/icons/24x24.css'));
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/css/admin/icons/16x16.css'));
				
		// Adiciona os arquivos JS básicos	
		$this->view->headScript()->setFile ( $this->view->baseUrl ('/js/admin/plugins/jquery-1.7.1.min.js'));
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/plugins/jquery-ui.js'));		
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/plugins/jquery.ui.datepicker-pt-BR.js'));
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/plugins/placeholder/jquery.placeholder-min.js'));				
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/plugins/datatables/jquery.dataTables-min.js'));				
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/plugins/validate/jquery.validate.js'));				
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/mws.js'));		
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/plugins/jquery.form/jquery.form.js'));						
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/plugins/mascaras/jquery.maskedinput-1.3.min.js'));		
		$this->view->headScript()->appendFile ($this->view->baseUrl ('/js/admin/plugins/mascaras/jquery.maskmoney.js'));
		
		//Plugin chosen
		$this->view->headLink()->appendStylesheet($this->view->baseUrl('/js/admin/plugins/chosen/chosen.css'));
		$this->view->headScript()->appendFile($this->view->baseUrl("/js/admin/plugins/chosen/chosen.jquery.js"));
		$this->view->headScript()->appendFile($this->view->baseUrl("/js/admin/plugins/chosen/ajax/chosen.ajaxaddition.jquery.js"));
							
		// Paths básicos para os arquivos de JS e CSS
		$pathStylesheet = SITE_PATH . '/css';
		$pathScript = SITE_PATH . '/js';
		
		// Com base no nome do módulo, atualiza os paths básicos
		if ($this->getRequest ()->getModuleName () != 'default') {
			$pathStylesheet .= '/' . $this->getRequest ()->getModuleName ();
			$pathScript .= '/' . $this->getRequest ()->getModuleName ();
		}
		
		// Incrementa cada path com a expressao <path>/<module>/<controller>/<action>.(css,js)
		$pathStylesheet .= '/' . $this->getRequest ()->getControllerName () . '/' . $this->getRequest ()->getActionName () . '.css';
		$pathScript .= '/' . $this->getRequest ()->getControllerName () . '/' . $this->getRequest ()->getActionName () . '.js';
		
		// Verifica se o path indicado é realmente um arquivo de estilos válido
		if (is_file ($pathStylesheet) && is_readable ($pathStylesheet)) {
			
			// Recupera o caminho relativo do arquivo
			$relativeStylesheet = str_replace (SITE_PATH, $this->view->baseUrl (), $pathStylesheet);
			
			// Adiciona o arquivo a lista de styles do site
			$this->view->headLink ()->appendStylesheet ($relativeStylesheet);		
		}
					
		// Verifica se o path indicado é realmente um arquivo de scripts válido
		if (is_file ($pathScript) && is_readable($pathScript)) {
			
			// Recupera o caminho relativo do arquivo
			$relativeScript = str_replace (SITE_PATH, $this->view->baseUrl (), $pathScript);
			
			// Adiciona o arquivo a lista de styles do site
			$this->view->headScript()->appendFile ($relativeScript.'?'.uniqid());
		
		}
		
		
		$this->_helper->layout()->setLayout ('admin');
		
		// Desabilita o layout sempre que uma requisição ajax ocorrer.
		if ($this->getRequest()->isXmlHttpRequest ()) {
			$this->_helper->layout()->disableLayout ();
		}
		
		//Armazenando os dados para as rotas
		$this->view->moduleName = $this->getRequest()->getModuleName();	
		$this->view->controllerName = $this->getRequest()->getControllerName();	
		$this->view->actionName = $this->getRequest()->getActionName();
		
		$this->view->headScript()->appendFile($this->view->baseUrl ('/js/admin/layout.js?v'.uniqid()));

		//Pegando os dados do usuário logado...
		$auth = Zend_Auth::getInstance();
		$this->view->usuario = $auth->getStorage()->read();
							
	}
	
	public function validaCPF($cpf){
	    // determina um valor inicial para o digito $d1 e $d2
	    // pra manter o respeito ;)
	    $d1 = 0;
	    $d2 = 0;
	    // remove tudo que não seja número
	    $cpf = preg_replace("/[^0-9]/", "", $cpf);
	    // lista de cpf inválidos que serão ignorados
	    $ignore_list = array(
	            '00000000000',
	            '01234567890',
	            '11111111111',
	            '22222222222',
	            '33333333333',
	            '44444444444',
	            '55555555555',
	            '66666666666',
	            '77777777777',
	            '88888888888'
	    );
	    // se o tamanho da string for dirente de 11 ou estiver
	    // na lista de cpf ignorados já retorna false
	    if(strlen($cpf) != 11 || in_array($cpf, $ignore_list)){
	        return false;
	    } else {
	        // inicia o processo para achar o primeiro
	        // número verificador usando os primeiros 9 dígitos
	        for($i = 0; $i < 9; $i++){
	            // inicialmente $d1 vale zero e é somando.
	            // O loop passa por todos os 9 dígitos iniciais
	            $d1 += $cpf[$i] * (10 - $i);
	        }
	        // acha o resto da divisão da soma acima por 11
	        $r1 = $d1 % 11;
	        // se $r1 maior que 1 retorna 11 menos $r1 se não
	        // retona o valor zero para $d1
	        $d1 = ($r1 > 1) ? (11 - $r1) : 0;
	        // inicia o processo para achar o segundo
	        // número verificador usando os primeiros 9 dígitos
	        for($i = 0; $i < 9; $i++) {
	            // inicialmente $d2 vale zero e é somando.
	            // O loop passa por todos os 9 dígitos iniciais
	            $d2 += $cpf[$i] * (11 - $i);
	        }
	        // $r2 será o resto da soma do cpf mais $d1 vezes 2
	        // dividido por 11
	        $r2 = ($d2 + ($d1 * 2)) % 11;
	        // se $r2 mair que 1 retorna 11 menos $r2 se não
	        // retorna o valor zeroa para $d2
	        $d2 = ($r2 > 1) ? (11 - $r2) : 0;
	        // retona true se os dois últimos dígitos do cpf
	        // forem igual a concatenação de $d1 e $d2 e se não
	        // deve retornar false.
	        return (substr($cpf, -2) == $d1 . $d2) ? true : false;
	    }
	}
	
	
	public function validaCNPJ($cnpj)
	{
	    $cnpj = trim($cnpj);
	    $soma = 0;
	    $multiplicador = 0;
	    $multiplo = 0;
	    $parte1_invertida = null;
	     
	     
	    # [^0-9]: RETIRA TUDO QUE NÃO É NUMÉRICO,  "^" ISTO NEGA A SUBSTITUIÇÃO, OU SEJA, SUBSTITUA TUDO QUE FOR DIFERENTE DE 0-9 POR "";
	    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
	     
	    if(empty($cnpj) || strlen($cnpj) != 14)
	        return FALSE;
	
	    # VERIFICAÇÃO DE VALORES REPETIDOS NO CNPJ DE 0 A 9 (EX. '00000000000000')
	    for($i = 0; $i <= 9; $i++)
	    {
	        $repetidos = str_pad('', 14, $i);
	         
	        if($cnpj === $repetidos)
	            return FALSE;
	}
	 
	# PEGA A PRIMEIRA PARTE DO CNPJ, SEM OS DÍGITOS VERIFICADORES
	$parte1 = substr($cnpj, 0, 12);
	 
	# INVERTE A 1ª PARTE DO CNPJ PARA CONTINUAR A VALIDAÇÃO    $parte1_invertida = strrev($parte1);
	 
	# PERCORRENDO A PARTE INVERTIDA PARA OBTER O FATOR DE CALCULO DO 1º DÍGITO VERIFICADOR
	for ($i = 0; $i <= 11; $i++)
	{
	$multiplicador = ($i == 0) || ($i == 8) ? 2 : $multiplicador;
	
	$multiplo = ($parte1_invertida[$i] * $multiplicador);
	
	$soma += $multiplo;
	 
	$multiplicador++;
	}
	 
	# OBTENDO O 1º DÍGITO VERIFICADOR
	$rest = $soma % 11;
	 
	$dv1 = ($rest == 0 || $rest == 1) ? 0 : 11 - $rest;
	 
	# PEGA A PRIMEIRA PARTE DO CNPJ CONCATENANDO COM O 1º DÍGITO OBTIDO
	$parte1 .= $dv1;
	 
	# MAIS UMA VEZ INVERTE A 1ª PARTE DO CNPJ PARA CONTINUAR A VALIDAÇÃO
	$parte1_invertida = strrev($parte1);
	 
	$soma = 0;
	 
	# MAIS UMA VEZ PERCORRE A PARTE INVERTIDA PARA OBTER O FATOR DE CALCULO DO 2º DÍGITO VERIFICADOR
	for ($i = 0; $i <= 12; $i++)
	{
	$multiplicador = ($i == 0) || ($i == 8) ? 2 : $multiplicador;
	
	$multiplo = ($parte1_invertida[$i] * $multiplicador);
	
	$soma += $multiplo;
	 
	$multiplicador++;
	}
	 
	# OBTENDO O 2º DÍGITO VERIFICADOR
	$rest = $soma % 11;
	 
	$dv2 = ($rest == 0 || $rest == 1) ? 0 : 11 - $rest;
	 
	# AO FINAL COMPARA SE OS DÍGITOS OBTIDOS SÃO IGUAIS AOS INFORMADOS (OU A SEGUNDA PARTE DO CNPJ)
	return ($dv1 == $cnpj[12] && $dv2 == $cnpj[13]) ? TRUE : FALSE;
	//echo ($dv1 == $cnpj[12] && $dv2 == $cnpj[13]) ? 'TRUE' : 'FALSE';
    }
	
	
}
