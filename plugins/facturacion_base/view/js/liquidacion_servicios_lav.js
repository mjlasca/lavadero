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

var tpv_url = '';
var val_fijo = 0;
var porcentaje = 0;




//función para crear el cliente en tpv sin ir al formulario para hacerlo
//solamente escribirá el cliente y dará clic
function actualizar_liquidacion(ref)
{

    if(revisar_valores(ref))
    {
        if (tpv_url !== '') {
            $.getJSON(tpv_url, '&ref=' + ref +'&val_fijo1='+ $("#val_fijo1_"+ref).val() +'&porcentaje1='+ $("#porcentaje1_"+ref).val() +'&val_fijo2='+ $("#val_fijo2_"+ref).val() +'&porcentaje2='+ $("#porcentaje2_"+ref).val() +'&val_fijo3='+ $("#val_fijo3_"+ref).val() +'&porcentaje3='+ $("#porcentaje3_"+ref).val() , function (json) {
                    if(json){
                        bootbox.alert("Datos guardados correctamente");
                        console.log(json);
                    }
            });
        }
    }
}




function revisar_valores(ref)
{
    var sumaErrores = 0;
    for(i=1; i<4 ; i++){
        resultadin = false;
        resultadin1 = false;
        resul = false;

        console.log("#porcentaje"+i+"_"+ref+" // "+$("#porcentaje"+i+"_"+ref).val());
        porcentaje = $("#porcentaje"+i+"_"+ref).val().replace(",", ".");
        
        
        if(porcentaje >= 0 && porcentaje <= 100 && porcentaje!="")
            resultadin = true;


        val_fijo = $("#val_fijo"+i+"_"+ref).val().replace(",", ".");

        if(val_fijo != "" && val_fijo >= 0)
            resultadin1 = true;

        if(!resultadin || !resultadin1){
            //alert("Por favor, revise los datos ingresados");
            sumaErrores++;
            if(!resultadin)
                $("#porcentaje"+i+"_"+ref).addClass("validandoCajas");
            else
                $("#porcentaje"+i+"_"+ref).removeClass("validandoCajas");
            if(!resultadin1)
                $("#val_fijo"+i+"_"+ref).addClass("validandoCajas");
            else
                $("#val_fijo"+i+"_"+ref).removeClass("validandoCajas");
        }
        else{
            resul = true;
            $("#val_fijo"+i+"_"+ref).removeClass("validandoCajas");
            $("#porcentaje"+i+"_"+ref).removeClass("validandoCajas");
        }
    }
    
    if(sumaErrores>0){
        bootbox.alert("Por favor, revise los datos ingresados");
        resul = false;
    }

    return resul;
   
}



function confirmarMasivo(fam, porcent, tipo){
    bootbox.confirm({
            message: 'Éste proceso cambiará de forma masiva los valores en campos de los artículos ¿Está seguro(a)?',
            title: '<b>Atención</b>',
            callback: function(result) {
               if (result) {
                   $.getJSON(tpv_url, 'fam=' + fam + '&porcentaje=' + porcent + '&tipo=' + tipo , function (json) {
                            console.log(json);
                    });
               }
            }
         });
}





$(document).ready(function () {
    /**
     * funciones para mostrar cuando la página esté lista
     */

    $(".bot_guardar").click(function (event) {
        actualizar_liquidacion($(this).val());
    });
    
    $(".table tbody tr").keypress(function (e) {
        if (e.which == 13) {
            e.preventDefault();
            actualizar_liquidacion($(this).attr("id"));
        }
    });
    
    $("#form_masivo_vta").submit(function (event) {
        event.preventDefault();
        confirmarMasivo($("#familia_masivo1").val(), $("#masivo_precio").val(), 1);
    });
    
    $("#form_masivo_com").submit(function (event) {
        event.preventDefault();
        confirmarMasivo($("#familia_masivo2").val(), $("#masivo_comision").val(), 2);
    });
    
 
});
