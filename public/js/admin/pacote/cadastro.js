function calcularValorPacote(){
	var $valor = 0;
	$("tbody.transporteTbody tr, tbody.hospedagemTbody tr, tbody.servicoTbody tr").each(function(){		
		$find = $(this).find("td.valorGrid").html();
		if(!empty($find)){			
			$valor += converteMoedaFloat($find);															
		}
	});
		
	$vagas = $("input[name=qtdparticipantes]").val();
	if(empty($vagas)){
		$vagas = 1;
	}
	
	$valorTotal = ($valor*$vagas).toFixed(2);		
	$("input[name=valor]").val(numberToMoeda($valorTotal));
	
	$valorVaga = $valor.toFixed(2);
	$valorVaga = numberToMoeda($valorVaga);	
	$("input#valorPorVaga").val(numberToMoeda($valorVaga));
	
	calcularLucroEsperado();
	somatorias();
}

function somatorias(){
	
	//Transportes
	var $valor = null;
	$("tbody.transporteTbody tr").each(function(){		
		$find = $(this).find("td.valorGrid").html();
		if(!empty($find)){			
			$valor += converteMoedaFloat($find);															
		}
	});
	if(!empty($valor)){
		$valor = numberToMoeda($valor.toFixed(2));
		$("tr.somatorioTransporte").remove();
		$html = "<tr class='gradeX even elem somatorioTransporte'><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><strong>"+$valor+"</strong></td><td>&nbsp;</td></tr>";
		$("tbody.transporteTbody").append($html);
	} else {
		$("tr.somatorioTransporte").remove();
	}
	
	//Hospedagens
	var $valor = null;
	$("tbody.hospedagemTbody tr").each(function(){		
		$find = $(this).find("td.valorGrid").html();
		if(!empty($find)){			
			$valor += converteMoedaFloat($find);															
		}
	});
	if(!empty($valor)){
		$valor = numberToMoeda($valor.toFixed(2));
		$("tr.somatorioHospedagem").remove();
		$html = "<tr class='gradeX even elem somatorioHospedagem'><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><strong>"+$valor+"</strong></td><td>&nbsp;</td></tr>";
		$("tbody.hospedagemTbody").append($html);
	} else {
		$("tr.somatorioHospedagem").remove();
	}
	
	//Serviços
	var $valor = null;
	$("tbody.servicoTbody tr").each(function(){		
		$find = $(this).find("td.valorGrid").html();
		if(!empty($find)){			
			$valor += converteMoedaFloat($find);															
		}
	});
	if(!empty($valor)){
		$valor = numberToMoeda($valor.toFixed(2));
		$("tr.somatorioServico").remove();
		$html = "<tr class='gradeX even elem somatorioServico'><td>&nbsp;</td><td><strong>"+$valor+"</strong></td><td>&nbsp;</td></tr>";
		$("tbody.servicoTbody").append($html);
	} else {
		$("tr.somatorioServico").remove();
	}
}

function calcularLucroEsperado(){
	var $valorEsperado = 0;
	$valorVaga = $("input#valorPorVaga").val();	
	$valorVaga = converteMoedaFloat($valorVaga);
	
	if(!empty($("input#lucroEsperado").val()) && $("input#lucroEsperado").val() != "0,00"){
		$("input#percentualLucro").val($("input#lucroEsperado").val());					
		$lucroEsperado = converteMoedaFloat($("input#lucroEsperado").val());
		$valorEsperado = ($lucroEsperado/100 * $valorVaga)+$valorVaga;
		$valorEsperado = $valorEsperado.toFixed(2);
		$valorEsperado = numberToMoeda($valorEsperado);
		$("input[name=valorvendaindividual]").val($valorEsperado);
				
		$("input#valorCobrado").val($valorEsperado);			
	}
	
	$valorCobrado = $("input#valorCobrado").val();	
	$valorCobrado = converteMoedaFloat($valorCobrado);
		
	$lucroIndividual = $valorCobrado - $valorVaga;	
	$lucroIndividual = numberToMoeda($lucroIndividual.toFixed(2));
	$("input#lucroIndividual").val($lucroIndividual);	
}

