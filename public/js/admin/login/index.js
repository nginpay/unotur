$(document).ready(function() {			
	$("#mws-login-form form").validate({
		errorPlacement: function(error, element) {  
		}, 
		invalidHandler: function(form, validator) {
			if($.fn.effect) {				
				$("#mws-login-wrapper").effect("shake", {distance: 6, times: 2}, 35);
			}
		},
		submitHandler:function(){
			
			var gravar = '';
			if($('input[name=gravar]').is(":checked")){
				gravar = 'sim';
			} else {
				gravar = 'nao';
			}
			
			$.ajax({			
				url : $("#formLogin").attr("action"),
				data : {
					usuario : $('input[name=usuario]').val(),
					senha : $('input[name=senha]').val(),
					gravar : gravar
				},
				type : "POST",
				dataType : 'json',
				beforeSend : function() {
					$('input[type=submit]').attr('disabled',true);
				},
				error : function() {
					$('input[type=submit]').attr('disabled',false);
					$("#retorno").html("Desculpe, a admin está em manutenção no momento...").fadeIn('slow').delay(2000).fadeOut('slow');
				},
				success : function(data) {
										
					if (data.erro == "0") {
						location.reload(true);
					} else {
						$("#mws-login-wrapper").effect("shake", {distance: 6, times: 2}, 35);
						$("#retorno").html("Usuário ou senha inválida").fadeIn('fast').delay(5000).fadeOut('slow');
					}
					
					$('input[type=submit]').attr('disabled',false);
					
				}
			});
		}
	});	
});

