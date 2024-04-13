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

var numlineas = 0;
var num_cuadros = 1;
var num_cuadros2 = 100;

function validar_vacio(){
    //if($("#cod_servicio").val()=="")
        
}


function generar_combo_servicio()
{
    if($("#ad1").val()!=0){
        if(revisar_ingresos($("#ad1").val(), "ad", num_cuadros)){
        num_cuadros++; 
            var combo = "<div class='col-sm-2' id='frag"+num_cuadros+"' >\n\
                        <input type='text' class='form-control'  name='ad"+num_cuadros+"'   id='ad"+num_cuadros+"'  value='"+$("#ad1").val()+"' readonly='readonly' ><a href='#' class='glyphicon glyphicon-trash' onClick='return borrar_control("+num_cuadros+")'></a>\n\
                        </div>";

            $("#articulos_adicion").append(combo);
        }
        else{
            alert("Este servicio ya está en el combo");
        }
    }
        
    
}



function borrar_control(dato){
    if(dato > 1){
            $("#frag"+dato).remove();
    }
}

function generar_combo_articulos()
{
    if($("#art1").val()!=0){
        if(revisar_ingresos($("#art1").val(), "ad", num_cuadros)){
        num_cuadros++; 
            var combo = "<div class='col-sm-2' id='frag"+num_cuadros+"' >\n\
                        <input type='text' class='form-control' name='ad"+num_cuadros+"'  id='ad"+num_cuadros+"'  value='"+$("#art1").val()+"' readonly='readonly' > <a href='#' class='glyphicon glyphicon-trash' onClick='return borrar_control("+num_cuadros+")'></a>\n\
                        </div>";
           $("#articulos_adicion").append(combo);
           
        }
        else{
            alert("Este artículo ya está en el combo");
        }
    }
        
    
}

function revisar_ingresos(dato, enVal, numer)
{
    console.log(dato+"--> "+enVal+" / "+ numer);
    result = true;
    for(i=2; i <= numer; i++){
        if(dato==$("#"+enVal+i).val())
            result = false;
    }
    return result;
}



function guardarCombos()
{
    /*
     var dataString = $('#form_servicios').serialize();
     
        $.ajax({
            type: 'POST',
            url: 'index.php?page=servicios_lav',
            dataType: 'json',
            data: dataString,
            success: function (datos) {
                if(datos.exito){
                    alert("EXITO");
                }
                    
            },
            error: function () {
                alert("Hay un error al guardar los datos");
            }
        });
    */
   
    
}


$(document).ready(function () {
    
    
    if($("#bandera_aviso").val()==1){
        bootbox.alert("Registro guardado con éxito");
    }
    if($("#bandera_aviso").val()==2){
        bootbox.alert("Error al guardar el combo");
    }
    /**
     * funciones para mostrar cuando la página esté lista
     */
    
    if($("#cant_conjunto").val()>0){
        num_cuadros = $("#cant_conjunto").val();
    }
    
    $("#bot_servicios").click(function (event) {
        generar_combo_servicio();
        $("#cant_conjunto").val(num_cuadros);
    });
    
    $("#bot_servicios1").click(function (event) {
        borrar_control();
    });
    
    $("#bot_articulos").click(function (event) {
        generar_combo_articulos();
        $("#cant_conjunto").val(num_cuadros);
    });
    
    /*
    $("#bot_articulos1").click(function (event) {
        if(num_cuadros2 > 1){
            $("#frag"+num_cuadros2).remove();
            num_cuadros2--;
        }
    });*/

    
    $("#form_servicios").submit(function (event) {
        //event.preventDefault();
        
        //guardarCombos();
    });
    
    $("#combo_servicio").click(function (event) {
        if($(this).prop('checked')){
            $("#select_servicios").show();
        }
        else{
            $("#select_servicios").hide();
        }
    });
    
     $("#mostrar_aviso").click(function (event) {
        if($(this).prop('checked')){
            $("#opcion_aviso").show();
        }
        else{
            $("#opcion_aviso").hide();
        }
            
    });
    
    
    
    
    
});