function uploadRoteiro(){	
	
	var maxfiles = 1; ////Quantidade máxima de uploads	
	var uploader = new plupload.Uploader({
		runtimes : 'gears,html5,html4,flash,silverlight,browserplus',
		browse_button : 'uploader',
		multi_selection: false,
		max_file_size : '10mb',
		max_file_count: maxfiles,
		url: _baseUrl+'admin/'+_controller+'/upload',
		unique_names : true,
		flash_swf_url : _baseUrl+'js/admin/libs/plupload/plupload.flash.swf',
		silverlight_xap_url : _baseUrl+'js/admin/libs/plupload/plupload.silverlight.xap',
		multipart_params: {codigo: $("#codigo").val()},
		filters : [
		            {title : "Arquivos de Imagens (pdf, doc, docx, txt)", extensions : "pdf,doc,docx,txt"}
		        ]
	});

	uploader.init();
	
	$("div.plupload").hide();

	//Ao adicionar arquivos
	uploader.bind('FilesAdded', function(up, files) {
		$('#filelist').html('');

		var aux = 1;

		$.each(files, function(i, file) {						

			$('#filelist').append(
				'<div id="' + file.id + '">' +
				file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
			'</div>');
			aux++;
		});

		up.refresh(); //Reposition Flash/Silverlight

		up.start();

	});

	//Barra de progresso
	uploader.bind('UploadProgress', function(up, file) {
		$('#' + file.id + " b").html(file.percent + "%");
	});

	//Mensagem de erro
	uploader.bind('Error', function(up, err) {
		$('#filelist').append("<div>Error: " + err.code +
			", Message: " + err.message +
			(err.file ? ", File: " + err.file.name : "") +
			"</div>"
		);

		up.refresh(); // Repositório Flash/Silverlight
	});
    
	//Retorno com o caminho do arquivo
	uploader.bind('FileUploaded', function(up, file) {		
				
		$('#' + file.id + " b").html("100%");
				
		var caminho_link = "/images/default/tmp/"+file.target_name;				
		$("input[name=roteiro]").val(caminho_link);		
		$("#pretty").attr("href",_baseUrl+caminho_link).attr("target","_blank");		
		$("#edicao").show();		
		$(".excluir-roteiro").show();
	});
	
}

