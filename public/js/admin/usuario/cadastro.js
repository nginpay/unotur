$(document).ready(function() {
	
	$('input[type=radio][name=administrador]').change(function() {
		if (this.value == '1' || $('input#liderTrue').is(':checked')) {
			$("div#permissoes").hide();
		}
		else {
			$("div#permissoes").show();
		}
	});
		
	$('form#formCadastro').validate({
		rules: {
			"nome": "required",        			      							        			      								
			"email": {"email":true, "required":true},
			"usuario": {"required":true},
			"senha": {"required":$("#usuario").val() != "" ? false : true},
			"confirmar-senha": {equalTo: "#senha"}
		},
		messages:{
			"nome": "Campo nome obrigatório",																																	
			"email": {"email":"Campo email inválido", "required":"Campo email obrigatório"},															
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
				success : function(data) {						
					if (data.erro == '0') {
						if($("#cadastro-iframe").val()){    								
							window.parent.$('select#usuario').append(data.option);
							window.parent.$('select#usuario').val(data.codigo);								    								
							window.parent.$('select#usuario').change();
							window.parent.$('#dialog').dialog('close');    								    								
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
	
	upload();	
	excluirFoto();
	
	//Efeito lightbox na foto
	$("a[rel^='prettyPhoto']").prettyPhoto({
		social_tools:''
	});
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
		resize : {width:100, height:100, quality:100}
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
		$("#foto").val(caminho_link);		
		$("#pretty").attr("href",caminho_link);
		$("#imagem").attr("src",_baseUrl+caminho_link);
		$("#edicao").show();		
		$(".excluir-foto").show();
	});
	
}

function excluirFoto(){
	$(".excluir-foto").click(function(){
		var categoriaEmpresa = $(this).attr("rel");
		$("#mensagem").dialog("option", "title", 'Exclusão');
		$("#mensagem").find('p').html('Confirma a exclusão deste(s) registro(s)?');
		
		var foto = $("#foto").val();
		
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
				         url: _baseUrl+"admin/"+_controller+"/excluir-foto",
				         data: {categoriaEmpresa:categoriaEmpresa, foto:foto},
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
				        		 $("#foto").val("");
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