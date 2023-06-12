$(document).ready(function() {
	mascaras();
	
	//Abrir filtro
	$("input#filtrar").click(function(){
		if($(this).val() == "Filtrar"){			
			$("input#buscar").show();			
			$("div.boxFiltro").show();			
			$("input#limparFiltro").show();			
			$(this).val("Fechar");			
		} else {
			$("input#buscar").hide();
			$("div.boxFiltro").hide();
			$("input#limparFiltro").hide();
			$(this).val("Filtrar");
		}	
	});
	
	$("input#limparFiltro").on("click", function(){
		$("input[type=text]").val('');
		$("select").val('');
		enviarBusca();
	});
	
	$('select#clientereplica').ajaxChosen({
        dataType: 'json',
        type: 'POST',
        url:_baseUrl+'/admin/cliente/obter-clientes'
	},{
	    loadingImg: _baseUrl+'/images/admin/loader.gif'
	});
	
	$("input#excel").click(function(){
		$url = $(this).data("url");		
		$.ajax({			
			url: $url,
	        data: $("form#form_consulta").serialize(),
			type : 'POST',
			dataType : 'json',				
			success : function(data) {
				if(data.erro == 0){
					if(!empty(data.arquivo)){
						window.location.href = _baseUrl+data.arquivo;						
					}
				} else {
					alert(data.msg);
				}
			}
		});		
	});
	
	//Ações no relatório de pagamento
	crudPagamento();
		
});

function crudPagamento(){
	
	//Abrir popup de venda
	$("a.viewVenda").live("click", function(){
		$codigo = $(this).data("venda");
		
		if(!empty($codigo)){
			url = _baseUrl+"/admin/venda/cadastro/iframe/true/codigo/"+$codigo;					
			//Box editar	
			$("#dialog").dialog({
				autoOpen: false,
		        modal: true,	        
		        width: "95%",
		        height: "880",	        
			    open: function(ev, ui){		    	
			    	$('#iframe').attr('src',url).load();		    	
		        },	        
		        position: "top"
			});					
			$('#dialog').dialog('open');
		}
	});
	
	//Abrir popup de atendimento
	$("a.viewAtendimento").live("click", function(){
		$codigo = $(this).data("conta");
		
		if(!empty($codigo)){
			url = _baseUrl+"/admin/atendimento/cadastro/iframe/true/conta/"+$codigo;			
			//Box editar	
			$("#dialog").dialog({
				autoOpen: false,
				modal: true,	        
				width: "95%",
				height: "680",	        
				open: function(ev, ui){		    	
					$('#iframe').attr('src',url).load();		    	
				},	        
				position: "top"
			});			
			$('#dialog').dialog('open');
		}
	});
	
	//Excluir
	//Ação excluir
	$(".deleteLinha").live('click',function(e) {		
		e.preventDefault();
		
		$codigo = $(this).data("codigo");
		
		console.log($codigo);
		if(!empty($codigo)){			
			$("#mensagem").dialog("option", "title", 'Exclusão');
			$("#mensagem").find('p').html('Confirma a exclusão deste registro?');
			
			var href = $(this).attr('href');
									
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
					         data: {codigo: $codigo},
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