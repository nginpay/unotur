function documentos(){
	uploadGaleria();
	function uploadGaleria() {
		var maxfiles = 2;
		$("#uploader").pluploadQueue({
			// General settings		
			runtimes : 'gears,html5,html4,flash,silverlight,browserplus',
			max_file_size : '100mb',
			max_file_count: maxfiles,
			url: _baseUrl+'sac/cliente-documento/upload-galeria',
			chunk_size : '10mb',
			unique_names : true,
			multiple_queues : true,
			multipart_params : {codigo: $('#codigo').val()},				
			// Renomeia arquivos para não ter nomes em duplicidades
			rename: true,
			
			// Sort files
			sortable: true,

			// Especifica os tipos de arquivos para o browser
			filters : [
			            {title : "Arquivos de Imagens (jpg, gif, png, mp4)", extensions : "jpg,gif,png,mp4"}
			        ],
			
			resize : {width: 600, height: null, quality: 100},
			
		    //Flash settings
			flash_swf_url : _baseUrl+'sac/libs/plugins/plupload/js/plupload.flash.swf',

			// Silverlight settings
			silverlight_xap_url : _baseUrl+'sac/libs/plugins/plupload/js/plupload.silverlight.xap',        
			        
			init:{
				//No fim de cada upload
	            FileUploaded: function(up, file) {
	            	var $caminhoLink = "/images/default/tmp/"+file.target_name+";";
	            	$novoValor = $("input[name=midias]").val()+$caminhoLink;
	            	if(empty($("#codigo").val())){
	            		$("input[name=midias]").val($novoValor);	            		
	            	}
	            },
	            //No fim da fila de uploads
	            UploadComplete: function(up, files) {
	            	buscarFotosGaleria();                
				}
			}
		});
				
		buscarFotosGaleria();		
	}

	function buscarFotosGaleria() {	
		var midias = $("input[name=midias]").val();
		//Ação consultar
		$.ajax({		
			type: 'post',
			data: {codigo: $("#codigo").val(), midias: midias},
			url: _baseUrl+'sac/cliente-documento/midias', 
			dataType : "html",		
			beforeSend : function() {				
				mostraDialog('Carregando', 'Por favor aguarde...');
			},
			complete: function(){
				$("#mensagem").dialog('close');
			},
			success : function(data) {								         	 
				$("div#galeria").html(data);
				//Efeito na galeria
	        	$("a[rel^='prettyPhoto']").prettyPhoto({social_tools:''});
				excluirFotoGaleria();
				editarFotoGaleria();				
				baixarFotoGaleria();				
			}
		});				
	}


	function excluirFotoGaleria(){

		$(".excluir-foto").click(function(){		
			$("#mensagem").dialog("option", "title", 'Exclusão');
			$("#mensagem").find('p').html('Confirma a exclusão deste registro?');
			var codigoFoto = $(this).data("codigo");

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
					         url: _baseUrl+"sac/cliente-documento/excluir-foto-galeria",
					         data: {codigo: codigoFoto},
					         type: "POST",	         	         
					         error: function(){				
								mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com suporte...');

							 },
					         success:function(data) {				        	 
					        	 if(data.erro == 0){
					        		 buscarFotosGaleria();
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

	function editarFotoGaleria(){
		//Box editar	
		$( "#dialog-form" ).dialog({
			autoOpen: false,			
			minWidth: 310,
			resizable: false,
			draggable: false,
			modal: true,		
			buttons: {
				"Salvar": function() {							
					gravarMidiaGaleria();
				},
				Fechar: function() {				
					$(this).dialog( "close" );				
				}
			}
		});	
		
		$(".editar-foto").click(function(){
			var codigo = $(this).attr("rel");

			//Busca os dados, preenche o formulário e depois abre o dialog
			$.ajax({
		         url: _baseUrl+"sac/cliente-documento/buscar-foto-galeria",
		         data: {codigo: codigo},
		         type: "POST",
		         dataType: 'json',
				 success : function(data) {
					 if(data.erro == 0){
						 $("#formFoto").find("input[name=codigo]").val(codigo);
						 $("input[name=legenda]").val(data.legenda);
						 $("input[name=ordem]").val(data.ordem);					 
						 $("#dialog-form").dialog("open");
					 }
				}
		     });			
		});

	}
	
	function baixarFotoGaleria(){

		$(".baixar-foto").click(function(){					
			var codigoFoto = $(this).data("codigo");
			
			if(!empty(codigoFoto)){
				
			}			
		});
	}

	function gravarMidiaGaleria(){
		$.ajax({
	         url: _baseUrl+"sac/cliente-documento/salvar-foto-galeria",
	         data: $("form#formFoto").serialize(),
	         type: "POST",
	         dataType: 'json',
	         error: function(){				
				mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com suporte...');
			 },
			 success : function(data) {				
					if(data.erro == "0") {
						buscarFotosGaleria();
						$( "#dialog-form" ).dialog("close");
					}
					else {
						mostraDialog('Atenção',"Falha ao enviar a requisição");
					}    						
				}
	     });	
	}

}


$(document).ready(function() {
	
	documentos();
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
    
    $("input[name=tipopessoa]").change(function(){
    	if($(this).val() == "F") {
    		$("div.fisica").show();
    		$("div.juridica").hide();
    	} else {
    		$("div.fisica").hide();
    		$("div.juridica").show();
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
				'nome':'required', 
				"email": {"email":true, "required":true}, 				
				'datanascimento':'required', 
				'endereco':'required', 
				'bairro':'required', 
				'cidade':'required',     				
				'status':'required',
				"usuario": {"required":true},
				"senha": {"required":$("#usuario").val() != "" ? false : true},
				"confirmar-senha": {equalTo: "#senha"}
			},
			messages:{									
				'nome':'Preencha o campo nome', 
				"email": {"email":"Campo email inválido", "required":"Campo email obrigatório"}, 				 
				'datanascimento':'Preencha o campo data nascimento', 
				'endereco':'Preencha o campo endereço', 
				'bairro':'Preencha o campo bairro', 
				'cidade':'Preencha o campo cidade',     				 
				'status':'Preencha o campo status',
				"usuario": {"required":"Campo usuário obrigatório"},
				"senha": {"required":"Campo senha obrigatório"},
				"confirmar-senha": {equalTo:"Confirmação da senha não coincide com o campo senha"}
			},
			submitHandler:function(){						
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
					success : function(data) {						
						if (data.erro == '0') {
							alert('Cadastro atualizado com sucesso!');
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