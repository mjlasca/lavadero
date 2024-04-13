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

var abono_temp = 0;



function number_format(amount, decimals) {

    amount += ''; // por si pasan un numero en vez de un string
    amount = parseFloat(amount.replace(/[^0-9\.]/g, '')); // elimino cualquier cosa que no sea numero o punto

    decimals = decimals || 0; // por si la variable no fue fue pasada

    // si no es un numero o es igual a cero retorno el mismo cero
    if (isNaN(amount) || amount === 0) 
        return parseFloat(0).toFixed(decimals);

    // si es mayor o menor que cero retorno el valor formateado como numero
    amount = '' + amount.toFixed(decimals);

    var amount_parts = amount.split('.'),
        regexp = /(\d+)(\d{3})/;

    while (regexp.test(amount_parts[0]))
        amount_parts[0] = amount_parts[0].replace(regexp, '$1' + ',' + '$2');

    return amount_parts.join('.');
}


function enviar_pestana(id){
    $("#nav_activo").val(id);
    $("#form_informe").submit();
}

function  cambiar_val_liquidacion(){
    
    bootbox.confirm("¿Está seguro de hacer los cambios?", function(result){ 
        if(result){
            $("#cambiar_valores_liquidacion").val(1);
            $("#form_informe").submit();
        }else{
            $("#cambiar_valores_liquidacion").val("");
        }
    });
}




$(document).ready(function () {
    /**
     * funciones para mostrar cuando la página esté lista
     */

    

    $("#abono_deducciones").change(function (event) {
        event.preventDefault();
        
        var abono =  0;
        $('#abono_deducciones option:selected').each(function() { 
            abono +=  parseFloat ($(this).text());
        }); 
       
        
        var comision = parseFloat($("#comision_imp").val());

            $("#comision_imp_aux").val(comision - abono);
            $("#total_imp").val(comision - abono);
            var total = number_format((comision - abono), 2);
            $("#mostrar_total").text("$"+total);
            

    });
  
    
 
});
