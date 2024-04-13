<?php if(!class_exists('raintpl')){exit;}?><?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("header") . ( substr("header",-1,1) != "/" ? "/" : "" ) . basename("header") );?>


<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="page-header">
                <h1>
                    <span class="glyphicon glyphicon-exclamation-sign"></span>
                    Acceso denegado
                </h1>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-8">
            <ul>
                <li>No tienes permiso para acceder a esta página.</li>
                <li>
                    <?php if( $fsc->user->admin ){ ?>

                    <b>¿Has movido de sitio la instalación de FacturaScripts o has eliminado plugins manualmente?</b>
                    El error de acceso denegado a una página también sucede cuando la página a la que se intenta
                    acceder ya no existe o <b>el plugin está desactivado</b>.
                    <ul>
                        <li>Ve a admin -&gt; Panel de control y activa los plugins que necesites.</li>
                    </ul>
                    <?php }else{ ?>

                    Consulta con el administrador <u>para que te dé acceso</u> a esta página.
                    <?php } ?>

                </li>
                <li>
                    Si crees que es un error de FacturaScripts, no dudes en notificármelo.
                    <b>Haz clic en el botón de ayuda</b>, arriba a la derecha y <b>notifícame el error</b>.
                </li>
            </ul>
        </div>
        <div class="col-sm-4">
            <div class="thumbnail">
                <img src='<?php  echo FS_PATH;?>view/img/big_lock.png' alt="Acceso denegado"/>
            </div>
        </div>
    </div>
</div>

<?php $tpl = new RainTPL;$tpl_dir_temp = self::$tpl_dir;$tpl->assign( $this->var );$tpl->draw( dirname("footer") . ( substr("footer",-1,1) != "/" ? "/" : "" ) . basename("footer") );?>