$(document).ready(function() {	
	$("a.imoveis").live("click", function(ev){
		ev.preventDefault();
		url = $(this).attr("href");
						
		//Box editar	
		$("#dialog").dialog({
			autoOpen: false,
	        modal: true,	        
	        width: "90%",
	        height: "2200",
	        draggable: false,
	        position : [ 'center', 'top' ],
		    open: function(ev, ui){			    	
		    	$('#iframe').attr('src',url);		    	
	        },
	        close: function(){
	        	$('#iframe').attr('src',null);
	        },
	        position: "top"
		});
				
		$('#dialog').dialog('open');		
	});		
});