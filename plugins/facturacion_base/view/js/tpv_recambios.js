/*
 * This file is part of facturacion_base
 * Copyright (C) 2014-2017  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

var fs_nf0 = 2;
var fs_nf0_art = 2;
var numlineas = 0;
var tpv_url = '';
var siniva = false;
var irpf = 0;
var all_impuestos = [];
var all_series = [];
var cliente = false;
var fin_busqueda1 = true;
var codbarras = false;
var divFamilias = "";
var ipUsuario = "";
var ventana_personas_tpv = false;
var persona_servicio ="";
var arreglo_lineas_servicios = [];
var cont_arreglo_lineas_servicios = 0;
var bot_add_combo = false;
var necesita_articulo = "";
var combo_activo = false;
var clientes_db;
var lineas_editar;

function usar_cliente(codcliente)
{
    if (tpv_url !== '') {
        $.getJSON(tpv_url, 'datoscliente=' + codcliente, function (json) {
            cliente = json;
            document.f_buscar_articulos.codcliente.value = cliente.codcliente;
            if (cliente.regimeniva == 'Exento') {
                irpf = 0;
                for (var j = 0; j < numlineas; j++) {
                    if ($("#linea_" + j).length > 0) {
                        $("#iva_" + j).val(0);
                        $("#recargo_" + j).val(0);
                    }
                }
            }
            recalcular();
        });
    }
}

function getClient(placa)
{
    const modal_save = document.querySelector('#modal_guardar');
    if (tpv_url !== '') {
        $.getJSON(tpv_url, 'placa=' + placa, function (json) {
            let flag = false;
            
            if(json){
                if( json.nombre2 == "" || json.email == "" || json.telefono1 == ""){
                    flag = true;       
                }
                document.querySelector('input[name="nombrecliente"]').value = placa
                document.querySelector('input[name="cifnif"]').value = json.cifnif;
                document.querySelector('input[name="nombre2"]').value = json.nombre2;
                document.querySelector('select[name="tipoidfiscal"]').value = json.tipoidfiscal;
                document.querySelector('input[name="telefono1"]').value = json.telefono1;
                document.querySelector('input[name="email"]').value = json.email;
            }else{
                flag = true;
                document.querySelector('input[name="nombrecliente"]').value = placa
            }

            if(flag)
            {
                const foot = modal_save.querySelector('.modal-footer');
                if(foot){
                    foot.classList.add('hidden_modal');
                    const tab_data = modal_save.querySelector('a[aria-controls="tab_cliente"]');
                    tab_data.click();
                }
            }
        });
    }
}

function setdataclient(){
    const data_client = document.querySelector('#tab_cliente');
    const placa = data_client.querySelector('input[name="nombrecliente"]').value

    const data = {
        cifnif: data_client.querySelector('input[name="cifnif"]').value,
        nombre2: data_client.querySelector('input[name="nombre2"]').value,
        tipo_cifnif: data_client.querySelector('select[name="tipoidfiscal"]').value,
        cifnif: data_client.querySelector('input[name="cifnif"]').value,
        telefono1: data_client.querySelector('input[name="telefono1"]').value,
        email: data_client.querySelector('input[name="email"]').value,
        razonsocial: data_client.querySelector('input[name="razonsocial"]').value,
    };

    
    let errors = "";
    if(data.cifnif == "")
        errors += "La identificación es obligatoria<br>";
    if(data.nombre2 == "")
        errors += "El nombre del usuario es obligatorio<br>";
    if(data.telefono1 == "")
        errors += "El teléfono es obligatorio<br>";
    if(data.tipo_cifnif == "")
        errors += "El tipo de documento es obligatorio<br>";
    if(data.email == "")
        errors += "El email es obligatorio<br>";
    if(data.tipo_cifnif == "NIT" && data.razonsocial == "")
        errors += "La razón social es obligatorio cuando es NIT<br>";    
    if(data.cifnif == placa)
        errors += "Identificación incorrecta<br>";    
    if(data.email != "" && validarEmail(data.email) == false) {
        errors += "El email está mal escrito<br>"
    }

    const err = data_client.querySelector('.msg-data');
    err.innerHTML = "";
    if(errors == ""){
        crear_cliente_tpv(placa,data);
    }
    else{
        err.innerHTML = errors;
    }
    

}

function validarEmail(email) {
    // Expresión regular para validar el formato del email
    var regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

function cliente_db()
{
    if (tpv_url !== '') {
        $.getJSON(tpv_url, 'clientes_db=1' , function (json) {
            if(json){
                clientes_db = json;
               //alert(JSON.stringify(clientes_db));
            }
            
        });
    }
}






function usar_serie()
{
    for (var i = 0; i < all_series.length; i++) {
        if (all_series[i].codserie == document.f_tpv.serie.value) {
            siniva = all_series[i].siniva;
            irpf = all_series[i].irpf;

            for (var j = 0; j < numlineas; j++) {
                if ($("#linea_" + j).length > 0 && siniva) {
                    $("#iva_" + j).val(0);
                    $("#recargo_" + j).val(0);
                }
            }

            break;
        }
    }
}

function recalcular()
{
    var subtotales = [];
    var total_irpf = 0;

    for (var i = 1; i <= numlineas; i++) {
        if ($("#linea_" + i).length > 0) {
            /// cambiamos coma por punto
            if (input_number == 'text' && $("#cantidad_" + i).val().search(",") >= 0) {
                $("#cantidad_" + i).val($("#cantidad_" + i).val().replace(",", "."));
            }
            if ($("#pvp_" + i).val().search(",") >= 0) {
                $("#pvp_" + i).val($("#pvp_" + i).val().replace(",", "."));
            }
            if ($("#dto_" + i).val().search(",") >= 0) {
                $("#dto_" + i).val($("#dto_" + i).val().replace(",", "."));
            }
            if ($("#iva_" + i).val().search(",") >= 0) {
                $("#iva_" + i).val($("#iva_" + i).val().replace(",", "."));
            }
            if ($("#irpf_" + i).val().search(",") >= 0) {
                $("#irpf_" + i).val($("#irpf_" + i).val().replace(",", "."));
            }
            if ($("#recargo_" + i).val().search(",") >= 0) {
                $("#recargo_" + i).val($("#recargo_" + i).val().replace(",", "."));
            }

            var l_uds = parseFloat($("#cantidad_" + i).val());
            var l_pvp = parseFloat($("#pvp_" + i).val());
            var l_dto = parseFloat($("#dto_" + i).val());
            var l_neto = l_uds * l_pvp * (100 - l_dto) / 100;
            var l_iva = parseFloat($("#iva_" + i).val());
            var l_irpf = parseFloat($("#irpf_" + i).val());
            var l_recargo = parseFloat($("#recargo_" + i).val());

            $("#neto_" + i).val(l_neto);
            if (numlineas == 1) {
                $("#total_" + i).val(fs_round(l_neto, fs_nf0) + fs_round(l_neto * (l_iva - l_irpf + l_recargo) / 100, fs_nf0));
            } else {
                $("#total_" + i).val(number_format(l_neto + (l_neto * (l_iva - l_irpf + l_recargo) / 100), fs_nf0, '.', ''));
            }

            /// adaptamos el alto del textarea al texto
            var txt = $("textarea[name='desc_" + i + "']").val();
            txt = txt.split(/\r*\n/);
            if (txt.length > 1) {
                $("textarea[name='desc_" + i + "']").prop('rows', txt.length);
            }

            /// calculamos los subtotales
            var l_codimpuesto = Math.round(l_iva * 100);
            if (subtotales[l_codimpuesto] === undefined) {
                subtotales[l_codimpuesto] = {
                    neto: 0,
                    iva: 0,
                    recargo: 0,
                };
            }

            subtotales[l_codimpuesto].neto += l_neto;
            subtotales[l_codimpuesto].iva += l_neto * l_iva / 100;
            subtotales[l_codimpuesto].recargo += l_neto * l_recargo / 100;
            total_irpf += l_neto * l_irpf / 100;
        }
    }

    /// redondeamos los subtotales
    var neto = 0;
    var total_iva = 0;
    var total_recargo = 0;
    total_irpf = fs_round(total_irpf, fs_nf0);
    subtotales.forEach(function (elem) {
        neto += fs_round(elem.neto, fs_nf0);
        total_iva += fs_round(elem.iva, fs_nf0);
        total_recargo += fs_round(elem.recargo, fs_nf0);
    });

    var total = fs_round(neto + total_iva - total_irpf + total_recargo, fs_nf0);

    $("#aneto").html(number_format(neto, fs_nf0, '.', ''));
    $("#aiva").html(number_format(total_iva, fs_nf0, '.', ''));
    $("#are").html(number_format(total_recargo, fs_nf0, '.', ''));
    $("#airpf").html(number_format(total_irpf, fs_nf0, '.', ''));
    $("#atotal").html(Math.round(total));


    console.log("---------");
    console.log("Neto: " + neto);
    console.log("IVA: " + total_iva);
    console.log("RE: " + total_recargo);
    console.log("IRPF: " + total_irpf);
    console.log("Total: " + (neto + total_iva - total_irpf + total_recargo));

    if (total_recargo == 0 && !cliente.recargo) {
        $(".recargo").hide();
    } else {
        $(".recargo").show();
    }

    if (total_irpf == 0 && irpf == 0) {
        $(".irpf").hide();
    } else {
        $(".irpf").show();
    }

    $("#tpv_total").val(fs_round(Math.round(total), fs_nf0));
    $("#tpv_total2").val(fs_round(total, fs_nf0));
    $("#tpv_total3").val(fs_round(total, fs_nf0));

    var tpv_efectivo = parseFloat($("#tpv_efectivo").val());
    $("#tpv_cambio").val(show_precio(tpv_efectivo - total));
}

function ajustar_total(i)
{
    var l_uds = 0;
    var l_pvp = 0;
    var l_dto = 0;
    var l_iva = 0;
    var l_irpf = 0;
    var l_recargo = 0;
    var l_neto = 0;
    var l_total = 0;

    if ($("#linea_" + i).length > 0) {
        /// cambiamos coma por punto
        if ($("#total_" + i).val().search(",") >= 0) {
            $("#total_" + i).val($("#total_" + i).val().replace(",", "."));
        }

        l_uds = parseFloat($("#cantidad_" + i).val());
        l_pvp = parseFloat($("#pvp_" + i).val());
        l_dto = parseFloat($("#dto_" + i).val());
        l_iva = parseFloat($("#iva_" + i).val());
        l_recargo = parseFloat($("#recargo_" + i).val());
        l_irpf = parseFloat($("#irpf_" + i).val());

        l_total = parseFloat($("#total_" + i).val());
        if (isNaN(l_total)) {
            l_total = 0;
        } else if (l_total < 0) {
            l_total = Math.abs(l_total);
        }

        if (l_total <= l_pvp * l_uds + (l_pvp * l_uds * (l_iva - l_irpf + l_recargo) / 100)) {
            l_neto = 100 * l_total / (100 + l_iva - l_irpf + l_recargo);
            l_dto = 100 - 100 * l_neto / (l_pvp * l_uds);
            if (isNaN(l_dto)) {
                l_dto = 0;
            }

            l_dto = fs_round(l_dto, 2);
        } else {
            l_dto = 0;
            l_neto = 100 * l_total / (100 + l_iva - l_irpf + l_recargo);
            l_pvp = fs_round(l_neto / l_uds, fs_nf0_art);
        }

        $("#pvp_" + i).val(l_pvp);
        $("#dto_" + i).val(l_dto);
    }

    recalcular();
}

function get_precios(ref)
{
    if (tpv_url !== '') {
        $.ajax({
            type: 'POST',
            url: tpv_url,
            dataType: 'html',
            data: "referencia4precios=" + ref + "&codcliente=" + document.f_tpv.cliente.value,
            success: function (datos) {
                $("#search_results").html(datos);
            },
            error: function () {
                bootbox.alert({
                    message: 'Se ha producido un error al obtener los precios.',
                    title: "<b>Atención</b>"
                });
            }
        });
    }
}

function new_articulo()
{

    if (nueva_venta_url !== '') {


        $.ajax({
            type: 'POST',
            url: nueva_venta_url + '&new_articulo=TRUE',
            dataType: 'json',
            data: $("form[name=f_nuevo_articulo]").serialize(),
            success: function (datos) {
                if (typeof datos[0] == 'undefined') {
                    bootbox.alert({
                        message: 'Se ha producido un error al crear el artículo.',
                        title: "<b>Atención</b>"
                    });
                } else {
                    document.f_buscar_articulos.query.value = document.f_nuevo_articulo.referencia.value;
                    $("#nav_articulos li").each(function () {
                        $(this).removeClass("active");
                    });
                    $("#li_mis_articulos").addClass('active');
                    $("#search_results").show();
                    $("#nuevo_articulo").hide();

                    add_articulo(datos[0].referencia, Base64.encode(datos[0].descripcion), datos[0].pvp, 0, datos[0].codimpuesto, 1);
                }
            },
            error: function () {
                bootbox.alert({
                    message: 'Se ha producido un error al crear el artículo.',
                    title: "<b>Atención</b>"
                });
            }
        });
    }
}


function traer_combos(ref_combo)
{
        combo_activo = true;
        ref_combo = ref_combo.replace("COMBO", "");
    if (tpv_url !== '') {
        $.getJSON(tpv_url, 'combo_seleccionado=' + ref_combo, function (json) {
                console.log(json);
                if(json.length>0){
                    
			for(i=0; i < json.length ; i++)
			{
					//buscar_articulosManual(json[i].articulo);
                                        descripcion = Base64.encode(json[i].descripcion);
                                        add_articulo(json[i].referencia, descripcion, json[i].pvp, 0, json[i].codimpuesto, 1, json[i].servicio, json[i].preciocombo, json[i].necesita_articulo);
			}
                        bot_add_combo = true;
                        $("#addCombo").show();
                        
                }

        });
    }
    
    
    
}


//función para verificar que, si combo está activo entonces, debe haber, mínimo 3 artículos combo

function verificar_combo_lineas()
{
    
    result = false;
    cont = 0;
    
    if(bot_add_combo){
      
        for(i=1; i<= numlineas; i++){
            if ($("#linea_" + i).length > 0) {
                console.log("#desc_"+i+" // "+$("#desc_"+i).val().indexOf("_COMBO"));
                if($("#desc_"+i).val().indexOf("_COMBO") != -1){
                    cont++;
                }
            }
        }

        if(cont > 1)
            result = true;
    }
    else
        result = true;
    
    
    
    return result;
}
        
function add_articulo(ref, desc, pvp, dto, codimpuesto, cantidad, servi_lav, prec_combo, necesita_art, codcombinacion)
{
    
    ventana_personas_tpv = true;
	per_temp = "";
	
    //Se revisa si el artículo adicionado es un servicio
        if(servi_lav == 1){

            if(persona_servicio==""){
                    $("#modal_personas").show();
                    $("#espacio_add").hide();
                    $("#bot_personas").hide();
            }
            per_temp = persona_servicio;
                            arreglo_lineas_servicios[cont_arreglo_lineas_servicios] = (numlineas+1);
                            cont_arreglo_lineas_servicios++;

        }
    
    //con ésta línea se garantiza que si, es mayot a cero abra el cuadro de diálogo y 
    //rellene el campo que le corresponde
    necesita_articulo = necesita_art;
    
        if(necesita_art > 0){
            $("#modal_personas").show();
            $("#espacio_add").hide();
            $("#bot_personas").hide();
        }
    
    
    
        
    
    
    //********************************
    
    if (typeof codcombinacion == 'undefined') {
        codcombinacion = '';
    }

    numlineas += 1;
    $("#numlineas").val(numlineas);
    
    desc = Base64.decode(desc);
    var iva = 0;
    var recargo = 0;
    if (cliente.regimeniva != 'Exento' && !siniva) {
        for (var i = 0; i < all_impuestos.length; i++) {
            if (all_impuestos[i].codimpuesto == codimpuesto) {
                iva = all_impuestos[i].iva;
                if (cliente.recargo) {
                    recargo = all_impuestos[i].recargo;
                }
                break;
            }
        }
    }
    
	
	
    if(combo_activo){
        pvp = prec_combo;
		desc = desc +" __COMBO";
    }
    
    

    $("#lineas_doc").prepend("<tr id=\"linea_" + numlineas + "\">\n\
         <td><input type=\"hidden\" name=\"referencia_" + numlineas + "\" value=\"" + ref + "\"/>\n\
            <input type=\"hidden\" name=\"codcombinacion_" + numlineas + "\" value=\"" + codcombinacion + "\"/>\n\
            <input type=\"hidden\" id=\"iva_" + numlineas + "\" name=\"iva_" + numlineas + "\" value=\"" + iva + "\"/>\n\
            <input type=\"hidden\" id=\"recargo_" + numlineas + "\" name=\"recargo_" + numlineas + "\" value=\"" + recargo + "\"/>\n\
            <input type=\"hidden\" id=\"irpf_" + numlineas + "\" name=\"irpf_" + numlineas + "\" value=\"" + irpf + "\"/>\n\
            <div class=\"form-control\"><a target=\"_blank\" href=\"index.php?page=ventas_articulo&ref=" + ref + "\">" + ref + "</a></div></td>\n\
         <td><textarea class=\"form-control\" id=\"desc_" + numlineas + "\" name=\"desc_" + numlineas + "\" rows=\"1\">" + desc + "</textarea></td>\n\
          <td><input type=\"text\"  id=\"prov_" + numlineas + "\" class=\"form-control text-left\" name=\"prov_" + numlineas +
            "\" readonly autocomplete=\"off\" value=\"" + per_temp	 + "\"/></td>\n\
          <td><input type=\"" + input_number + "\" step=\"any\" id=\"cantidad_" + numlineas + "\" class=\"form-control text-right\" name=\"cantidad_" + numlineas +
            "\" onchange=\"recalcular()\" onkeyup=\"recalcular()\" autocomplete=\"off\" value=\"" + cantidad + "\"/></td>\n\
         <td><button class=\"btn btn-sm btn-danger\" type=\"button\" onclick=\"nueva_persona("+numlineas+");$('#linea_" + numlineas + "').remove();recalcular();\">\n\
            <span class=\"glyphicon glyphicon-trash\"></span></button></td>\n\
         <td><input type=\"text\" class=\"form-control text-right\" id=\"pvp_" + numlineas + "\" name=\"pvp_" + numlineas + "\" value=\"" + pvp +
            "\" onkeyup=\"recalcular()\" onclick=\"this.select()\" readonly autocomplete=\"off\"/></td>\n\
         <td><input type=\"text\" id=\"dto_" + numlineas + "\" name=\"dto_" + numlineas + "\" value=\"" + dto +
            "\" class=\"form-control text-right\" onkeyup=\"recalcular()\" onchange=\"recalcular()\" onclick=\"this.select()\" autocomplete=\"off\"/></td>\n\
         <td><input type=\"text\" class=\"form-control text-right\" id=\"neto_" + numlineas + "\" name=\"neto_" + numlineas +
            "\" readonly/></td>\n\
         <td class=\"text-right\"><div class=\"form-control\">" + iva + "</div></td>\n\
         <td class=\"text-right recargo\"><div class=\"form-control\">" + recargo + "</div></td>\n\
         <td class=\"text-right irpf\"><div class=\"form-control\">" + irpf + "</div></td>\n\
         <td class=\"warning\" title=\"Cálculo aproximado del total de la linea\">\n\
            <input type=\"text\" class=\"form-control text-right\" id=\"total_" + numlineas + "\" name=\"total_" + numlineas +
            "\" onchange=\"ajustar_total(" + numlineas + ")\" onclick=\"this.select()\" autocomplete=\"off\"/></td></tr>");
    recalcular();
    $("#modal_articulos").modal('hide');

    $("#cantidad_" + (numlineas)).focus();

   /* setTimeout(function() {
        traerPesoBalanza((numlineas));
         
      }, 500);
*/
    $("#referenciaManual").val("");
    $("#referenciaManual").select();
	
}

