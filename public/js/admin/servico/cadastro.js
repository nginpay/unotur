$(document).ready(function() {	
	
	$("input[name=tipopessoa]").change(function(){
    	if($(this).val() == "F") {
    		$("div.fisica").show();
    		$("div.juridica").hide();
    	} else {
    		$("div.fisica").hide();
    		$("div.juridica").show();
    	}
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
				'nome':'required'				
			},
			messages:{									
				'nome':'Preencha o campo Nome'
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
							if($("#cadastro-iframe").val()){    																
								window.parent.$('select#servico').html(data.options);    								
								window.parent.$('select#servico').change();
								window.parent.$('select#servico.chzn-select').trigger("chosen:updated");
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
	}		
});