function excluirRoteiro(){
	$(".excluir-roteiro").click(function(){		
		$("#mensagem").dialog("option", "title", 'Exclusão');
		$("#mensagem").find('p').html('Confirma a exclusão deste(s) registro(s)?');
		
		var roteiro = $("#roteiro").val();
		
		$("#mensagem").dialog({
			resizable : false,
			position : [ 'center', 'middle' ],
			modal : true,
			height: 180,
			width: 310,
			buttons : {
				'Sim apagar' : function() {
					
					$.ajax({
						 dataType : "json",
				         url: _baseUrl+"admin/"+_controller+"/excluir-roteiro",
				         data: {roteiro:roteiro, codigo: $("#codigo").val()},
				         type: "POST",	         	         
				         error: function(){				
							mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com suporte...');
						 },
				         success:function(data) {				        	 
				        	 if(data.erro == 0){
				        		 $("#mensagem").dialog('close');
				        		 $("#edicao").hide();				        		 
				        		 $("#pretty").attr("href","javascript://").attr("target","");;
				        		 $("input[name=roteiro]").val("");
				        	 } else {
				        		 mostraDialog('Erro',data.mensagem);
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
	});
}

$(document).ready(function() {
	
	uploadRoteiro();	
	excluirRoteiro();
	
	//Abrir popup de adição de líder
	$("input.adicionarLider").live("click", function(){		
		url = _baseUrl+"/admin/cliente/cadastro/iframe/true";
								
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "600",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',url).load("#iframe");		    	
	        },	        
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});
	
	//Colocando moeda do pacote
	$("select[name=moeda]").change(function(){
		$moeda = $(this).find("option:selected").text();
		$("span.moeda").html($moeda);
	});
	$("select[name=moeda]").change();
	
	//Abrir popup de venda
	$("a.viewVenda").live("click", function(){		
		$codigo = $(this).data("venda");
		if(empty($codigo)){
			return;
		}
		url = _baseUrl+"/admin/venda/cadastro/iframe/true/codigo/"+$codigo;
				
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "880",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',url).load(resizeIframe("#iframe"));		    	
	        },	        
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});
	
	//Relatório de vendas
	$("a.relatorioVenda").click(function(){				
		$.ajax({			
			url: _baseUrl+'admin/pacote/relatorio-venda',
	        data: {pacote: $("#codigo").val()},
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
	
	crudTransporte();
	crudHospedagem();
	crudServico();
	
	setTimeout(function(){calcularValorPacote();}, 1000);
		
	$("input[name=valorvendaindividual]").blur(function(){
		$("input[name=lucroesperado]").val("0,00");
	});
	
	$("input[name=qtdparticipantes]").keyup(function(){
		calcularValorPacote();
	});
	
	//Calcular lucro esperado	
	$("input#lucroEsperado").blur(function(){calcularLucroEsperado();});
	
	$("div.abaContent").hide();	
	$("div.current").show();
	$("li.aba").click(function(){
		var $class = $(this).attr("id");	
		$("div.abaContent").hide();
		$("div."+$class+"").show();
		
		$("li.aba").removeClass("current");
		$(this).addClass("current");
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
        url:_baseUrl+'/admin/cliente/obter-clientes/liderpacote/1'
	},{
	    loadingImg: _baseUrl+'/images/admin/loader.gif'
	});
	
	mascaras();	
	enviarCadastro();
	
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
				'descricao':'required', 
				'qtdparticipantes':'required' 				
			},
			messages:{									
				'descricao':'Preencha o campo Descrição', 
				'qtdparticipantes':'Preencha o campo Vagas' 
			},
			submitHandler:function(){
				$('input[name=valor]').removeAttr("disabled");				
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
						$("input[name=valor]").attr("disabled","disabled");
					},
					success : function(data) {						
						if (data.erro == '0') {
							if(data.novo){
								location.href = _baseUrl+'admin/'+_controller+'/cadastro/codigo/'+data.codigo;
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
});

//Aba de transporte
function crudTransporte(){
	//Abrir cadastro
	$("input#cadastroTransporte").click(function(){
		if($(this).val() == "Novo"){
			$("div.formTransporte").show();
			$("input#adicionarTransporte").show().val('Adicionar');
			$("input.adicionarTransporte").show();
			$("div.chosen-container").css("width","100%");
			$(this).val("Fechar");
			$("div.formTransporte").find("input").val('');
			$("div.formTransporte").find("textarea").val('');
		} else {
			$("div.formTransporte").hide();
			$("input#adicionarTransporte").hide();
			$("input.adicionarTransporte").hide();
			$(this).val("Novo");
		}	
	});
	
	//Abrir edição
	$("a.editRow").live("click", function(){
		$codigo = $(this).data("codigo");
		$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/pacote/busca-transporte",
    		dataType: 'json',
    		data:{    		
    			codigo: $codigo
    		},
    		beforeSend:function(){
    			
    		},
    		success:function(data){
    			if(!empty(data.html.codigo)){    				
    				if(!empty(data.categoriaTransporte)){
	    				$("select#categoriaTransporte").attr("data-transporte",data.html.transporte);
	    				$("select#categoriaTransporte").val(data.categoriaTransporte);
	    				$("select#categoriaTransporte").change();
    				}
    				
    				$("input#idPacoteTransporte").val(data.html.codigo);
    				$("input#transporteOrigem").val(data.html.origem);
    				$("input#transporteDestino").val(data.html.destino);
    				$("input#transporteDataSaida").val(data.html.datasaida);
    				$("input#transporteHoraSaida").val(data.html.horasaida);
    				$("input#transporteDataChegada").val(data.html.datachegada);
    				$("input#transporteHoraChegada").val(data.html.horachegada);
    				$("input#valorTransporte").val(data.html.valor);
    				$("textarea#historico_viagem").val(data.html.historico_viagem);
    				
    				$("div.formTransporte").show();
    				$("input#adicionarTransporte").show().val('Alterar');
    				$("input.adicionarTransporte").show();
    				$("div.chosen-container").css("width","100%");
    				$("input#cadastroTransporte").val("Fechar");
    				$(window).scrollTop(100);    			
    			}
    		}
    	});
	});
	
	//Deletar da grid
	$("a.deleteRow").live("click", function(){
		$codigo = $(this).data("codigo");
		$.ajax({
			type:'post',
			url:_baseUrl+"admin/pacote/del-transporte",
			dataType: 'json',
			data:{
				pacote: $("#codigo").val(),
				codigo: $codigo
			},
			beforeSend:function(){
				$("a.deleteRow").hide();
			},
			success:function(data){
				$("a.deleteRow").show();
				if(data.erro){
					alert(data.msg);
					return;
				}
				
				$("tbody.transporteTbody").html(data.html);            		
				
				calcularValorPacote();	    			
			}
		});
	});
	
	//Adicionar na grid
	$("#adicionarTransporte").click(function(){
		$transporte = $("select#transporte").find("option:selected").text();		
		$transporteOrigem = $("input#transporteOrigem").val();
		$transporteDestino = $("input#transporteDestino").val();
		$transporteDataSaida = $("input#transporteDataSaida").val();
		$transporteHoraSaida = $("input#transporteHoraSaida").val();
		$transporteDataChegada = $("input#transporteDataChegada").val();
		$transporteHoraChegada = $("input#transporteHoraChegada").val();
		$historicoViagem = $("textarea#historico_viagem").val();
		$transporteVal = $("select#transporte").val();
		var $adicionado = false;
		$("tbody.transporteTbody tr").each(function(){
			$find = $(this).find("td").eq(0).html();			
			if($find == $transporte){				
				$adicionado = true;
				return;
			}
		});
		
		$valor = $("input#valorTransporte").val();
						
		if(!$adicionado && (empty($transporteOrigem) || empty($transporteDestino) || empty($transporte) || empty($transporteVal) || empty($valor))){
			alert("Por favor preencha todos campos obrigatórios (*)");
			return;
		} else {			
			$.ajax({
	    		type:'post',
	    		url:_baseUrl+"admin/pacote/add-transporte",
	    		dataType: 'json',
	    		data:{
	    			codigo: $("input#idPacoteTransporte").val(),
	    			pacote: $("#codigo").val(),
	    			transporte:$transporteVal,
	    			valor:$valor,
	    			origem:$transporteOrigem,
	    			destino:$transporteDestino,
	    			datasaida:$transporteDataSaida,
	    			horasaida:$transporteHoraSaida,
	    			datachegada:$transporteDataChegada,
	    			horachegada:$transporteHoraChegada,
	    			historico_viagem:$historicoViagem,
	    			adicionado:$adicionado
	    		},
	    		beforeSend:function(){
	    			$("input#adicionarTransporte").prop('disabled', true);
	    		},
	    		success:function(data){
	    			$("input#adicionarTransporte").prop('disabled', false);
	    			if(data.erro){
	    				alert(data.msg);
	    				return;
	    			}	    			
	    			$("tbody.transporteTbody").html(data.html);            		
	    			
	    			$("div.formTransporte").find("input").val("");
	    			$("div.formTransporte").find("textarea").val("");
	    			$("input#adicionarTransporte").val("Adicionar");
	    			
	    				    		    			
	    			calcularValorPacote();	    			
	    		}
	    	});
			
		}
	});
	
	//Abrir popup de adição de transporte
	$("input.adicionarTransporte, a.viewTransporte").live("click", function(){		
		url = _baseUrl+"/admin/transporte/cadastro/iframe/true";
		
		var codigo = $(this).data("transporte");
		if(!empty(codigo)){
			url += "/codigo/"+codigo;
		}
						
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "600",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',url).load("#iframe");		    	
	        },	        
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});	
	
	$("select#categoriaTransporte").change(function(){        
    	obterTransportes($(this).val(), $("select#transporte"),$(this).attr('data-transporte'));
    });
    $("select#categoriaTransporte").change();
    function obterTransportes(categoria,obj,selecionar) {
    	$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/index/obter-transportes",
    		data:{
    			categoria:categoria,
    			selecionar:selecionar
    		},
    		beforeSend:function(){
    			obj.html('<option>Carregando...</option>');            
    		},
    		success:function(data){        	
    			obj.html(data);
    			$('select#transporte.chzn-select').trigger("chosen:updated");
    		}
    	});
    }
    
    if(!empty($("#codigo").val())){
    	gridTransportes($("#codigo").val());
    }
    
    //Populando a grid de tranportes
    function gridTransportes($pacote) {
    	$.ajax({
    		type:'post',
    		dataType: 'json',
    		url:_baseUrl+"admin/pacote/grid-transporte",
    		data:{
    			pacote:$pacote
    		},
    		beforeSend:function(){
    			          
    		},
    		success:function(data){        	
    			$("tbody.transporteTbody").html(data.html);            
    		}
    	});
    }
	
}

//Aba de hospedagem
function crudHospedagem(){
	
	//Abrir cadastro
	$("input#cadastroHospedagem").click(function(){
		if($(this).val() == "Novo"){
			$("div.formHospedagem").show();
			$("input#adicionarHospedagem").show().val('Adicionar');
			$("input.adicionarHospedagem").show();
			$("div.chosen-container").css("width","100%");
			$(this).val("Fechar");
			$("div.formHospedagem").find("input").val('');
		} else {
			$("div.formHospedagem").hide();
			$("input#adicionarHospedagem").hide();
			$("input.adicionarHospedagem").hide();
			$(this).val("Novo");
		}	
	});
	
	//Abrir edição
	$("a.editRowHospedagem").live("click", function(){
		$codigo = $(this).data("codigo");
		$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/pacote/busca-hospedagem",
    		dataType: 'json',
    		data:{    		
    			codigo: $codigo
    		},
    		beforeSend:function(){
    			
    		},
    		success:function(data){
    			if(!empty(data.html.codigo)){    				
    				if(!empty(data.pais)){
	    				$("select#pais").attr("data-hospedagem",data.html.hospedagem);
	    				$("select#pais").val(data.pais);
	    				$("select#pais").change();
    				}
    				
    				$("input#idPacoteHospedagem").val(data.html.codigo);    				
    				$("input#hospedagemDataSaida").val(data.html.datasaida);
    				$("input#hospedagemHoraSaida").val(data.html.horasaida);
    				$("input#hospedagemDataChegada").val(data.html.datachegada);
    				$("input#hospedagemHoraChegada").val(data.html.horachegada);
    				$("input#valorHospedagem").val(data.html.valor);
    				
    				$("div.formHospedagem").show();
    				$("input#adicionarHospedagem").show().val('Alterar');
    				$("input.adicionarHospedagem").show();
    				$("div.chosen-container").css("width","100%");
    				$("input#cadastroHospedagem").val("Fechar");
    				$(window).scrollTop(100);    			
    			}
    		}
    	});
	});
	
	//Deletar da grid
	$("a.deleteRowHospedagem").live("click", function(){
		$codigo = $(this).data("codigo");
		$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/pacote/del-hospedagem",
    		dataType: 'json',
    		data:{
    			pacote: $("#codigo").val(),
    			codigo: $codigo
    		},
    		beforeSend:function(){
    			$("a.deleteRow").hide();
    		},
    		success:function(data){
    			$("a.deleteRow").show();
    			if(data.erro){
    				alert(data.msg);
    				return;
    			}
    			
    			$("tbody.hospedagemTbody").html(data.html);            		
    			    				    		    			
    			calcularValorPacote();	    			
    		}
    	});
	});
		
	//Adicionar na grid
	$("#adicionarHospedagem").click(function(){
		$hospedagem = $("select#hospedagem").find("option:selected").text();
		$hospedagemDataSaida = $("input#hospedagemDataSaida").val();
		$hospedagemHoraSaida = $("input#hospedagemHoraSaida").val();
		$hospedagemDataChegada = $("input#hospedagemDataChegada").val();
		$hospedagemHoraChegada = $("input#hospedagemHoraChegada").val();
		$hospedagemVal = $("select#hospedagem").val();
		var $adicionado = false;
		$("tbody.hospedagemTbody tr").each(function(){
			$find = $(this).find("td").eq(0).html();			
			if($find == $hospedagem){				
				$adicionado = true;				
			}
		});
		
		$valor = $("input#valorHospedagem").val();
		$size = $("tbody.hospedagemTbody").find("tr.zero").size();		
		if(!$adicionado && (empty($hospedagem) || empty($hospedagemVal) || empty($valor))){
			alert("Por favor preencha o campo hospedagem e/ou valor");
			return;
		} else {
			$.ajax({
	    		type:'post',
	    		url:_baseUrl+"admin/pacote/add-hospedagem",
	    		dataType: 'json',
	    		data:{
	    			codigo: $("input#idPacoteHospedagem").val(),
	    			adicionado:$adicionado,
	    			pacote:$("#codigo").val(),
	    			hospedagem:$hospedagemVal,	    			
	    			datasaida:$hospedagemDataSaida,
	    			horasaida:$hospedagemHoraSaida,
	    			datachegada:$hospedagemDataChegada,
	    			horachegada:$hospedagemHoraChegada,
	    			valor:$valor	    			
	    		},
	    		beforeSend:function(){
	    			$("input#adicionarHospedagem").prop('disabled', true);
	    		},
	    		success:function(data){
	    			$("input#adicionarHospedagem").prop('disabled', false);
	    			if(data.erro){
	    				alert(data.msg);
	    				return;
	    			}
	    			
	    			$("tbody.hospedagemTbody").html(data.html);
	    			
	    			$("div.formHospedagem").find("input").val("");
	    			$("input#adicionarHospedagem").val("Adicionar");
	    				    			
	    			calcularValorPacote();           
	    		}
	    	});			
		}
	});
	
	if(!empty($("#codigo").val())){
    	gridHospedagens($("#codigo").val());
    }
    
    //Populando a grid de tranportes
    function gridHospedagens($pacote) {
    	$.ajax({
    		type:'post',
    		dataType: 'json',
    		url:_baseUrl+"admin/pacote/grid-hospedagem",
    		data:{
    			pacote:$pacote
    		},
    		beforeSend:function(){
    			          
    		},
    		success:function(data){        	
    			$("tbody.hospedagemTbody").html(data.html);            
    		}
    	});
    }
	
	//Abrir popup de adição de hospedagem
	$("input.adicionarHospedagem, a.viewHospedagem").live("click", function(){		
		url = _baseUrl+"/admin/hospedagem/cadastro/iframe/true";
		
		var codigo = $(this).data("hospedagem");
		if(!empty(codigo)){
			url += "/codigo/"+codigo;
		}
						
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "600",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',url).load("#iframe");		    	
	        },	        
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});
	
	$("select#pais").change(function(){        
    	obterHospedagens($(this).val(), $("select#hospedagem"),$(this).attr('data-hospedagem'));
    });
    $("select#pais").val("BR").change();
    
    function obterHospedagens(pais,obj,selecionar) {
    	$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/index/obter-hospedagens",
    		data:{
    			pais:pais,
    			selecionar:selecionar
    		},
    		beforeSend:function(){
    			obj.html('<option>Carregando...</option>');            
    		},
    		success:function(data){        	
    			obj.html(data);
    			$('select#hospedagem.chzn-select').trigger("chosen:updated");
    		}
    	});
    }
}

