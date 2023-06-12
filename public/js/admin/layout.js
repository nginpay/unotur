$(window).bind("load", startup);
function startup()
{			
	//Começa enviando o formulário
	if(_action == "index" || _controller == "relatorio"){
		enviarBusca();		
	}
		
	$("#form_consulta").bind('submit', enviarBusca);
	acoesPaginacao();
	acoesGrid();
	
	dialogAlterar();
	
	//Filtro de busca
	$("#txtBusca").keyup(function(){ 
		var texto = $(this).val(); 
		$("div#mws-navigation ul li").css("display", "block"); 
		
		$("div#mws-navigation ul li").each(function(){ 
			if($(this).find("a").text().toUpperCase().indexOf(texto.toUpperCase()) < 0){ 
				$(this).css("display", "none"); 
			}
		});		
	});
	
	//Filtro de pesquisa 
	$("select.chzn-select").chosen({
		allow_single_deselect:true,
		disable_search_threshold:10,
		search_contains:true,		
		no_results_text: "Nenhum registro encontrado para"
	});
        
};

function dialogAlterar(){	
	//Box editar	
	$( "#dialog-alterar-senha" ).dialog({
		autoOpen: false,				
		minWidth: 310,
		resizable: false,
		draggable: false,
		modal: true,		
		buttons: {
			"Salvar": function() {				
				gravarSenha();
			},
			Fechar: function() {				
				$(this).dialog( "close" );				
			}
		},		
		open: function(event, ui) {	
					
		},					
		close: function() {			
			
		}				
	});	
	
	$("#alterar-senha").click(function(){
		$("#dialog-alterar-senha").dialog("open");		
	});
}

function gravarSenha(){
	
	var senha = $("form#formAlterarSenha").find("input[name=senha]").val();
	var confirmar_senha = $("form#formAlterarSenha").find("input[name=confirmar-senha]").val();
	var url = $("form#formAlterarSenha").attr("action");
		
	if(empty(senha)){
		$("#retorno-dialog").html("Campo senha inválido").fadeIn('fast').delay(2000).fadeOut('slow');
	} else if(senha != confirmar_senha) {
		$("#retorno-dialog").html("Campos não coincidem").fadeIn('fast').delay(2000).fadeOut('slow');
	} else {	
		$.ajax({
	         //url: _baseUrl+"admin/login/salvar-senha",
	         url: url,
	         data: $("form#formAlterarSenha").serialize(),
	         type: "POST",
	         dataType: 'json',
	         error: function(){				
				mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com o suporte...');
			 },
			 success : function(data) {				
					if(data.erro == "0") {					
						$( "#dialog-alterar-senha" ).dialog("close");
					}
					else {
						mostraDialog('Atenção',"Falha ao enviar a requisição");
					}    						
				}
	     });
	}
}

function enviarBusca(e) {
	
	//Impede que o form seja enviado seguindo a action ao invés do ajax
	if(e != null){
		e.preventDefault();				
	}
	
	//Ação consultar
	$.ajax({
		cache : false,
		type : 'post',
		data: $("#form_consulta").serialize(),
		url: $("#form_consulta").attr('action'),
		dataType : "html",		
		beforeSend : function() {
			mostraDialog('Carregando', 'Efetuando busca...');
		},
		success : function(data) {
			 $("#mensagem").dialog('close');			         	 
        	 $("table.mws-table").find('tbody').html(data);
        	         	 
        	 var paginacao = $("table.mws-table tbody tr#paginacao-temp td").html();         	 
        	 $("div#paginacao").html(paginacao);
        	 
        	 //Se existir ordenação marco a coluna
        	 if($("#ordenacao").val() != ""){
        		marcarColuna(); 
        	 }
        	 
        	 if($("tr.gradeX td.center").html() == "Nenhum registro encontrado"){
        		 $("#selectAll").hide();
        		 $("table.mws-datatable-fn thead tr th").removeClass("sorting");
        	 }
        	 
		},
		error : function(error) {
			mostraDialog('Erro', 'Falha no envio contate o suporte ou tente novamente');
		}

	});				
}