function nueva_persona(linea){
    if($("#prov_"+linea).val() != "")
        persona_servicio = "";
}

function traerPesoBalanza(ref)
{
    $.ajax({
            url     : 'pesoBalanza.php',
            type    : 'POST',
            dataType: 'json',
            data    : "ipUsuario=" + ipUsuario +"&peticion=" + "peso"
        })
        .done(function(data){
                         console.log("--> "+data.exito);
                if(data.exito != null){
                    
                    if(data.estad == 0){
                        $("#cantidad_" + ref).val(1);    
                    }
                    else{
                        
                        $("#cantidad_" + ref).val(data.exito);
                        console.log(ref+" / "+data.exito+ " *--* "+data.estad);
                    }
                }
                else{

                    if(data.estad == 0){
                        $("#cantidad_" + ref).val(1);    
                    }
                    else{
                        alert("No se ha podido leer el peso del producto, presione la tecla (Bloq Mayús)");
                        $('#linea_'+ref).remove();
                    }
                }

           
        });

        setTimeout(function() {
            permitirPeso(0);
			$("#balanza").removeClass("btn-warning").addClass("btn-apagado");
            recalcular();
        }, 500);
        
}


function permitirPeso(dat)
{
	
    $.ajax({
            url     : 'pesoBalanza.php',
            type    : 'POST',
            dataType: 'json',
            data    : "ipUsuario=" + ipUsuario +"&peticion=" + "estado" +"&estado=" + dat 
        })
        .done(function(data){
            if(data.estado)
            {
				$("#balanza").removeClass("btn-apagado").addClass("btn-warning");
                console.log("Balanza ESTADO "+data.estado);
                //alert("No se pudo tener precio de la balanza");
            }
        });
}



