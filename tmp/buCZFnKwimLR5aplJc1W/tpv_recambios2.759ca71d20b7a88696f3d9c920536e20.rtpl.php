<?php if(!class_exists('raintpl')){exit;}?><script type="text/javascript" src="<?php echo $fsc->get_js_location('tpv_recambios.js');?>"></script>
<script type="text/javascript">
   fs_nf0 = <?php  echo FS_NF0;?>;
   tpv_url = '<?php echo $fsc->url();?>';
   cliente = <?php echo json_encode($fsc->cliente_s); ?>;
   all_impuestos = <?php echo json_encode($fsc->impuesto->all()); ?>;
   all_series = <?php echo json_encode($fsc->serie->all()); ?>;
   
   $(document).ready(function() {
      usar_serie();
      
      $("#ac_cliente").autocomplete({
         serviceUrl: tpv_url,
         paramName: 'buscar_cliente',
         onSearchComplete :  function (query, suggestions) {
             if(suggestions["length"]<1)
             {
                 document.f_tpv.cliente_existe.value = 0;
             }
         },
         onSelect: function (suggestion) {
            if(suggestion)
            {
               if(document.f_tpv.cliente.value != suggestion.data && suggestion.data != '')
               {
                  document.f_tpv.cliente.value = suggestion.data;
                  document.f_tpv.nombrecliente.value = suggestion.full.razonsocial;
                  document.f_tpv.cifnif.value = suggestion.full.cifnif;
                  document.f_tpv.cliente_existe.value = 1;
                  usar_cliente(suggestion.data);
               }
               $("#bot_nuevo_cliente").hide();
            }
            
         }
         
      });
   });
</script>