function acoesPaginacao(){
	$("div.dataTables_paginate").find('span.paginate_button').live('click',function() {
		if(!$(this).hasClass("paginate_button_disabled")){
			var pagina = $(this).attr('title');		
			$("#form_consulta input#pagina").val(pagina);
			$("#form_consulta").submit();
			return false;	
		}
	});
	
	$("#select-por-pagina").live('change',function(){
		var porpagina = $(this).val();
		$("#form_consulta input#porpagina").val(porpagina);
		$("#form_consulta").submit();
		return false;		
	});
}

function marcarColuna(){
		
	var index = null;
	
	//Verificando indice
	$("table.mws-table thead tr th").each(function(i) {			
		if($(this).hasClass('sorting_asc') || $(this).hasClass('sorting_desc')){
			index = $(this).index();			
		}
	});
	
	//Desmarcando tudo
	$("table.mws-table tbody tr td").removeClass("sorting_1");
	
	//Marcando as colunas
	$("table.mws-table tbody tr").each(function(i) {	
		$(this).find('td').each(function(i) {			
			if(i == index){			
				$(this).addClass('sorting_1');
			}
			
		});
	});
	
}

function acoesGrid(){
	//Ação marca todos
	$('#selectAll').click(function() {
		if(this.checked == true){
			$("input[type=checkbox]").each(function() { 
				this.checked = true; 
			});
		} else {
			$("input[type=checkbox]").each(function() { 
				this.checked = false; 
			});
		}
	});
		
	//Ordenação
	$('.sorting').live("click", function(){					
		if ($(this).hasClass("sorting_asc")) {						
			$(".sorting").removeClass("sorting_asc");
			$(".sorting").removeClass("sorting_desc");
			$(this).addClass("sorting_desc");
			$("input[name=ordenacao]").val($(this).attr('char')+" desc");							   
		 } else {			 
			$(".sorting").removeClass("sorting_asc");
			$(".sorting").removeClass("sorting_desc");
			$(this).addClass("sorting_asc");
			$("input[name=ordenacao]").val($(this).attr('char')+" asc");
		 }
		
		$("#form_consulta").submit();
		
	});
		
	//Ação atualizar
	$('.ic-arrow-refresh').live('click',function(){$("#form_consulta").submit();});
		
	//Ação excluir
	$(".delete").live('click',function(e) {
		
		e.preventDefault();
				
		if($(this).hasClass('deleteRow')){
			$("input[type=checkbox]").each(function() { 
				this.checked = false; 
			});
									
			$(this).parent().parent('tr.gradeX').find('input.checkCodigo').attr("checked","checked");
		}
		
		var existeCheck = false;
		$("input[type=checkbox]").each(function() { 
			if(this.checked == true){
				existeCheck = true;
			} 
		});
		
		if(existeCheck){
			
			$("#mensagem").dialog("option", "title", 'Exclusão');
			$("#mensagem").find('p').html('Confirma a exclusão deste(s) registro(s)?');
			
			var href = $(this).attr('href');
			
			var itens=$("input[type='checkbox']:checked").map(function () {
	            return this.value;
	        }).get().join(',');
						
			$("#mensagem").dialog({
				resizable : false,
				position : [ 'top', 'middle' ],
				modal : true,
				height: 180,
				width: 310,
				buttons : {
					'Sim apagar' : function() {
						
						$.ajax({
							 dataType : "json",
					         url: href,
					         data: {itens:itens },
					         type: "POST",	         	         
					         error: function(){				
								mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com suporte...');
							 },
					         success:function(data) {				        	 
					        	 if(data.erro == 0){
					        		 $("#mensagem").dialog('close');
					        		 $("#form_consulta").submit();
					        	 } else {
					        		 mostraDialog('Erro',data.msg);
					        	 }
					        	 
					         }
					     });						
					},
					'Não cancelar' : function() {
						$(this).dialog('close');
					}
				}
			});

			$("#mensagem").dialog('open');
			return false;
		} else {
			mostraDialog('Atenção','Escolha ao menos um registro para ser excluído');
		}
		
	});
	
}

