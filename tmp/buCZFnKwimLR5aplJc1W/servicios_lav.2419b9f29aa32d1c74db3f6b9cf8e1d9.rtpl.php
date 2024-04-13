<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript" src="<?php echo $fsc->get_js_location('servicios_lav.js');?>"></script>
<input type="hidden" value="<?php echo $fsc->bandera_aviso;?>" name="bandera_aviso" id="bandera_aviso">
<div class="container-fluid" style="margin-bottom: 10px;">
   <div class="row">
      <div class="col-xs-12">
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
        
      </div>
   </div>
</div>


<!-- Nav tabs -->
<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item active">
    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true" >Nuevo Combo</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Listado de Combos</a>
  </li>
</ul>



<div class="tab-content">
  <div class="tab-pane active tarjetas" id="home" role="tabpanel" aria-labelledby="home-tab">
        <div class="tarjetas col col-lg-10">
    <input type="hidden" class="form-control"  name="bandera"  id="bandera" value="<?php echo $fsc->bandera;?>">
      <form id="form_servicios" action="<?php echo $fsc->url();?>" method="post">
          
          <input type="hidden" class="form-control"  name="fecha"  id="fecha" value="<?php echo $fsc->today();?>">
          <input type="hidden" class="form-control"  name="hora"  id="hora" value="<?php echo $fsc->hour();?>">
          <input type="hidden" class="form-control"  name="bandera_lav"  id="bandera_lav" value="<?php echo $fsc->bandera;?>">
          <input type="hidden" class="form-control"  name="actualizar_lav"  id="actualizar_lav" value="<?php echo $fsc->actualiza_ban;?>">
          <input type="hidden" class="form-control"  name="cant_conjunto"  id="cant_conjunto" <?php if( $fsc->conjunto_combo_cant>0 ){ ?> value="<?php echo $fsc->conjunto_combo_cant;?>" <?php }else{ ?> value="0" <?php } ?>>
        
        
          <div class="form-group row">
            <!--<label for="cod_servicio" class="col-sm-2 col-form-label">Código </label>-->
            <div class="col-sm-10">
                <input type="hidden" class="form-control" name="cod_servicio" id="cod_servicio" placeholder="Código combo"   value="<?php echo $fsc->consultar_indi('idcombo');?>" >
            </div>
          </div>  

          <div class="form-group row">
              
            <label for="des_servicio" class="col-sm-2 col-form-label">Descripción</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="des_servicio" required=""  id="des_servicio" placeholder="Descripción combo" value="<?php echo $fsc->consultar_indi('descripcion');?>" >
            </div>
          </div>

          <div class="form-group row" id="servicios_adicion">
              <div class="col-sm-2" ></div>
            <div class="col-sm-5">
                <div class="row">
                      <div class="col-sm-10">
                    Servicios
                    <select  class="form-control"  name="ad1"  id="ad1" >
                            <option value="0" selected="selected">---</option>
                        <?php $loop_var1=$fsc->adi1; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                <option value="<?php echo $value1['descripcion'];?>"><?php echo $value1['descripcion'];?></option>
                        <?php } ?>

                    </select>
                    </div>
                    <div class="col-sm-1">
                        ADD
                    <a id="bot_servicios" type="submit" href="#" class="btn btn-default btn-sm">+</a>
                    </div>
                   
                </div>  
            </div>
              
              <div class="col-sm-5">
                  <div class="row">
                      <div class="col-sm-10">
                Artículos
               <select  class="form-control"  name="art1"  id="art1" >
                        <option value="0" selected="selected">---</option>
                    <?php $loop_var1=$fsc->art1; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <option value="<?php echo $value1['descripcion'];?>"><?php echo $value1['descripcion'];?></option>
                    <?php } ?>

               </select>
                      </div>
                <div class="col-sm-1">
                    ADD
               <a id="bot_articulos" type="submit" href="#" class="btn btn-default btn-sm">+</a>
                </div>
                  </div>
            </div>
          </div>
          
          <div class="form-group row" >
            
              <div class="row">
              <div class="col-sm-2"></div>
              <div class="col-sm-10" id="articulos_adicion">
                  
                  <?php if( $fsc->actualiza_ban ){ ?>

                  
                  <?php $loop_var1=$fsc->consulta_combos_conjunto; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                    <input type="hidden" class="form-control"  name="cant_act"  id="cant_act" value="<?php echo $fsc->cont_lav++;?>">
                    <div class='col-sm-2' id="frag<?php echo $fsc->cont_lav;?>">
                        <input type='text' class='form-control'  name="ad<?php echo $fsc->cont_lav;?>"    id="ad<?php echo $fsc->cont_lav;?>"   value="<?php echo $fsc->busq_articulo($value1['articulo']);?>" readonly='readonly'> <a href='#' class='glyphicon glyphicon-trash' onClick="return borrar_control(<?php echo $fsc->cont_lav;?>)"></a>
                        
                    </div>
                  <?php } ?>


                  <?php } ?>

                  
              </div>
              </div>

          </div>
          

          
          <div class="form-group row">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
          </div>
        </form>


        </div>
    </div>
    
    <div class="tab-pane tarjetas" id="profile" role="tabpanel" aria-labelledby="profile-tab">
        <table class="table">
            <thead>
              <tr>
                <th scope="col">REFERENCIA</th>
                <th scope="col">Descripción</th>
                <th scope="col" class="text-right">Valor</th>
                <th scope="col">Servicios / Artículos</th>
                <th scope="col">Editar</th>
              </tr>
            </thead>
            <tbody>
              <?php $loop_var1=$fsc->consulta_combos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>  
                <tr>
                  <td>COMBO<?php echo $value1['idcombo'];?></td>
                  <td><?php echo $value1['descripcion'];?></td>
                  <td class="text-right"><?php echo $fsc->formato_moneda($fsc->valor_combo($value1['idcombo']));?></td>
                  <td><?php echo $fsc->conjunto_consulta($value1['idcombo']);?></td>
                  <td>
                      <div class="form-group row">
                          <?php if( $fsc->allow_delete ){ ?>

                          <a href="<?php echo $fsc->url().'&ref='.$value1['idcombo'];?>"><button class="btn btn-sm btn-primary">+</button></a>
                          <a href="#" onclick="return eliminar_combo(<?php echo $value1['idcombo'];?>)"><button class="btn btn-sm btn-danger"><span class="glyphicon glyphicon-trash"></span></button></a>
                          <?php } ?>

                      </div>
                  </td>
                </tr>
              <?php } ?>

            </tbody>
        </table>
    </div>
    
</div>      
      
<script type="text/javascript">
    
    function eliminar_combo(id){
        
        bootbox.confirm({
            message: '¿Realmente desea eliminar el combo?',
            title: '<b>Atención</b>',
            callback: function(result) {
               if (result) {
                  window.location.href = '<?php echo $fsc->url();?>&eliminar='+id;
               }
            }
         });
    }
    
</script>


<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>



