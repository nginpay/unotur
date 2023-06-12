function atendimentosPainel($data){	
	$.plot("#mws-line-chart",[$data], {
		grid: { hoverable: true, clickable: true},
		series: {
			lines: {show: true},			
			points: {show: true}
		},
		xaxis: {
			mode: "categories",
			tickLength: 0
		}
	});
	
    var previousPoint = null;
    $("#mws-line-chart").bind("plothover", function (event, pos, item) {
        $("#x").text(pos.x);
        $("#y").text(pos.y);

            if (item) {
                if (previousPoint != item.datapoint) {
                    previousPoint = item.datapoint;
                    $("#tooltip").remove();
                    y = item.datapoint[1];
                    showTooltip(item.pageX, item.pageY, y+" atendimento(s)");
                }
            }
            else {
                $("#tooltip").remove();
                previousPoint = null;            
            }

    });

    // mostrar o tooltip
    function showTooltip(x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y - 35,
            left: x + 5,
            border: '1px solid #fdd',
            padding: '2px',
            'background-color': '#fee',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
    }

}

function vendasPainel($data){
	$.plot("#mws-bar-chart", [$data], {
		grid: { hoverable: true, clickable: true},
		series: {
			bars: {
				show: true,
				barWidth: 0.6,
				align: "center"
			}
		},
		xaxis: {
			mode: "categories",
			tickLength: 0
		}
	});
	
    var previousPoint = null;
    $("#mws-bar-chart").bind("plothover", function (event, pos, item) {
        $("#x").text(pos.x.toFixed(2));
        $("#y").text(pos.y.toFixed(2));

            if (item) {
                if (previousPoint != item.datapoint) {
                    previousPoint = item.datapoint;
                    $("#tooltip").remove();
                    y = item.datapoint[1];
                    showTooltip(item.pageX, item.pageY, y+" venda(s)");
                }
            }
            else {
                $("#tooltip").remove();
                previousPoint = null;            
            }

    });

    // mostrar o tooltip
    function showTooltip(x, y, contents) {
        $('<div id="tooltip">' + contents + '</div>').css( {
            position: 'absolute',
            display: 'none',
            top: y - 35,
            left: x + 5,
            border: '1px solid #fdd',
            padding: '2px',
            'background-color': '#fee',
            opacity: 0.80
        }).appendTo("body").fadeIn(200);
    }
}

$(document).ready(function() {
	mascaras();
	
	//Atendimentos no ano
	var ticksPush = [];	
	ticksPush.push(["08/2014",0]);
	$("input.atendimentos").each(function(){			
		ticksPush.push([$(this).data("name"),$(this).val()]);		
	});	
	var data = ticksPush;		
	atendimentosPainel(data);
		
	//Vendas no ano
	var ticksPush = [];	
	ticksPush.push(["08/2014",0]);
	$("input.vendas").each(function(){			
		ticksPush.push([$(this).data("name"),$(this).val()]);		
	});	
	var data = ticksPush;			
	vendasPainel(data);
	
	//Box Cotação
	dialogCotacao();
	
	if($("span.cotacao-dolar").html() == '0,00'){
		$("#dialog-cotacao").dialog("open");
	}
	
	$("span.cotacao").click(function(){
		$("#dialog-cotacao").dialog("open");
	});
	
	
});

function dialogCotacao(){	
	//Box cotação	
	$( "#dialog-cotacao" ).dialog({
		autoOpen: false,				
		minWidth: 310,
		resizable: false,
		draggable: false,
		modal: true,		
		buttons: {
			"Salvar": function() {				
				gravarCotacao();
			},
			"Fechar": function() {				
				$(this).dialog( "close" );				
			}
		}		
	});
	
	function gravarCotacao(){		
		$.ajax({
		     url: _baseUrl+"admin/index/salvar-cotacao",
		     data: $("form#formCotacao").serialize(),
		     type: "POST",
		     dataType: 'json',
		     error: function(){				
				mostraDialog('Erro','Erro no envio, tente novamente ou entre em contato com o suporte...');
			 },
			 success : function(data) {	
				if(data.erro){
					alert(data.msg);
					return;
				} else {					
					location.href = _baseUrl+"/admin";
				}
				  						
			}
		 });	
	}
	
	
}