function popupView(){
	//Abrir popup de adição
	$("li.addProduto, li.viewProduto").on("click",function(){
		var $url = _baseUrl+$("input#urlProduto").val();
				
		if(!empty($("select.selectProduto").val())){
			$url += "codigo/"+$("select.selectProduto").val();
		}				
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "945",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',$url).load(resizeIframe("#iframe"));		    	
	        },	        
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});
	
	//Select
	$("select.selectProduto").change(function(){
		if(empty($(this).val())){
			$("#addProduto").show();
			$("#viewProduto").hide();
			$("input#valorProduto").val('');
		} else {
			$("#addProduto").hide();
			$("#viewProduto").show();
			
			//Se for pacote populo o campo valor
			$valor = $('select.selectProduto option:selected').data("valor");			
			if(!empty($valor)){
				$valor = numberToMoeda($valor.toFixed(2));
				$("input#valorProduto").val($valor);
			}
			
			//Se for pacote populo o campo moeda
			$moeda = $('select.selectProduto option:selected').data("moeda");			
			if(!empty($moeda)){
				$("select#moeda").val($moeda);
			}
			
		}
	});
	$("select.selectProduto").change();
}

$(document).ready(function() {		
	$("div.abaContent").hide();	
	$("div.current").show();
	$("li.aba").click(function(){
		var $class = $(this).attr("id");	
		$("div.abaContent").hide();
		$("div."+$class+"").show();
		
		$("li.aba").removeClass("current");
		$(this).addClass("current");
	});
			
	mascaras();	
	enviarCadastro();
	
	//Abrir popup de adição de cliente
	$("li.adicionarCliente").live("click", function(){		
		url = _baseUrl+"/admin/cliente/cadastro/iframe/true/";
		
		if(!empty($("select#cliente").val())){
			url += "codigo/"+$("select#cliente").val();
		}
		
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "920",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',url).load(resizeIframe("#iframe"));		    	
	        },	        
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});
	
	//Select cliente
	$("select#cliente").change(function(){
		if(empty($(this).val())){
			$("#addCliente").show();
			$("#viewCliente").hide();
		} else {
			$("#addCliente").hide();
			$("#viewCliente").show();
		}
	});
	$("select#cliente").change();
	
	$('select#cliente').ajaxChosen({
        dataType: 'json',
        type: 'POST',
        url:_baseUrl+'/admin/cliente/obter-clientes'
	},{
	    loadingImg: _baseUrl+'/images/admin/loader.gif'
	});
			
	crudProduto();	
	crudPagamento();
		
	$("select#tipoPagamento").change(function(){
		if($(this).val() == "À Vista"){						
			
			$("input#valorPagamento").val(null);			
		} else {
			$("input#parcela").val(null).show();
			$("input#dataVencimento").val(null).show();
			$("input#valorPagamento").val(null);
		}
	});
	
	function enviarCadastro(e){
	
	    //Impede que o form seja enviado seguindo a action ao invés do ajax
		if(e != null){
			e.preventDefault();	        									
		}
	
		$('form#formCadastro').validate({
		    errorLabelContainer: $('#retorno'),
			errorElement: 'div',
			invalidHandler: function(form, validator) {
				$('#retorno').fadeIn('fast').delay(2000).fadeOut('slow');
			},
			rules: {					
				'cliente':'required',
				'datavenda':'required'			
			},
			messages:{									
				'cliente':'Preencha o campo Cliente', 
				'datavenda':'Preencha o campo Data da Venda' 
			},
			submitHandler:function(){
				$('input[name=valortotal]').removeAttr("disabled");
				$.ajax({			
					url: $('form#formCadastro').attr('action'),
			        data: $('form#formCadastro').serialize(),
					type : 'POST',
					dataType : 'json',
					beforeSend : function() {
						$('button[type=submit]').attr('disabled',true);
					},
					error : function() {
						$('button[type=submit]').attr('disabled',false);
						alert('Desculpe, a admin está em manutenção no momento...');
					},
					complete: function(){
						$("input[name=valortotal]").attr("disabled","disabled");
					},
					success : function(data) {						
						if (data.erro == '0') {
							if($("#cadastro-iframe").val()){    								
								window.parent.$('select#venda').append(data.option);
								window.parent.$('select#venda').val(data.codigo);								    								
								window.parent.$('select#venda').change();								
								window.parent.$('select#venda.chzn-select').trigger("liszt:updated");
								
								window.parent.$('#dialog').dialog('close');    								    								
								window.parent.$('form#form_consulta').submit();
							} else if(data.adicionou){
								location.href = _baseUrl+'admin/'+_controller+"/cadastro/codigo/"+data.codigo;
							} else {
								location.href = _baseUrl+'admin/'+_controller;
							}
							
						} else {													
							alert(data.msg);
						}
						
						$('button[type=submit]').attr('disabled',false);						
					}
				});
			}
		});
	}
	
	//Select produtos
	$("select#tipoProduto").change(function(){
		//Carregar Select
		if(!empty($(this).val())){
			$tipo = $(this).val();
			$produtoSelecionado = $(this).data("produto");
			$obj = $("select#produto");
			 $.ajax({
		        type:'post',
		        url:_baseUrl+"admin/venda/obter-produtos",
		        dataType: "html",
		        data:{
		            tipo:$tipo,
		            produto: $produtoSelecionado
		        },
		        beforeSend:function(){
		            $obj.html('<option>Carregando...</option>'); 		            
		            $('select#produto').trigger("chosen:updated");		            
		        },
		        success:function(data){		        	 
		            $obj.html(data);		            
		        }
		    });
		}
	});
	$("select#tipoProduto").change();
	
	dialogPagar();
	dialogReembolso();
	

	$('select#contratante').ajaxChosen({
        dataType: 'json',
        type: 'POST',
        url:_baseUrl+'/admin/cliente/obter-clientes'
	},{
	    loadingImg: _baseUrl+'/images/admin/loader.gif'
	});
	
});