//Aba de serviço
function crudServico(){
	//Abrir cadastro
	$("input#cadastroServico").click(function(){
		if($(this).val() == "Novo"){
			$("div.formServico").show();
			$("input#adicionarServico").show().val('Adicionar');
			$("input.adicionarServico").show();
			$("div.chosen-container").css("width","100%");
			$(this).val("Fechar");
			$("div.formServico").find("input").val('');
		} else {
			$("div.formServico").hide();
			$("input#adicionarServico").hide();
			$("input.adicionarServico").hide();
			$(this).val("Novo");
		}	
	});
	
	//Abrir edição
	$("a.editRowServico").live("click", function(){
		$codigo = $(this).data("codigo");
		$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/pacote/busca-servico",
    		dataType: 'json',
    		data:{    		
    			codigo: $codigo
    		},
    		beforeSend:function(){
    			
    		},
    		success:function(data){
    			if(!empty(data.html.codigo)){    				
    				
	    			$("select#servico").val(data.html.servico);	    				    				    			
    				$("input#valorServico").val(data.html.valor);
    				$("input#idPacoteServico").val(data.html.codigo);
    				
    				$("div.formServico").show();
    				$("input#adicionarServico").show().val('Alterar');
    				$("input.adicionarServico").show();    				
    				$("input#cadastroServico").val("Fechar");
    				$(window).scrollTop(100);    			
    			}
    		}
    	});
	});
	
	if(!empty($("#codigo").val())){
    	gridServicos($("#codigo").val());
    }
    
    //Populando a grid de serviços
    function gridServicos($pacote) {
    	$.ajax({
    		type:'post',
    		dataType: 'json',
    		url:_baseUrl+"admin/pacote/grid-servico",
    		data:{
    			pacote:$pacote
    		},
    		beforeSend:function(){
    			          
    		},
    		success:function(data){        	
    			$("tbody.servicoTbody").html(data.html);            
    		}
    	});
    }
	
	//Deletar da grid
	$("a.deleteRowServico").live("click", function(){
		$codigo = $(this).data("codigo");
		$.ajax({
    		type:'post',
    		url:_baseUrl+"admin/pacote/del-servico",
    		dataType: 'json',
    		data:{
    			pacote: $("#codigo").val(),
    			codigo: $codigo
    		},
    		beforeSend:function(){
    			$("a.deleteRow").hide();
    		},
    		success:function(data){
    			$("a.deleteRow").show();
    			if(data.erro){
    				alert(data.msg);
    				return;
    			}
    			
    			$("tbody.servicoTbody").html(data.html);            		
    			    				    		    			
    			calcularValorPacote();	    			
    		}
    	});
	});
	
	//Adicionar na grid
	$("#adicionarServico").click(function(){		
		$servico = $("select#servico").find("option:selected").text();
		$servicoVal = $("select#servico").val();
		var $adicionado = false;
		$("tbody.servicoTbody tr").each(function(){
			$find = $(this).find("td").eq(0).html();			
			if($find == $servico){
				alert("Esse servico já se encontra cadastrado neste pacote");
				$adicionado = true;
				return;
			}
		});
		
		$valor = $("input#valorServico").val();
		$size = $("tbody.servicoTbody").find("tr.zero").size();	
				
		if(!$adicionado && (empty($servico) || empty($servicoVal) || empty($valor))){
			alert("Por favor preencha o campo serviço e/ou valor");
			return;
		} else {			
			$.ajax({
	    		type:'post',
	    		url:_baseUrl+"admin/pacote/add-servico",
	    		dataType: 'json',
	    		data:{
	    			codigo: $("input#idPacoteServico").val(),
	    			adicionado:$adicionado,
	    			pacote:$("#codigo").val(),
	    			servico:$servicoVal,	    				    			
	    			valor:$valor	    			
	    		},
	    		beforeSend:function(){
	    			$("input#adicionarServico").prop('disabled', true);
	    		},
	    		success:function(data){
	    			$("input#adicionarServico").prop('disabled', false);
	    			if(data.erro){
	    				alert(data.msg);
	    				return;
	    			}
	    			
	    			$("tbody.servicoTbody").html(data.html);
	    			
	    			$("div.formServico").find("input").val("");
	    			$("input#adicionarServico").val("Adicionar");
	    				    			
	    			calcularValorPacote();           
	    		}
	    	});	
			
		}
		
	});
	
	//Abrir popup de adição de serviço
	$("input.adicionarServico, a.viewServico").live("click", function(){		
		url = _baseUrl+"/admin/servico/cadastro/iframe/true";
		
		var codigo = $(this).data("servico");
		if(!empty(codigo)){
			url += "/codigo/"+codigo;
		}
						
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "95%",
	        height: "600",	        
		    open: function(ev, ui){		    	
		    	$('#iframe').attr('src',url).load("#iframe");		    	
	        },	        
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});
	
}