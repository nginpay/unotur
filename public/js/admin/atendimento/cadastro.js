
//Aba de Historico
function crudHistorico() {
       
    //Abrir cadastro
    $("input#cadastroHistorico").click(function () {
        if ($(this).val() == "Novo") {
            $("div.formHistorico").show();
            $("input#adicionarHistorico").show().val('Adicionar');
            $("input.adicionarHistorico").show();
            $("div.chosen-container").css("width", "100%");
            $(this).val("Fechar");
            $("div.formHistorico").find("input").val('');
        } else {
            $("div.formHistorico").hide();
            $("input#adicionarHistorico").hide();
            $("input.adicionarHistorico").hide();
            $(this).val("Novo").hide();
        }
    });

    //Abrir edição
    $("a.editRowHistorico").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/busca-historico",
            dataType: 'json',
            data: {
                codigo: $codigo
            },
            beforeSend: function () {

            },
            success: function (data) {
                if (!empty(data.html.codigo)) {
                    
                    $("input#idAtendimentoHistorico").val(data.html.codigo);                                       
                    $("input#historicoDataRetorno").val(data.html.dataretorno);                    
                    $("input#historicoObservacao").val(data.html.observacao);                    
                    
                    $("div.formHistorico").show();
                    $("input#adicionarHistorico").show().val('Alterar');
                    $("input.adicionarHistorico").show();                    
                    $("input#cadastroHistorico").val("Fechar").show();
                    $(window).scrollTop(100);
                }
            }
        });
    });

    //Deletar da grid
    $("a.deleteRowHistorico").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/del-historico",
            dataType: 'json',
            data: {
                atendimento: $("#codigo").val(),
                codigo: $codigo
            },
            beforeSend: function () {
                $("a.deleteRow").hide();
            },
            success: function (data) {
                $("a.deleteRow").show();
                if (data.erro) {
                    alert(data.msg);
                    return;
                }

                $("tbody.historicoTbody").html(data.html);

                
            }
        });
    });

    //Adicionar na grid
    $("#adicionarHistorico").click(function () {        
        $historicoDataRetorno = $("input#historicoDataRetorno").val();                        
        $historicoObservacao = $("input#historicoObservacao").val();        
                                
        $size = $("tbody.historicoTbody").find("tr.zero").size();
        if ((empty($historicoDataRetorno) || empty($historicoObservacao))) {            
            alert("Por favor preencha os campos obrigatórios (*)");
            return;
        } else {
            $.ajax({
                type: 'post',
                url: _baseUrl + "admin/atendimento/add-historico",
                dataType: 'json',
                data: {
                    codigo: $("input#idAtendimentoHistorico").val(),                                                           
                    atendimento: $("input#codigo").val(),                                                           
                    dataretorno: $historicoDataRetorno,                                        
                    observacao: $historicoObservacao
                },
                beforeSend: function () {
                    $("input#adicionarHistorico").prop('disabled', true);
                },
                success: function (data) {
                    $("input#adicionarHistorico").prop('disabled', false);
                    if (data.erro) {
                        alert(data.msg);
                        return;
                    }

                    $("tbody.historicoTbody").html(data.html);

                    $("div.formHistorico").find("input").val("");                    
                    $("input#adicionarHistorico").val("Adicionar");
                    $("input#cadastroHistorico").click();
                }
            });
        }
    });

    if (!empty($("#codigo").val())) {
        gridHistoricos($("#codigo").val());
    }

    //Populando a grid de tranportes
    function gridHistoricos($atendimento) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: _baseUrl + "admin/atendimento/grid-historico",
            data: {
                atendimento: $atendimento
            },
            beforeSend: function () {

            },
            success: function (data) {
                $("tbody.historicoTbody").html(data.html);
            }
        });
    }

}




