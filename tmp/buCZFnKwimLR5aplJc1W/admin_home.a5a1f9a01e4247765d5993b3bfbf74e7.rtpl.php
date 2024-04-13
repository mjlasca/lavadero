<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<script type="text/javascript">
    function fs_marcar_todo() {
        $('#f_enable_pages input:checkbox').prop('checked', true);
    }
    function eliminar(name) {
        bootbox.confirm({
            message: '¿Realmente desea eliminar este plugin?',
            title: '<b>Atención</b>',
            callback: function (result) {
                if (result) {
                    window.location.href = '<?php echo $fsc->url();?>&delete_plugin=' + name + '#plugins';
                }
            }
        });
    }
    function descargar_plugin_inestable(id) {
        bootbox.confirm({
            message: 'Este plugin está marcado como inestable. Significa que <b>no se recomienda su uso</b>,\n\
 que todavía está en desarrollo y puede contener errores. ¿Estas seguro de que quieres descargarlo?',
            title: '<b>Atención</b>',
            callback: function (result) {
                if (result) {
                    window.location.href = '<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&download2=' + id + '#plugins';
                }
            }
        });
    }
    $(document).ready(function () {
        <?php if( $fsc->step=='1' ){ ?>

        $('#tab_panel_control a[href="#t_descargas"]').tab('show');
        <?php } ?>


        if (window.location.hash.substring(1) == 'paginas') {
            $('#tab_panel_control a[href="#t_paginas"]').tab('show');
        } else if (window.location.hash.substring(1) == 'plugins') {
            $('#tab_panel_control a[href="#t_plugins"]').tab('show');
        } else if (window.location.hash.substring(1) == 'descargas') {
            $('#tab_panel_control a[href="#t_descargas"]').tab('show');
        } else if (window.location.hash.substring(1) == 'avanzado') {
            $('#tab_panel_control a[href="#t_avanzado"]').tab('show');
        }

        $('#marcar_todo_enabled').click(function () {
            var checked = $(this).prop('checked');
            $("#f_enable_pages input[name='enabled[]']").prop('checked', checked);
        });
    });
</script>

<?php if( !$fsc->step ){ ?>

<div class="well">
    <div class="page-header" style="margin-top: 0px;">
        <h1>
            Bienvenido a FacturaScripts
            <small><?php echo $fsc->version();?></small>
        </h1>
    </div>
    <p class="help-block">
        Ya tienes instalado el núcleo. Como ves, el núcleo permite gestionar páginas,
        plugins y usuarios. Pulsa continuar para seguir con la instalación.
    </p>
    <a href="#" class="btn btn-sm btn-primary" onclick="fs_marcar_todo();f_enable_pages.submit();">
        <span class="glyphicon glyphicon-ok"></span>&nbsp; Continuar
    </a>
</div>
<?php }elseif( $fsc->step=='1' ){ ?>

<div class="well">
    <div class="page-header" style="margin-top: 0px;">
        <h1>
            <i class="fa fa-plug"></i> Plugins
        </h1>
    </div>
    <p class="help-block">
        El núcleo solamente se encarga de la gestión de usuarios, plugins y páginas.
        Para todo lo demás tienes los plugins.
        En la pestaña <b>descargas</b> tienes disponibles los principales plugins.
        Elige los que necesites. Hay de todo y la lista se actualiza periódicamente.
        <br/>
        Los plugins instalados los tienes en la pestaña <b>plugins</b>. Puedes añadir
        plugins manualmente, si lo deseas, y también puedes activar o desactivar,
        incluso eliminarlos.
        <br/>
        Además, toda la facturación y contabilidad básica ha sido movida al plugin
        <b>facturacion_base</b>. Puedes descargarlo automáticamente e instalarlo pulsando
        este botón.
    </p>
    <?php if( file_exists('plugins/facturacion_base') ){ ?>

    <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&enable=facturacion_base#plugins" class="btn btn-sm btn-primary">
        <span class="glyphicon glyphicon-ok"></span>&nbsp; Activar facturacion_base
    </a>
    <?php }else{ ?>

    <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&download=facturacion_base#plugins" class="btn btn-sm btn-primary">
        <span class="glyphicon glyphicon-download-alt"></span>&nbsp; Descargar facturacion_base
    </a>
    <?php } ?>

    <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&skip=TRUE#descargas" class="btn btn-sm btn-warning">
        <span class="glyphicon glyphicon-remove"></span>&nbsp; No, gracias
    </a>
</div>
<?php }else{ ?>

<div class="container-fluid" style="margin-top: 10px;">
    <div class="row">
        <div class="col-xs-6">
            <div class="btn-group">
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
            <?php if( !$fsc->disable_mod_plugins ){ ?>

            <div class="btn-group">
                <a href="#" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal_add_plugin">
                    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                    <span class="hidden-xs">&nbsp;Añadir</span>
                </a>
                <a class='btn btn-sm <?php if( $fsc->check_for_updates() ){ ?>btn-info<?php }else{ ?>btn-default<?php } ?>' href='updater.php' title='Actualizador'>
                    <span class="glyphicon glyphicon-upload"></span>
                    <span class="hidden-xs">&nbsp;Actualizador</span>
                </a>
            </div>
            <?php } ?>

            <div class="btn-group">
                <?php $loop_var1=$fsc->extensions; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                <?php if( $value1->type=='button' ){ ?>

                <a href="index.php?page=<?php echo $value1->from;?><?php echo $value1->params;?>" class="btn btn-sm btn-default"><?php echo $value1->text;?></a>
                <?php } ?>

                <?php } ?>

            </div>
        </div>
        <div class="col-xs-6 text-right">
            <h2 style="margin-top: 0px;">Panel de control</h2>
        </div>
    </div>
