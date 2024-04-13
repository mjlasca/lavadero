/*
 * This file is part of FacturaScripts
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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
var ipUsuario = "";

var empleados_adicionados = 0;

var cont_adiciones = 1;
var cont_aspiradas = 1;

var personas_adicion = "";

var adminCerrado = false;

var input_number = "number";
if (navigator.userAgent.toLowerCase().indexOf("firefox") > -1) {
    input_number = "text";
}

function fs_round(value, precision)
{
    var m, f, isHalf, sgn;
    precision |= 0;
    m = Math.pow(10, precision);
    value *= m;
    sgn = (value > 0) | -(value < 0);
    isHalf = value % 1 === 0.5 * sgn;
    f = Math.floor(value);

    if (isHalf) {
        value = f + (sgn > 0);
    }

    return (isHalf ? value : Math.round(value)) / m;
}

function number_format(number, decimals, dec_point, thousands_sep)
{
    var n = number, c = isNaN(decimals = Math.abs(decimals)) ? 2 : decimals;
    var d = (dec_point == undefined) ? "," : dec_point;
    var t = (thousands_sep == undefined) ? "." : thousands_sep, s = n < 0 ? "-" : "";
    var i = parseInt(n = Math.abs(+n || 0).toFixed(c)) + "", j = (j = i.length) > 3 ? j % 3 : 0;
    return s + (j ? i.substr(0, j) + t : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + t) + (c ? d + Math.abs(n - i).toFixed(c).slice(2) : "");
}

var Base64 = {
    // private property
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",

    // public method for encoding
    encode: function (input) {
        var output = "";
        var chr1, chr2, chr3, enc1, enc2, enc3, enc4;
        var i = 0;

        input = Base64._utf8_encode(input);

        while (i < input.length) {

            chr1 = input.charCodeAt(i++);
            chr2 = input.charCodeAt(i++);
            chr3 = input.charCodeAt(i++);

            enc1 = chr1 >> 2;
            enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
            enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
            enc4 = chr3 & 63;

            if (isNaN(chr2)) {
                enc3 = enc4 = 64;
            } else if (isNaN(chr3)) {
                enc4 = 64;
            }

            output = output +
                    this._keyStr.charAt(enc1) + this._keyStr.charAt(enc2) +
                    this._keyStr.charAt(enc3) + this._keyStr.charAt(enc4);

        }

        return output;
    },

    // public method for decoding
    decode: function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;

        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

        while (i < input.length) {

            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));

            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;

            output = output + String.fromCharCode(chr1);

            if (enc3 != 64) {
                output = output + String.fromCharCode(chr2);
            }
            if (enc4 != 64) {
                output = output + String.fromCharCode(chr3);
            }

        }

        output = Base64._utf8_decode(output);

        return output;

    },

    // private method for UTF-8 encoding
    _utf8_encode: function (string) {
        string = string.replace(/\r\n/g, "\n");
        var utftext = "";

        for (var n = 0; n < string.length; n++) {

            var c = string.charCodeAt(n);

            if (c < 128) {
                utftext += String.fromCharCode(c);
            } else if ((c > 127) && (c < 2048)) {
                utftext += String.fromCharCode((c >> 6) | 192);
                utftext += String.fromCharCode((c & 63) | 128);
            } else {
                utftext += String.fromCharCode((c >> 12) | 224);
                utftext += String.fromCharCode(((c >> 6) & 63) | 128);
                utftext += String.fromCharCode((c & 63) | 128);
            }

        }

        return utftext;
    },

    // private method for UTF-8 decoding
    _utf8_decode: function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while (i < utftext.length) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
    }

}





function adicionando(dato){
    
    if(revisar_ingresos(dato)){
    personas_adicion = "<div class='form-group' id='frag"+cont_adiciones+"'> \n\
                        <input type='text' class='form-control'  name='ad"+cont_adiciones+"'   id='ad"+cont_adiciones+"'  value='"+dato+"' readonly='readonly' ><a href='#' class='glyphicon glyphicon-trash' onClick='return borrar_control("+cont_adiciones+", 1)'></a>\n\
                         </div>\n\
                        ";
        cont_adiciones++;
    
        $("#espacio_add").append(personas_adicion);
        var cant_jefes = parseInt($("#jefes_agregados").val());
        $("#jefes_agregados").val(cant_jefes + 1);
        
    }
}


function adicionandoAsp(dato){
    
    if(revisar_ingresos(dato)){
    personas_adicion = "<div class='form-group' id='aspf"+cont_aspiradas+"'> \n\
                        <input type='text' class='form-control'  name='asp"+cont_aspiradas+"'   id='asp"+cont_aspiradas+"'  value='"+dato+"' readonly='readonly' ><a href='#' class='glyphicon glyphicon-trash' onClick='return borrar_control("+cont_aspiradas+",  2 )'></a>\n\
                         </div>\n\
                        ";
        cont_aspiradas++;
        $("#aspirado_add").append(personas_adicion);
    }
}

function revisar_ingresos(dato)
{
    result = true;
    for(i=1; i <= 5; i++){
        //console.log(" DATO "+dato+" AD "+i+ " --- > "+$("#ad"+i).val());
        if(dato==$("#ad"+i).val() || dato==$("#asp"+i).val())
            result = false;
    }
    return result;
}

function empleados_mas()
{
    //alert($("#bandera_lav").val());
    /*console.log("BANDERA "+$("#bandera_lav").val()+ " ///  "+adminCerrado);
    if($("#bandera_lav").val() == 0){
        $("#modal_personas").show();
        if($("#adminAbierto").val() == ""){
            
            //adicionando($("#adminAbierto").val());
            //$("#bandera_lav").val(1);
            
        }else{
            adminCerrado = true;
        }
    }*/
}