//Aba de pacote
function crudPacote() {
    
    //Filtro de pesquisa 
    $("select.chosen-tipo-pacote").chosen({
        allow_single_deselect: true,
        disable_search_threshold: 1,
        search_contains: true,
        no_results_text: "Cadastrar:"
    });
    $(".chosen-tipo-pacote + .chosen-container .chosen-results li.no-results span").live("click", function () {
        $filtro = $(this).html();

        //Enviando cadastro via ajax
        $.ajax({
            url: _baseUrl + "/admin/atendimento/salvar-tipo-pacote",
            data: {nome: $filtro},
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
                $('button[type=submit]').attr('disabled', true);
            },
            error: function () {
                $('button[type=submit]').attr('disabled', false);
                alert('Desculpe, a admin está em manutenção no momento...');
            },
            success: function (data) {
                $("select.chosen-tipo-pacote").append(data.option).trigger("chosen:updated");
                $.jGrowl(data.msg, {position: "top-right", life: 3000});
            }
        });
    });
    
    //Abrir cadastro
    $("input#cadastroPacote").click(function () {
        if ($(this).val() == "Novo") {
            $("div.formPacote").show();
            $("input#adicionarPacote").show().val('Adicionar');
            $("input.adicionarPacote").show();
            $("div.chosen-container").css("width", "100%");
            $(this).val("Fechar");
            $("div.formPacote").find("input").val('');
        } else {
            $("div.formPacote").hide();
            $("input#adicionarPacote").hide();
            $("input.adicionarPacote").hide();
            $(this).val("Novo");
        }
    });

    //Abrir edição
    $("a.editRowPacote").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/busca-pacote",
            dataType: 'json',
            data: {
                codigo: $codigo
            },
            beforeSend: function () {

            },
            success: function (data) {
                if (!empty(data.html.codigo)) {
                    if (!empty(data.html.tipo_pacote)) {                         
                        $('select.chosen-tipo-pacote').val(data.html.tipo_pacote);                         
                        $("select.chosen-tipo-pacote").trigger("chosen:updated");
                    }

                    $("input#idAtendimentoPacote").val(data.html.codigo);
                    $("input#pacoteData").val(data.html.data);                                                          
                    $("input#pacoteDestino").val(data.html.destino);

                    $("div.formPacote").show();
                    $("input#adicionarPacote").show().val('Alterar');
                    $("input.adicionarPacote").show();
                    $("div.chosen-container").css("width", "100%");
                    $("input#cadastroPacote").val("Fechar");
                    $(window).scrollTop(100);
                }
            }
        });
    });

    //Deletar da grid
    $("a.deleteRowPacote").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/del-pacote",
            dataType: 'json',
            data: {
                atendimento: $("#codigo").val(),
                codigo: $codigo
            },
            beforeSend: function () {
                $("a.deleteRow").hide();
            },
            success: function (data) {
                $("a.deleteRow").show();
                if (data.erro) {
                    alert(data.msg);
                    return;
                }

                $("tbody.pacoteTbody").html(data.html);

                
            }
        });
    });

    //Adicionar na grid
    $("#adicionarPacote").click(function () {
        $tipoPacote = $("select#tipo_pacote").val();
        $pacoteDestino = $("input#pacoteDestino").val();        
        $pacoteData = $("input#pacoteData").val();
        
        $size = $("tbody.pacoteTbody").find("tr.zero").size();
        if ((empty($tipoPacote) || empty($pacoteDestino) || empty($pacoteData))) {
            //console.log($tipoPacote, $pacoteDataSaida, $pacoteDataChegada, $pacoteLocalidade);
            alert("Por favor preencha todos campos");
            return;
        } else {
            $.ajax({
                type: 'post',
                url: _baseUrl + "admin/atendimento/add-pacote",
                dataType: 'json',
                data: {
                    codigo: $("input#idAtendimentoPacote").val(),                    
                    atendimento: $("#codigo").val(),
                    tipo_pacote: $tipoPacote,
                    destino: $pacoteDestino,
                    data: $pacoteData
                },
                beforeSend: function () {
                    $("input#adicionarPacote").prop('disabled', true);
                },
                success: function (data) {
                    $("input#adicionarPacote").prop('disabled', false);
                    if (data.erro) {
                        alert(data.msg);
                        return;
                    }

                    $("tbody.pacoteTbody").html(data.html);

                    $("div.formPacote").find("input").val("");
                    $("select.chosen-tipo-pacote").val("");
                    $("select.chosen-tipo-pacote").trigger("chosen:updated");
                    $("input#adicionarPacote").val("Adicionar");
                }
            });
        }
    });

    if (!empty($("#codigo").val())) {
        gridPacotes($("#codigo").val());
    }

    //Populando a grid de tranportes
    function gridPacotes($atendimento) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: _baseUrl + "admin/atendimento/grid-pacote",
            data: {
                atendimento: $atendimento
            },
            beforeSend: function () {

            },
            success: function (data) {
                $("tbody.pacoteTbody").html(data.html);
            }
        });
    }

}

