$(document).ready(function() {
	
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
			
});