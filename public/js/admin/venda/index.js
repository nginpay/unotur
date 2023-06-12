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
		enviarBusca();
	});
	
	$('select#clientereplica').ajaxChosen({
        dataType: 'json',
        type: 'POST',
        url:_baseUrl+'/admin/cliente/obter-clientes'
	},{
	    loadingImg: _baseUrl+'/images/admin/loader.gif'
	});
	
	$("a.relatorio").click(function(){				
		$.ajax({			
			url: _baseUrl+'admin/atendimento/relatorio',
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
		
	//Ação duplicar venda
	$(".duplicar").live('click',function(e) {
		
		e.preventDefault();
		
		var existeCheck = false;
		var count = 0;
		var venda = null;
		$("input[type=checkbox]").each(function() { 
			if(this.checked == true){
				existeCheck = true;
				venda = $(this).val();
				count++;
			}			
		});
		
		if(count > 1){
			mostraDialog('Atenção','Marque apenas um registro por vez');
			return;
		}
		
		if(existeCheck && !empty(venda)){
			
			$("#mensagem").dialog("option", "title", 'Atenção');
			$("#mensagem").find('p').html('Esta ação, duplicará todos os dados do registro selecionado, deseja continuar?');
									
			$("#mensagem").dialog({
				resizable : false,
				position : ['top','top'],
				modal : true,
				height: 180,
				width: 310,
				buttons : {
					'Sim duplicar': function() {
						$('form#formReplica').find("input[name=venda]").val(venda);
						
						$(this).dialog('close');
						$("#dialog-duplicar").dialog('open');						
					},
					'Não cancelar': function() {
						$(this).dialog('close');
					}
				}
			});

			$("#mensagem").dialog('open');
			return false;
		} else {
			mostraDialog('Atenção','Marque o registro que deseja ser duplicado');
		}		
	});
		
	//Box editar	
	$("#dialog-duplicar").dialog({
		autoOpen: false,				
		minWidth: 310,
		resizable: false,
		draggable: false,
		modal: true,		
		buttons: {
			"Salvar": function() {				
				gravarReplica();
			},
			Fechar: function() {				
				$(this).dialog( "close" );				
			}
		},		
		open: function(event, ui) {	
			$("div#clientereplica_chosen").css("width","100%");
		},					
		close: function() {			
			
		}				
	});	
	
	//Abrir popup de pacote
	$("a.viewPacote").live("click", function(){		
		url = _baseUrl+"/admin/venda/pacote/iframe/true";
								
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "600",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',url).load("#iframe");		    	
	        },
	        buttons : {				
				'Fechar' : function() {
					$(this).dialog('close');
				}
			},
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});
	
});

function gravarReplica(){		
	$.ajax({
	     url: _baseUrl+"admin/venda/salvar-replica",
	     data: $("form#formReplica").serialize(),
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
				$("#dialog-duplicar").dialog('close');
       		 	$("#form_consulta").submit();
			}			  						
		}
	 });	
}