//Aba de Seguro
function crudSeguro() {
       
    //Abrir cadastro
    $("input#cadastroSeguro").click(function () {
        if ($(this).val() == "Novo") {
            $("div.formSeguro").show();
            $("input#adicionarSeguro").show().val('Adicionar');
            $("input.adicionarSeguro").show();
            $("div.chosen-container").css("width", "100%");
            $(this).val("Fechar");
            $("div.formSeguro").find("input").val('');
        } else {
            $("div.formSeguro").hide();
            $("input#adicionarSeguro").hide();
            $("input.adicionarSeguro").hide();
            $(this).val("Novo");
        }
    });

    //Abrir edição
    $("a.editRowSeguro").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/busca-seguro",
            dataType: 'json',
            data: {
                codigo: $codigo
            },
            beforeSend: function () {

            },
            success: function (data) {
                if (!empty(data.html.codigo)) {
                    
                    $("input#idAtendimentoSeguro").val(data.html.codigo);                                       
                    $("input#seguroDataInicio").val(data.html.datainicio);                    
                    $("input#seguroDataFim").val(data.html.datafim);                    
                    $("input#seguroDescricao").val(data.html.descricao);

                    $("div.formSeguro").show();
                    $("input#adicionarSeguro").show().val('Alterar');
                    $("input.adicionarSeguro").show();                    
                    $("input#cadastroSeguro").val("Fechar");
                    $(window).scrollTop(100);
                }
            }
        });
    });

    //Deletar da grid
    $("a.deleteRowSeguro").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/del-seguro",
            dataType: 'json',
            data: {
                atendimento: $("#codigo").val(),
                codigo: $codigo
            },
            beforeSend: function () {
                $("a.deleteRow").hide();
            },
            success: function (data) {
                $("a.deleteRow").show();
                if (data.erro) {
                    alert(data.msg);
                    return;
                }

                $("tbody.seguroTbody").html(data.html);

                
            }
        });
    });

    //Adicionar na grid
    $("#adicionarSeguro").click(function () {        
        $seguroDataInicio = $("input#seguroDataInicio").val();        
        $seguroDataFim = $("input#seguroDataFim").val();        
        $seguroDescricao = $("input#seguroDescricao").val();        
                                
        $size = $("tbody.seguroTbody").find("tr.zero").size();
        if ((empty($seguroDataInicio) || empty($seguroDataFim) || empty($seguroDescricao))) {            
            alert("Por favor preencha os campos obrigatórios (*)");
            return;
        } else {
            $.ajax({
                type: 'post',
                url: _baseUrl + "admin/atendimento/add-seguro",
                dataType: 'json',
                data: {
                    codigo: $("input#idAtendimentoSeguro").val(),                                                           
                    atendimento: $("input#codigo").val(),                                                           
                    datainicio: $seguroDataInicio,                    
                    datafim: $seguroDataFim,
                    descricao: $seguroDescricao
                },
                beforeSend: function () {
                    $("input#adicionarSeguro").prop('disabled', true);
                },
                success: function (data) {
                    $("input#adicionarSeguro").prop('disabled', false);
                    if (data.erro) {
                        alert(data.msg);
                        return;
                    }

                    $("tbody.seguroTbody").html(data.html);

                    $("div.formSeguro").find("input").val("");                    
                    $("input#adicionarSeguro").val("Adicionar");
                }
            });
        }
    });

    if (!empty($("#codigo").val())) {
        gridPassagens($("#codigo").val());
    }

    //Populando a grid de tranportes
    function gridPassagens($atendimento) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: _baseUrl + "admin/atendimento/grid-seguro",
            data: {
                atendimento: $atendimento
            },
            beforeSend: function () {

            },
            success: function (data) {
                $("tbody.seguroTbody").html(data.html);
            }
        });
    }

}