<form id="f_tpv" name="f_tpv" action="<?php echo $fsc->url();?>" method="post" class="form">
    <input type="hidden" name="factura_editar" id="factura_editar" value="<?php echo $fsc->factura_editar;?>">
   <input type="hidden" name="petition_id" value="<?php echo $fsc->random_string();?>"/>
   <input type="hidden" id="numlineas" name="numlineas" value="0"/>
   <input type="hidden" id="tpv_total2" name="tpv_total2" value="0"/>
   <input type="hidden" name="cliente" value="<?php echo $fsc->cliente_s->codcliente;?>"/>
   <input type="hidden" name="regalo" value="FALSE"/>
   <input type="hidden" name="almacen" value="<?php echo $fsc->terminal->codalmacen;?>"/>
   <input type="hidden" name="serie" value="<?php echo $fsc->terminal->codserie;?>"/>
   <input type="hidden" name="cliente_existe" value="0" id="cliente_existe"/>
   <input type="hidden" id="idTerminal"  name="idTerminal" value="<?php echo $fsc->terminal->id;?>"/>
   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-3">
            <div class="form-group">
               <div class="input-group">
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-barcode"></span>
                  </span>
                  <input id="b_codbar" class="form-control" type="text" name="codbar" placeholder="Código de barras" autofocus="" autocomplete="off"/>
               </div>
               <p class="help-block">
                  Nada + INTRO = guardar ticket
               </p>
            </div>
         </div>
         <div class="col-sm-3">
            <div class="form-group">
               <div class="input-group">
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-user"></span>
                  </span>
                  <input class="form-control" type="text" name="ac_cliente" id="ac_cliente" value="" placeholder="Buscar" autocomplete="off"/>
                  <span class="input-group-btn">
                     <button class="btn btn-default" type="button" id="bot_nuevo_cliente" >
                        <span class="glyphicon glyphicon-floppy-disk"></span>
                     </button>
                  </span>
               </div>
               <!--<p class="help-block">
                  <a href="<?php echo $fsc->cliente->url();?>#nuevo" target="_blank">Nuevo cliente</a>.
               </p>-->
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-calendar"></span>
                  </span>
                   <?php if( $fsc->user->admin ){ ?>

                   
                        <?php if( $fsc->factura_editar >-1 ){ ?>

                            <input class="form-control" type="text" id="fecha" name="fecha"  readonly=""/>
                        <?php }else{ ?>

                            <input class="form-control datepicker" type="text" id="fecha" name="fecha" value="<?php echo $fsc->today();?>" readonly=""/>
                        <?php } ?>

                  <?php }else{ ?>

                  <input class="form-control" type="text" id="fecha" name="fecha" value="<?php echo $fsc->today();?>" readonly=""/>
                  <?php } ?>

               </div>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <span class="input-group-addon">
                     <span class="glyphicon glyphicon-user"></span>
                  </span>
                  <div class="form-control">
                  <input class="form-control" type="hidden" name="empleado" id="empleado" value="<?php echo $fsc->agente->codagente;?>" readonly=""/>
                     <a href="<?php echo $fsc->agente->url();?>"><?php echo $fsc->agente->get_fullname();?></a>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-sm-2">
            <div class="form-group">
               <div class="input-group">
                  <input id="tpv_total3" class="form-control text-right" type="text" name="tpv_total3" value="0" readonly=""/>
                  <span class="input-group-addon"><?php echo $fsc->simbolo_divisa();?></span>
               </div>
            </div>
         </div>
      </div>
      <div class="row">
         <div class="col-sm-6">
            <div class="btn-group hidden-xs">
               <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>" title="recargar la página">
                  <span class="glyphicon glyphicon-refresh"></span>
               </a>
               <?php if( $fsc->page->show_on_menu ){ ?>

                  <?php if( $fsc->page->is_default() ){ ?>

                  <a class="btn btn-sm btn-default active" href="<?php echo $fsc->url();?>&amp;default_page=FALSE" title="Marcada como página de inicio (pulsa de nuevo para desmarcar)">
                     <i class="fa fa-bookmark" aria-hidden="true"></i>
                  </a>
                  <?php }else{ ?>

                  <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>&amp;default_page=TRUE" title="Marcar como página de inicio">
                     <i class="fa fa-bookmark-o" aria-hidden="true"></i>
                  </a>
                  <?php } ?>

               <?php } ?>

            </div>
            <div class="btn-group">
               <a href="#" id="b_reticket" class="btn btn-sm btn-default">
                  <span class="glyphicon glyphicon-print"></span>
                  <span class="hidden-xs">&nbsp;Reimprimir ticket</span>
               </a>
               <a href="index.php?page=facturas_lav" target="_blank" id="pago_factura" class="btn btn-sm btn-primary">
                  <span class="hidden-xs">Pagar Facturas</span>
               </a>
                
            </div>
            <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <?php if( $value1->type=='button' ){ ?>

               <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
               <?php }elseif( $value1->type=='btn_javascript' ){ ?>

               <button class="btn btn-sm btn-default" type="button" onclick="<?php echo $value1->params;?>"><?php echo $value1->text;?></button>
               <?php } ?>

            <?php } ?>

         </div>
         <div class="col-sm-6 text-right">
            <div class="btn-group">
                
             <button type="button" id="addCombo" class="btn btn-sm btn-info">
               <span class="glyphicon glyphicon-off"></span>
               <span class="hidden-xs">Add Combo</span>
            </button>
                
                <a href="#" id="b_cerrar_caja" class="btn btn-sm btn-danger" onclick="cerrar_caja()">
                  <span class="glyphicon glyphicon-lock"></span>&nbsp; Cerrar caja
               </a>
               <a href="<?php echo $fsc->url();?>&abrir_caja=TRUE" id="b_abrir_caja" class="btn btn-sm btn-default">
                  <span class="glyphicon glyphicon-inbox"></span>
                  <span class="hidden-xs hidden-sm">&nbsp;Abrir cajón</span>
               </a>
            </div>
            
             
             
            
	    
                        
            &nbsp; &nbsp;
            <button type="button" id="b_tpv_guardar" class="btn btn-sm btn-primary">
               <span class="glyphicon glyphicon-floppy-disk"></span>
               <span class="hidden-xs">&nbsp;Guardar (F9)</span>
            </button>
         </div>
      </div>
   </div>
   
   <br/>
   <div id="lineas">
   <!--   <div id="articuloscinta" class="table-responsive"></div>-->
   <input id="referenciaManual" class="form-control" type="text" placeholder="# Referencia" autocomplete="off"/>
      <!--<div id="familiasManual"></div>-->
   </div>
   <ul class="nav nav-tabs" role="tablist">
      <li class="active">
         <a href="#tab_lineas" role="tab" data-toggle="tab">
            <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span>
            <span class="hidden-xs">&nbsp;Carrito</span>
         </a>
      </li>
      <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         <?php if( $value1->type=='tab' ){ ?>

         <li role="presentation">
            <a href="#ext_<?php echo $value1->name;?>" aria-controls="ext_<?php echo $value1->name;?>" role="tab" data-toggle="tab"><?php echo $value1->text;?></a>
         </li>
         <?php } ?>

      <?php } ?>

   </ul>
   
   <div class="tab-content">
      <div class="tab-pane active" id="tab_lineas">
         <div class="table-responsive">
            <table class="table table-condensed">
               <thead>
                  <tr>
                     <th class="text-left" width="180">Referencia</th>
                     <th class="text-left" >Descripción</th>
                     <th class="text-left" width="200">Proveedor</th>
                     <th class="text-right" width="90">Cantidad</th>
                     <th width="60"></th>
                     <th class="text-right" width="110">Precio</th>
                     <th class="text-right" width="90">Dto. %</th>
                     <th class="text-right" width="130">Neto</th>
                     <th class="text-right" width="115"><?php  echo FS_IVA;?></th>
                     <th class="text-right recargo" width="115">RE %</th>
                     <th class="text-right irpf" width="115"><?php  echo FS_IRPF;?> %</th>
                     <th class="text-right" width="140">Total</th>
                  </tr>
               </thead>
               <tbody id="lineas_doc">
                  <tr class="info">
                     <td><input id="i_new_line" class="form-control" type="text" placeholder="Buscar para añadir..." autocomplete="off"/></td>
                     <td colspan="4"></td>
                     <td colspan="2">
                        <div class="form-control text-right">Totales</div>
                     </td>
                     <td><div id="aneto" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div></td>
                     <td><div id="aiva" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div></td>
                     <td class="recargo">
                        <div id="are" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div>
                     </td>
                     <td class="irpf">
                        <div id="airpf" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div>
                     </td>
                     <td><div id="atotal" class="form-control text-right" style="font-weight: bold;"><?php echo $fsc->show_numero(0);?></div></td>
                  </tr>
               </tbody>
            </table>
         </div>
      </div>
      <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         <?php if( $value1->type=='tab' ){ ?>

            <div role="tabpanel" class="tab-pane" id="ext_<?php echo $value1->name;?>">
               <iframe src="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>&cod=<?php echo $fsc->cliente_s->codcliente;?>" width="100%" height="2000" frameborder="0"></iframe>
            </div>
         <?php } ?>

      <?php } ?>

   </div>
   
   <div class="modal" id="modal_guardar" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
               <h4 class="modal-title">Guardar ticket</h4>
               <p class="help-block">
                  <b>Recuerda</b> que para poder imprimir tickets necesitas estar ejecutando la aplicación
                  <a target="_blank" href="https://www.facturascripts.com/descargar?remoteprinter=TRUE">Remote printer</a>.
               </p>
            </div>
            <ul class="nav nav-tabs nav-justified" role="tablist">
               <li role="presentation" class="active">
                  <a href="#tab_pago" aria-controls="tab_pago" role="tab" data-toggle="tab">
                     <span class="glyphicon glyphicon-usd"></span>
                     <span class="hidden-xs">&nbsp;Pago</span>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_cliente" aria-controls="tab_cliente" role="tab" data-toggle="tab">
                     <span class="glyphicon glyphicon-user"></span>
                     <span class="hidden-xs">&nbsp;Cliente</span>
                  </a>
               </li>
               <li role="presentation">
                  <a href="#tab_opciones" aria-controls="tab_opciones" role="tab" data-toggle="tab">
                     <span class="glyphicon glyphicon-wrench"></span>
                     <span class="hidden-xs">&nbsp;Opciones</span>
                  </a>
               </li>
            </ul>
            <div class="modal-body">
               <div class="tab-content">
                  <div role="tabpanel" class="tab-pane active" id="tab_pago">
                     <div class="form-group">
                        <div class="input-group">
                           <span class="input-group-addon">Total</span>
                           <input type="text" name="tpv_total" id="tpv_total" class="form-control" disabled="disabled"/>
                        </div>
                     </div>
                     <div class="form-group">
                        <div class="input-group">
                           <span class="input-group-addon">Efectivo</span>
                           <input type="hidden" name="tpv_efectivo" id="tpv_efectivo" class="form-control" autocomplete="off"/>
                           <input type="text" name="tpv_efectivo1" id="tpv_efectivo1" class="form-control" autocomplete="off"/>
                        </div>
                     </div>
                     <div class="form-group">
                        <div class="input-group">
                           <span class="input-group-addon">Cambio</span>
                           <input type="text" name="tpv_cambio" id="tpv_cambio" class="form-control" disabled="disabled"/>
                        </div>
                     </div>
                     <div class="form-group">
                        <a href="<?php echo $fsc->forma_pago->url();?>">Forma de pago</a>:
                        <!--<p><?php echo $fsc->cliente->codpago;?></p>-->
                        <select name="forma_pago" class="form-control">
                        <?php $loop_var1=$fsc->forma_pago->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        
                           <?php if( $value1->codpago == 'CONT' ){ ?>

                           <option value="<?php echo $value1->codpago;?>" selected=""><?php echo $value1->descripcion;?></option>
                           <?php }else{ ?>

                           <option value="<?php echo $value1->codpago;?>"><?php echo $value1->descripcion;?></option>
                           <?php } ?>

                        <?php } ?>

                        </select>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="tab_cliente">
                     <div class="form-group">
                        Nombre:
                        <input class="form-control" type="text" name="nombrecliente" value="<?php echo $fsc->cliente_s->razonsocial;?>" autocomplete="off"/>
                     </div>
                     <div class="form-group">
                        <div class="input-group">
                           <span class="input-group-addon"><?php  echo FS_CIFNIF;?></span>
                           <input class="form-control" type="text" name="cifnif" value="<?php echo $fsc->cliente_s->cifnif;?>" maxlength="30" autocomplete="off"/>
                        </div>
                     </div>
                     <div class="form-group">
                        <input class="form-control" type="text" name="numero2" placeholder="<?php  echo FS_NUMERO2;?>" autocomplete="off"/>
                     </div>
                     <div class="form-group">
                        <textarea class="form-control" name="observaciones" placeholder="Observaciones" rows="4"></textarea>
                     </div>
                  </div>
                  <div role="tabpanel" class="tab-pane" id="tab_opciones">
                     <div class="form-group">
                        <a href="<?php echo $fsc->divisa->url();?>">Divisa</a>:
                        <select name="divisa" class="form-control">
                        <?php $loop_var1=$fsc->divisa->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <?php if( $value1->is_default() ){ ?>

                           <option value="<?php echo $value1->coddivisa;?>" selected=""><?php echo $value1->descripcion;?></option>
                           <?php }else{ ?>

                           <option value="<?php echo $value1->coddivisa;?>"><?php echo $value1->descripcion;?></option>
                           <?php } ?>

                        <?php } ?>

                        </select>
                     </div>
                     <div class="form-group">
                        Tasa de conversión (1€ = X)
                        <input type="text" name="tasaconv" class="form-control" placeholder="(predeterminada)" autocomplete="off"/>
                     </div>
                     <div class="form-group">
                        Nº de tickets:
                        <input class="form-control" type="number" id="num_tickets" name="num_tickets" value="<?php echo $fsc->terminal->num_tickets;?>"/>
                     </div>
                     <div class="checkbox">
                        <label>
                           <input type="checkbox" name="imprimir_desc" value="TRUE"<?php if( $fsc->imprimir_descripciones ){ ?> checked="checked"<?php } ?>/>
                           Imprimir descripciones
                        </label>
                     </div>
                     <div class="checkbox">
                        <label>
                           <input type="checkbox" name="imprimir_obs" value="TRUE"<?php if( $fsc->imprimir_observaciones ){ ?> checked="checked"<?php } ?>/>
                           Imprimir observaciones
                        </label>
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer">
               <div class="btn-group">
				<div class="checkbox">
                   <label>
				  <input type="checkbox" id="siImprimir" name="siImprimir" value="TRUE" />
				     Imprimir Ticket
                    </label>
				</div>
                  <button class="btn btn-sm btn-primary" type="button" onclick="this.disabled=true;$('#tpv_total').prop('disabled',false);this.form.submit();">
                     <span class="glyphicon glyphicon-floppy-disk"></span>&nbsp; Guardar e imprimir
                  </button>
                  <button class="btn btn-sm btn-info" type="button" onclick="this.disabled=true;$('#tpv_total').prop('disabled',false);document.f_tpv.regalo.value='TRUE';this.form.submit();" title="Imprimir ticket para regalo (sin precios)">
                     <span class="glyphicon glyphicon-gift"></span>
                  </button>
               </div>
            </div>
         </div>
      </div>
   </div>