function mostraDialog(titulo, mensagem) {	
	// definir padrao
	$("#mensagem").dialog('close');
	modal = true;
	if (titulo == 'Erro') {
		modal = false;
	}

	$("#mensagem").dialog({
		position : [ 'top', 'middle' ],
		autoOpen : false,
		modal : true,		
		width : 320,
		title : titulo,
		buttons : '',
		draggable : false,
		resizable : false
	});

	$("#mensagem").find('p').html(mensagem);
	$("#mensagem").dialog('open');
	$('body').css('overflow-x','hidden');
}

function phone(v) {

    v = v.replace(/\D/g,"");

    var onzeDigitos = false;

    if(v.length>=11)onzeDigitos = true;    
    v = v.replace(/^(\d\d)(\d)/g,"($1) $2");

    if(onzeDigitos)v = v.replace(/(\d{5})(\d)/,"$1-$2");

    else v = v.replace(/(\d{4})(\d)/,"$1-$2");

    v = v.slice(0, 15);

    return v;
}

function mascaras() {
	$('.data').mask("99/99/9999");
	$('.hora').mask("99:99");	

	$(".data").datepicker({
		showAnim : 'slideDown',
		dateFormat : 'dd/mm/yyyy',
		onSelect : function(dateText, inst) {
			var data = dateText.slice(0, 10);
			$(this).val(data);
			$(this).parent('.input').find('span').remove();
		}
	});
				
	$('.placa').mask("aaa-9999");

	$('.placa').blur(function() {
		$(this).val($(this).val().toUpperCase());
	});

	$('.mesAno').mask("aaa/9999");
	$('.ano').mask("9999");
	//$('.telefone').mask("(99) 9999-9999");
	$('.telefone').bind("keyup",function(){
		$(this).val(phone($(this).val()));
	});
	$(".cpf").mask("999.999.999-99");
	$(".cnpj").mask("99.999.999/9999-99");
	$(".cep").mask("99999-999");
	
	//Buscando localidades
	$("input.cep").blur(function(){
		var cep = $(this).val();
		if(!empty(cep)){
			$.ajax({
				url: _baseUrl+"admin/index/busca-endereco",
				data: {cep:cep},
				type: "post",
				dataType : "json",
				beforeSend : function() {				
					$("div#loading_overlay").show().find("div").show();
				},
				complete: function(){
					$("div#loading_overlay").hide().find("div").hide();
				},
				success : function(data) {					
					var endereco = data.localidade.tipo_logradouro+" "+data.localidade.logradouro;
					$("input#endereco").val(endereco);
					$("input#bairro").val(data.localidade.bairro);
				
					//Setando estado e cidade
					$("select#estado").attr("data-cidade",data.localidade.cidade);
					$("select#estado").val(data.localidade.uf);
					$("select#estado").trigger("liszt:updated");
					obterCidades($("select#estado").val(), $("select#cidade"),data.cidade);
				}
			});
		}
	});
	
	$("select#estado").change(function(){        
        obterCidades($(this).val(), $("select#cidade"),$(this).attr('data-cidade'));
    });
    $("select#estado").change();
    
    $("select#statuspacote").change(function(){        
    	obterPacotes($(this).val(), $("select#pacote"),$(this).attr('data-pacote'));
    });
    $("select#statuspacote").change();

	$(".dinheiro").maskMoney({
		symbol : "R$",
		decimal : ",",
		thousands : "."
	});
	
	$(".aproveitamento").maskMoney({		
		decimal : "."
	});

	$(".numero").keyup(function(e) {
		this.value = this.value.replace(/[^0-9\.]/g, '');
	});
}

function obterCidades(uf,obj,selecionar) {	
    $.ajax({
        type:'post',
        url:_baseUrl+"/"+_module+"/index/obter-cidades",
        data:{
            estado:uf,
            selecionar:selecionar
        },
        beforeSend:function(){
            obj.html('<option>Carregando...</option>');            
        },
        success:function(data){        	
            obj.html(data);            
        }
    });
}

function obterPacotes(statuspacote,obj,selecionar) {	
	$.ajax({
		type:'post',
		url:_baseUrl+"/"+_module+"/index/obter-pacotes",
		data:{
			statuspacote:statuspacote,
			selecionar:selecionar
		},
		beforeSend:function(){
			obj.html('<option>Carregando...</option>');            
		},
		success:function(data){        	
			obj.html(data);            
		}
	});
}