function add_articulo_atributos(ref, desc, pvp, dto, codimpuesto, cantidad)
{
    if (tpv_url !== '') {
        $.ajax({
            type: 'POST',
            url: 'index.php?page=tpv_recambios',
            dataType: 'html',
            data: "referencia4combi=" + ref + "&desc=" + desc + "&pvp=" + pvp + "&dto=" + dto
                    + "&codimpuesto=" + codimpuesto + "&cantidad=" + cantidad,
            success: function (datos) {
                $("#nav_articulos").hide();
                $("#search_results").html(datos);
            },
            error: function () {
                bootbox.alert({
                    message: 'Se ha producido un error al obtener los atributos.',
                    title: "<b>Atención</b>"
                });
            }
        });
    }
}

function buscar_articulos()
{


    if (document.f_buscar_articulos.query.value == '') {
        $("#search_results").html('');
    } else if (tpv_url !== '') {
        document.f_buscar_articulos.codcliente.value = document.f_tpv.cliente.value;

        fin_busqueda1 = false;
        $.getJSON(tpv_url, $("form[name=f_buscar_articulos]").serialize(), function (json) {
            var items = [];
            var insertar = false;
            var contadorColumnas = 0;
            var conti = 0;

                $.each(json, function (key, val) {
                    //console.log("---> "+  json);
                    conti++;
                
                    contadorColumnas++;
                    var tdtr = "</td>";  
                    var tr_aux = "<td class='mostrador'>";  

                    //ésta es la condición que devuelve el contador en 1
                    if(contadorColumnas == 3){
                        tdtr = "</td></tr>";
                        contadorColumnas = 0;
                    }

                    var stock = val.stockalm;
                    if (val.nostock) {
                        stock = '-';
                    } else if (val.stockalm != val.stockfis) {
                        stock += ' <span title="stock general">(' + val.stockfis + ')</span>';
                    }

                    var descripcion = Base64.encode(val.descripcion);
                    var descripcion_visible = val.descripcion;
                    /*
                    if (val.codfamilia) {
                        descripcion_visible += ' <span class="label label-default" title="Familia: ' + val.codfamilia + '">'
                                + val.codfamilia + '</span>';
                    }
                    
                    if (val.codfabricante) {
                        descripcion_visible += ' <span class="label label-default" title="Fabricante: ' + val.codfabricante + '">'
                                + val.codfabricante + '</span>';
                    }
                    if (val.trazabilidad) {
                        descripcion_visible += ' &nbsp; <i class="fa fa-code-fork" aria-hidden="true" title="Trazabilidad activada"></i>';
                    }
                    */
                                        
                    if ((val.bloqueado || (val.stockalm < 1 && !val.controlstock) )) {
                        tr_aux = "<td class=\"danger mostrador\">";
                    } else if (val.stockfis < val.stockmin) {
                        tr_aux = "<td class=\"warning mostrador\">";
                    } else if (val.stockalm > 0) {
                        tr_aux = "<td class=\"success mostrador\">";
                    }
					
                    

                    if (val.sevende) {
                        var funcion = "add_articulo('" + val.referencia + "','" + descripcion + "','" + val.pvp + "','"
                                + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "','" + val.servicio + "','" + val.preciocombo + "','" + val.necesita_articulo + "')";

                        if (val.tipo) {
                            funcion = "add_articulo_" + val.tipo + "('" + val.referencia + "','" + descripcion + "','"
                                    + val.pvp + "','" + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "')";
                        }
						if(val.es_combo == 1){
							funcion = "traer_combos('"+val.referencia+"')";
						}
                    


                        items.push(tr_aux + "<div id='desflotante'><a href=\"#\" onclick=\"get_precios('" + val.referencia + "')\" title=\"más detalles\">\n\
                     <span class=\"glyphicon glyphicon-eye-open\"></span></a>\n\
                     &nbsp; <a href=\"#\" onclick=\"return " + funcion + "\">" + val.referencia + " - " + descripcion_visible + '</a> ' + "</div><p>\n\
                     <a href=\"#\" onclick=\"return " + funcion + "\" title=\"actualizado el " + val.factualizado
                                + "\">" + show_pvp_iva(val.pvp * (100 - val.dtopor) / 100, val.codimpuesto, val.coddivisa) + "</a>\n\
                     Stock : " + stock + " </p><a class='text-left' href=\"#\" onclick=\"return " + funcion + "\"></a> "+tdtr);
                    }

                    if (val.query == document.f_buscar_articulos.query.value) {
                        insertar = true;
                        fin_busqueda1 = true;
                    }
                    

                });

                if (items.length == 0 && !fin_busqueda1) {
                    items.push("<tr><td colspan=\"4\" class=\"warning\">Sin resultados. Usa la pestaña\n\
                              <b>Nuevo</b> para crear uno.</td></tr>");
                    document.f_nuevo_articulo.referencia.value = document.f_buscar_articulos.query.value;
                    insertar = true;
                }

                if (insertar) {
                    $("#search_results").html("<div class=\"table-responsive\"><table class=\"table table-hover\"><thead><tr>\n\
                  <th class=\"text-left\" >Artículo</th>\n\
                  <th class=\"text-left\" >Artículo</th>\n\
                  <th class=\"text-left\" >Artículo</th>\n\
                  </tr></thead>" + items.join('') + "</table></div>");
                    
                }
        });
    }
}

