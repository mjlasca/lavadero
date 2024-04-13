<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript" src="<?php echo $fsc->get_js_location('informes_lav.js');?>"></script>
   
<script type="text/javascript">
            $(function () {
                
                var dicho = "<?php echo $fsc->nav_activo;?>";
                /*
                $('.datepicker').datetimepicker({
					format: 'YYYY-MM-DD'
                });*/
				
				$('#lavadores_info-tab').click(function () {
					$("#nav_activo").val("1"); 
				});
				
				$('#aspiradores_info-tab').click(function () {
					$("#nav_activo").val("2"); 
				});
                                $('#jefes_info-tab').click(function () {
					$("#nav_activo").val("3"); 
				});
                                $('#pago_comisiones-tab').click(function () {
					$("#nav_activo").val("4"); 
				});
                       
                        if(dicho == 1){
                            $("#lavadores_info").addClass("active");
                            $("#aspiradores_info").removeClass("active");
                            $("#jefes_info").removeClass("active");
                            $("#pago_comisiones").removeClass("active");
                        }
                        if(dicho == 2){
                            $("#lavadores_info").removeClass("active");
                            $("#aspiradores_info").addClass("active");
                            $("#jefes_info").removeClass("active");
                            $("#pago_comisiones").removeClass("active");
                        }
                        if(dicho == 3){
                            $("#lavadores_info").removeClass("active");
                            $("#aspiradores_info").removeClass("active");
                            $("#jefes_info").addClass("active");
                            $("#pago_comisiones").removeClass("active");
                        }
                        if(dicho == 4){
                            $("#lavadores_info").removeClass("active");
                            $("#aspiradores_info").removeClass("active");
                            $("#jefes_info").removeClass("active");
                            $("#pago_comisiones").addClass("active");
                        }
                        
            });
			
			
        </script>	
	
	
		<div class="rows col-12 col-md-12">
                    
		<form action="<?php echo $fsc->url();?>" method="post" class="form" id="form_informe">
                    
			<input type="hidden" value="<?php echo $fsc->nav_activo;?>" id="nav_activo" name="nav_activo" >
                        <input type="hidden"  id="cambiar_valores_liquidacion" name="cambiar_valores_liquidacion" >
                        
			<div class="col-12 col-md-2">
				<div class="input-group">
					<span class="input-group-addon">Desde</span>
                    <input name="fec_desde" id="fec_desde"  value="<?php echo $fsc->fecha_inicio;?>" class="form-control datepicker" autocomplete="off"  type="text">
                
                    <input name="hora_desde" id="hora_desde"  value="<?php echo $fsc->hora_inicio;?>" class="form-control" autocomplete="off"  type="hidden">
                </div>
				
			</div>
			
			<div class="col-12 col-md-2">
				<div class="input-group">
					<span class="input-group-addon">Hasta</span>
                    <input name="fec_hasta"  id="fec_hasta"  value="<?php echo $fsc->fecha_final;?>" class="form-control datepicker" autocomplete="off"  type="text">
                    <input name="hora_hasta" id="hora_hasta"  value="<?php echo $fsc->hora_final;?>" class="form-control" autocomplete="off"  type="hidden">
                </div>
			</div>	
			
			
			<div class="col-12 col-md-3">
				<div class="input-group">
					<span class="input-group-addon">Proveedor</span>
                        <select  class="form-control"  name="info_provee"  id="info_provee" >
                            <option value="" <?php if( $fsc->info_provee=='' ){ ?>selected="selected" <?php } ?>>Todos</option>
                        <?php $loop_var1=$fsc->personas_lav_informe(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                <option value="<?php echo $value1;?>" <?php if( $fsc->info_provee==$value1 ){ ?>selected="selected" <?php } ?>><?php echo $value1;?></option>
                        <?php } ?>

                        </select>
                            
			</div>
                            <label><input type="checkbox" name="consolidado" id="consolidado" value="<?php echo $fsc->consolidado;?>" <?php if( $fsc->consolidado==1 ){ ?> checked <?php } ?> > Consolidado </label>
			</div>	
			<!--<div class="col-12 col-md-2">
				<button type="submit" class="btn btn-primary">Enviar</button>
			</div>	
                        -->
                        <div class="col-12 col-md-5 text-right">
                                <?php if( $fsc->reciboSI && $fsc->info_provee ){ ?>

                                    <!-- Button trigger modal -->
                                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#exampleModalLong">
                                      <span class="fa fa-print"></span>
                                    </button>
                                    
                                    
                                <?php } ?>

                                <?php if( $fsc->allow_delete && $fsc->vista_cambio_valores ){ ?>

                                <button type="button" onclick="return cambiar_val_liquidacion()" class="btn btn-danger" title="Cambiar valores de liquidación" >
                                      <span class="fa fa-bolt"></span>
                                </button>
                                <?php } ?>

                        </div>
		</form>
		</div>
	
		<div class="rows col-12 col-md-12">
			<h1></h1>
		</div>

	
