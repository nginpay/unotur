$(document).ready(function() {
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
    				'nome':'required', 
    				'cpf_cnpj':'required', 
    				'endereco':'required', 
    				'cidade':'required', 
    				'uf':'required', 
    				'banco':'required', 
    				'agencia':'required', 
    				'agencia_dv':'required', 
    				'conta':'required', 
    				'conta_dv':'required', 
    				'carteira':'required'			
    			},
    			messages:{									
    				'nome':'Preencha o campo Nome', 
    				'cpf_cnpj':'Preencha o campo Cpf Cnpj', 
    				'endereco':'Preencha o campo Endereco', 
    				'cidade':'Preencha o campo Cidade', 
    				'uf':'Preencha o campo Uf', 
    				'banco':'Preencha o campo Banco', 
    				'agencia':'Preencha o campo Agencia', 
    				'agencia_dv':'Preencha o campo Agencia Dv', 
    				'conta':'Preencha o campo Conta', 
    				'conta_dv':'Preencha o campo Conta Dv', 
    				'carteira':'Preencha o campo Carteira' 
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
    							location.href = _baseUrl+'admin/'+_controller;
    						} else {													
    							alert(data.msg);
    						}
    						
    						$('button[type=submit]').attr('disabled',false);						
    					}
    				});
    			}
    		});
		}
        

    	upload();	
    	excluirFoto();
	
});

function upload(){	
	
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
		multipart_params: {usuario: $("#usuario").val()},
		filters : [
		            {title : "Arquivos de Imagens (jpg, gif, png)", extensions : "jpg,gif,png"}
		        ],
		resize : {width:240, height:63, quality:100}
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
		$("#logomarca").val(caminho_link);		
		$("#pretty").attr("href",caminho_link);
		$("#imagem").attr("src",_baseUrl+caminho_link);
		$("#edicao").show();		
		$(".excluir-logomarca").show();
	});
	
}

function excluirFoto(){
	$(".excluir-logomarca").click(function(){
		var codigo = $(this).attr("rel");
		$("#mensagem").dialog("option", "title", 'Exclusão');
		$("#mensagem").find('p').html('Confirma a exclusão deste(s) registro(s)?');
		
		var logomarca = $("#logomarca").val();
		
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
				         url: _baseUrl+"admin/"+_controller+"/excluir-logomarca",
				         data: {codigo:codigo, logomarca:logomarca},
				         type: "POST",	         	         
				         error: function(){				
							mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com suporte...');
						 },
				         success:function(data) {				        	 
				        	 if(data.erro == 0){
				        		 $("#mensagem").dialog('close');
				        		 $("#edicao").hide();
				        		 $("#imagem").attr("src","http://dummyimage.com/100x100/d6d6d6/686a82.gif&text=Sem+Foto");
				        		 $("#pretty").attr("href","javascript://");
				        		 $("#logomarca").val("");
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