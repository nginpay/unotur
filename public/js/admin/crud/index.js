$(document).ready(function() {		
		$("form#formCadastro").validate({
			rules: {					
				"nome": "required"				
			},
			messages:{									
				"nome": "Campo model obrigatório"
			},
			submitHandler:function(){						
				$.ajax({			
					url: $("form#formCadastro").attr('action'),
			        data: $("form#formCadastro").serialize(),
					type : "POST",
					dataType : 'json',
					beforeSend : function() {
						$('input[type=submit]').attr('disabled',true);
					},
					error : function() {
						$('button[type=submit]').attr('disabled',false);
						alert("Desculpe, a admin está em manutenção no momento...");
					},
					success : function(data) {						
						if (data.erro == "0") {
							location.href = _baseUrl+"admin/"+_controller;
						} else {													
							alert(data.msg);
						}
						
						$('input[type=submit]').attr('disabled',false);						
					}
				});
			}
		});

});