<!-- Nav tabs -->
<ul class="nav nav-tabs" id="myTab" role="tablist">

  <li <?php if( $fsc->nav_activo==1 ){ ?> class="nav-item active" <?php }else{ ?> class="nav-item" <?php } ?>>
    <a class="nav-link active" id="lavadores_info-tab" onclick="enviar_pestana(1)" data-toggle="tab" href="#lavadores_info" role="tab" aria-controls="lavadores_info" aria-selected="true" >Lavadores</a>
  </li>
  
  <li <?php if( $fsc->nav_activo==2 ){ ?> class="nav-item active" <?php }else{ ?> class="nav-item" <?php } ?>>
      <a class="nav-link" id="aspiradores_info-tab" data-toggle="tab" onclick="enviar_pestana(2)" href="#aspiradores_info" role="tab" aria-controls="aspiradores_info" aria-selected="false">Aspiradores</a>
  </li>
  
  <li <?php if( $fsc->nav_activo==3 ){ ?> class="nav-item active" <?php }else{ ?> class="nav-item" <?php } ?>>
    <a class="nav-link" id="jefes_info-tab" data-toggle="tab" onclick="enviar_pestana(3)" href="#jefes_info" role="tab" aria-controls="jefes_info" aria-selected="false">Jefes de Patio</a>
  </li>
  
  <li <?php if( $fsc->nav_activo==4 ){ ?> class="nav-item active" <?php }else{ ?> class="nav-item" <?php } ?>>
    <a class="nav-link" id="pago_comisiones-tab" data-toggle="tab" onclick="enviar_pestana(4)" href="#pago_comisiones" role="tab" aria-controls="pago_comisiones" aria-selected="false">Pago comisiones</a>
  </li>
  
</ul>

		