/*
function buscar_articulos()
{
    if (document.f_buscar_articulos.query.value == '') {
        $("#search_results").html('');
    } else if (tpv_url !== '') {
        document.f_buscar_articulos.codcliente.value = document.f_tpv.cliente.value;

        fin_busqueda1 = false;
        $.getJSON(tpv_url, $("form[name=f_buscar_articulos]").serialize(), function (json) {
            var items = [];
            var insertar = false;
            $.each(json, function (key, val) {
                var stock = val.stockalm;
                if (val.nostock) {
                    stock = '-';
                } else if (val.stockalm != val.stockfis) {
                    stock += ' (' + val.stockfis + ')';
                }

                var descripcion = Base64.encode(val.descripcion);
                var descripcion_visible = val.descripcion;
                if (val.codfamilia) {
                    descripcion_visible += ' <span class="label label-default" title="Familia: ' + val.codfamilia + '">'
                            + val.codfamilia + '</span>';
                }
                if (val.codfabricante) {
                    descripcion_visible += ' <span class="label label-default" title="Fabricante: ' + val.codfabricante + '">'
                            + val.codfabricante + '</span>';
                }
                if (val.trazabilidad) {
                    descripcion_visible += ' &nbsp; <i class="fa fa-code-fork" aria-hidden="true" title="Trazabilidad activada"></i>';
                }

                var tr_aux = '<tr>';
                if (val.bloqueado) {
                    tr_aux = "<tr class=\"danger\">";
                } else if (val.stockfis < val.stockmin) {
                    tr_aux = "<tr class=\"warning\">";
                } else if (val.stockalm > 0) {
                    tr_aux = "<tr class=\"success\">";
                }

                if (val.sevende) {
                    if (val.stockalm > 0 || val.controlstock) {
                        var funcion = "add_articulo('" + val.referencia + "','" + descripcion + "','" + val.pvp + "','"
                                + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "')";

                        if (val.tipo) {
                            funcion = "add_articulo_" + val.tipo + "('" + val.referencia + "','" + descripcion + "','"
                                    + val.pvp + "','" + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "')";
                        }
                    } else {
                        var funcion = "bootbox.alert({message: 'Sin stock.',title: '<b>Atención</b>'});";
                    }

                    items.push(tr_aux + "<td><a href=\"#\" onclick=\"get_precios('" + val.referencia + "')\" title=\"más detalles\">\n\
                  <span class=\"glyphicon glyphicon-eye-open\"></span></a>\n\
                  &nbsp; <a href=\"#\" onclick=\"" + funcion + "\">" + val.referencia + '</a> ' + descripcion_visible + "</td>\n\
                  <td class=\"text-right\"><a href=\"#\" onclick=\"" + funcion + "\" title=\"actualizado el " + val.factualizado
                            + "\">" + show_precio(val.pvp * (100 - val.dtopor) / 100) + "</a></td>\n\
                  <td class=\"text-right\"><a href=\"#\" onclick=\"" + funcion + "\" title=\"actualizado el " + val.factualizado
                            + "\">" + show_pvp_iva(val.pvp * (100 - val.dtopor) / 100, val.codimpuesto) + "</a></td>\n\
                  <td class=\"text-right\">" + stock + "</td></tr>");
                }

                if (val.query == document.f_buscar_articulos.query.value) {
                    insertar = true;
                    fin_busqueda1 = true;
                }
            });

            if (items.length == 0 && !fin_busqueda1) {
                items.push("<tr><td colspan=\"4\" class=\"warning\">Sin resultados.</td></tr>");
                insertar = true;
            }

            if (insertar) {
                $("#search_results").html("<div class=\"table-responsive\"><table class=\"table table-hover\"><thead><tr>\n\
               <th class=\"text-left\">Referencia + descripción</th>\n\
               <th class=\"text-right\" width=\"80\">Precio</th>\n\
               <th class=\"text-right\" width=\"80\">Precio+IVA</th>\n\
               <th class=\"text-right\" width=\"80\">Stock</th>\n\
               </tr></thead>" + items.join('') + "</table></div>");
            }
        });
    }
}

*/

