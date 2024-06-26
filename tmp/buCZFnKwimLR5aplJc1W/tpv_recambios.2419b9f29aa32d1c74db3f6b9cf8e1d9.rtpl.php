<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<?php if( $fsc->agente ){ ?>

   <?php if( $fsc->caja ){ ?>

      <?php if( $fsc->caja->codagente!=$fsc->user->codagente ){ ?>

      <div class="container-fluid">
         <div class="row">
            <div class="col-sm-12">
               <div class="thumbnail">
                  <img src='<?php  echo FS_PATH;?>view/img/big_lock.png' alt="acceso denegado"/>
               </div>
            </div>
         </div>
      </div>
      <?php }elseif( !$fsc->cliente_s ){ ?>

      <div class="container-fluid">
         <div class="row">
            <div class="col-sm-12">
               <div class="alert alert-danger">
                  No hay clientes. Debes añadir al menos uno, por ejemplo <b>Contado</b>.
               </div>
               <div class="thumbnail">
                  <img src='<?php  echo FS_PATH;?>view/img/fuuu_face.png' alt="acceso denegado"/>
               </div>
            </div>
         </div>
      </div>
      <?php }else{ ?>

         <?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("block/tpv_recambios2") . ( substr("block/tpv_recambios2",-1,1) != "/" ? "/" : "" ) . basename("block/tpv_recambios2") );?>

      <?php } ?>

   <?php }elseif( $fsc->terminal ){ ?>

   <script type="text/javascript">
      $(document).ready(function() {
         document.f_caja.d_inicial.select();
      });
   </script>
   <form name="f_caja" action="<?php echo $fsc->url();?>" method="post" class="form">
       
      <input type="hidden" name="terminal" value="<?php echo $fsc->terminal->id;?>"/>
      <div class="container-fluid">
         <div class="row">
            <div class="col-sm-12">
               <div class="page-header">
                  <h1>
                     <i class="fa fa-desktop" aria-hidden="true"></i>&nbsp;
                     Terminal <?php echo $fsc->terminal->id;?>

                  </h1>
                  <p class="help-block">
                     El TPV imprime tickets y por tanto necesitas una impresora
                     de tickets, además de la aplicación
                     <a target="_blank" href="https://www.facturascripts.com/descargar?remoteprinter=TRUE">Remote printer</a>
                     para poder imprimir.
                  </p>
               </div>
            </div>
         </div>
         <div class="row">
            <div class="col-sm-4">
               <div class="panel panel-primary">
                  <div class="panel-heading">
                     <h3 class="panel-title">¿Cuanto dinero hay en la caja?</h3>
                  </div>
                  <div class="panel-body">
                     <div class="form-group">
                        <input type="number" step="any" name="d_inicial" value="0.00" class="form-control" autocomplete="off"/>
                        <p class="help-block">
                           Si el
                           <a target="_blank" href="https://www.facturascripts.com/descargar?remoteprinter=TRUE">remote printer</a>
                           está instalado y tienes la impresora de tickets
                           y el cajón portamonedas conectados, se debe haber abierto el cajón.
                           Es el momento de que introduzcas el cambio que vas a usar hoy.
                        </p>
                     </div>
                  </div>
                  <div class="panel-footer">
                     <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                        <span class="glyphicon glyphicon-play"></span>&nbsp; Continuar...
                     </button>
                  </div>
               </div>
            </div>
            <div class="col-sm-3">
               <div class="thumbnail">
                  <img src="<?php  echo FS_PATH;?>plugins/facturacion_base/view/img/cajon.jpg" alt="tpv"/>
               </div>
            </div>
         </div>
      </div>
   </form>
   <?php }else{ ?>

   <div class="container-fluid">
      <div class="row">
         <div class="col-sm-12">
            <div class="page-header">
               <h1>
                  <i class="fa fa-desktop" aria-hidden="true"></i>&nbsp;
                  Elige un terminal para empezar
                  <a href="#" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal_ayuda_terminal">
                     <span class="glyphicon glyphicon-question-sign" aria-hidden="true"></span>
                  </a>
               </h1>
               <p class="help-block">
                  Una vez abierta la caja de un terminal, ningún otro usuario podrá
                  usarlo hasta que se cierre la caja.
               </p>
            </div>
         </div>
      </div>
      <div class="row">
         <?php $loop_var1=$fsc->results; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

         <div class="col-sm-4">
            <a href="<?php echo $fsc->url();?>&terminal=<?php echo $value1->id;?>" class="btn btn-block btn-default">
               <i class="fa fa-desktop" aria-hidden="true"></i>&nbsp; Terminal <?php echo $value1->id;?>

            </a>
         </div>
         <?php }else{ ?>

         <div class="col-sm-6">
            <a href="index.php?page=tpv_caja#terminales" class="btn btn-block btn-info">
               <span class="glyphicon glyphicon-wrench" aria-hidden="true"></span>&nbsp; Administrar terminales
            </a>
            <p class="help-block">
               Los terminales son la suma de PC + impresora de tickets + cajón
               portamonedas. Ahora mismo no tiene ninguno disponible, bien porque
               no hay configurado ningún terminal, o bien porque están todos
               ocupados.
            </p>
         </div>
         <?php } ?>

      </div>
   </div>
   
   <div class="modal fade" id="modal_ayuda_terminal" tabindex="-1" role="dialog">
      <div class="modal-dialog" role="document">
         <div class="modal-content">
            <div class="modal-header">
               <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
               </button>
               <h4 class="modal-title">¿Qué es un terminal?</h4>
            </div>
            <div class="modal-body">
               <p class="help-block">
                  Los terminales son la suma de PC + impresora de tickets + cajón portamonedas.
               </p>
               <div class="thumbnail">
                  <img src="<?php  echo FS_PATH;?>plugins/facturacion_base/view/img/cajon.jpg" alt="tpv"/>
               </div>
               <p class="help-block">
                  Puedes configurar los terminales desde el menú <b>TPV &gt; Arqueos y terminales</b>.
               </p>
            </div>
         </div>
      </div>
   </div>
   <?php } ?>

<?php }else{ ?>

<div class="thumbnail">
   <img src='<?php  echo FS_PATH;?>view/img/big_lock.png' alt="Acceso denegado"/>
</div>
<?php } ?>


<?php if( $fsc->terminal ){ ?>

<div class="hidden">
   <img src="http://localhost:10080?terminal=<?php echo $fsc->terminal->id;?>" alt="remote-printer"/>
</div>
<?php } ?>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>