<!-- Tab panes -->
<div class="tab-content">
	
	<div class="tab-pane active tarjetas" id="lavadores_info" role="tabpanel" aria-labelledby="lavadores_info-tab">
		<table class="table table-hover">
            <thead>
              <tr>
                <th scope="col">Fecha</th>
                <th scope="col">Proveedor</th>
                <?php if( $fsc->consolidado!=1 ){ ?>

                <th scope="col">Fact.# / Servicio</th>
                <?php }else{ ?>

                <th scope="col">Total Servicios</th>
                <?php } ?>

                <th scope="col" class="text-right">Valor Facturado</th>
                <th scope="col" class="text-right">Valor Liquidado</th>
              </tr>
            </thead>
            <tbody>
				<?php $loop_var1=$fsc->info_lavaderos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

				<tr>
						<td><?php echo $value1["horaas"];?></td>
						<td><?php echo $value1["proveedor_lav"];?></td>
                        <?php if( $fsc->consolidado!=1 ){ ?>

                        <td><a href="index.php?page=ventas_factura&id=<?php echo $value1['idfactura'];?>"><?php echo $value1['codigo'];?> </a> / <?php echo $value1["descripcion"];?></td>
                        <?php }else{ ?>

                        <td ><?php echo $value1["contador"];?></td>
                        <?php } ?>

                        <?php if( $fsc->consolidado!=1 ){ ?>

						<td class="text-right"><?php echo $fsc->formato_moneda($value1["pvptotal"]);?></td>
                        <?php }else{ ?>

                        <td class="text-right"><?php echo $fsc->formato_moneda($value1["totalito"]);?></td>
                        <?php } ?>

                        <?php if( $fsc->consolidado!=1 ){ ?>

                        <td class="text-right"><?php echo $fsc->formato_moneda($value1["val_liquidacion"]);?></td>
                        <?php }else{ ?>

                        <td class="text-right"><?php echo $fsc->formato_moneda($value1["totalliqui"]);?></td>
                        <?php } ?>

				</tr>
				<?php } ?>

                
            </tbody>
			<tfoot>
                            <tr><td class="text-left"></td><td class="text-right" ><td> </td><td> <h3 class="panel-title">TOTAL</h3> </td><td class="text-right"><h3 class="panel-title"><?php echo $fsc->formato_moneda($fsc->info_lavaderos_total);?></h3></td></tr>
			</tfoot>
        </table>
	
    </div>

    <div class="tab-pane tarjetas" id="aspiradores_info" role="tabpanel" aria-labelledby="aspiradores_info-tab">
		<table class="table">
            <thead>
              <tr>
                <th scope="col">Fecha</th>
                <th scope="col">Proveedor</th>
                <th scope="col">Factura # comisionado</th>
                <th scope="col" class="text-right">Valor Factura</th>
                <th scope="col" class="text-right">Valor comisión</th>
              </tr>
            </thead>
            <tbody>
                    <?php $loop_var1=$fsc->informe_aspiradores; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <tr>
                                        <td><?php echo $value1["horaas"];?></td>
                                        <td><?php echo $value1["aspirador_add"];?></td>
                                        <td ><a href="index.php?page=ventas_factura&id=<?php echo $value1['idfactura'];?>"><?php echo $value1['codigo'];?> </a></td>
                                        <td class="text-right"><?php echo $fsc->formato_moneda($value1["pvptotal"]);?></td>
                                        <td class="text-right"><?php echo $fsc->formato_moneda($value1["val_liquidacion"]);?></td>
                        </tr>
                    <?php } ?>

            </tbody>
                <tfoot>
                    <tr><td colspan="3"></td><td class="text-right" > <h3 class="panel-title">TOTAL</h3> </td><td class="text-right"><h3 class="panel-title"><?php echo $fsc->formato_moneda($fsc->info_aspiradores_total);?></h3></td></tr>
                </tfoot>
            
        </table>
    </div>
    
    
    <div class="tab-pane tarjetas" id="jefes_info" role="tabpanel" aria-labelledby="jefes_info-tab">
		<table class="table table-hover">
            <thead>
              <tr>
                <th scope="col">Fecha</th>
                <th scope="col">Proveedor</th>
                <th scope="col">Fact.# / Servicio</th>
                <th scope="col" class="text-right">Valor Facturado</th>
                <th scope="col" class="text-right">Valor Liquidado</th>
              </tr>
            </thead>
            <tbody>
				<?php $loop_var1=$fsc->informe_jefes; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

				<tr>
						<td><?php echo $value1["horaas"];?></td>
                                                <td><?php echo $value1["jefe_adicionado"];?></td>
                                                <?php if( $fsc->consolidado!=1 ){ ?>

                                                <td><a href="index.php?page=ventas_factura&id=<?php echo $value1['idfactura'];?>"><?php echo $value1['codigo'];?> </a> / <?php echo $value1["descripcion"];?></td>
                                                <?php }else{ ?>

                                                <td>Consolidado</td>
                                                <?php } ?>

						<td class="text-right"><?php echo $fsc->formato_moneda($value1["pvptotal"]);?></td>
						<td class="text-right"><?php echo $fsc->formato_moneda($value1["val_liquidacion"]);?></td>
				</tr>
				<?php } ?>

                
            </tbody>
			<tfoot>
                            <tr><td colspan="3"></td><td class="text-right" > <h3 class="panel-title">TOTAL</h3> </td><td class="text-right"><h3 class="panel-title"><?php echo $fsc->formato_moneda($fsc->info_jefes_total);?></h3></td></tr>
			</tfoot>
        </table>
    </div>
    
    <div class="tab-pane tarjetas" id="pago_comisiones" role="tabpanel" aria-labelledby="pago_comisiones-tab">
        
        <form action="<?php echo $fsc->url();?>" method="post" class="form">
            
           
            <div class="col col-md-8">
                <div class="col col-md-2">
                    
                    <select class="form-control" name="ano_con">
                        <?php $loop_var1=$fsc->ano; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1;?>"><?php echo $value1;?></option>
                        <?php } ?>

                        
                    </select>
                </div>
                
                <div class="col col-md-3">
                    <select  class="form-control"  name="tip_lavador"  id="tip_lavador" >
                        <?php if( $fsc->tip_lavador == 1 ){ ?>

                            <option value="1" selected="" >LAVADOR</option>
                        <?php }else{ ?>

                            <option value="1" >LAVADOR</option>
                        <?php } ?>

                        <?php if( $fsc->tip_lavador  == 2 ){ ?>

                            <option value="2" selected="" >ASPIRADOR</option>
                        <?php }else{ ?>

                            <option value="2" >ASPIRADOR</option>
                        <?php } ?>

                        <?php if( $fsc->tip_lavador  == 3 ){ ?>

                            <option value="3" selected="" >JEFE PATIO</option>
                        <?php }else{ ?>

                            <option value="3" >JEFE PATIO</option>
                        <?php } ?>

                    </select>
                </div>
                <div class="col col-md-5">
                    <input type="text" name="buscar_empleado" class="form-control" placeholder="Coincidir nombre o # recibo" value="<?php echo $fsc->buscar_empleado;?>">
                </div>
                
                <div class="col col-md-2">
                    <button class="btn btn-primary">
                        Enviar
                    </button>
                </div>
                

            </div>
        </form>
        
        
        <table class="table table-hover" name="tabla_comsiones">
            <thead>
            <tr>
            <th>REG</th>
            <th>Lavador</th>
            <th>Fecha recibo</th>
            <th>Periodo pagado</th>
            <th>Comisión</th>
            <th>Abono a deducciones</th>
            <th>Total pagado</th>
            </tr>
            </thead>
            <tbody>
             <?php $loop_var1=$fsc->registros_lavador_comsion; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                <tr>
                    <td><?php echo $value1["reg"];?></td>
                    <td><?php echo $value1["nombre_empleado"];?></td>
                    <td><?php echo $value1["ultmod"];?></td>
                    <td><?php echo $value1["fecha_inicial"];?> a <?php echo $value1["fecha_final"];?></td>
                    <td><?php echo $fsc->formato_moneda($value1["comision"]);?></td>
                    <td><?php echo $fsc->formato_moneda($value1["deducciones"]);?></td>
                    <td><?php echo $fsc->formato_moneda($value1["total"]);?></td>
                </tr>
             <?php } ?>

            
            </tbody>
            
        </table>
            
        
    </div>
    
	