</form>

<div class="modal" id="modal_articulos">
   <div class="modal-dialog" style="width: 99%; max-width: 950px;">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h4 class="modal-title">Buscar artículos</h4>
            <p class="help-block">
               <span class="glyphicon glyphicon-info-sign"></span>
               Coloca el puntero sobre un precio para ver la fecha en la que fue actualizado.
            </p>
         </div>
         <div class="modal-body">
            <form id="f_buscar_articulos" name="f_buscar_articulos" action="<?php echo $fsc->url();?>" method="post" class="form">
               <input type="hidden" name="codcliente"/>
               <input type="hidden" name="codalmacen" value="<?php echo $fsc->terminal->codalmacen;?>"/>
               <div class="container-fluid">
                  <div class="row">
                     <div class="col-sm-4">
                        <div class="input-group">
                           <input class="form-control" type="text" name="query" autocomplete="off"/>
                           <span class="input-group-btn">
                              <button class="btn btn-primary" type="submit">
                                 <span class="glyphicon glyphicon-search"></span>
                              </button>
                           </span>
                        </div>
                        <label class="checkbox-inline">
                           <input type="checkbox" name="con_stock" value="TRUE" onchange="buscar_articulos()"/>
                           sólo con stock
                        </label>
                     </div>
                     <div class="col-sm-4">
                        <select class="form-control" name="codfamilia" onchange="buscar_articulos()">
                           <option value="">Cualquier familia</option>
                           <option value="">------</option>
                           <?php $loop_var1=$fsc->familia->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <option value="<?php echo $value1->codfamilia;?>"><?php echo $value1->nivel;?><?php echo $value1->descripcion;?></option>
                           <?php } ?>

                        </select>
                     </div>
                     <div class="col-sm-4">
                        <select class="form-control" name="codfabricante" onchange="buscar_articulos()">
                           <option value="">Cualquier fabricante</option>
                           <option value="">------</option>
                           <?php $loop_var1=$fsc->fabricante->all(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                           <option value="<?php echo $value1->codfabricante;?>"><?php echo $value1->nombre;?></option>
                           <?php } ?>

                        </select>
                     </div>
                  </div>
               </div>
            </form>
         </div>
         <div id="search_results"></div>
      </div>
   </div>