function mostrarArticulos(fam){

    
    if (tpv_url !== '') {
        
            fin_busqueda1 = false;


            $.getJSON(tpv_url, "familia_mostrar=" +fam, function (json) {
                
                var items = [];
                var insertar = false;
                //alert(JSON.stringify(json));
                //PARA MOSTRAR LOS PRODUCTOS EN CUATRO COLUMNAS... se van colocando los productos en una fila, cuando se cumpla los cuatro, vuelve a 1 y así, hasta completar todos los productos

                var contadorColumnas = 0;
                var con = 0;
                var otroHtml = "";

                $.each(json, function (key, val) {

                    con++;
                    
                
                    contadorColumnas++;
                    var tdtr = "</td>";  
                    var tr_aux = '<td>';  

                    //ésta es la condición que devuelve el contador en 1
                    if(contadorColumnas == 15){
                        tdtr = "</td></tr>";
                        contadorColumnas = 0;
                    }

                    var stock = val.stockalm;

                    if (val.nostock) {
                        stock = '-';
                    } else if (val.stockalm != val.stockfis) {
                        stock += ' <span title="stock general">(' + val.stockfis + ')</span>';
                    }

                    var descripcion = Base64.encode(val.descripcion);
                    var descripcion_visible = val.descripcion;
                    /*
                    if (val.codfamilia) {
                        descripcion_visible += ' <span class="label label-default" title="Familia: ' + val.codfamilia + '">'
                                + val.codfamilia + '</span>';
                    }
                    
                    if (val.codfabricante) {
                        descripcion_visible += ' <span class="label label-default" title="Fabricante: ' + val.codfabricante + '">'
                                + val.codfabricante + '</span>';
                    }
                    if (val.trazabilidad) {
                        descripcion_visible += ' &nbsp; <i class="fa fa-code-fork" aria-hidden="true" title="Trazabilidad activada"></i>';
                    }
                    */
                   
                   
                                        
                    if ((val.bloqueado || (val.stockalm < 1 && !val.controlstock) )) {
                        tr_aux = "<td class=\"danger\">";
                    } else if (val.stockfis < val.stockmin) {
                        tr_aux = "<td class=\"warning\">";
                    } else if (val.stockalm > 0) {
                        tr_aux = "<td class=\"success\">";
                    }

                    

                    if (val.sevende) {
                        var funcion = "add_articulo('" + val.referencia + "','" + descripcion + "','" + val.pvp + "','"
                                + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "','" + val.servicio + "','" + val.preciocombo + "','" + val.necesita_articulo + "')";


                        var datoProducto = val.referencia + "," + descripcion + "," + val.pvp + ","
                                + val.dtopor + "," + val.codimpuesto + "," + val.cantidad;
                        if (val.tipo) {
                            funcion = "add_articulo_" + val.tipo + "('" + val.referencia + "','" + descripcion + "','"
                                    + val.pvp + "','" + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "')";
                            datoProducto = val.referencia + "," + descripcion + "," + val.pvp + ","
                                + val.dtopor + "," + val.codimpuesto + "," + val.cantidad;
                        }
						
						if(val.es_combo == 1){
							funcion = "traer_combos('"+val.referencia+"')";
						}
                    
                    
                        /**/
                        otroHtml += tr_aux + "<a id='pr"+val.referencia+"' value='"+datoProducto+"'  title='"+descripcion_visible+"' href=\"#\" onclick=\"return " + funcion + "\">"+ get_imagen(val.referencia) +"</a> "+"<p class='info'>"+ val.referencia.substr(0, 10) + "</p>"+tdtr;
                        items.push(tr_aux + "<a id='pr"+val.referencia+"' value='"+datoProducto+"'  title='"+descripcion_visible+"' href=\"#\" onclick=\"return " + funcion + "\">"+ get_imagen(val.referencia) +"</a> "+"<p class='info'>"+ val.referencia.substr(0, 10) + "</p>"+tdtr);

                    }

                   /* if (val.query == document.f_buscar_articulos.query.value) {
                        insertar = true;
                        fin_busqueda1 = true;
                    }
                    */

                });
/*
                if (items.length == 0 && !fin_busqueda1) {
                    alert("ENTRAS");
                    items.push("<tr><td colspan=\"4\" class=\"warning\">Sin resultados. Usa la pestaña\n\
                              <b>Nuevo</b> para crear uno.</td></tr>");
                    document.f_nuevo_articulo.referencia.value = document.f_buscar_articulos.query.value;
                    insertar = true;
                }*/


                //if (insertar) {
                    if(divFamilias == ""){
                        $("#articuloscinta").html("<table id='tablaCinta' class=\"table table-hover\" ><thead>\n\
                      </thead>" + items.join('') + "</table>");
                        divFamilias = "S";
                    }else{
                       $("#articuloscinta").html("<table id='tablaCinta' class=\"table table-hover\" ><thead>\n\
                      </thead>" + items.join('') + "</table>");
                    }
                //}
            });
        }
}

function mostrarArticulos1(fam){

    
    if (tpv_url !== '') {
        
            fin_busqueda1 = false;


            $.getJSON(tpv_url, "codcliente=" + document.f_tpv.cliente.value + "&codalmacen="+document.f_tpv.almacen.value+"&coddivisa=" + document.f_tpv.divisa.value + "&query=%20&codfamilia="+fam+"&codfabricante=", function (json) {
                
                var items = [];
                var insertar = false;
                
                //PARA MOSTRAR LOS PRODUCTOS EN CUATRO COLUMNAS... se van colocando los productos en una fila, cuando se cumpla los cuatro, vuelve a 1 y así, hasta completar todos los productos

                var contadorColumnas = 0;
                var con = 0;
                var otroHtml = "";

                $.each(json, function (key, val) {

                    con++;
                    
                
                    contadorColumnas++;
                    var tdtr = "</td>";  
                    var tr_aux = '<td>';  

                    //ésta es la condición que devuelve el contador en 1
                    if(contadorColumnas == 15){
                        tdtr = "</td></tr>";
                        contadorColumnas = 0;
                    }

                    var stock = val.stockalm;

                    if (val.nostock) {
                        stock = '-';
                    } else if (val.stockalm != val.stockfis) {
                        stock += ' <span title="stock general">(' + val.stockfis + ')</span>';
                    }

                    var descripcion = Base64.encode(val.descripcion);
                    var descripcion_visible = val.descripcion;
                    /*
                    if (val.codfamilia) {
                        descripcion_visible += ' <span class="label label-default" title="Familia: ' + val.codfamilia + '">'
                                + val.codfamilia + '</span>';
                    }
                    
                    if (val.codfabricante) {
                        descripcion_visible += ' <span class="label label-default" title="Fabricante: ' + val.codfabricante + '">'
                                + val.codfabricante + '</span>';
                    }
                    if (val.trazabilidad) {
                        descripcion_visible += ' &nbsp; <i class="fa fa-code-fork" aria-hidden="true" title="Trazabilidad activada"></i>';
                    }
                    */
                   
                   
                                        
                    if ((val.bloqueado || (val.stockalm < 1 && !val.controlstock) )) {
                        tr_aux = "<td class=\"danger\">";
                    } else if (val.stockfis < val.stockmin) {
                        tr_aux = "<td class=\"warning\">";
                    } else if (val.stockalm > 0) {
                        tr_aux = "<td class=\"success\">";
                    }

                    

                    if (val.sevende) {
                        var funcion = "add_articulo('" + val.referencia + "','" + descripcion + "','" + val.pvp + "','"
                                + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "','" + val.servicio + "','" + val.preciocombo + "','" + val.necesita_articulo + "')";


                        var datoProducto = val.referencia + "," + descripcion + "," + val.pvp + ","
                                + val.dtopor + "," + val.codimpuesto + "," + val.cantidad;
                        if (val.tipo) {
                            funcion = "add_articulo_" + val.tipo + "('" + val.referencia + "','" + descripcion + "','"
                                    + val.pvp + "','" + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "')";
                            datoProducto = val.referencia + "," + descripcion + "," + val.pvp + ","
                                + val.dtopor + "," + val.codimpuesto + "," + val.cantidad;
                        }
						
						if(val.es_combo == 1){
							funcion = "traer_combos('"+val.referencia+"')";
						}
                    
                    
                        /**/
                        otroHtml += tr_aux + "<a id='pr"+val.referencia+"' value='"+datoProducto+"'  title='"+descripcion_visible+"' href=\"#\" onclick=\"return " + funcion + "\">"+ get_imagen(val.referencia) +"</a> "+"<p class='info'>"+ val.referencia.substr(0, 10) + "</p>"+tdtr;
                        items.push(tr_aux + "<a id='pr"+val.referencia+"' value='"+datoProducto+"'  title='"+descripcion_visible+"' href=\"#\" onclick=\"return " + funcion + "\">"+ get_imagen(val.referencia) +"</a> "+"<p class='info'>"+ val.referencia.substr(0, 10) + "</p>"+tdtr);

                    }

                   /* if (val.query == document.f_buscar_articulos.query.value) {
                        insertar = true;
                        fin_busqueda1 = true;
                    }
                    */

                });
/*
                if (items.length == 0 && !fin_busqueda1) {
                    alert("ENTRAS");
                    items.push("<tr><td colspan=\"4\" class=\"warning\">Sin resultados. Usa la pestaña\n\
                              <b>Nuevo</b> para crear uno.</td></tr>");
                    document.f_nuevo_articulo.referencia.value = document.f_buscar_articulos.query.value;
                    insertar = true;
                }*/


                //if (insertar) {
                    if(divFamilias == ""){
                        $("#articuloscinta").html("<table id='tablaCinta' class=\"table table-hover\" ><thead>\n\
                      </thead>" + items.join('') + "</table>");
                        divFamilias = "S";
                    }else{
                       $("#articuloscinta").html("<table id='tablaCinta' class=\"table table-hover\" ><thead>\n\
                      </thead>" + items.join('') + "</table>");
                    }
                //}
            });
        }
}