//Aba de Passagem
function crudPassagem() {
       
    //Abrir cadastro
    $("input#cadastroPassagem").click(function () {
        if ($(this).val() == "Novo") {
            $("div.formPassagem").show();
            $("input#adicionarPassagem").show().val('Adicionar');
            $("input.adicionarPassagem").show();
            $("div.chosen-container").css("width", "100%");
            $(this).val("Fechar");
            $("div.formPassagem").find("input").val('');
        } else {
            $("div.formPassagem").hide();
            $("input#adicionarPassagem").hide();
            $("input.adicionarPassagem").hide();
            $(this).val("Novo");
        }
    });

    //Abrir edição
    $("a.editRowPassagem").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/busca-passagem",
            dataType: 'json',
            data: {
                codigo: $codigo
            },
            beforeSend: function () {

            },
            success: function (data) {
                if (!empty(data.html.codigo)) {
                    
                    $("input#idAtendimentoPassagem").val(data.html.codigo);
                    $("input#passagemOrigem").val(data.html.origem);                    
                    $("input#passagemDestino").val(data.html.destino);                    
                    $("input#passagemData").val(data.html.data);
                    $("input#passagemQtdAdulto").val(data.html.qtdadulto);
                    $("input#passagemQtdCrianca").val(data.html.qtdcrianca);

                    $("div.formPassagem").show();
                    $("input#adicionarPassagem").show().val('Alterar');
                    $("input.adicionarPassagem").show();                    
                    $("input#cadastroPassagem").val("Fechar");
                    $(window).scrollTop(100);
                }
            }
        });
    });

    //Deletar da grid
    $("a.deleteRowPassagem").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/del-passagem",
            dataType: 'json',
            data: {
                atendimento: $("#codigo").val(),
                codigo: $codigo
            },
            beforeSend: function () {
                $("a.deleteRow").hide();
            },
            success: function (data) {
                $("a.deleteRow").show();
                if (data.erro) {
                    alert(data.msg);
                    return;
                }

                $("tbody.passagemTbody").html(data.html);

                
            }
        });
    });

    //Adicionar na grid
    $("#adicionarPassagem").click(function () {        
        $passagemOrigem = $("input#passagemOrigem").val();        
        $passagemDestino = $("input#passagemDestino").val();        
        $passagemData = $("input#passagemData").val();
        $passagemQtdAdulto = $("input#passagemQtdAdulto").val();
        $passagemQtdCrianca = $("input#passagemQtdCrianca").val();
                        
        $size = $("tbody.passagemTbody").find("tr.zero").size();
        if ((empty($passagemOrigem) || empty($passagemDestino) || empty($passagemData) || empty($passagemQtdAdulto))) {            
            alert("Por favor preencha os campos obrigatórios (*)");
            return;
        } else {
            $.ajax({
                type: 'post',
                url: _baseUrl + "admin/atendimento/add-passagem",
                dataType: 'json',
                data: {
                    codigo: $("input#idAtendimentoPassagem").val(),                    
                    atendimento: $("#codigo").val(),
                    origem: $passagemOrigem,
                    destino: $passagemDestino,                    
                    data: $passagemData,                    
                    qtdadulto: $passagemQtdAdulto,
                    qtdcrianca: $passagemQtdCrianca
                },
                beforeSend: function () {
                    $("input#adicionarPassagem").prop('disabled', true);
                },
                success: function (data) {
                    $("input#adicionarPassagem").prop('disabled', false);
                    if (data.erro) {
                        alert(data.msg);
                        return;
                    }

                    $("tbody.passagemTbody").html(data.html);

                    $("div.formPassagem").find("input").val("");                    
                    $("input#adicionarPassagem").val("Adicionar");
                }
            });
        }
    });

    if (!empty($("#codigo").val())) {
        gridPassagens($("#codigo").val());
    }

    //Populando a grid de tranportes
    function gridPassagens($atendimento) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: _baseUrl + "admin/atendimento/grid-passagem",
            data: {
                atendimento: $atendimento
            },
            beforeSend: function () {

            },
            success: function (data) {
                $("tbody.passagemTbody").html(data.html);
            }
        });
    }

}