function actualizar_personas()
{
    //alert("AJKA" + ipUsuario);
    $.ajax({
            url     : '#',
            type    : 'POST',
            dataType: 'json',
            data    : "ipUsuario=" + ipUsuario +"&usuario=" + $("#user_lav").val()
        })
        .done(function(data){
            alert("ENTRA");
        });
}


/*
 * Esta función sirve para las extensiones tipo modal.
 */
function fs_modal(txt, url)
{
    $("#modal_iframe h4.modal-title").html(Base64.decode(txt));
    $("#modal_iframe iframe").attr("src", url);
    $("#modal_iframe").modal("show");
}





    

function borrar_control(dato, pref){
    if(pref == 1){
            $("#frag"+dato).remove();
            var cant_jefes = parseInt($("#jefes_agregados").val());
            $("#jefes_agregados").val(cant_jefes - 1);
        }
    if(pref == 2)
            $("#aspf"+dato).remove();
}


function cerrar_ventana(){
            $("#form_personas").submit();
            //$("#modal_personas").hide();
}

$(document).ready(function () {
    
    $("#modal_personas").show();
    
    
    
    $("#agregar_persona").click(function (event) {
        if(cont_adiciones < 6 && $("#persona1").val()!="" ){
            adicionando($("#persona1").val());
        }
    });
    
    $("#agregar_aspira").click(function (event) {
        if(cont_aspiradas < 6 && $("#persona2").val()!="" ){
            adicionandoAsp($("#persona2").val());
        }
    });
    
    
    
    
    

    

    $(".datepicker").datepicker();
    $("#b_feedback").click(function (event) {
        event.preventDefault();
        $("#modal_feedback").modal("show");
        document.f_feedback.feedback_text.focus();
    });
    $(".clickableRow").mousedown(function (event) {
        if (event.which === 1) {
            var href = $(this).attr("href");
            var target = $(this).attr("target");
            if (typeof href !== typeof undefined && href !== false) {
                if (typeof target !== typeof undefined && target === "_blank") {
                    window.open($(this).attr("href"));
                } else {
                    parent.document.location = $(this).attr("href");
                }
            }
        }
    });
    $(".cancel_clickable").mousedown(function (event) {
        event.preventDefault();
        event.stopPropagation();
    });



    
});