</div>


<!-- Modal -->
<div class="modal fade" id="exampleModalLong" tabindex="-1" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
        <form action="<?php echo $fsc->url();?>" method="post" class="form">
      <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle"><b>Detalle Recibo</b> </h5>
          <?php if( $fsc->allow_delete ){ ?>

          <input type="checkbox" id="reporte_comision" name="reporte_comision" value="TRUE"> Generar registro de comisión
          <?php } ?>

      </div>
      <div class="modal-body">
          <div class="col-md-12" ><?php echo $fsc->titulo_proveedor;?> : <b> <?php if( $fsc->info_provee ){ ?> <?php echo $fsc->info_provee;?> <?php } ?> </b></div></br>
          <div class="col-md-12" >Fecha : INICIO <b>[ <?php echo $fsc->fecha_inicio;?> <?php echo $fsc->hora_inicio;?>]</b> - FINAL <b>[ <?php echo $fsc->fecha_final;?> <?php echo $fsc->hora_final;?>] </b> </div></br>
          <div class="col-md-6" >No. SERVICIOS :   <b> <?php echo $fsc->num_servicios;?></b> </div>   </br>
          <div class="col-md-6" > COMISIÓN : <b> 
              <?php if( $fsc->info_lavaderos_total ){ ?> <?php echo $fsc->formato_moneda($fsc->info_lavaderos_total);?> <?php } ?>

              <?php if( $fsc->info_jefes_total ){ ?> <?php echo $fsc->formato_moneda($fsc->info_jefes_total);?> <?php } ?>

              <?php if( $fsc->info_aspiradores_total ){ ?> <?php echo $fsc->formato_moneda($fsc->info_aspiradores_total);?> <?php } ?>

              </b></div> <?php if( $fsc->allow_delete ){ ?>  <div class="col-md-6" >DEDUCCIONES :   <b> <?php echo $fsc->formato_moneda($fsc->deducciones_prov);?></b> </div> <?php } ?> </br>
              <?php if( $fsc->allow_delete ){ ?>

          <div class="col-12 " > 
              ABONO DEDUCCIONES : 
              <select multiple="" class="form-control" name="abono_deducciones[]" id="abono_deducciones" >
                  <option value="0" selected="">0</option>
                  <?php $loop_var1=$fsc->facturas_deducciones(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <option value="<?php echo $value1['id'];?>"><?php echo $value1['total'];?></option>
                  <?php } ?>

              </select>
          </div> 
              </br>
         <?php } ?>

           
           <input type="hidden" name="comision_imp" id="comision_imp" value="<?php echo $fsc->total_a_liquidar;?>">
           <input type="hidden" name="comision_imp_aux" id="comision_imp_aux" value="0">
           <input type="hidden" name="imprime_recibo" value="TRUE">
              <input type="hidden" name="lavador_imp" value="<?php echo $fsc->info_provee;?>">
              <input type="hidden" name="fecha_imp" value="<?php echo $fsc->fecha_inicio;?> <?php echo $fsc->hora_inicio;?> <?php echo $fsc->fecha_final;?> <?php echo $fsc->hora_final;?>">
              <input type="hidden" name="fec_desde" value="<?php echo $fsc->fecha_inicio;?>">
              <input type="hidden" name="fec_hasta" value="<?php echo $fsc->fecha_final;?>">
              <input type="hidden" name="servicios_imp" value="<?php echo $fsc->num_servicios;?>">
              <input type="hidden" name="nav_activo" value="<?php echo $fsc->nav_activo;?>">
              <input type="hidden" name="deduccion_imp" value="<?php echo $fsc->deducciones_prov;?>">
              <input type="hidden" name="total_imp" id="total_imp" value="<?php echo $fsc->total_a_liquidar;?>">
          <div class="col-md-12" > <h4> TOTAL LIQUIDADO :   <b id="mostrar_total"> 
                <?php if( $fsc->info_lavaderos_total ){ ?> <?php echo $fsc->formato_moneda($fsc->info_lavaderos_total);?> <?php } ?>

                <?php if( $fsc->info_jefes_total ){ ?> <?php echo $fsc->formato_moneda($fsc->info_jefes_total);?> <?php } ?>

                <?php if( $fsc->info_aspiradores_total ){ ?> <?php echo $fsc->formato_moneda($fsc->info_aspiradores_total);?> <?php } ?>

                  </b>  </h4> </div>  </br>
      </div>
      <div class="modal-footer">
          
              
             
           <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
           <button type="submit" class="btn btn-primary">Imrimir</button>
        
      </div>
      </form>
    </div>
  </div>
    
    
    
    
    
</div>


<?php if( $fsc->terminal ){ ?>

<div class="hidden">
   <img src="http://localhost:10080?terminal=<?php echo $fsc->terminal->id;?>" alt="remote-printer1"/>
</div>
<?php } ?>



<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>



