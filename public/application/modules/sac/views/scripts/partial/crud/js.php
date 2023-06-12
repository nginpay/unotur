<?php
$rules = "";
$messages = "";
$upload = "";
$texto = "";
$saveText = "";
foreach($this->metadados as $metadata=>$key){
	if(!$key["PRIMARY_POSITION"]){
		$label = $metadata;
		$label = str_replace("_", " ", $label);
		$label = ucwords($label);
				
		if(!$key["NULLABLE"]){
			$tipo = null;
			$msgTipo = null;
			if($key["COLUMN_NAME"] == "email"){
				$tipo = ", email:true ";
				$msgTipo = ", 'email': 'Digite um email válido'";
			}
			$rules.= "'{$key["COLUMN_NAME"]}':'required' {$tipo}, ";			
			$messages.= "'{$key["COLUMN_NAME"]}':'Preencha o campo {$label}' {$msgTipo} , ";				
		}
		
		if ($key["DATA_TYPE"] == "text" || $key["DATA_TYPE"] == "longtext"){
		    $saveText = '
        		//Configurando o editor de texto
        		var texto = tinymce.get("texto").getContent();		
        		$("input[name='.$key["COLUMN_NAME"].']").val(texto);
        		tinymce.triggerSave();
		    ';
		    
			$texto = '
		carregarEditor(function($tinymce){
    		if(!empty($("#codigo").val())){	
    			$("input[name='.$key["COLUMN_NAME"].']").val($tinymce.val());
    		}	
    	});	
        function carregarEditor(callBack){
        
        	$.getScript(_baseUrl + "js/admin/plugins/tiny_mce/jquery.tinymce.js", function() {
        						
        		callBack($("textarea").tinymce({
        			
        			// Location of TinyMCE script
        			script_url: _baseUrl + "js/admin/plugins/tiny_mce/tiny_mce.js",
        			
        			plugins: "pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template",
        			
        			// General options
        			height: "250",
        			width: "100%",
        			async: false,
        			mode: "textareas",
        			theme: "advanced",
        		    theme_advanced_buttons1: "mybutton,bold,italic,underline,separator,undo,redo,link,unlink,pastetext,fullscreen",
        		    paste_auto_cleanup_on_paste: true,
        		    paste_text_use_dialog: true,
        		    paste_remove_styles: true,
        		    theme_advanced_buttons2: "",
        		    theme_advanced_buttons3: "",
        		    theme_advanced_toolbar_location: "top",
        		    theme_advanced_toolbar_align: "left",
        		    theme_advanced_statusbar_location: "bottom",
        		    theme_advanced_resizing: false,
        		    
        			// Example content CSS (should be your site CSS)
        			content_css: _baseUrl + "/css/admin/editor.css?v2",
        
        			// Language
        			language: "pt"
        
        		}));
        	});
        }						
					';
		}
		
		if($key["COLUMN_NAME"] == "foto" || $key["COLUMN_NAME"] == "imagem"){
			$upload = "
		upload();
		//Efeito lightbox na foto
    	$(\"a[rel^='prettyPhoto']\").prettyPhoto({
    		social_tools:''
    	});	
		function upload(){		
        	var maxfiles = 1; //Quantidade máxima de uploads	
        	var uploader = new plupload.Uploader({
        		runtimes: 'html5',
        		browse_button: 'uploader',
        		multi_selection: false,
        		max_file_size: '10mb',
        		max_file_count: maxfiles,
        		url: _baseUrl+'admin/'+_controller+'/upload',
        		unique_names: true,
        		flash_swf_url: _baseUrl+'js/admin/plugins/plupload/plupload.flash.swf',
        		silverlight_xap_url: _baseUrl+'js/admin/plugins/plupload/plupload.silverlight.xap',
        		multipart_params: {codigo: $('#codigo').val()},
        		filters: [
        		            {title: 'Arquivos de Imagens (jpg, gif, png)', extensions: 'jpg,gif,png'}
        		        ],
        		resize: {width: 880, height: null, quality: 90}
        	});
        
        	uploader.init();
        	
        	$('div.plupload').hide();
        
        	//Ao adicionar arquivos
        	uploader.bind('FilesAdded', function(up, files) {
        		$('#filelist').html('');
        
        		var aux = 1;
        
        		$.each(files, function(i, file) {						
        
        			$('#filelist').append(
        				'<div id=\"' + file.id + '\">' +
        				file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
        			'</div>');
        			aux++;
        		});
        
        		up.refresh(); //Reposition Flash/Silverlight
        
        		up.start();
        
        	});
        
        	//Barra de progresso
        	uploader.bind('UploadProgress', function(up, file) {
        		$('#' + file.id + ' b').html(file.percent + '%');
        	});
        
        	//Mensagem de erro
        	uploader.bind('Error', function(up, err) {
        		$('#filelist').append('<div>Error: ' + err.code +
        			', Message: ' + err.message +
        			(err.file ? ', File: ' + err.file.name: '') +
        			'</div>'
        		);
        
        		up.refresh(); // Repositório Flash/Silverlight
        	});
            
        	//Retorno com o caminho do arquivo
        	uploader.bind('FileUploaded', function(up, file) {		
        				
        		$('#' + file.id + ' b').html('100%');
        				
        		var caminho_link = '/images/default/tmp/'+file.target_name;				
        		$('#foto').val(caminho_link);		
        		$('#pretty').attr('href',caminho_link);
        		$('#imagem').attr('src',_baseUrl+caminho_link);
        		$('#edicao').show();		
        		$('.excluir-foto').show();
        	});
        	
        }
        
        excluirFoto();
        function excluirFoto(){
        	$('.excluir-foto').click(function(){
        		var codigo = $(this).attr('rel');
        		$('#mensagem').dialog('option', 'title', 'Exclusão');
        		$('#mensagem').find('p').html('Confirma a exclusão deste(s) registro(s)?');
        					
        		var foto = $('#foto').val();
        		
        		$('#mensagem').dialog({
        			resizable: false,
        			position: [ 'center', 'middle' ],
        			modal: true,
        			height: 180,
        			width: 310,
        			buttons: {
        				'Sim apagar': function() {
        					
        					$.ajax({
        						 dataType: 'json',
        				         url: _baseUrl+'admin/'+_controller+'/excluir-foto',
        				         data: {codigo:codigo, foto:foto},
        				         type: 'POST',	         	         
        				         error: function(){				
        							mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com suporte...');
        						 },
        				         success:function(data) {				        	 
        				        	 if(data.erro == 0){
        				        		 $('#mensagem').dialog('close');
        				        		 $('#edicao').hide();
        				        		 $('#imagem').attr('src','http://dummyimage.com/300x300/d6d6d6/686a82.gif&text=Sem+Foto');
        				        		 $('#pretty').attr('href','http://dummyimage.com/300x300/d6d6d6/686a82.gif&text=Sem+Foto');
        				        		 $('#foto').val('');
        				        	 } else {
        				        		 mostraDialog('Erro',data.mensagem);
        				        	 }			        	
        				         }
        				     });						
        				},
        				'Não cancelar': function() {
        					$(this).dialog('close');
        				}
        			}
        		});
        	
        		$('#mensagem').dialog('open');
        		return false;
        	});
        }        
        
			";
		}
		
	}
}

echo "$(document).ready(function() {
        mascaras();	
        enviarCadastro();
        function enviarCadastro(e){
        
            //Impede que o form seja enviado seguindo a action ao invés do ajax
        	if(e != null){
        		e.preventDefault();	        						
        		".$saveText."
        	}
        
    		$('form#formCadastro').validate({
    		    errorLabelContainer: $('#retorno'),
        		errorElement: 'div',
        		invalidHandler: function(form, validator) {
        			$('#retorno').fadeIn('fast').delay(2000).fadeOut('slow');
        		},
    			rules: {					
    				{$rules}				
    			},
    			messages:{									
    				{$messages}
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
		
		{$upload}
		{$texto}

});";