//Aba de hospedagem
function crudHospedagem() {
    
    //Filtro de pesquisa 
    $("select.chosen-tipo-hospedagem").chosen({
        allow_single_deselect: true,
        disable_search_threshold: 1,
        search_contains: true,
        no_results_text: "Cadastrar:"
    });
    $(".chosen-tipo-hospedagem + .chosen-container .chosen-results li.no-results span").live("click", function () {
        $filtro = $(this).html();

        //Enviando cadastro via ajax
        $.ajax({
            url: _baseUrl + "/admin/atendimento/salvar-tipo-hospedagem",
            data: {nome: $filtro},
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
                $('button[type=submit]').attr('disabled', true);
            },
            error: function () {
                $('button[type=submit]').attr('disabled', false);
                alert('Desculpe, a admin está em manutenção no momento...');
            },
            success: function (data) {
                $("select.chosen-tipo-hospedagem").append(data.option).trigger("chosen:updated");
                $.jGrowl(data.msg, {position: "top-right", life: 3000});
            }
        });
    });
    
    //Abrir cadastro
    $("input#cadastroHospedagem").click(function () {
        if ($(this).val() == "Novo") {
            $("div.formHospedagem").show();
            $("input#adicionarHospedagem").show().val('Adicionar');
            $("input.adicionarHospedagem").show();
            $("div.chosen-container").css("width", "100%");
            $(this).val("Fechar");
            $("div.formHospedagem").find("input").val('');
        } else {
            $("div.formHospedagem").hide();
            $("input#adicionarHospedagem").hide();
            $("input.adicionarHospedagem").hide();
            $(this).val("Novo");
        }
    });

    //Abrir edição
    $("a.editRowHospedagem").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/busca-hospedagem",
            dataType: 'json',
            data: {
                codigo: $codigo
            },
            beforeSend: function () {

            },
            success: function (data) {
                if (!empty(data.html.codigo)) {
                    if (!empty(data.html.tipo_hospedagem)) {                         
                        $('select.chosen-tipo-hospedagem').val(data.html.tipo_hospedagem);                         
                        $("select.chosen-tipo-hospedagem").trigger("chosen:updated");
                    }

                    $("input#idAtendimentoHospedagem").val(data.html.codigo);
                    $("input#hospedagemDataSaida").val(data.html.datasaida);                    
                    $("input#hospedagemDataChegada").val(data.html.datachegada);                    
                    $("input#hospedagemLocalidade").val(data.html.local);

                    $("div.formHospedagem").show();
                    $("input#adicionarHospedagem").show().val('Alterar');
                    $("input.adicionarHospedagem").show();
                    $("div.chosen-container").css("width", "100%");
                    $("input#cadastroHospedagem").val("Fechar");
                    $(window).scrollTop(100);
                }
            }
        });
    });

    //Deletar da grid
    $("a.deleteRowHospedagem").live("click", function () {
        $codigo = $(this).data("codigo");
        $.ajax({
            type: 'post',
            url: _baseUrl + "admin/atendimento/del-hospedagem",
            dataType: 'json',
            data: {
                atendimento: $("#codigo").val(),
                codigo: $codigo
            },
            beforeSend: function () {
                $("a.deleteRow").hide();
            },
            success: function (data) {
                $("a.deleteRow").show();
                if (data.erro) {
                    alert(data.msg);
                    return;
                }

                $("tbody.hospedagemTbody").html(data.html);

                
            }
        });
    });

    //Adicionar na grid
    $("#adicionarHospedagem").click(function () {
        $tipoHospedagem = $("select#tipo_hospedagem").val();
        $hospedagemDataSaida = $("input#hospedagemDataSaida").val();        
        $hospedagemDataChegada = $("input#hospedagemDataChegada").val();
        $hospedagemLocalidade = $("input#hospedagemLocalidade").val();
        
        $valor = $("input#valorHospedagem").val();
        $size = $("tbody.hospedagemTbody").find("tr.zero").size();
        if ((empty($tipoHospedagem) || empty($hospedagemDataSaida) || empty($hospedagemDataChegada) || empty($hospedagemLocalidade))) {
            //console.log($tipoHospedagem, $hospedagemDataSaida, $hospedagemDataChegada, $hospedagemLocalidade);
            alert("Por favor preencha todos campos");
            return;
        } else {
            $.ajax({
                type: 'post',
                url: _baseUrl + "admin/atendimento/add-hospedagem",
                dataType: 'json',
                data: {
                    codigo: $("input#idAtendimentoHospedagem").val(),                    
                    atendimento: $("#codigo").val(),
                    tipo_hospedagem: $tipoHospedagem,
                    datasaida: $hospedagemDataSaida,                    
                    datachegada: $hospedagemDataChegada,                    
                    local: $hospedagemLocalidade
                },
                beforeSend: function () {
                    $("input#adicionarHospedagem").prop('disabled', true);
                },
                success: function (data) {
                    $("input#adicionarHospedagem").prop('disabled', false);
                    if (data.erro) {
                        alert(data.msg);
                        return;
                    }

                    $("tbody.hospedagemTbody").html(data.html);

                    $("div.formHospedagem").find("input").val("");
                    $("select.chosen-tipo-hospedagem").val("");
                    $("select.chosen-tipo-hospedagem").trigger("chosen:updated");
                    $("input#adicionarHospedagem").val("Adicionar");
                }
            });
        }
    });

    if (!empty($("#codigo").val())) {
        gridHospedagens($("#codigo").val());
    }

    //Populando a grid de tranportes
    function gridHospedagens($atendimento) {
        $.ajax({
            type: 'post',
            dataType: 'json',
            url: _baseUrl + "admin/atendimento/grid-hospedagem",
            data: {
                atendimento: $atendimento
            },
            beforeSend: function () {

            },
            success: function (data) {
                $("tbody.hospedagemTbody").html(data.html);
            }
        });
    }

}