</div>


<div class="modal" id="modal_personas">
       
      <div class="modal-dialog">
          
         <div class="modal-content">
             
            <div class="modal-header">
                
               
               
            </div>

            <div class="modal-body bg-info">
                
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true" id="cerrar_personas" >&times;</button>
                
                <p id="titulo_personas">Asignar proveedor de servicio</p>
            </div>
             
            <div class="modal-body" >
               
                <form action="#" id="form_personas" method="POST" name="form_personas">
                    <input type="hidden" class="form-control"  name="bandera_lav"  id="bandera_lav" value="<?php echo $fsc->bandera;?>">
                    <input type="hidden" class="form-control"  name="adminAbierto"  id="adminAbierto" value="<?php echo $fsc->adminAbierto;?>">
                
                    
                        <div class="row">
                            
                            <div class="col-sm-8">
                                <div class="form-group">
                                Proveedor de Servicio
                                    <select class="form-control form-group-lg" id="persona1" name="persona1">
                                        <option value="" selected="selected"></option>
                                        <?php $loop_var1=$fsc->aspiradoresInicio(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                            <?php if( $value1!=$fsc->user->nick ){ ?>

                                                <option value="<?php echo $value1;?>"><?php echo $value1;?></option>
                                            <?php } ?>

                                        <?php } ?>

                                    </select>
                              
                                    <a id="agregar_persona" type="submit" href="#" class="btn btn-default btn-sm">+</a>
                                </div>    
                            </div>
                        </div>
                    
                    <button type="submit" class="btn btn-primary" id='bot_personas'  >Guardar</button>
                </form>
                <div id="mensaje_provservi" class="warning"></div>
            </div>
             
            <div class="modal-footer">
               
            </div>
         </div>
      </div>
   </div>

<div class="modal fade" id="modal_ayuda_ticket" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">
               <span class="glyphicon glyphicon-print"></span> Imprimir tickets
            </h4>
            <p class="help-block">
               Para poder imprimir tickets son necesarias varias cosas:
            </p>
         </div>
         <div class="modal-body">
            <ul>
               <li>
                  Una impresora de tickets.
                  <i>No se imprimen tickets en otro tipo de impresoras.</i>
               </li>
               <li>
                  Ejecutar la aplicación
                  <a target="_blank" href="https://www.facturascripts.com/descargar?remoteprinter=TRUE">Remote printer</a>.
               </li>
            </ul>
            <a target="_blank" href="https://www.facturascripts.com/descargar?remoteprinter=TRUE" class="thumbnail">
               <img src="https://i.imgur.com/BFLzvS4.png" alt="remote-printer"/>
            </a>
            <p class="help-block">
               El campo <b>URL de la API de FacturaScripts</b> se refiere a la dirección
               web donde tengas FacturaScripts. Si en la barra de tu nevagador pone
               <b>http://localhost/...</b>, entonces debes poner: <b>http://localhost/api.php</b>
            </p>
         </div>
      </div>
   </div>
</div>