function buscar_articulosManual(ref)
{
    
    document.f_buscar_articulos.query.value = ref;
    
    if (document.f_buscar_articulos.query.value === '') {
        $("#nav_articulos").show();
        $("#search_results").html('');
        $("#nuevo_articulo").hide();


        fin_busqueda1 = true;
        fin_busqueda2 = true;
    } else {
        $("#nav_articulos").show();



        if (tpv_url !== '') {
            fin_busqueda1 = false;
            $.getJSON(tpv_url, $("form[name=f_buscar_articulos]").serialize(), function (json) {

                var items = [];
                var insertar = false;
                //PARA MOSTRAR LOS PRODUCTOS EN CUATRO COLUMNAS... se van colocando los productos en una fila, cuando se cumpla los cuatro, vuelve a 1 y así, hasta completar todos los productos
                var contadorColumnas = 0;


                $.each(json, function (key, val) {
                    if(val.referencia == ref){
                        
                        var descripcion = Base64.encode(val.descripcion);
                        
                        //alert(val.referencia+","+ descripcion+","+ val.pvp+","+ val.dtopor+","+ val.codimpuesto+","+ val.cantidad)
                        add_articulo(val.referencia, descripcion, val.pvp, val.dtopor, val.codimpuesto, val.cantidad, val.servicio, val.preciocombo, val.necesita_articulo);
                    }
                });
               
            });
        }
    }
}

function buscar_codbarras()
{
    if (tpv_url !== '') {
        var url = tpv_url + '&codcliente=' + document.f_tpv.cliente.value + '&codalmacen=' + document.f_tpv.almacen.value
                + '&query=' + document.f_tpv.codbar.value;

        $.getJSON(url, function (json) {
            if (jQuery.isEmptyObject(json)) {
                bootbox.alert('Ningún artículo encontrado.');
            }
            $.each(json, function (key, val) {
                if (val.codbarras == document.f_tpv.codbar.value) {
                    if (val.sevende && (val.stockalm > 0 || val.controlstock)) {
                        var funcion = "add_articulo('" + val.referencia + "','" + Base64.encode(val.descripcion) + "','" + val.pvp + "','"
                                + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "')";

                        if (val.tipo) {
                            funcion = "add_articulo_" + val.tipo + "('" + val.referencia + "','" + Base64.encode(val.descripcion) + "','"
                                    + val.pvp + "','" + val.dtopor + "','" + val.codimpuesto + "','" + val.cantidad + "')";

                            $("#modal_articulos").modal('show');
                        }

                        eval(funcion);
                    } else if (val.sevende) {
                        bootbox.alert({
                            message: 'Sin stock.',
                            title: '<b>Atención</b>'
                        });
                    }
                }
            });

            document.f_tpv.codbar.value = '';
            document.f_tpv.codbar.focus();
        });
    }
}

function get_imagen(dato){
        var result = "<img src='"+ 'images/articulos/' + dato + '-1.jpg'+"' WIDTH='70' HEIGHT='70'>";
        //alert("--> "+dato);
       return result;
}

function traerfamilia(){

   $("#modal_articulos").show();

    var cantFamilia =  document.f_buscar_articulos.codfamilia.length;
    var htmlFam = "<div id='familiaCinta' class='collapse navbar-collapse'><ul class='nav navbar-nav'>";

        for(i=2; i<cantFamilia;i++){
            var bus =  "'"+document.f_buscar_articulos.codfamilia[i].value+"'";   
            htmlFam += "<li><a href='#' onclick=\"mostrarArticulos("+bus+")\">"+document.f_buscar_articulos.codfamilia[i].value+"</a></li>";
        }
        
    htmlFam += "</ul><div>";
    $("#familiasManual").html(htmlFam);    
    $("#modal_articulos").hide();
    
    return document.f_buscar_articulos.codfamilia[2].value;
    
}

function show_pvp_iva(pvp, codimpuesto)
{
    var iva = 0;
    if (cliente.regimeniva != 'Exento' && !siniva) {
        for (var i = 0; i < all_impuestos.length; i++) {
            if (all_impuestos[i].codimpuesto == codimpuesto) {
                iva = all_impuestos[i].iva;
                break;
            }
        }
    }

    return show_precio(pvp + pvp * iva / 100);
}


function botonfactura(){
    //console.log("--------------->"+$("#cliente_existe").val());
    const modal_save = document.querySelector('#modal_guardar');
    if(modal_save){
        const foot = modal_save.querySelector('.modal-footer');
        if(foot)
            foot.classList.remove('hidden_modal');
        const tab_data = modal_save.querySelector('a[aria-controls="tab_pago"]');
        if(tab_data)
            tab_data.click();
    }

    const data_client = document.querySelector('#tab_cliente');
    const err = data_client.querySelector('.msg-data');
    if(err){
        if(err.classList.contains('msg-data--green'))
            err.classList.remove('msg-data--green')
        err.innerHTML = "";
    }
    

    var totalito = $("#tpv_total3").val();
        if($("#cliente_existe").val()>0){
            
            getClient($("#ac_cliente").val());
            
            if(parseFloat(totalito)>=0){
                if(verificar_combo_lineas()){
                    $("#modal_guardar").modal('show');
                    document.f_tpv.tpv_efectivo.focus();
                }
                else{
                    bootbox.alert("Al haber seleccionado un COMBO, debe haber mínimo 2 artículos")
                }
            }
            else{
                bootbox.alert("No hay productos seleccionados")
            }
        }else{
            bootbox.alert("No hay un cliente seleccionado")
        }
}

//obtiene la direccion IP:
 

    function obtenerHora(){
        var f = new Date();
        var h = f.getHours();
        var m = f.getMinutes();
        var s = f.getSeconds();
        if(h<10)
            h="0"+h;
        if(m<10)
            m="0"+m;
        if(s<10)
            s="0"+s;

        return h+":"+m+":"+s;
    }

    function cierreCaja(idEmpleado, fecha, valor, idTermi)
    {
        fecha = fecha.substr(6, 4) +"-"+ fecha.substr(3, 2) +"-"+ fecha.substr(0, 2);
        var cad = obtenerHora();
        //alert(idEmpleado + " / "+ fecha + " / " + valor + " / " + idTermi );
       $.ajax({
               url     : 'cierrecaja.php',
               type    : 'POST',
               dataType: 'json',
               data    : "idEmpleado=" + idEmpleado +"&peticion=" + "cierrecaja" +"&fecha=" + fecha +"&valor=" + valor +"&idTermi=" + idTermi + "&hora=" + cad 
           })
           .done(function(data){
               if(data.cierrecaja)
               {
                   //alert("Resultado cierre caja "+data.cierrecaja);
                   console.log("Total crédito "+data.sumacredito);
                   console.log("Total Asiento "+data.asientos);
                   console.log("Consulta Asiento "+data.consultaasiento);
                   console.log("Consulta crédito "+data.consultacredito);

                   //alert("No se pudo tener precio de la balanza");
               }
           });

           
   }


