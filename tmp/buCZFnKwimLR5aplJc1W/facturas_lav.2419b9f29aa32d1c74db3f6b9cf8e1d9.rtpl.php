<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>



<script type="text/javascript">
    
    function pagar_fact_contado(){
        bootbox.confirm("¿Segur@ quiere pagar todas las facturas contado?", function(result){ 
            if(result){
                location.href = "<?php echo $fsc->url();?>"+"&pagar_facturas_all=1";
            }
        });
    }
    
</script>


<div class="container-fluid" style="margin-bottom: 10px;">
   <div class="row">
      <div class="col-md-2">
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
       <div class="col-md-8">
       
        
            <form action="<?php echo $fsc->url();?>" method="POST">
                
                <div class="col-lg-4 text-right">
                <label class="control-label"> <h5>Ordenar por : </h5> </label>
                </div>
                <div class="col-lg-4">
                <select class="form-control" id="sele_fact" name="sele_fact" onchange="this.form.submit()">
                    <option value="cli" <?php if( $fsc->sele_fact=='cli' ){ ?> selected="" <?php } ?>>Clientes</option>
                    <option value="prov" <?php if( $fsc->sele_fact=='prov' ){ ?> selected="" <?php } ?>>Proveedores</option>
                </select>
                </div>
                
                <div class="col-lg-4 text-right">
                    <input type="text" class="form-control" id="buscar" name="buscar" value="<?php echo $fsc->buscar;?>" placeholder="Buscar...">
                </div>
            </form>
        </div>
       <div class="col-md-2 text-right">
            <button type="button" onclick="return pagar_fact_contado()" class="btn btn-primary" title="Pagar todas las facturas contado" >
                    <span class="fa fa-money"></span>
             </button>
       </div>
           
       
   </div>
</div>

    



<!-- Nav tabs -->
<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item active">
    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true" >En proceso</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Crédito</a>
  </li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
  <div class="tab-pane active tarjetas" id="home" role="tabpanel" aria-labelledby="home-tab">
      
			
           <?php $loop_var1=$fsc->enProceso; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

		   <div class="row" >
                       <h3><?php if( $value1['nombre']=='0' ){ ?> ARTÍCULOS <?php }else{ ?>  <?php echo $value1['nombre'];?> <?php } ?><a href="<?php echo $fsc->url();?>&pagar_facturas=<?php echo $value1['nombre'];?>"> <span class="fa fa-money"></span> </a></h3>
				
				<?php $loop_var2=$fsc->listado_facturas($value1['nombre'],   1); $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

					<div class="col-12 col-md-2">
						<div class="card color<?php echo $key1;?>">
						  <div class="card-header">
						   <?php echo $value2['nombrecliente'].' / ';?> <?php if( $value2['proveedor_servicio']=='0' ){ ?> ARTÍCULOS <?php }else{ ?>  <?php echo $value2['proveedor_servicio'];?> <?php } ?> | $<?php echo $value2['total'];?>

						  </div>
						  <div class="card-body">
							<p class="card-text"><?php echo $value2['observaciones'];?></p>
                                                        <div class="btn-group">
							<a href="index.php?page=ventas_factura&id=<?php echo $value2['idfactura'];?>"  class="btn btn-primary">PAGAR</a>
                                                        <a href="index.php?page=tpv_recambios&factura_editar=<?php echo $value2['idfactura'];?>"  class="btn btn-success">EDITAR</a>
                                                        </div>
						  </div>
						</div>
					</div>
				<?php } ?>

			</div>
			<hr />
           <?php } ?>

        	
       
    </div>

    <div class="tab-pane tarjetas" id="profile" role="tabpanel" aria-labelledby="profile-tab">
       
           <?php $loop_var1=$fsc->creditos; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

		   <div class="row">
			<h3 ><?php if( $value1['nombre']=='0' ){ ?> ARTÍCULOS <?php }else{ ?>  <?php echo $value1['nombre'];?> <?php } ?></h3>
				
				<?php $loop_var2=$fsc->listado_facturas($value1['nombre'],   2); $counter2=-1; if($loop_var2) foreach( $loop_var2 as $key2 => $value2 ){ $counter2++; ?>

					<div class="col-12 col-md-2">
						<div class="card color<?php echo $key1;?>">
						  <div class="card-header">
						   <?php echo $value2['nombrecliente'].' / ';?> <?php if( $value2['proveedor_servicio']=='0' ){ ?> ARTÍCULOS <?php }else{ ?>  <?php echo $value2['proveedor_servicio'];?> <?php } ?>

						  </div>
						  <div class="card-body">
							<p class="card-text"><?php echo $value2['observaciones'];?></p>
                                                        <div class="btn-group">
							<a href="index.php?page=ventas_factura&id=<?php echo $value2['idfactura'];?>" class="btn btn-primary">PAGAR</a>
                                                        <a href="index.php?page=tpv_recambios&factura_editar=<?php echo $value2['idfactura'];?>"  class="btn btn-success">EDITAR</a>
                                                        </div>
						  </div>
						</div>
					</div>
				<?php } ?>

			</div>
           <?php } ?>

    </div>        
    
</div>



<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>