function dialogReembolso(){	
	
	//Box editar	
	$("#dialog-reembolso").dialog({
		autoOpen: false,				
		minWidth: 310,
		resizable: false,
		draggable: false,
		modal: true,		
		buttons: {
			"Salvar": function() {				
				gravarReembolso();
			},
			Fechar: function() {				
				$(this).dialog("close");				
			}
		},		
		open: function(event, ui) {
			crudReembolso();			
		},					
		close: function() {			
			$("form#formReembolso").trigger("reset");			
		}				
	});
	

	//Reembolso
	$("a.reembolsoRow").live("click",function(){
		$("form#formReembolso").find("input, select").removeAttr("disabled");
		$("form#formReembolso").find("div.box-tipo").show();
		$("form#formReembolso").find("select[name=tipo]").val("Reembolso");
		$("form#formReembolso").find("select[name=tipo]").change();
		$("form#formReembolso").find("select[name=destino]").val("Contratante");
		$("form#formReembolso").find("select[name=destino]").change();
		$codigo = $(this).data("codigo");
		$valor = $(this).data("valorpago");
		$("form#formReembolso").find("input#codigoRecebimento").val($codigo);
		$("form#formReembolso").find("input[name=valorpago]").val($valor);
		$("form#formReembolso").find("input[name=valorcambio]").val($(this).data("cambio"));
		$("#dialog-reembolso").dialog("open");
		
		var options = {
		    buttons: {
		    	"Salvar": function() {				
					gravarReembolso();
				},
				Fechar: function() {				
					$(this).dialog("close");				
				}
		    }
		};
		$("#dialog-reembolso").dialog('option', options);
		
		$status = $(this).data("status"); 
		if($status == "remanejado"){
			console.log("oi");
			$("form#formReembolso").find("input, select").attr("disabled","disabled");
			$("form#formReembolso").find("div.box-tipo").hide();
			var options = {
			    buttons: {
			    	"Estornar": function() {
			    		$("form#formReembolso input").attr("disabled",false);
						$("form#formReembolso select").attr("disabled",false);
						estornarReembolso();
					},
			    	Fechar: function() {				
						$(this).dialog("close");				
					}
			    }
			};			
			$("#dialog-reembolso").dialog('option', options);
							
			$.ajax({
			     url: _baseUrl+"admin/venda/get-reembolso",
			     data: {codigo: $codigo},
			     type: "POST",
			     dataType: 'json',
			     error: function(){				
					mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com o suporte...');
				 },
				 success : function(data) {	
					if(data.erro){
						alert(data.msg);
						return;
					} else {
						$("form#formReembolso").find("select[name=destino]").val(data.destino);
						$("form#formReembolso").find("select[name=destino]").change();
						$("form#formReembolso").find("input[name=venda_destino]").val(data.venda_destino);
																		
	    				$('select#contratante').append(data.optionCliente);													    																					
						$('select#contratante.chzn-select').trigger("chosen:updated");
												
						$("form#formReembolso").find("input[name=valor_reembolso]").val(data.valor_reembolso);
						$("form#formReembolso").find("input[name=valor_credito]").val(data.valor_credito);
						$("form#formReembolso").find("input[name=valorcambio]").val(data.valorcambio);
					}				  						
				}
			 });
			
			 		
		}
		
	});
	
	function crudReembolso(){		
		$("div#contratante_chosen").css("width","100%");
		$('select#contratante').html("<option value=''>Selecione o contratante</option>");
		$('select#contratante.chzn-select').trigger("chosen:updated");
		
		$("form#formReembolso").find("select#destino").change(function(){			
			if($(this).val() == "Venda"){
				$("div#box-contratante").hide();
				$("div#box-venda").show();
			} else {
				$("div#box-contratante").show();
				$("div#box-venda").hide();
			}
		});
		
		$("form#formReembolso").find("select#tipo").change(function(){			
			if($(this).val() == "Crédito"){
				$("div#box-reembolso").find("input[name=valor_reembolso]").val("0,00");
				$("div#box-reembolso").hide();				
				$("div#box-credito").find("input[name=valor_credito]").val($("form#formReembolso").find("input[name=valorpago]").val()).attr("disabled","disabled");
			} else {
				$("div#box-reembolso").find("input[name=valor_reembolso]").val("0,00");
				$("div#box-reembolso").show();
				$("div#box-credito").show();
				$("div#box-credito").find("input[name=valor_credito]").val("0,00").removeAttr("disabled");
			}
		});
	}
	
	function gravarReembolso(){	
		$("div#box-credito").find("input[name=valor_credito]").removeAttr("disabled");
		$.ajax({
		     url: _baseUrl+"admin/venda/salvar-reembolso",
		     data: $("form#formReembolso").serialize(),
		     type: "POST",
		     dataType: 'json',
		     error: function(){				
				mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com o suporte...');
			 },
			 beforeSend: function(){
				 
			 },
			 complete: function(){
				 $("select[name=tipo]").change(); 
			 },
			 success : function(data) {	
				if(data.erro){
					alert(data.msg);
					return;
				} else {
					$("#dialog-reembolso").dialog("close");
					$("tbody.pagamentoTbody").html(data.html); 
					$("input#totalValorReceber").val(data.valorReceber);
	    			$("input#totalValorPago").val(data.valorPago);
	    			$("input#saldoDevedor").val(data.saldoDevedor);
	    			$("input#saldoDevedorReal").val(data.saldoDevedorReal);
				}				  						
			}
		 });	
	}
	
	function estornarReembolso(){	
		$("div#box-credito").find("input[name=valor_credito]").removeAttr("disabled");
		$.ajax({
			url: _baseUrl+"admin/venda/estornar-reembolso",
			data: $("form#formReembolso").serialize(),
			type: "POST",
			dataType: 'json',
			error: function(){				
				mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com o suporte...');
			},
			beforeSend: function(){
				$("form#formReembolso input").attr("disabled",false);
				$("form#formReembolso select").attr("disabled",false);
			},
			complete: function(){
				$("form#formReembolso input").attr("disabled",true);
				$("form#formReembolso select").attr("disabled",true); 
			},
			success : function(data) {	
				if(data.erro){
					alert(data.msg);
					return;
				} else {
					$("#dialog-reembolso").dialog("close");
					$("tbody.pagamentoTbody").html(data.html); 					
				}				  						
			}
		});	
	}
	
}