function formatNumber (n) {
    n = String(n).replace(/\D/g, "");
  return n === '' ? n : Number(n).toLocaleString();
}


function revisar_proveedores(datico){
    resultadi = true;
    for(i=0; i< numlineas ; i++){
        if($("#prov_"+i).val() == datico)
            resultadi = false;
    }
    
    return resultadi;
    
}


//función para crear el cliente en tpv sin ir al formulario para hacerlo
//solamente escribirá el cliente y dará clic
function crear_cliente_tpv(dato, more_data = null)
{
    
    dato = dato.toUpperCase();
    $("#ac_cliente").val(dato);
    
    var resultado = false;
    if (tpv_url !== '') {
        let concat = "";
        if(more_data != null){
            if(more_data.nombre2)
                concat += "&nombre2="+more_data.nombre2;
            if(more_data.tipo_cifnif)
                concat += "&tipo_cifnif="+more_data.tipo_cifnif;
            if(more_data.cifnif)
                concat += "&cifnif="+more_data.cifnif;
            if(more_data.telefono1)
                concat += "&telefono1="+more_data.telefono1;
            if(more_data.email)
                concat += "&email="+more_data.email;
            if(more_data.razonsocial)
                concat += "&razonsocial="+more_data.razonsocial;
                
        }
        if(document.f_tpv.cliente_existe.value == 1){
            concat += "&cod="+document.f_tpv.cliente.value;
        }

        $.getJSON(tpv_url+concat, 'crear_cliente=' + dato, function (json) {

            if(json.exito)
            {
                cliente = JSON.stringify(json.cod);
                document.f_tpv.cliente.value = json.cod.codcliente;
                document.f_tpv.nombrecliente.value = dato;
                document.f_tpv.cifnif.value = json.cod.cifnif;
                document.f_tpv.cliente_existe.value = 1;
                $("#bot_nuevo_cliente").hide();
                resultado = true;

                const modal_save = document.querySelector('#modal_guardar');
                const foot = modal_save.querySelector('.modal-footer');
                if(foot){
                    foot.classList.remove('hidden_modal');
                    const tab_data = modal_save.querySelector('a[aria-controls="tab_pago"]');
                    if(tab_data)
                        tab_data.click();
                }

                const data_client = document.querySelector('#tab_cliente');
                const err = data_client.querySelector('.msg-data');
                err.classList.add('msg-data--green')
                err.innerHTML = "Datos guardados con éxito";
                setTimeout(() => {
                    err.innerHTML = "";
                }, 3000);
                
            }
            
        });
    }
    
    return resultado;
}


//esta función va a colocar en cada campo del proveedor de servicio el que corresponde
function llenar_personas_servicios(datico)
{
	for(i=0; i < arreglo_lineas_servicios.length; i++ )
	{
		//console.log("COMBO ACTIVO "+combo_activo);
		$("#prov_"+arreglo_lineas_servicios[i]).val(persona_servicio);
	}
}



//con ésta función se trae las líneas de facturas clientes
//para editarlas
function traer_lineas_editar(id_fact){
    
    
    
    if (tpv_url !== '') {
        //alert(tpv_url+'editando_lineas=' + id_fact);
        $.getJSON(tpv_url, 'editando_lineas=' + id_fact, function (json) {
            
            if(json)
            {
                $("#numlineas").val(json.length);

                for(i=0 ; i < json.length ; i++){

                    //alert(json[i].fecha);
                    numlineas = i + 1;
                    ref = json[i].referencia;
                    codcombinacion = json[i].codcombinacion;
                    iva = json[i].iva;
                    recargo = json[i].recargo;
                    irpf = json[i].irpf;
                    desc = json[i].descripcion;
                    per_temp = json[i].proveedor_lav;
                    cantidad = json[i].cantidad;
                    pvp = json[i].pvpunitario;
                    dto = 0;
                    
                    
                    if(desc.indexOf("_COMBO") != -1){
                        combo_activo = true;
                        bot_add_combo = true;
                        $("#addCombo").show();
                    }
                    
                    $("#lineas_doc").prepend("<tr id=\"linea_" + numlineas + "\">\n\
                         <td><input type=\"hidden\" name=\"referencia_" + numlineas + "\" value=\"" + ref + "\"/>\n\
                            <input type=\"hidden\" name=\"codcombinacion_" + numlineas + "\" value=\"" + codcombinacion + "\"/>\n\
                            <input type=\"hidden\" id=\"iva_" + numlineas + "\" name=\"iva_" + numlineas + "\" value=\"" + iva + "\"/>\n\
                            <input type=\"hidden\" id=\"recargo_" + numlineas + "\" name=\"recargo_" + numlineas + "\" value=\"" + recargo + "\"/>\n\
                            <input type=\"hidden\" id=\"irpf_" + numlineas + "\" name=\"irpf_" + numlineas + "\" value=\"" + irpf + "\"/>\n\
                            <div class=\"form-control\"><a target=\"_blank\" href=\"index.php?page=ventas_articulo&ref=" + ref + "\">" + ref + "</a></div></td>\n\
                         <td><textarea class=\"form-control\" id=\"desc_" + numlineas + "\" name=\"desc_" + numlineas + "\" rows=\"1\">" + desc + "</textarea></td>\n\
                          <td><input type=\"text\"  id=\"prov_" + numlineas + "\" class=\"form-control text-left\" name=\"prov_" + numlineas +
                            "\" readonly autocomplete=\"off\" value=\"" + per_temp	 + "\"/></td>\n\
                          <td><input type=\"" + 'text' + "\" step=\"any\" id=\"cantidad_" + numlineas + "\" class=\"form-control text-right\" name=\"cantidad_" + numlineas +
                            "\" onchange=\"recalcular()\" onkeyup=\"recalcular()\" autocomplete=\"off\" value=\"" + cantidad + "\"/></td>\n\
                         <td><button class=\"btn btn-sm btn-danger\" type=\"button\" onclick=\"$('#linea_" + numlineas + "').remove();recalcular();\">\n\
                            <span class=\"glyphicon glyphicon-trash\"></span></button></td>\n\
                         <td><input type=\"text\" class=\"form-control text-right\" id=\"pvp_" + numlineas + "\" name=\"pvp_" + numlineas + "\" value=\"" + pvp +
                            "\" onkeyup=\"recalcular()\" onclick=\"this.select()\" autocomplete=\"off\"/></td>\n\
                         <td><input type=\"text\" id=\"dto_" + numlineas + "\" name=\"dto_" + numlineas + "\" value=\"" + dto +
                            "\" class=\"form-control text-right\" onkeyup=\"recalcular()\" onchange=\"recalcular()\" onclick=\"this.select()\" autocomplete=\"off\"/></td>\n\
                         <td><input type=\"text\" class=\"form-control text-right\" id=\"neto_" + numlineas + "\" name=\"neto_" + numlineas +
                            "\" readonly/></td>\n\
                         <td class=\"text-right\"><div class=\"form-control\">" + iva + "</div></td>\n\
                         <td class=\"text-right recargo\"><div class=\"form-control\">" + recargo + "</div></td>\n\
                         <td class=\"text-right irpf\"><div class=\"form-control\">" + irpf + "</div></td>\n\
                         <td class=\"warning\" title=\"Cálculo aproximado del total de la linea\">\n\
                            <input type=\"text\" class=\"form-control text-right\" id=\"total_" + numlineas + "\" name=\"total_" + numlineas +
                            "\" onchange=\"ajustar_total(" + numlineas + ")\" onclick=\"this.select()\" autocomplete=\"off\"/></td></tr>");
                    recalcular();
               }
               
               persona_servicio = json[0].proveedor_servicio;
               $("#ac_cliente").val(json[0].nombrecliente);
               $("#ac_cliente").select();
               fechatemporal = json[0].fecha .substr(8, 2) +"-"+ json[0].fecha .substr(5, 2) +"-"+ json[0].fecha .substr(0, 4);
               $("#fecha").val(fechatemporal);
               
               
               
               
              
            }
            
        });
    }
    
}
    