</div>
<?php } ?>


<div id="tab_panel_control" role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="active">
            <a href="#t_paginas" aria-controls="t_paginas" role="tab" data-toggle="tab">
                <i class="fa fa-check-square"></i>
                <span class="hidden-xs">&nbsp;Menú</span>
            </a>
        </li>
        <li role="presentation">
            <a href="#t_plugins" aria-controls="t_plugins" role="tab" data-toggle="tab">
                <i class="fa fa-plug"></i>
                <span class="hidden-xs">&nbsp;Plugins</span>
            </a>
        </li>
        <?php if( !$fsc->disable_mod_plugins ){ ?>

        <li role="presentation">
            <a href="#t_descargas" aria-controls="t_descargas" role="tab" data-toggle="tab">
                <span class="glyphicon glyphicon-download-alt"></span>
                <span class="hidden-xs">&nbsp;Descargas</span>
            </a>
        </li>
        <?php } ?>

        <li role="presentation">
            <a href="#t_avanzado" aria-controls="t_avanzado" role="tab" data-toggle="tab">
                <span class="glyphicon glyphicon-wrench"></span>
                <span class="hidden-xs">&nbsp;Avanzado</span>
            </a>
        </li>
    </ul>
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="t_paginas">
            <form id="f_enable_pages" action="<?php echo $fsc->url();?>" method="post" class="form">
                <input type="hidden" name="modpages" value="TRUE"/>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" width="40">
                                    <input id="marcar_todo_enabled" type="checkbox" name="c_enabled" value=""/>
                                </th>
                                <th class="text-left">Página</th>
                                <th class="text-left">Menú</th>
                                <th class="text-center">Existe</th>
                            </tr>
                        </thead>
                        <?php $loop_var1=$fsc->paginas; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <tr<?php if( !$value1->exists ){ ?> class="danger"<?php } ?>>
                            <td class="text-center">
                                <?php if( $value1->enabled ){ ?>

                                <input class="checkbox-inline" type="checkbox" name="enabled[]" value="<?php echo $value1->name;?>" checked=""/>
                                <?php }else{ ?>

                                <input class="checkbox-inline" type="checkbox" name="enabled[]" value="<?php echo $value1->name;?>"/>
                                <?php } ?>

                            </td>
                            <td>
                                <a target="_blank" href="<?php echo $value1->url();?>"><?php echo $value1->name;?></a>
                            </td>
                            <td>
                                <?php if( $value1->important ){ ?>

                                <span class="glyphicon glyphicon-star"></span> » <?php echo $value1->title;?>

                                <?php }elseif( $value1->show_on_menu ){ ?>

                                <span class="text-capitalize"><?php echo $value1->folder;?></span> » <?php echo $value1->title;?>

                                <?php }else{ ?>

                                -
                                <?php } ?>

                            </td>
                            <td class="text-center">
                                <?php if( $value1->exists ){ ?>

                                <span class="glyphicon glyphicon-ok"></span>
                                <?php }else{ ?>

                                <span class="glyphicon glyphicon-exclamation-sign" title="No se encuentra el controlador o pertenece a un plugin inactivo"></span>
                                <?php } ?>

                            </td>
                        </tr>
                        <?php } ?>

                    </table>
                </div>
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-xs-12 text-right">
                            <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                                <span class="glyphicon glyphicon-floppy-disk"></span>
                                <span class="hidden-xs">&nbsp;Guardar</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div role="tabpanel" class="tab-pane" id="t_plugins">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th class="text-left">Plugin</th>
                            <th class="text-left">Descripción</th>
                            <th class="text-right">Versión</th>
                            <th class="text-right">
                                <span class="glyphicon glyphicon-flash" aria-hidden="true" title="Prioridad"></span>
                            </th>
                        </tr>
                    </thead>
                    <?php $loop_var1=$fsc->plugin_advanced_list(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                    <!--<?php $tr_class=$this->var['tr_class']='';?>-->
                    <?php if( !$value1['compatible'] ){ ?><!--<?php echo $tr_class=' class="danger"';?>--><?php }elseif( $value1['enabled'] ){ ?><!--<?php echo $tr_class=' class="success"';?>--><?php } ?>

                    <tr<?php echo $tr_class;?>>
                        <td><?php echo $value1['name'];?></td>
                        <td>
                            <p><?php echo $value1['description'];?></p>
                            <?php if( $value1['wizard']!='' && $value1['enabled'] ){ ?>

                            <a href="index.php?page=<?php echo $value1['wizard'];?>" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-cog" aria-hidden="true"></span>&nbsp; Configurar
                            </a>
                            <?php } ?>

                            <a href="https://www.facturascripts.com/plugin/<?php echo $value1['name'];?>" target="_blank" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>&nbsp; más información
                            </a>
                            <?php if( $value1['enabled'] ){ ?>

                            <a class="btn btn-xs btn-warning" href="<?php echo $fsc->url();?>&disable=<?php echo $value1['name'];?>#plugins">
                                <span class="glyphicon glyphicon-remove"></span> Desactivar
                            </a>
                            <?php }else{ ?>

                            <div class="btn-group">
                                <?php if( !$fsc->disable_rm_plugins ){ ?>

                                <a class="btn btn-xs btn-danger" onclick="eliminar('<?php echo $value1['name'];?>')" title="eliminar plugin">
                                    <span class="glyphicon glyphicon-trash"></span>
                                </a>
                                <?php } ?>

                                <?php if( $value1['compatible'] ){ ?>

                                <a class="btn btn-xs btn-primary" href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&enable=<?php echo $value1['name'];?>#plugins">
                                    <span class="glyphicon glyphicon-ok"></span>&nbsp; Activar
                                </a>
                                <?php }else{ ?>

                                <a class="btn btn-xs btn-default" href="#" onclick="bootbox.alert({message: 'Le falta el archivo facturascripts.ini', title: '<b>Atención</b>'});">
                                    <span class="glyphicon glyphicon-remove"></span>&nbsp; Incompatible
                                </a>
                                <?php } ?>

                            </div>
                            <?php } ?>

                        </td>
                        <td class="text-right">
                            <a href="<?php  echo FS_COMMUNITY_URL;?>/index.php?page=community_changelog&plugin=<?php echo $value1['name'];?>&version=<?php echo $value1['version'];?>" target="_blank">
                                <?php echo $value1['version'];?>

                            </a>
                        </td>
                        <td class="text-right"><?php echo $value1['prioridad'];?></td>
                    </tr>
                    <?php }else{ ?>

                    <tr class="warning">
                        <td colspan="5">
                            No tienes plugins instalados.
                            <?php if( !$fsc->disable_mod_plugins ){ ?>

                            Mira en la pestaña <b>Descargas</b>.
                            <?php } ?>

                        </td>
                    </tr>
                    <?php } ?>

                </table>
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-xs-12">
                        <?php if( !$fsc->disable_add_plugins ){ ?>

                        <a href="#" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modal_add_plugin">
                            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
                            <span class="hidden-xs">&nbsp;Añadir</span>
                        </a>
                        <?php } ?>

                    </div>
                </div>
            </div>
        </div>
        <div role="tabpanel" class="tab-pane" id="t_descargas">
            <?php if( !$fsc->disable_mod_plugins ){ ?>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-left">Plugin</th>
                            <th class="text-left">Descripción</th>
                        </tr>
                    </thead>
                    <?php if( !$fsc->download_list2 ){ ?>

                    <?php $loop_var1=$fsc->download_list; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                    <!--<?php $tr_class=$this->var['tr_class']='';?>-->
                    <?php if( file_exists('plugins/'.$key1) ){ ?><!--<?php echo $tr_class=' class="success"';?>--><?php } ?>

                    <tr<?php echo $tr_class;?>>
                        <td class="text-right">
                            <span class="glyphicon glyphicon-bookmark" aria-hidden="true" title="Destacado"></span>
                        </td>
                        <td><?php echo $key1;?></td>
                        <td>
                            <?php echo $value1['description'];?><br/>
                            <a href="<?php echo $value1['url_repo'];?>" target="_blank">Web del proyecto</a>.
                            <?php if( file_exists('plugins/'.$key1) ){ ?>

                            <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>#plugins" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp; Instalado
                            </a>
                            <?php }else{ ?>

                            <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&download=<?php echo $key1;?>#plugins" class="btn btn-xs btn-primary">
                                <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>&nbsp; Descargar
                            </a>
                            <?php } ?>

                        </td>
                    </tr>
                    <?php } ?>

                    <?php } ?>

                    <?php $loop_var1=$fsc->download_list2; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                    <!--<?php $tr_class=$this->var['tr_class']='';?>-->
                    <?php if( file_exists('plugins/'.$value1->nombre) ){ ?>

                    <!--<?php echo $tr_class=' class="warning"';?>-->
                    <?php }elseif( !$value1->estable ){ ?>

                    <!--<?php echo $tr_class=' class="danger"';?>-->
                    <?php }elseif( $value1->tipo=='gratis' ){ ?>

                    <!--<?php echo $tr_class=' class="success"';?>-->
                    <?php } ?>

                    <tr<?php echo $tr_class;?>>
                        <td class="text-center" width='80'>
                            <?php if( $value1->imagen ){ ?>

                            <a href="https://www.facturascripts.com/plugin/<?php echo $value1->nombre;?>" target="_blank" class="thumbnail">
                                <img src="<?php echo $value1->imagen;?>" alt="<?php echo $value1->nombre;?>"/>
                            </a>
                            <?php }else{ ?>

                            <a href="https://www.facturascripts.com/plugin/<?php echo $value1->nombre;?>" target="_blank" class="btn btn-block btn-default">
                                <i class="fa fa-plug fa-3x" aria-hidden="true"></i>
                            </a>
                            <?php } ?>

                        </td>
                        <td>
                            <?php echo $value1->nombre;?> &nbsp; v<?php echo $value1->version;?>

                            <br/><br/>
                            <?php if( file_exists('plugins/'.$value1->nombre) ){ ?>

                            <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>#plugins" class="btn btn-xs btn-default">
                                <span class="glyphicon glyphicon-ok" aria-hidden="true"></span>&nbsp; Instalado
                            </a>
                            <?php }elseif( $value1->zip_link ){ ?>

                            <?php if( $value1->estable ){ ?>

                            <a href="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&download2=<?php echo $value1->id;?>#plugins" class="btn btn-xs btn-primary">
                                <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>&nbsp; Descargar
                            </a>
                            <?php }else{ ?>

                            <a href="#" class="btn btn-xs btn-primary" onclick="descargar_plugin_inestable('<?php echo $value1->id;?>')">
                                <span class="glyphicon glyphicon-download-alt" aria-hidden="true"></span>&nbsp; Descargar
                            </a>
                            <?php } ?>

                            <?php }else{ ?>

                            <a href="https://www.facturascripts.com/plugin/<?php echo $value1->nombre;?>" target="_blank" class="btn btn-xs btn-info">
                                <span class="glyphicon glyphicon-shopping-cart" aria-hidden="true"></span>&nbsp; Comprar
                            </a>
                            <?php } ?>

                        </td>
                        <td>
                            <?php echo nl2br($value1->descripcion); ?><br/>
                            Última modificación: <?php echo $value1->ultima_modificacion;?>

                            <?php if( !$value1->estable ){ ?>

                            <span class="label label-danger" title="inestable: no lo instales a menos que sepas lo que haces">inestable</span>
                            <?php } ?>

                        </td>
                    </tr>
                    <?php } ?>

                    <tr>
                        <td colspan="3">
                            <a href="https://www.facturascripts.com/plugins" target="_blank" class="btn btn-block btn-info">
                                <i class="fa fa-plug"></i>&nbsp; Más plugins...
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
            <?php } ?>

        </div>
        <div role="tabpanel" class="tab-pane" id="t_avanzado">
            <form class="form" action="<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>#avanzado" method="post">
                <div class="container-fluid" style="margin-top: 10px;">
                    <div class="row">
                        <div class="col-md-3 col-sm-4">
                            <div class="form-group">
                                Zona horaria:
                                <select class="form-control" name="zona_horaria">
                                    <?php $loop_var1=$fsc->get_timezone_list(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                    <!--<?php $selected=$this->var['selected']='';?>-->
                                    <?php if( $value1['zone']==$GLOBALS['config2']['zona_horaria'] ){ ?>

                                    <option value="<?php echo $value1['zone'];?>" selected=""><?php echo $value1['diff_from_GMT'];?> - <?php echo $value1['zone'];?></option>
                                    <?php }else{ ?>

                                    <option value="<?php echo $value1['zone'];?>"><?php echo $value1['diff_from_GMT'];?> - <?php echo $value1['zone'];?></option>
                                    <?php } ?>

                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Portada:
                                <select name="homepage" class="form-control">
                                    <?php $loop_var1=$fsc->paginas; $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                    <?php if( $value1->name==$GLOBALS['config2']['homepage'] ){ ?>

                                    <option value="<?php echo $value1->name;?>" selected=""><?php echo $value1->name;?></option>
                                    <?php }else{ ?>

                                    <option value="<?php echo $value1->name;?>"><?php echo $value1->name;?></option>
                                    <?php } ?>

                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Decimales de los totales:
                                <select name="nf0" class="form-control">
                                    <?php $loop_var1=$fsc->nf0(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                    <?php if( $value1==$GLOBALS['config2']['nf0'] ){ ?>

                                    <option value="<?php echo $value1;?>" selected=""><?php echo $value1;?></option>
                                    <?php }else{ ?>

                                    <option value="<?php echo $value1;?>"><?php echo $value1;?></option>
                                    <?php } ?>

                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Decimales de los precios:
                                <select name="nf0_art" class="form-control">
                                    <?php $loop_var1=$fsc->nf0(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                    <?php if( $value1==$GLOBALS['config2']['nf0_art'] ){ ?>

                                    <option value="<?php echo $value1;?>" selected=""><?php echo $value1;?></option>
                                    <?php }else{ ?>

                                    <option value="<?php echo $value1;?>"><?php echo $value1;?></option>
                                    <?php } ?>

                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Separador para los Decimales:
                                <select name="nf1" class="form-control">
                                    <?php $loop_var1=$fsc->nf1(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                    <?php if( $key1==$GLOBALS['config2']['nf1'] ){ ?>

                                    <option value="<?php echo $key1;?>" selected=""><?php echo $value1;?></option>
                                    <?php }else{ ?>

                                    <option value="<?php echo $key1;?>"><?php echo $value1;?></option>
                                    <?php } ?>

                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Separador para los Millares:
                                <select name="nf2" class="form-control">
                                    <option value="">(Ninguno)</option>
                                    <?php $loop_var1=$fsc->nf1(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                    <?php if( $key1==$GLOBALS['config2']['nf2'] ){ ?>

                                    <option value="<?php echo $key1;?>" selected=""><?php echo $value1;?></option>
                                    <?php }else{ ?>

                                    <option value="<?php echo $key1;?>"><?php echo $value1;?></option>
                                    <?php } ?>

                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Símbolo Divisa:
                                <select name="pos_divisa" class="form-control">
                                    <?php if( $GLOBALS['config2']['pos_divisa']=='right' ){ ?>

                                    <option value="right" selected="">123 <?php echo $fsc->simbolo_divisa();?></option>
                                    <option value="left"><?php echo $fsc->simbolo_divisa();?>123</option>
                                    <?php }else{ ?>

                                    <option value="right">123 <?php echo $fsc->simbolo_divisa();?></option>
                                    <option value="left" selected=""><?php echo $fsc->simbolo_divisa();?>123</option>
                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <p class="help-block">
                                <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
                                La configuración de decimales y separadores de decimales y millares
                                se aplica únicamente a los listados y formatos de impresión. Para los
                                campos editables se utiliza la configuración del sistema operativo.
                            </p>
                        </div>
                    </div>
                    <div class="row bg-success">
                        <div class="col-md-12 col-sm-12">
                            <h2>
                                <i class="fa fa-language"></i>&nbsp; Traducciones:
                            </h2>
                            <p class="help-block">
                                FACTURA y FACTURAS se traducen únicamente en los documentos de ventas.
                                FACTURA_SIMPLIFICADA se utiliza en los tickets.
                            </p>
                        </div>
                    </div>
                    <div class="row bg-success">
                        <?php $loop_var1=$fsc->traducciones(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                        <div class="col-md-2 col-sm-3">
                            <div class="form-group">
                                <span class="text-uppercase"><?php echo $value1['nombre'];?>:</span>
                                <input class="form-control" type="text" name="<?php echo $value1['nombre'];?>" value="<?php echo $value1['valor'];?>" autocomplete="off"/>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                    <div class="row bg-warning">
                        <div class="col-md-12 col-sm-12">
                            <h2>
                                <span class="glyphicon glyphicon-warning-sign" aria-hidden="true"></span>
                                &nbsp; Desarrollo:
                            </h2>
                            <p class='help-block'>
                                Estos son parametros de configuración sensibles de FacturaScripts. No
                                los modifiques si no sabes lo que haces.
                            </p>
                        </div>
                    </div>
                    <div class="row bg-warning">
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Comprobaciones en la base de datos:
                                <select name="check_db_types" class="form-control">
                                    <?php if( $GLOBALS['config2']['check_db_types']==1 ){ ?>

                                    <option value="1" selected=''>Comprobar los tipos de las columnas de las tablas</option>
                                    <option value="0">No comprobar los tipos</option>
                                    <?php }else{ ?>

                                    <option value="1">Comprobar los tipos de las columnas de las tablas</option>
                                    <option value="0" selected=''>No comprobar los tipos</option>
                                    <?php } ?>

                                </select>
                                <p class="help-block">
                                    Tendrás que <a href="index.php?page=admin_info">limpiar la caché</a>
                                    para que comiencen las comprobaciones.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Tipo entero:
                                <input class="form-control" type="text" name="db_integer" value="<?php echo $GLOBALS['config2']['db_integer'];?>"/>
                                <p class="help-block">Tipo a usar en la base de datos (MySQL).</p>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-2">
                            <!--<?php $form_class=$this->var['form_class']='';?>-->
                            <?php if( $GLOBALS['config2']['foreign_keys']==0 ){ ?><!--<?php $form_class=$this->var['form_class']=' has-warning';?>--><?php } ?>

                            <div class="form-group<?php echo $form_class;?>">
                                Comprobar claves ajenas:
                                <select name="foreign_keys" class="form-control">
                                    <?php if( $GLOBALS['config2']['foreign_keys']==1 ){ ?>

                                    <option value="1" selected=''>Si</option>
                                    <option value="0">No</option>
                                    <?php }else{ ?>

                                    <option value="1">Si</option>
                                    <option value="0" selected=''>No</option>
                                    <?php } ?>

                                </select>
                                <p class="help-block">
                                    <?php if( strtolower(FS_DB_TYPE)=='mysql' ){ ?>

                                    Peligro: no tocar si no sabes lo que haces.
                                    <?php }else{ ?>

                                    Sólo se puede desactivar en MySQL.
                                    <?php } ?>

                                </p>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4">
                            <div class="form-group">
                                Permitir acceso desde estas IPs:
                                <input class="form-control" type="text" name="ip_whitelist" value="<?php echo $GLOBALS['config2']['ip_whitelist'];?>"/>
                                <p class="help-block">Los admninistradores pueden acceder desde cualquier IP.</p>
                            </div>
                        </div>
                    </div>
                    <div class="row bg-warning">
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Generar los libros contables:
                                <select name="libros_contables" class="form-control">
                                    <?php if( $GLOBALS['config2']['libros_contables']==1 ){ ?>

                                    <option value="1" selected=''>Si</option>
                                    <option value="0">No</option>
                                    <?php }else{ ?>

                                    <option value="1">Si</option>
                                    <option value="0" selected=''>No</option>
                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-3">
                            <div class="form-group">
                                Algoritmo de nuevo código:
                                <select name="new_codigo" class="form-control">
                                    <?php $loop_var1=$fsc->new_codigo_options(); $counter1=-1; if($loop_var1) foreach( $loop_var1 as $key1 => $value1 ){ $counter1++; ?>

                                    <?php if( $GLOBALS['config2']['new_codigo']==$key1 ){ ?>

                                    <option value="<?php echo $key1;?>" selected=''><?php echo $value1;?></option>
                                    <?php }else{ ?>

                                    <option value="<?php echo $key1;?>"><?php echo $value1;?></option>
                                    <?php } ?>

                                    <?php } ?>

                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6 col-sm-6">
                            <button class="btn btn-sm btn-danger" type="button" onclick="window.location.href='<?php echo $fsc->url();?>&caca=<?php echo $fsc->random_string(4);?>&reset=TRUE#avanzado'">
                                <span class="glyphicon glyphicon-edit"></span>&nbsp; Configuración por defecto
                            </button>
                        </div>
                        <div class="col-md-6 col-sm-6 text-right">
                            <button class="btn btn-sm btn-primary" type="submit" onclick="this.disabled=true;this.form.submit();">
                                <span class="glyphicon glyphicon-floppy-disk"></span>&nbsp; Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modal_add_plugin" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <i class="fa fa-plug"></i>&nbsp; Añadir un plugin
                </h4>
                <p class="help-block">
                    Si tienes un plugin en un archivo .zip, puedes subirlo e instalarlo desde aquí.
                </p>
            </div>
            <div class="modal-body">
                <form class="form" action="<?php echo $fsc->url();?>#plugins" enctype="multipart/form-data" method="post">
                    <input type="hidden" name="install" value="TRUE"/>
                    <div class="form-group">
                        <input type="file" name="fplugin" accept="application/zip"/>
                    </div>
                    <p class="help-block">
                        Este servidor admite un tamaño máximo de <?php echo $fsc->get_max_file_upload();?> MB.
                        Si el plugin ocupa más, dará error al subirlo, incluso puede que no salga
                        nada.
                    </p>
                    <button type="submit" class="btn btn-primary" onclick="this.disabled=true;this.form.submit();">
                        <span class="glyphicon glyphicon-import" aria-hidden="true"></span>&nbsp; Añadir
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>