function dialogPagar(){	
	//Box editar	
	$("#dialog-pagamento").dialog({
		autoOpen: false,				
		minWidth: 310,
		resizable: false,
		draggable: false,
		modal: true,		
		buttons: {
			"Salvar": function() {				
				gravarPagamento();
			},
			Fechar: function() {				
				$(this).dialog( "close" );				
			}
		},		
		open: function(event, ui) {	
			crudRecebimento();
		},					
		close: function() {			
			$("form#formPagamento").trigger("reset");
			$("input#valortroco").val("0,00");
			$("form#formPagamento input[name=valortroco]").val("0,00");
		}				
	});	
	
	//Pagar
	$("a.pagarRow").live("click",function(){
		$codigo = $(this).data("pagamento");
		$valor = $(this).data("valor");
		$("form#formPagamento").find("input#codigoRecebimento").val($codigo);
		$("form#formPagamento").find("input[name=valorreceber]").val($valor);
		$("form#formPagamento").find("input[name=valorcambioreceber]").val($(this).data("cambio"));
		$("form#formPagamento").find("input#valorReceber").val($valor);				
		$("#dialog-pagamento").dialog("open");		
	});
	$('input#datapagamento').mask("99/99/9999");
		
	//Estornar
	$("a.estornarRow").live("click",function(){
		$pagamento = $(this).data("pagamento");
		
		$("#mensagem").dialog("option", "title", 'Estorno');
		$("#mensagem").find('p').html('Confirma o estorno deste registro?');
		$("#mensagem").dialog({
			resizable : false,
			position : [ 'top', 'middle' ],
			modal : true,
			height: 180,
			width: 310,
			buttons : {
				'Sim estornar' : function() {
					
					$.ajax({
			    		type:'post',
			    		url:_baseUrl+"admin/venda/estornar-pagamento",
			    		dataType: 'json',
			    		data:{    			
			    			codigo:$pagamento,
			    			venda:$("input#codigo").val()
			    		},
			    		beforeSend:function(){
			    			$("input#adicionarPagamento").prop('disabled', true);
			    			$("a.deleteRow").hide();
			    		},
			    		success:function(data){
			    			$("input#adicionarPagamento").prop('disabled', false);
			    			$("a.deleteRow").show();
			    			if(data.erro){
			    				alert(data.msg);
			    				return;
			    			}
			    						    			
			    			$("tbody.pagamentoTbody").html(data.html); 
			    			$("input#totalValorReceber").val(data.valorReceber);
			    			$("input#totalValorPago").val(data.valorPago);
			    			$("input#saldoDevedor").val(data.saldoDevedor);			    			
			    			$("input#saldoDevedorReal").val(data.saldoDevedorReal);			    			
			    			
			    			$("#mensagem").dialog('close');
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
				
	});
}

function crudRecebimento(){	
	
	/* Calculando valor de troco */	
	$("input[name=valorpago]").bind({
		blur: function(event) {
		    calcularTroco();
		},
		keyup: function(event) {
			calcularTroco();
		}
	});
	
	function calcularTroco(e) {
		$valorAReceber = converteMoedaFloat($("input#valorReceber").val());
		$valorRecebido = converteMoedaFloat($("input#valorpago").val());
		$valorTroco = $valorRecebido - $valorAReceber;		
		if($valorTroco > 0){
			$("input#valortroco").val(numberToMoeda($valorTroco.toFixed(2)));
		} else {
			$("input#valortroco").val("0,00");
		}
		
		$("form#formPagamento input[name=valortroco]").val($("input#valortroco").val());
	}
		
}

function gravarPagamento(){		
	$.ajax({
	     url: _baseUrl+"admin/venda/salvar-pagamento",
	     data: $("form#formPagamento").serialize(),
	     type: "POST",
	     dataType: 'json',
	     error: function(){				
			mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com o suporte...');
		 },
		 success : function(data) {	
			if(data.erro){
				alert(data.msg);
				return;
			} else {
				$("tbody.pagamentoTbody").html(data.html); 
				$("input#totalValorReceber").val(data.valorReceber);
    			$("input#totalValorPago").val(data.valorPago);
    			$("input#saldoDevedor").val(data.saldoDevedor);
    			$("input#saldoDevedorReal").val(data.saldoDevedorReal);
				$("#dialog-pagamento").dialog("close");
			}
			  						
		}
	 });	
}

//Aba de produto
function crudProduto(){
	//Abrir cadastro
	$("input#cadastroProduto").click(function(){
		if($(this).val() == "Novo"){
			$("div.formProduto").show();
			$("input#adicionarProduto").show().val('Adicionar');
			$(this).val("Fechar");
			$("div#passageiro_chosen").css("width","100%");
			$('select#passageiro').html("<option value=''>Selecione o passageiro</option>");
			$('select#passageiro.chzn-select').trigger("chosen:updated");
			$("input.adicionarPassageiro").show();
			$("div.formProduto").find("input").val('');
		} else {
			$("div.formProduto").hide();
			$("input#adicionarProduto").hide();
			$("input.adicionarPassageiro").hide();
			$(this).val("Novo");
		}	
	});
	
	//Abrir popup de adição de passageiro
	$("input.adicionarPassageiro").click(function(){		
		url = _baseUrl+"/admin/passageiro/cadastro/iframe/true/";
		
		$codigo = $("select#passageiro").val();		
		if(!empty($codigo)){
			url += "codigo/"+$codigo;
		}
				
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "600",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',url).load();		    	
	        },	        
	        position: "top",
	        close: function(ev, ui){
	        	gridPassageiros($("#codigo").val());
	        }
		});
				
		$('#dialog').dialog('open');		
	});
	
	//Select passageiro
	$("select#passageiro").change(function(){
		if(empty($(this).val())){
			$("#addPassageiro").show();
			$("#viewPassageiro").hide();
		} else {
			$("#addPassageiro").hide();
			$("#viewPassageiro").show();
		}
	});
	$("select#passageiro").change();
	
	$('select#passageiro').ajaxChosen({
        dataType: 'json',
        type: 'POST',
        url:_baseUrl+'/admin/passageiro/obter-passageiros'
	},{
	    loadingImg: _baseUrl+'/images/admin/loader.gif'
	});
	
	if(!empty($("#codigo").val())){
    	gridProdutos($("#codigo").val());
    }
	
	//Populando a grid de produtos
	function gridProdutos($venda) {
		$.ajax({
			type:'post',
			dataType: 'json',
			url:_baseUrl+"admin/venda/grid-produto",
			data:{
				venda:$venda
			},			
			success:function(data){        	
				$("tbody.produtoTbody").html(data.html);    			  
				$("input#moedaSigla").val(data.sigla);
				$("input#valorcambio").val(data.cambio);
				$("input#valorCambioParcela").val(data.cambio);				
				$("input[name=valortotal]").val(data.valorTotal);
				$("input#saldoDevedor").val(data.saldoDevedor);
				$("input#saldoDevedorReal").val(data.saldoDevedorReal);
				$("span.moeda").html(data.sigla);
				
				$("div.formProduto").find("input").val('');				
			}
		});
	}
	
	//Abrir edição
	$("a.editRowProduto").live("click", function(){
		$codigo = $(this).data("codigo");
		$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/venda/busca-produto",
    		dataType: 'json',
    		data:{    		
    			codigo: $codigo
    		},
    		beforeSend:function(){
    			
    		},
    		success:function(data){
    			if(!empty(data.html.codigo)){ 
    				$("div.formProduto").show();
    				$("select#tipoProduto").attr("data-produto",data.html.produto);
	    			$("select#tipoProduto").val(data.html.tipo);	
	    			$("select#tipoProduto").change();
	    			
    				$("input#descricaoProduto").val(data.html.descricao);
    				$("select#moeda").val(data.html.moeda);
    				$("input#valorProduto").val(data.html.valor);
    				$("input#codigoProduto").val(data.html.codigo);
    				
    				$('select#passageiro').html("<option value=''>Selecione o passageiro</option>");
    				$('select#passageiro').append(data.option);													    																					
					$('select#passageiro.chzn-select').trigger("chosen:updated");
					$("div#passageiro_chosen").css("width","100%");
    				
    				$("input#adicionarProduto").show().val('Alterar');
    				$("input.adicionarPassageiro").show();
    				$("input#cadastroProduto").val("Fechar");
    				$(window).scrollTop(100);    				
    				
    			}
    		}
    	});
	});
		
	//Adicionar na grid
	$("#adicionarProduto").click(function(){		
		$produtoTipo = $("select#tipoProduto").val();
		$produtoDescricao = $("input#descricaoProduto").val();		
		$produtoVal = $("select#produto").val();
		$produto = $("select#produto").find("option:selected").text();
		var $adicionado = false;
		$("tbody.produtoTbody tr").each(function(){
			$find = $(this).find("td").eq(0).html();			
			if($find == $produto){				
				$adicionado = true;				
			}
		});
		
		$valor = $("input#valorProduto").val();
				
		if(!$adicionado && (empty($produtoTipo) || empty($produtoVal) || empty($valor))){
			alert("Por favor preencha o campo empresa e/ou valor");
			return;
		} else {			
			$.ajax({
	    		type:'post',
	    		url:_baseUrl+"admin/venda/add-produto",
	    		dataType: 'json',
	    		data:{
	    			adicionado:$adicionado,
	    			venda:$("#codigo").val(),	    				    			
	    			codigo:$("#codigoProduto").val(),	    				    			
	    			tipo:$produtoTipo,	    				    			
	    			descricao:$produtoDescricao,	    				    			
	    			produto:$produtoVal,	    				    				    				    				    			
	    			valor:$valor,
	    			passageiro:$("select#passageiro").val(),
	    			moeda:$("select#moeda").val()
	    		},
	    		beforeSend:function(){
	    			$("input#adicionarProduto").prop('disabled', true);
	    		},
	    		success:function(data){
	    			$("input#adicionarProduto").prop('disabled', false);
	    			if(data.erro){
	    				alert(data.msg);
	    				return;
	    			}
	    			
	    			$("tbody.produtoTbody").html(data.html);  
	    			$("input#moedaSigla").val($("select#moeda option:selected").text());
	    			$("div.formProduto").find("input").val("");
	    			$("div.formProduto").find("select#produto").val("");
	    			$('select#passageiro').html("<option value=''>Selecione o passageiro</option>");
	    			$('select#passageiro.chzn-select').trigger("chosen:updated");
	    			
	    			$("input#adicionarProduto").show().val('Adicionar');
	    			
	    			$("input[name=valortotal]").val(data.valorTotal);
	    			$("input#saldoDevedor").val(data.saldoDevedor);
	    			$("input#saldoDevedorReal").val(data.saldoDevedorReal);
	    				    			           
	    		}
	    	});
		}
	});
	
	//Deletar da grid
	$("a.deleteRowProduto").live("click",function(){
		$codigo = $(this).data("codigo");
				
		$("#mensagem").dialog("option", "title", 'Exclusão');
		$("#mensagem").find('p').html('Confirma a exclusão deste(s) registro(s)?');
		$("#mensagem").dialog({
			resizable : false,
			position : [ 'top', 'middle' ],
			modal : true,
			height: 180,
			width: 310,
			buttons : {
				'Sim apagar' : function() {
					$.ajax({
			    		type:'post',
			    		url:_baseUrl+"admin/venda/del-produto",
			    		dataType: 'json',
			    		data:{
			    			venda: $("#codigo").val(),
			    			codigo: $codigo
			    		},
			    		beforeSend:function(){
			    			$("a.deleteRowProduto").hide();
			    		},
			    		success:function(data){
			    			$("a.deleteRowProduto").show();
			    			if(data.erro){
			    				alert(data.msg);
			    				return;
			    			}
			    			$("#mensagem").dialog('close');
			    			$("tbody.produtoTbody").html(data.html); 
			    			$("input[name=valortotal]").val(data.valorTotal);
			    			$("input#saldoDevedor").val(data.saldoDevedor);
			    			$("input#saldoDevedorReal").val(data.saldoDevedorReal);
			    				    			
			    		}
			    	});					
				},
				'Não cancelar' : function() {
					$(this).dialog('close');
				}
			}
		});
		
		
	});
	
	$('select#produto').change(function(){
		$valor = $(this).find('option:selected').data("valor");
		$("input#valorProduto").val($valor);
	});
				
}

//Aba de pagamento
function crudPagamento(){
	//Abrir cadastro
	$("input#cadastroPagamento").click(function(){
		if($(this).val() == "Novo"){
			$("div.formPagamento").show();
			$("div.formPagamento").find("input.edit").val('');
			$("div.default-pagamento").show();
			$("div.box-parcelamento").hide();
			$("input#adicionarPagamento").show().val('Adicionar');
			$("input#gerarParcelas").show();
			$(this).val("Fechar");
		} else {
			$("div.formPagamento").hide();
			$("input#adicionarPagamento").hide();
			$("input#gerarParcelas").hide().val("Gerar Automaticamente");
			$(this).val("Novo");
		}	
	});
	
	if(!empty($("#codigo").val())){
    	gridPagamentos($("#codigo").val());
    }
	
	//Populando a grid de serviços
    function gridPagamentos($venda) {
    	$.ajax({
    		type:'post',
    		dataType: 'json',
    		url:_baseUrl+"admin/venda/grid-pagamento",
    		data:{
    			venda:$venda
    		},
    		beforeSend:function(){
    			          
    		},
    		success:function(data){
    			$("tbody.pagamentoTbody").html(data.html); 
    			$("input#totalValorReceber").val(data.valorReceber);
    			$("input#totalValorPago").val(data.valorPago);
    			$("input#saldoDevedor").val(data.saldoDevedor);
    			$("input#saldoDevedorReal").val(data.saldoDevedorReal);    			
    		}
    	});
    }
    
    //Gerar parcelas automaticamente
    $("#gerarParcelas").click(function(){
    	if($(this).val() == "Gerar Automaticamente"){
			$("div.box-parcelamento").show();
			$("div.default-pagamento").hide();
			$(this).val("Gerar Manualmente");
		} else {
			$("div.box-parcelamento").hide();
			$("div.default-pagamento").show();
			$(this).val("Gerar Automaticamente");
		}	
    });
    
	//Adicionar na grid
	$("#adicionarPagamento").click(function(){		
		$pagamentoTipo = $("select#tipoPagamento").val();
		$parcela = $("input#parcela").val();
		$dataVencimento = $("input#dataVencimento").val();		
		$valorPagamento = $("input#valorPagamento").val();
		$valorCambio = $("input#valorcambio").val();
		$url = _baseUrl+"admin/venda/add-pagamento";
		
		if($("input#gerarParcelas").val() == "Gerar Manualmente"){
			$pagamentoTipo = $("select#tipoPagamentoParcela").val();
			$parcela = $("input#parcelaParcela").val();
			$dataVencimento = $("input#dataVencimentoParcela").val();		
			$valorPagamento = $("input#valorPagamentoParcela").val();
			$valorCambio = $("input#valorCambioParcela").val();
			$url = _baseUrl+"admin/venda/add-pagamento-automatico";
		}
				
		$.ajax({
    		type:'post',
    		url:$url,
    		dataType: 'json',
    		data:{    			
    			codigo:$("input#codigoVendaReceber").val(),	    				    			
    			venda:$("input#codigo").val(),	    				    			
    			tipo:$pagamentoTipo,	    				    			
    			parcela:$parcela,	    				    			
    			datavencimento:$dataVencimento,	    				    			
    			valor:$valorPagamento,
    			valorcambio: $valorCambio
    		},
    		beforeSend:function(){
    			$("input#adicionarPagamento").prop('disabled', true);
    		},
    		complete: function(){
    			$("input#adicionarPagamento").prop('disabled', false);
    		},
    		success:function(data){
    			$("input#adicionarPagamento").prop('disabled', false);
    			if(data.erro){
    				alert(data.msg);
    				return;
    			}
    			
    			$("tbody.pagamentoTbody").html(data.html); 
    			$("input#totalValorReceber").val(data.valorReceber);
    			$("input#totalValorPago").val(data.valorPago);
    			$("input#saldoDevedor").val(data.saldoDevedor);
    			$("input#saldoDevedorReal").val(data.saldoDevedorReal);
    			    			
    			$("select#tipoPagamento").val(null);    			
    			$("div.formPagamento").hide();
    			$("input#adicionarPagamento").hide();
    			$("input#gerarParcelas").hide().val("Gerar Automaticamente");
    			$("input#cadastroPagamento").val("Novo");
    		}
    	});
		
	});
	
	//Deletar da grid
	$("a.deleteRowPagamento").live("click",function(){
		$pagamento = $(this).data("pagamento");
		
		$("#mensagem").dialog("option", "title", 'Exclusão');
		$("#mensagem").find('p').html('Confirma a exclusão deste(s) registro(s)?');
		$("#mensagem").dialog({
			resizable : false,
			position : [ 'top', 'middle' ],
			modal : true,
			height: 180,
			width: 310,
			buttons : {
				'Sim apagar' : function() {
					$.ajax({
			    		type:'post',
			    		url:_baseUrl+"admin/venda/del-pagamento",
			    		dataType: 'json',
			    		data:{    			
			    			codigo:$pagamento,
			    			venda:$("input#codigo").val()
			    		},
			    		beforeSend:function(){
			    			$("input#adicionarPagamento").prop('disabled', true);
			    			$("a.deleteRow").hide();
			    		},
			    		success:function(data){
			    			$("input#adicionarPagamento").prop('disabled', false);
			    			$("a.deleteRow").show();
			    			if(data.erro){
			    				alert(data.msg);
			    				return;
			    			}
			    			$("#mensagem").dialog('close');
			    			$("tbody.pagamentoTbody").html(data.html); 
			    			$("input#totalValorReceber").val(data.valorReceber);
			    			$("input#totalValorPago").val(data.valorPago);
			    			$("input#saldoDevedor").val(data.saldoDevedor);
			    			$("input#saldoDevedorReal").val(data.saldoDevedorReal);
			    		}
			    	});			
				},
				'Não cancelar' : function() {
					$(this).dialog('close');
				}
			}
		});
				
	});
	
	//Notificar da grid
	$("a.emailRowPagamento").live("click",function(){
		$pagamento = $(this).data("pagamento");
		$email = $(this).data("email");
		//$email = "vilmarphp@gmail.com";
		$dataNotificacao = $(this).data("notificacao");
		
		$("#mensagem").dialog("option", "title", 'Notificar');
		$msg = '';
		if(!empty($dataNotificacao)){
			$msg += "Data da última notificação <strong>"+$dataNotificacao+"</strong> <br/><br/>";
		}
		$msg += "Confirma o envio do email de cobrança para: <br/>&nbsp;";
		
		$msg += '<input size="35" type="text" class="mws-textinput" id="emailNotificacaoEnvio" value="'+$email+'">';
		
		
		
		$("#mensagem").find('p').html($msg);
		$("#mensagem").dialog({
			resizable : false,
			position : [ 'top', 'middle' ],
			modal : true,
			height: 250,
			width: 310,
			buttons : {				
				'Sim enviar' : function() {					
					$.ajax({
			    		type:'post',
			    		url:_baseUrl+"admin/venda/mail-cobranca",
			    		dataType: 'json',
			    		data:{    			
			    			codigo:$pagamento,
			    			email:$("input#emailNotificacaoEnvio").val(),
			    			venda:$("input#codigo").val()
			    		},
			    		beforeSend:function(){
			    			$("input#adicionarPagamento").prop('disabled', true);
			    			$("a.deleteRow").hide();
			    		},
			    		success:function(data){
			    			$("input#adicionarPagamento").prop('disabled', false);
			    			$("a.deleteRow").show();
			    			if(data.erro){
			    				alert(data.msg);
			    				return;
			    			}
			    			$("#mensagem").dialog('close');
			    			$("tbody.pagamentoTbody").html(data.html); 			    			
			    		}
			    	});					
				},
				'Não cancelar' : function() {
					$(this).dialog('close');
				}
			}
		});
				
	});
	
	//Abrir edição
	$("a.editRowPagamento").live("click", function(){
		$codigo = $(this).data("codigo");
		$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/venda/busca-pagamento",
    		dataType: 'json',
    		data:{    		
    			codigo: $codigo
    		},
    		beforeSend:function(){
    			
    		},
    		success:function(data){
    			if(!empty(data.html.codigo)){     				
    				$("div.formPagamento").show();
    				$("div.default-pagamento").show();
    				$("div.box-parcelamento").hide();
    				$("input#adicionarPagamento").show().val("Alterar");
    				$("input#gerarParcelas").hide();
    				$("input#cadastroPagamento").val("Fechar");
    				
    				$("select#tipoPagamento").val(data.html.tipo);
    				$("input#parcela").val(data.html.parcela);
    				$("input#dataVencimento").val(data.html.datavencimento);
    				$("input#valorPagamento").val(data.html.valor);
    				$("input#valorcambio").val(data.html.valorcambio);
    				$("input#codigoVendaReceber").val(data.html.codigo);
    				
    				calcularCambio();
    				
    				$(window).scrollTop(100);    				
    			}
    		}
    	});
	});
	

	/* Calculando valor de câmbio */
	calcularCambio();
	$("input#valorcambio, input#valorCambioParcela, input#valorPagamento, input#valorPagamentoParcela").bind({
		blur: function(event) {
		    calcularCambio();
		},
		keyup: function(event) {
			calcularCambio();
		}
	});
	
	function calcularCambio(e) {
		if($("input#gerarParcelas").val() == "Gerar Manualmente"){
			$valorCambio = converteMoedaFloat($("input#valorCambioParcela").val());
			$valorPagamento = converteMoedaFloat($("input#valorPagamentoParcela").val());
		} else {
			$valorCambio = converteMoedaFloat($("input#valorcambio").val());
			$valorPagamento = converteMoedaFloat($("input#valorPagamento").val());
		}
				
		$valorCambiado = $valorPagamento/$valorCambio;
		
		if($valorCambiado > 0){
			$("input#valorcambiado, input#valorReceberParcela").val(numberToMoeda($valorCambiado.toFixed(2)));			
		} else {
			$("input#valorcambiado, input#valorReceberParcela").val($valorPagamento);
		}
	}
	
				
}