<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<div class="container-fluid">
   <div class="row" style="margin-bottom: 10px;">
      <div class="col-sm-12">
         <?php if( $fsc->check_for_updates() ){ ?>

         <div class="alert alert-info">
            <a href="updater.php" class="btn btn-sm btn-primary">
               <span class="glyphicon glyphicon-upload"></span>&nbsp; Actualizar
            </a>
            &nbsp; Hay actualizaciones disponibles. Las actualizaciones añaden nuevas
            características y solucionan errores.
         </div>
         <?php }else{ ?>

         <div class="btn-group hidden-xs">
            <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>" title="Recargar la página">
               <span class="glyphicon glyphicon-refresh"></span>
            </a>
            <?php if( $fsc->page->is_default() ){ ?>

            <a class="btn btn-sm btn-default active" href="<?php echo $fsc->url();?>&amp;default_page=FALSE" title="Marcada como página de inicio (pulsa de nuevo para desmarcar)">
               <i class="fa fa-bookmark" aria-hidden="true"></i>
            </a>
            <?php }else{ ?>

            <a class="btn btn-sm btn-default" href="<?php echo $fsc->url();?>&amp;default_page=TRUE" title="Marcar como página de inicio">
               <i class="fa fa-bookmark-o" aria-hidden="true"></i>
            </a>
            <?php } ?>

         </div>
         <div class="btn-group">
            <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <?php if( $value1->type=='button' ){ ?>

               <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
               <?php } ?>

            <?php } ?>

         </div>
         <?php } ?>

      </div>
   </div>
   <div class="row">
      <div class="col-sm-12">
         <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active">
               <a href="#mes" aria-controls="mes" role="tab" data-toggle="tab">
                  <span class="glyphicon glyphicon-calendar"></span> Este mes
               </a>
            </li>
            <li role="presentation">
               <a href="#trimestre" aria-controls="trimestre" role="tab" data-toggle="tab">
                  <span class="glyphicon glyphicon-calendar"></span> Trimestre
               </a>
            </li>
            <li role="presentation">
               <a href="#anyo" aria-controls="anyo" role="tab" data-toggle="tab">
                  <span class="glyphicon glyphicon-calendar"></span> Año
               </a>
            </li>
            <li role="presentation">
               <a href="#dashconfig" aria-controls="dashconfig" role="tab" data-toggle="tab">
                  <span class="glyphicon glyphicon-wrench"></span>
               </a>
            </li>
         </ul>
      </div>
   </div>
   <div class="tab-content">
      <div role="tabpanel" class="tab-pane active" id="mes">
         <div class="row">
            <div class="col-sm-12">
               <br/>
               <p class="help-block">
                  Del <b><?php echo $fsc->mes['desde'];?></b> al <b><?php echo $fsc->mes['hasta'];?></b>.
                  <?php if( $fsc->anterior=='periodo' ){ ?>

                     Comparado con el mes anterior <b>hasta el mismo día</b>.
                  <?php }else{ ?>

                     Comparado con el mismo mes del <b>año anterior hasta el mismo día</b>.
                  <?php } ?>

               </p>
            </div>
         </div>
         <div class="row">
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-danger">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     Compras: <?php echo $fsc->show_precio($fsc->mes['compras_neto']);?>

                  <?php }else{ ?>

                     Compras: <?php echo $fsc->show_precio($fsc->mes['compras']);?>

                  <?php } ?>

                  <br/>
                  <?php if( $fsc->mes['compras_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->mes['compras_mejora']);?> %
                  <?php }elseif( $fsc->mes['compras_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->mes['compras_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class='help-block'>
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     <b><?php echo $fsc->show_precio($fsc->mes['compras_anterior_neto']);?></b>
                  <?php }else{ ?>

                     <b><?php echo $fsc->show_precio($fsc->mes['compras_anterior']);?></b>
                  <?php } ?>

                  <?php if( $fsc->anterior=='periodo' ){ ?>

                     el mes anterior hasta este mismo día.
                  <?php }else{ ?>

                     el mismo mes del año anterior hasta este mismo día.
                  <?php } ?>

               </p>
               <ul>
                  <li>
                     <b><?php echo $fsc->show_precio($fsc->mes['compras_albaranes_pte']);?></b> en <?php  echo FS_ALBARANES;?> pendientes.
                  </li>
                  <li>
                     <b><?php echo $fsc->show_precio($fsc->mes['compras_pedidos_pte']);?></b> en <?php  echo FS_PEDIDOS;?> pendientes.
                  </li>
               </ul>
            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-success">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     Ventas: <?php echo $fsc->show_precio($fsc->mes['ventas_neto']);?>

                  <?php }else{ ?>

                     Ventas: <?php echo $fsc->show_precio($fsc->mes['ventas']);?>

                  <?php } ?>

                  <br/>
                  <?php if( $fsc->mes['ventas_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->mes['ventas_mejora']);?> %
                  <?php }elseif( $fsc->mes['ventas_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->mes['ventas_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class='help-block'>
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     <b><?php echo $fsc->show_precio($fsc->mes['ventas_anterior_neto']);?></b>
                  <?php }else{ ?>

                     <b><?php echo $fsc->show_precio($fsc->mes['ventas_anterior']);?></b>
                  <?php } ?>

                  <?php if( $fsc->anterior=='periodo' ){ ?>

                     el mes anterior hasta este mismo día.
                  <?php }else{ ?>

                     el mismo mes del año anterior hasta este mismo día.
                  <?php } ?>

               </p>
               <ul>
                  <li>
                     <b><?php echo $fsc->show_precio($fsc->mes['ventas_albaranes_pte']);?></b> en <?php  echo FS_ALBARANES;?> pendientes.
                  </li>
                  <li>
                     <b><?php echo $fsc->show_precio($fsc->mes['ventas_pedidos_pte']);?></b> en <?php  echo FS_PEDIDOS;?> pendientes.
                  </li>
               </ul>
            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-warning">
                  Impuestos: <?php echo $fsc->show_precio($fsc->mes['impuestos']);?>

                  <br/>
                  <?php if( $fsc->mes['impuestos_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->mes['impuestos_mejora']);?> %
                  <?php }elseif( $fsc->mes['impuestos_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->mes['impuestos_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <b><?php echo $fsc->show_precio($fsc->mes['impuestos_anterior']);?></b>
                  <?php if( $fsc->anterior=='periodo' ){ ?>

                     el mes anterior hasta este mismo día.
                  <?php }else{ ?>

                     el mismo mes del año anterior hasta este mismo día.
                  <?php } ?>

               </p>
               <?php if( $fsc->empresa->codpais=='ESP' ){ ?>

               <p class="help-block">
                  <a href="index.php?page=contabilidad_nuevo_asiento#asistente">Cuota de autónomo</a>,
                  <a href="index.php?page=contabilidad_nuevo_asiento#asistente">modelo 130</a>,
                  <a href="#" data-toggle="modal" data-target="#modal_303">modelos 303 y 390</a>,
                  <a href="#" data-toggle="modal" data-target="#modal_347">modelo 347</a>.
               </p>
               <?php } ?>

            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-info">
                  Beneficio: <?php echo $fsc->show_precio($fsc->mes['beneficios']);?>

                  <br/>
                  <?php if( $fsc->mes['beneficios_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->mes['beneficios_mejora']);?> %
                  <?php }elseif( $fsc->mes['beneficios_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->mes['beneficios_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <b><?php echo $fsc->show_precio($fsc->mes['beneficios_anterior']);?></b>
                  <?php if( $fsc->anterior=='periodo' ){ ?>

                     el mes anterior hasta este mismo día.
                  <?php }else{ ?>

                     el mismo mes del año anterior hasta este mismo día.
                  <?php } ?>

               </p>
               <ul>
                  <li>
                     <b><?php echo $fsc->show_precio($fsc->mes['compras_sinpagar']);?></b> en facturas de compra sin pagar.
                  </li>
                  <li>
                     <b><?php echo $fsc->show_precio($fsc->mes['ventas_sinpagar']);?></b> en facturas de venta vencidas y sin pagar.
                  </li>
               </ul>
            </div>
         </div>
      </div>
      <div role="tabpanel" class="tab-pane" id="trimestre">
         <div class="row">
            <div class="col-sm-12">
               <br/>
               <p class="help-block">
                  Del <b><?php echo $fsc->trimestre['desde'];?></b> al <b><?php echo $fsc->trimestre['hasta'];?></b>.
                  <?php if( $fsc->anterior=='periodo' ){ ?>

                     Comparado con el <b>trimestre anterior completo</b>.
                  <?php }else{ ?>

                     Comparado con el mismo trimestre del <b>año anterior completo</b>.
                  <?php } ?>

               </p>
            </div>
         </div>
         <div class="row">
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-danger">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     Compras: <?php echo $fsc->show_precio($fsc->trimestre['compras_neto']);?>

                  <?php }else{ ?>

                     Compras: <?php echo $fsc->show_precio($fsc->trimestre['compras']);?>

                  <?php } ?>

                  <br/>
                  <?php if( $fsc->trimestre['compras_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->trimestre['compras_mejora']);?> %
                  <?php }elseif( $fsc->trimestre['compras_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->trimestre['compras_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     <b><?php echo $fsc->show_precio($fsc->trimestre['compras_anterior_neto']);?></b> el trimestre anterior.
                  <?php }else{ ?>

                     <b><?php echo $fsc->show_precio($fsc->trimestre['compras_anterior']);?></b> el trimestre anterior.
                  <?php } ?>

               </p>
            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-success">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     Ventas: <?php echo $fsc->show_precio($fsc->trimestre['ventas_neto']);?>

                  <?php }else{ ?>

                     Ventas: <?php echo $fsc->show_precio($fsc->trimestre['ventas']);?>

                  <?php } ?>

                  <br/>
                  <?php if( $fsc->trimestre['ventas_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->trimestre['ventas_mejora']);?> %
                  <?php }elseif( $fsc->trimestre['ventas_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->trimestre['ventas_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     <b><?php echo $fsc->show_precio($fsc->trimestre['ventas_anterior_neto']);?></b> el trimestre anterior.
                  <?php }else{ ?>

                     <b><?php echo $fsc->show_precio($fsc->trimestre['ventas_anterior']);?></b> el trimestre anterior.
                  <?php } ?>

               </p>
            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-warning">
                  Impuestos: <?php echo $fsc->show_precio($fsc->trimestre['impuestos']);?>

                  <br/>
                  <?php if( $fsc->trimestre['impuestos_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->trimestre['impuestos_mejora']);?> %
                  <?php }elseif( $fsc->trimestre['impuestos_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->trimestre['impuestos_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <b><?php echo $fsc->show_precio($fsc->trimestre['impuestos_anterior']);?></b> el trimestre anterior.
               </p>
            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-info">
                  Beneficio: <?php echo $fsc->show_precio($fsc->trimestre['beneficios']);?>

                  <br/>
                  <?php if( $fsc->trimestre['beneficios_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->trimestre['beneficios_mejora']);?> %
                  <?php }elseif( $fsc->trimestre['beneficios_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->trimestre['beneficios_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <b><?php echo $fsc->show_precio($fsc->trimestre['beneficios_anterior']);?></b> el trimestre anterior.
               </p>
            </div>
         </div>
      </div>
      <div role="tabpanel" class="tab-pane" id="anyo">
         <div class="row">
            <div class="col-sm-12">
               <br/>
               <p class="help-block">
                  Comparado con el año anterior hasta el mismo día.
               </p>
            </div>
         </div>
         <div class="row">
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-danger">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     Compras: <?php echo $fsc->show_precio($fsc->anyo['compras_neto']);?>

                  <?php }else{ ?>

                     Compras: <?php echo $fsc->show_precio($fsc->anyo['compras']);?>

                  <?php } ?>

                  <br/>
                  <?php if( $fsc->anyo['compras_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->anyo['compras_mejora']);?> %
                  <?php }elseif( $fsc->anyo['compras_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->anyo['compras_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     <b><?php echo $fsc->show_precio($fsc->anyo['compras_anterior_neto']);?></b> el año anterior hasta este mismo día.
                  <?php }else{ ?>

                     <b><?php echo $fsc->show_precio($fsc->anyo['compras_anterior']);?></b> el año anterior hasta este mismo día.
                  <?php } ?>

               </p>
            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-success">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     Ventas: <?php echo $fsc->show_precio($fsc->anyo['ventas_neto']);?>

                  <?php }else{ ?>

                     Ventas: <?php echo $fsc->show_precio($fsc->anyo['ventas']);?>

                  <?php } ?>

                  <br/>
                  <?php if( $fsc->anyo['ventas_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->anyo['ventas_mejora']);?> %
                  <?php }elseif( $fsc->anyo['ventas_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->anyo['ventas_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <?php if( $fsc->neto=='TRUE' ){ ?>

                     <b><?php echo $fsc->show_precio($fsc->anyo['ventas_anterior_neto']);?></b> el año anterior hasta este mismo día.
                  <?php }else{ ?>

                     <b><?php echo $fsc->show_precio($fsc->anyo['ventas_anterior']);?></b> el año anterior hasta este mismo día.
                  <?php } ?>

               </p>
            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-warning">
                  Impuestos: <?php echo $fsc->show_precio($fsc->anyo['impuestos']);?>

                  <br/>
                  <?php if( $fsc->anyo['impuestos_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->anyo['impuestos_mejora']);?> %
                  <?php }elseif( $fsc->anyo['impuestos_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->anyo['impuestos_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <b><?php echo $fsc->show_precio($fsc->anyo['impuestos_anterior']);?></b> el año anterior hasta este mismo día.
               </p>
            </div>
            <div class="col-sm-3">
               <a href="index.php?page=informe_facturas" class="btn btn-lg btn-block btn-info">
                  Beneficio: <?php echo $fsc->show_precio($fsc->anyo['beneficios']);?>

                  <br/>
                  <?php if( $fsc->anyo['beneficios_mejora']>0 ){ ?>

                     +<?php echo $fsc->show_numero($fsc->anyo['beneficios_mejora']);?> %
                  <?php }elseif( $fsc->anyo['beneficios_mejora']<0 ){ ?>

                     <?php echo $fsc->show_numero($fsc->anyo['beneficios_mejora']);?> %
                  <?php }else{ ?>

                     =
                  <?php } ?>

               </a>
               <p class="help-block">
                  <b><?php echo $fsc->show_precio($fsc->anyo['beneficios_anterior']);?></b> el año anterior hasta este mismo día.
               </p>
            </div>
         </div>
      </div>
      <div role="tabpanel" class="tab-pane" id="dashconfig">
         <form action="<?php echo $fsc->url();?>" method="post" class="form">
            <div class="row">
               <div class="col-sm-12">
                  <br/>
                  <p class="help-block">
                     Desde aquí puedes modificar la <b>configuración</b> del dashboard.
                  </p>
               </div>
            </div>
            <div class="row">
               <div class="col-sm-5">
                  <div class="form-group">
                     <div class="radio">
                        <label>
                           <?php if( $fsc->anterior=='periodo' ){ ?>

                           <input type="radio" name="anterior" value="periodo" checked=""/>
                           <?php }else{ ?>

                           <input type="radio" name="anterior" value="periodo"/>
                           <?php } ?>

                           Comprarar con el <b>periodo anterior</b> (comprar este mes con el anterior).
                        </label>
                     </div>
                     <div class="radio">
                        <label>
                           <?php if( $fsc->anterior=='año' ){ ?>

                           <input type="radio" name="anterior" value="año" checked=""/>
                           <?php }else{ ?>

                           <input type="radio" name="anterior" value="año"/>
                           <?php } ?>

                           Comparar con el periodo del <b>año anterior</b> (comparar este mes con el
                           mismo mes del año anterior).
                        </label>
                     </div>
                  </div>
               </div>
               <div class="col-sm-5">
                  <div class="form-group">
                     <div class="radio">
                        <label>
                           <?php if( $fsc->neto=='FALSE' ){ ?>

                           <input type="radio" name="neto" value="FALSE" checked=""/>
                           <?php }else{ ?>

                           <input type="radio" name="neto" value="FALSE"/>
                           <?php } ?>

                           Mostrar valores <b>brutos</b> en compras y ventas.
                        </label>
                     </div>
                     <div class="radio">
                        <label>
                           <?php if( $fsc->neto=='TRUE' ){ ?>

                           <input type="radio" name="neto" value="TRUE" checked=""/>
                           <?php }else{ ?>

                           <input type="radio" name="neto" value="TRUE"/>
                           <?php } ?>

                           Mostrar valores <b>netos</b> en compras y ventas.
                        </label>
                     </div>
                  </div>
               </div>
               <div class="col-sm-2 text-right">
                  <button type="submit" class="btn btn-sm btn-primary">
                     <span class="glyphicon glyphicon-floppy-disk"></span>
                     <span class="hidden-xs">&nbsp;Guardar</span>
                  </button>
               </div>
            </div>
            <div class="row">
               <div class="col-sm-12">
                  <br/><br/>
               </div>
            </div>
         </form>
      </div>
   </div>
</div>

<br/>

<ul class="nav nav-tabs" role="tablist">
   <li role="presentation" class="active">
      <a href="#comunidad" aria-controls="comunidad" role="tab" data-toggle="tab">
         <i class="fa fa-users"></i> Comunidad
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
   <div role="tabpanel" class="tab-pane active" id="comunidad">
      <br/>
      <div class="container-fluid">
         <div class="row">
            <div class="col-sm-3">
               <form action="https://www.facturascripts.com/buscar" method="post" target="_blank" class="form">
                  <div class="form-group">
                     ¿Tienes dudas?
                     <div class="input-group">
                        <input type="text" name="query" placeholder="Buscar en facturascripts.com" class="form-control" autocomplete="off"/>
                        <span class="input-group-btn">
                           <button type="submit" class="btn btn-primary">
                              <span class="glyphicon glyphicon-search"></span>
                           </button>
                        </span>
                     </div>
                  </div>
               </form>
               <?php if( $fsc->anyo['ventas_neto']==0 ){ ?>

               <a href="https://www.facturascripts.com/documentacion#primeros-pasos" target="_blank" class="btn btn-block btn-default">
                  <i class="fa fa-info-circle fa-3x" aria-hidden="true"></i>
                  <br/>Primeros pasos
               </a>
               <p class="help-block">
                  En la web de FacturaScripts encontrarás la documentación para resolver
                  cualquier duda que tengas: presupuestos y pedidos, IVA, IRPF,
                  <b>Recargo de equivalencia</b>, traducciones, configuración de decimales, etc...
               </p>
               <br/>
               <?php } ?>

               <?php if( !$fsc->empresa->can_send_mail() ){ ?>

               <a href="index.php?page=admin_empresa#email" class="btn btn-block btn-warning">
                  <i class="fa fa-envelope fa-3x" aria-hidden="true"></i>
                  <br/>Configurar email
               </a>
               <p class="help-block">
                  ¿Quieres enviar emails desde FacturaScripts?
                  Puedes usar tu cuenta de <b>gmail</b>, <b>hotmail</b> o tu dominio personalizado
                  para enviar documentos cómodamente desde FacturaScripts.
               </p>
               <br/>
               <?php } ?>

               <a href="index.php?page=admin_home#descargas" class="btn btn-block btn-default">
                  <i class="fa fa-puzzle-piece fa-3x" aria-hidden="true"></i>
                  <br/>Plugins
               </a>
               <p class="help-block">
                  ¿Te falta algo? Revisa la lista de plugins disponibles en la sección
                  descargas del panel de control. Hay de todo, y con un desarrollo constante ;-)
               </p>
               <br/>
               <a href="https://www.facturascripts.com/ideas#mejoras" target="_blank" class="btn btn-block btn-default">
                  <i class="fa fa-trophy fa-3x" aria-hidden="true"></i>
                  <br/>Mejoras
               </a>
               <p class="help-block">
                  Tenemos una serie de mejoras en el horno que te pueden interesar,
                  pero necesitan financiación ¿Te apuntas? Sólo necesitas <b>5 €</b>.
               </p>
            </div>
            <div class="col-sm-5">
               <?php $loop_var1=$fsc->noticias; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <blockquote class="bg-info">
                  <div class="row">
                     <div class="col-sm-11">
                        <p class="help-block">
                           <a href="<?php echo $value1->url;?>" target="_blank"><?php echo $value1->texto;?></a><br/>
                           <small>
                              <?php echo $value1->fecha;?> &nbsp; <i class="fa fa-user"></i> <?php echo $value1->autor;?>

                           </small>
                        </p>
                     </div>
                     <div class="col-sm-1 text-right">
                        <i class="fa fa-newspaper-o" aria-hidden="true"></i>
                     </div>
                  </div>
               </blockquote>
               <?php } ?>

            </div>
            <div class="col-sm-4">
               <?php $loop_var1=$fsc->consejos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

               <blockquote class="bg-success">
                  <div class="row">
                     <div class="col-sm-11">
                        <b><?php echo $value1['titulo'];?></b>
                        <br/><?php echo $value1['html'];?>

                     </div>
                     <div class="col-sm-1 text-right"><?php echo $value1['icono'];?></div>
                  </div>
               </blockquote>
               <?php } ?>

            </div>
         </div>
      </div>
   </div>
   <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <?php if( $value1->type=='tab' ){ ?>

      <div role="tabpanel" class="tab-pane" id="ext_<?php echo $value1->name;?>">
         <iframe src="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" width="100%" height="2000" frameborder="0"></iframe>
      </div>
      <?php } ?>

   <?php } ?>

</div>

<div class="modal fade" id="modal_303" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Modelos 303 y 390</h4>
         </div>
         <div class="modal-body">
            Los modelos 303 y 390 para la hacienda española los tienes disponibles en el
            <b>plugin modelos_303_390</b>.
         </div>
         <?php if( in_array('modelos_303_390', $GLOBALS['plugins']) ){ ?>

         <div class="panel-footer">
            <a href="index.php?page=contabilidad_regusiva" class="btn btn-sm btn-default">
               <span class="glyphicon glyphicon-book"></span>&nbsp; Modelo 303
            </a>
            <a href="index.php?page=modelo_390" class="btn btn-sm btn-default">
               <span class="glyphicon glyphicon-book"></span>&nbsp; Modelo 390
            </a>
         </div>
         <?php }else{ ?>

         <div class="panel-footer">
            <a href="index.php?page=admin_home#descargas" class="btn btn-sm btn-default">
               <i class="fa fa-puzzle-piece" aria-hidden="true"></i>&nbsp; Plugins
            </a>
         </div>
         <?php } ?>

      </div>
   </div>
</div>

<div class="modal fade" id="modal_347" tabindex="-1" role="dialog">
   <div class="modal-dialog" role="document">
      <div class="modal-content">
         <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
               <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Modelos 303 y 390</h4>
         </div>
         <div class="modal-body">
            El modelo 347 para la hacienda española lo tienes disponible en el
            <b>plugin modelo_347</b>.
         </div>
         <?php if( in_array('modelo_347', $GLOBALS['plugins']) ){ ?>

         <div class="panel-footer">
            <a href="index.php?page=informe_347" class="btn btn-sm btn-default">
               <span class="glyphicon glyphicon-book"></span>&nbsp; Modelo 347
            </a>
         </div>
         <?php }else{ ?>

         <div class="panel-footer">
            <a href="index.php?page=admin_home#descargas" class="btn btn-sm btn-default">
               <i class="fa fa-puzzle-piece" aria-hidden="true"></i>&nbsp; Plugins
            </a>
         </div>
         <?php } ?>

      </div>
   </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>