function converteMoedaFloat(valor){	
	if(valor === "" || empty(valor)){
	   valor =  0;
	}else{
	   valor = valor.replace(".","");
	   valor = valor.replace(",",".");
	   valor = parseFloat(valor);
	}
	return valor;
}

function empty (mixed_var) {
    
    var key;
 
    if (mixed_var === "" || mixed_var === 0 || mixed_var === "0" || mixed_var === null || mixed_var === false || typeof mixed_var === 'undefined') {
        return true;
    }
 
    if (typeof mixed_var == 'object') {
        for (key in mixed_var) {
            return false;
        }
        return true;
    }
 
    return false;
}

function ltrim (str, charlist) {   
    charlist = !charlist ? ' \\s\u00A0' : (charlist + '').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
    var re = new RegExp('^[' + charlist + ']+', 'g');
    return (str + '').replace(re, '');
}

function uniqid(prefix, more_entropy) {
	  //  discuss at: http://phpjs.org/functions/uniqid/
	  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	  //  revised by: Kankrelune (http://www.webfaktory.info/)
	  //        note: Uses an internal counter (in php_js global) to avoid collision
	  //        test: skip
	  //   example 1: uniqid();
	  //   returns 1: 'a30285b160c14'
	  //   example 2: uniqid('foo');
	  //   returns 2: 'fooa30285b1cd361'
	  //   example 3: uniqid('bar', true);
	  //   returns 3: 'bara20285b23dfd1.31879087'

	  if (typeof prefix === 'undefined') {
	    prefix = '';
	  }

	  var retId;
	  var formatSeed = function(seed, reqWidth) {
	    seed = parseInt(seed, 10)
	      .toString(16); // to hex str
	    if (reqWidth < seed.length) { // so long we split
	      return seed.slice(seed.length - reqWidth);
	    }
	    if (reqWidth > seed.length) { // so short we pad
	      return Array(1 + (reqWidth - seed.length))
	        .join('0') + seed;
	    }
	    return seed;
	  };

	  // BEGIN REDUNDANT
	  if (!this.php_js) {
	    this.php_js = {};
	  }
	  // END REDUNDANT
	  if (!this.php_js.uniqidSeed) { // init seed with big random int
	    this.php_js.uniqidSeed = Math.floor(Math.random() * 0x75bcd15);
	  }
	  this.php_js.uniqidSeed++;

	  retId = prefix; // start with prefix, add current milliseconds hex string
	  retId += formatSeed(parseInt(new Date()
	    .getTime() / 1000, 10), 8);
	  retId += formatSeed(this.php_js.uniqidSeed, 5); // add seed hex string
	  if (more_entropy) {
	    // for more entropy we add a float lower to 10
	    retId += (Math.random() * 10)
	      .toFixed(8)
	      .toString();
	  }

	  return retId;
}

function numberToMoeda(valor) {
    var SeparadorDecimal = ",";
    var SeparadorMilesimo = ".";    
    var key = '';
    var i = j = 0;
    var len = len2 = 0;
    var strCheck = '-0123456789';
    var aux = aux2 = '';
    len = valor.length;
    for(i = 0; i < len; i++)
        if ((valor.charAt(i) != '0') && (valor.charAt(i) != SeparadorDecimal)) break;
    aux = '';
    for(; i < len; i++)
        if (strCheck.indexOf(valor.charAt(i))!=-1) aux += valor.charAt(i);
    aux += key;
    len = aux.length;
    if (len == 0) valor = '';
    if (len == 1) valor = '0'+ SeparadorDecimal + '0' + aux;
    if (len == 2) valor = '0'+ SeparadorDecimal + aux;
    if (len > 2) {
        aux2 = '';
        for (j = 0, i = len - 3; i >= 0; i--) {
            if (j == 3) {
                aux2 += SeparadorMilesimo;
                j = 0;
            }
            aux2 += aux.charAt(i);
            j++;
        }
        valor = '';
        len2 = aux2.length;
        for (i = len2 - 1; i >= 0; i--)
        valor += aux2.charAt(i);
        valor += SeparadorDecimal + aux.substr(len - 2, len);
    }
    return valor;
}