$(document).ready(function () {
    mascaras();
    enviarCadastro();
    crudHospedagem();
    crudPacote();
    crudPassagem();
    crudSeguro();
    crudHistorico();
    
    $("div.abaContent").hide();
    $("div.current").show();
    $("li.aba").click(function () {
        var $class = $(this).attr("id");
        $("div.abaContent").hide();
        $("div." + $class + "").show();

        $("li.aba").removeClass("current");
        $(this).addClass("current");
        
        $("div.chosen-container").css("width","100%");
    });
    
    
    if(!empty($("#codigo").val())){
        $("li#historico").click();
    }

    
    //Filtro de pesquisa 
    $("select.chosen-sd, select.chosen-cliente").chosen({
        allow_single_deselect: true,
        disable_search_threshold: 1,
        search_contains: true,
        no_results_text: "Cadastrar:"
    });
    $(".chosen-sd + .chosen-container .chosen-results li.no-results span").live("click", function () {
        $filtro = $(this).html();

        //Enviando cadastro via ajax
        $.ajax({
            url: _baseUrl + "/admin/atendimento/salvar-categoria",
            data: {nome: $filtro},
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
                $('button[type=submit]').attr('disabled', true);
            },
            error: function () {
                $('button[type=submit]').attr('disabled', false);
                alert('Desculpe, a admin está em manutenção no momento...');
            },
            success: function (data) {
                $("select.chosen-sd").append(data.option).trigger("chosen:updated");
                $.jGrowl(data.msg, {position: "top-right", life: 3000});
            }
        });
    });

    //Cliente
    $('select#cliente').ajaxChosen({
        dataType: 'json',
        type: 'POST',
        url: _baseUrl + '/admin/cliente/obter-clientes'
    }, {
        loadingImg: _baseUrl + '/images/admin/loader.gif'
    });

    $("#cliente_chosen .chosen-results li.no-results span").live("click", function () {
        $filtro = $(this).html();

        //Enviando cadastro via ajax
        $.ajax({
            url: _baseUrl + "/admin/atendimento/salvar-cliente",
            data: {nome: $filtro},
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
                $('button[type=submit]').attr('disabled', true);
            },
            error: function () {
                $('button[type=submit]').attr('disabled', false);
                alert('Desculpe, a admin está em manutenção no momento...');
            },
            success: function (data) {
                $("select#cliente").append(data.option).trigger("chosen:updated");
                $.jGrowl(data.msg, {position: "top-right", life: 3000});
            }
        });
    });

    $("select[name=statusatendimento]").change(function () {
        if ($(this).val() == 1) {
            $("div#conta").show();
        } else {
            $("div#conta").hide();
            $("div#conta_areceber").hide();
            $("div#conta_areceber").find("input").val("");
        }
    });
    $("select[name=statusatendimento]").change();

    $("input[name=venda_areceber]").blur(function () {
        $codigo = $(this).val();

        $.ajax({
            url: _baseUrl + "/admin/atendimento/busca-conta",
            data: {codigo: $codigo},
            type: 'POST',
            dataType: 'json',
            beforeSend: function () {
                $('button[type=submit]').attr('disabled', true);
            },
            error: function () {
                $('button[type=submit]').attr('disabled', false);
                alert('Desculpe, a admin está em manutenção no momento...');
            },
            success: function (data) {
                if (data.erro == '0') {
                    $("div#conta_areceber").show();
                    $("input#tipo").val(data.tipo);
                    $("input#parcela").val(data.parcela);
                    $("input#vencimento").val(data.vencimento);
                    $("input#valor").val(data.valor);
                    $("input[name=cliente]").val(data.nome);
                }

                $('button[type=submit]').attr('disabled', false);
            }
        });
    });

    if (!empty($("input[name=venda_areceber]").val())) {
        $("input[name=venda_areceber]").blur();
    }


    function enviarCadastro(e) {

        //Impede que o form seja enviado seguindo a action ao invés do ajax
        if (e != null) {
            e.preventDefault();
        }

        $('form#formCadastro').validate({
            errorLabelContainer: $('#retorno'),
            errorElement: 'div',
            invalidHandler: function (form, validator) {
                $('#retorno').fadeIn('fast').delay(2000).fadeOut('slow');
            },
            rules: {
                'usuario': 'required',
                'cliente': 'required',
                'dataretorno': 'required',
                'telefone': 'required',
                'statusatendimento': 'required'
            },
            messages: {
                'usuario': 'Preencha o campo Usuário',
                'cliente': 'Preencha o campo Nome Completo',
                'dataretorno': 'O campo Data para Retorno não pode ser vazio',
                'telefone': 'O campo Telefone não pode ser vazio',
                'statusatendimento': 'Preencha o campo Status do Atendimento'
            },
            submitHandler: function () {
                $.ajax({
                    url: $('form#formCadastro').attr('action'),
                    data: $('form#formCadastro').serialize(),
                    type: 'POST',
                    dataType: 'json',
                    beforeSend: function () {
                        $('button[type=submit]').attr('disabled', true);
                    },
                    error: function () {
                        $('button[type=submit]').attr('disabled', false);
                        alert('Desculpe, a admin está em manutenção no momento...');
                    },
                    success: function (data) {
                        if (data.erro == '0') {
                            if ($("#cadastro-iframe").val()) {
                                window.parent.$('#dialog').dialog('close');
                            } else if (data.adicionou) {
                                location.href = _baseUrl + 'admin/' + _controller + "/cadastro/codigo/" + data.codigo;
                            } else {
                                location.href = _baseUrl + 'admin/' + _controller;
                            }

                        } else {
                            alert(data.msg);
                        }

                        $('button[type=submit]').attr('disabled', false);
                    }
                });
            }
        });
    }

});