function verificar_cliente(){
    
    var estado = 0;
    var dato_escogido ="";
    var buscar = $("#ac_cliente").val();
    
    if(buscar.length>4){
            $("#bot_nuevo_cliente").show();
            document.f_tpv.cliente_existe.value = 0;
            
                for(i=0 ; i < clientes_db.length ; i++){
                        
                        //if(clientes_db[i].nombre.indexOf(buscar.toUpperCase()) != -1){
                        if(clientes_db[i].nombre.toUpperCase() == buscar.toUpperCase()){
                           estado = 1;
                           dato_escogido = clientes_db[i].nombre;
                           //console.log(clientes_db[i].nombre+"---> "+clientes_db[i].nombre.indexOf(buscar.toUpperCase()));
                        }

                }
                
                //alert("ESTADO "+estado);
                if(estado == 1){
                    $("#bot_nuevo_cliente").hide();
                    document.f_tpv.cliente_existe.value = 1;
                }
    }
                    
}

function cerrar_caja()
   {
       
       bootbox.prompt("Escriba el dinero que hay en caja", function(result){ 
            if (result) {
                if((result * 0) == 0){
                    if(result > 0){
                        window.location.href = tpv_url + "&cerrar_caja=TRUE&dinero_caja="+result;
                    }
                }
            }
        });
       
      /*bootbox.confirm({
         message: '¿Realmente desea cerrar la caja ?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = tpv_url + "&cerrar_caja=TRUE";
            }
         }
      });*/
   }


$(document).ready(function () {
    
    
    
    $("#modal_personas").hide();
    
    //base de datos de clientes
        cliente_db();
    
    //SI SE QUIERE EDITAR UNA DE LAS FACTURAS
    if($("#factura_editar").val() != -1 ){
        traer_lineas_editar($("#factura_editar").val());
    }
    

    $("#addCombo").hide();
    $("#bot_nuevo_cliente").hide();
    

   /*  getIPs(function(ip){
            ipUsuario = ip;
            //console.log("saludos hermandad :D !");
        });
*/

    var familiaInicial = traerfamilia();

    //Artículos en la  cinta inicial
    //mostrarArticulos("0");
    

    $("#b_codbar").keypress(function (e) {
        if (e.which == 13) {
            e.preventDefault();

            if (document.f_tpv.codbar.value == '') {
                $("#modal_guardar").modal('show');
                document.f_tpv.tpv_efectivo.focus();
            } else {
                buscar_codbarras();
            }
        }
    });
    $("#b_reticket").click(function () {
        window.location.href = tpv_url + "&reticket=" + prompt('Introduce el código del ticket (o déjalo en blanco para re-imprimir el último):');
    });
    $("#b_cerrar_caja").click(function () {

        //SOLICITO AL CLIENTE EL VALOR QUE TIENE EN CAJA MANUALMENTE
       /* var totalCierreCajaManual = prompt("Coloque el total de dinero en  caja", "");
        if(totalCierreCajaManual>0){
                cierreCaja($("#empleado").val(), $("#fecha").val(), totalCierreCajaManual, $("#idTerminal").val());

                
               setTimeout(function() {

                    window.location.href = tpv_url + "&cerrar_caja=TRUE";
                     
                  }, 500);

        }*/
    });


    $("#referenciaManual").keydown(function (event) {
        //event.preventDefault();

        var cod =event.which; 
            
           if(cod == 13){
               var palabra = $(this).val();
               palabra = palabra.toUpperCase();
               if(palabra.indexOf("COMBO") != -1){
                   traer_combos(palabra);
                   
               }
               else
                  buscar_articulosManual($(this).val());

           }
    });
	
	/*$("#balanza").click(function (event) {
		permitirPeso(1);
            $("#referenciaManual").select();
		
        });*/


    $(document).keydown(function(e){
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code == 120) {
            botonfactura();
      }
      /*if(code == 20) {
            permitirPeso(1);
            $("#referenciaManual").select();
      }*/
    });

    $("#i_new_line").click(function () {
        $("#i_new_line").val("");
        $("#modal_articulos").modal('show');
        document.f_buscar_articulos.query.select();
        document.f_buscar_articulos.query.value = " ";
        buscar_articulos();
    });

    $("#i_new_line").keyup(function () {
        document.f_buscar_articulos.query.value = $("#i_new_line").val();
        $("#i_new_line").val('');
        buscar_articulos();
        $("#modal_articulos").modal('show');
        document.f_buscar_articulos.query.focus();
    });

    $("#f_buscar_articulos").keyup(function () {
        buscar_articulos();
    });

    $("#f_buscar_articulos").submit(function (event) {
        event.preventDefault();
        buscar_articulos();
    });

    $("#b_tpv_guardar").click(function () {
        
        
            botonfactura();
        
        //$("#modal_guardar").modal('show');
        //document.f_tpv.tpv_efectivo.focus();
    });
	
    $("#siImprimir").click(function () {
		if(!$(this).prop('checked')) {
			$("#num_tickets").val("0");
		}
		else
			$("#num_tickets").val("1");
    });
    
    
    
    $("#addCombo").click(function () {
        
	if(combo_activo){
            combo_activo = false;
            $(this).removeClass("btn-info");
            $(this).addClass("btn-default");
        }
        else{
            combo_activo = true;
            $(this).removeClass("btn-default");
            $(this).addClass("btn-info");
        }
            
    });
    
    $("#bot_nuevo_cliente").click(function () {
        if($("#ac_cliente").val().length > 2){
            $("#modal_guardar").modal('show');
            $("#ac_cliente").val($("#ac_cliente").val().toUpperCase());
            getClient($("#ac_cliente").val());
            document.f_tpv.tpv_efectivo.focus();
            //crear_cliente_tpv($("#ac_cliente").val());
        }
                
    });
    
    //Si el cliente no existe, se muestra el botón bot_nuevo_cliente para crearlo
    $("#ac_cliente").keyup(function (e) {
        let inputText = $(this).val(); // Obtener el valor del input
        let alphanumericRegex = /^[a-zA-Z0-9]*$/;
        if (!alphanumericRegex.test(inputText)) {
            // Si el valor no coincide con el patrón alfanumérico, eliminar los caracteres no permitidos
            $(this).val(inputText.replace(/[^a-zA-Z0-9]/g, ''));
        }
        verificar_cliente();
    });
    
    $("#ac_cliente").change(function (e) {
        
                verificar_cliente();
    });
    


		//se coloca una variable para saber si la ventana personas está abierta al incio o en TPV
    
    
		$("#cerrar_personas").click(function () {
			if(ventana_personas_tpv){
				$('#linea_'+numlineas).remove();
				recalcular();
				$("#modal_personas").hide();
				//combo_activo = false;
			}
		});
		
		
		$("#agregar_persona").click(function () {
                    if($("#persona1").val() != ""){
			if(ventana_personas_tpv){
					$("#modal_personas").hide();
                                        if(necesita_articulo > 0){
                                            $("#prov_"+numlineas).val($("#persona1").val());
                                        }else{
                                        persona_servicio = $("#persona1").val();
					llenar_personas_servicios($("#persona1").val());
                                        }
//					$("#prov_"+numlineas).val($("#persona1").val());
					
				
				//	combo_activo = false;
			}
                    }
		});
	




    $("#tpv_efectivo1").keypress(function (e) {
        if (e.which == 13) {
            e.preventDefault();
            document.f_tpv.submit();
        }
    });

    $("#tpv_efectivo1").keyup(function (e) {
           $("#tpv_efectivo").val($(this).val().replace(".",""));
        $(this).val(formatNumber($(this).val()));
        
        $("#tpv_cambio").val(number_format(Math.round(parseFloat($("#tpv_efectivo").val()) - parseFloat($("#tpv_total2").val())), 2, '.', ''));
    });


    $("#tpv_efectivo").keyup(function (e) {
        $("#tpv_cambio").val(number_format(Math.round(parseFloat($("#tpv_efectivo").val()) - parseFloat($("#tpv_total2").val())), 2, '.', ''));
    });
});
