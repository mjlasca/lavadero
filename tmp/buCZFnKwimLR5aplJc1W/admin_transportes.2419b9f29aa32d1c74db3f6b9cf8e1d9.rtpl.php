<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
   function delete_agencia(cod) {
      bootbox.confirm({
         message: '¿Realmente desea eliminar la agencia ' + cod + '?',
         title: '<b>Atención</b>',
         callback: function(result) {
            if (result) {
               window.location.href = '<?php echo $fsc->url();?>&delete=' + encodeURIComponent(cod);
            }
         }
      });
   }
</script>

<div class="container-fluid">
   <div class="row">
      <div class="col-sm-12">
         <div class="page-header">
            <h1>
               <span class="glyphicon glyphicon-plane"></span>
               Agencias de transporte
               <a class="btn btn-xs btn-default" href="<?php echo $fsc->url();?>" title="Recargar la página">
                  <span class="glyphicon glyphicon-refresh"></span>
               </a>
               <span class="btn-group">
               <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                  <?php if( $value1->type=='button' ){ ?>

                  <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-xs btn-default"><?php echo $value1->text;?></a>
                  <?php } ?>

               <?php } ?>

               </span>
            </h1>
            <p class="help-block">
               Aquí puedes dar de alta las agencias que uses para los envíos.
               Por ejemplo UPS, MRW, SEUR o cualquier otra.
            </p>
         </div>
      </div>
   </div>
</div>

<div class="table-responsive">
   <table class="table table-hover">
      <thead>
         <tr>
            <th class="text-left" width="150">Código</th>
            <th class="text-left">Nombre</th>
            <th class="text-left">Teléfono</th>
            <th class="text-left">Web</th>
            <th class="text-center">Activo</th>
            <th width="120"></th>
         </tr>
      </thead>
      <?php $loop_var1=$fsc->listado; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

      <form action="<?php echo $fsc->url();?>" method="post" class="form">
         <tr<?php if( !$value1->activo ){ ?> class="danger"<?php } ?>>
            <td><input type="text" name="codtrans" value="<?php echo $value1->codtrans;?>" class="form-control" readonly=""/></td>
            <td><input type="text" name="nombre" value="<?php echo $value1->nombre;?>" class="form-control" maxlength="100" autocomplete="off"/></td>
            <td><input type="text" name="telefono" value="<?php echo $value1->telefono;?>" class="form-control" maxlength="20" autocomplete="off"/></td>
            <td><input type="text" name="web" value="<?php echo $value1->web;?>" class="form-control" maxlength="100" autocomplete="off"/></td>
            <td class="text-center">
               <div class="checkbox">
                  <label>
                     <?php if( $value1->activo ){ ?>

                     <input type="checkbox" name="activo" value="TRUE" checked=""/>
                     <?php }else{ ?>

                     <input type="checkbox" name="activo" value="TRUE"/>
                     <?php } ?>

                  </label>
               </div>
            </td>
            <td class="text-right">
               <div class="btn-group">
                  <?php if( $fsc->allow_delete ){ ?>

                  <button class="btn btn-sm btn-danger" type="button" title="Eliminar" onclick="delete_agencia('<?php echo $value1->codtrans;?>')">
                     <span class="glyphicon glyphicon-trash"></span>
                  </button>
                  <?php } ?>

                  <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();" title="Guardar">
                     <span class="glyphicon glyphicon-floppy-disk"></span>
                  </button>
               </div>
            </td>
         </tr>
      </form>
      <?php } ?>

      <form action="<?php echo $fsc->url();?>" method="post" class="form">
         <tr class="info">
            <td><input type="text" name="codtrans" class="form-control" maxlength="8" autocomplete="off" placeholder="Nuevo código" required=""/></td>
            <td><input type="text" name="nombre" class="form-control" maxlength="100" autocomplete="off" placeholder="Nueva agencia..."/></td>
            <td><input type="text" name="telefono" class="form-control" maxlength="20" autocomplete="off" placeholder="Teléfono"/></td>
            <td><input type="text" name="web" class="form-control" maxlength="100" autocomplete="off" placeholder="Web"/></td>
            <td class="text-center">
               <div class="checkbox">
                  <label>
                     <input type="checkbox" name="activo" value="TRUE" checked=""/>
                  </label>
               </div>
            </td>
            <td class="text-right">
               <button class="btn btn-sm btn-primary" type="submit" title="Nueva">
                  <span class="glyphicon glyphicon-plus-sign"></span>
                  <span class="hidden-sm">&nbsp;Nueva</span>
               </button>
            </td>
         </tr>
      </form>
   </table>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>