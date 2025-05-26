<?php
/*
 * This file is part of facturacion_base
 * Copyright (C) 2013-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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

require_once 'plugins/facturacion_base/extras/fbase_controller.php';

class ventas_gasto extends fbase_controller
{

    public $agrupar_stock_fecha;
    public $almacen;
    public $gasto;
    public $equivalentes;
    public $fabricante;
    public $familia;
    public $hay_atributos;
    public $impuesto;
    public $mgrupo;
    public $mostrar_boton_publicar;
    public $mostrar_tab_atributos;
    public $mostrar_tab_precios;
    public $mostrar_tab_stock;
    public $nuevos_almacenes;
    public $stock;
    public $stocks;
    public $regularizaciones;
    public $servicio_lav;
    public $auditoria;
    

    public function __construct()
    {
        parent::__construct(__CLASS__, 'gasto', 'gastos', FALSE, FALSE);
    }

    protected function private_core()
    {
        parent::private_core();

        $gasto = new gasto();
        $this->almacen = new almacen();
        $this->gasto = FALSE;
        $this->fabricante = new fabricante();
        $this->impuesto = new impuesto();
        $this->stock = new stock();
        


        $this->check_extensions();

        if (isset($_POST['referencia'])) {
            $this->gasto = $gasto->get($_POST['referencia']);
        } else if (isset($_GET['ref'])) {
            $this->gasto = $gasto->get($_GET['ref']);
        }


        if ($this->gasto) {
            $this->modificar(); /// todas las modificaciones van aquí
            $this->page->title = $this->gasto->referencia;

            if ($this->gasto->bloqueado) {
                $this->new_advice("Este artículo está bloqueado / obsoleto.");
            }

            /**
             * Si no es un artículo con atributos, ocultamos la pestaña
             */
            if ($this->gasto->tipo != 'atributos') {
                $this->mostrar_tab_atributos = FALSE;
            }

            /**
             * Si está desactivado el control de stok en el artículo, ocultamos la pestaña
             */
            if ($this->gasto->nostock) {
                $this->mostrar_tab_stock = FALSE;
            }

            $this->familia = $this->gasto->get_familia();
            if (!$this->familia) {
                $this->familia = new familia();
            }

            $this->fabricante = $this->gasto->get_fabricante();
            if (!$this->fabricante) {
                $this->fabricante = new fabricante();
            }

            //$this->stocks = $this->gasto->get_stock();

            /// metemos en un array los almacenes que no tengan stock de este producto
            $this->nuevos_almacenes = array();
            

            $reg = new regularizacion_stock();
            //$this->regularizaciones = $reg->all_from_gasto($this->gasto->referencia);

            $this->equivalentes = $this->gasto->get_equivalentes();
            $this->agrupar_stock_fecha = isset($_GET['agrupar_stock_fecha']);
        } else {
            $this->new_error_msg("Artículo no encontrado.", 'error', FALSE, FALSE);
        }
        //$this->auditoria = new auditoriapreciogasto();
        //$this->auditoria->idgasto = $this->gasto->referencia;
    }
    
    
    public function servicio_lav($dato)
    {
        if($dato=="TRUE"){
            $sql = "UPDATE gastos SET servicio='1' WHERE referencia='".$this->gasto->referencia."'";
            $this->servicio_lav = $this->db->exec($sql);
        }
    }

    public function url()
    {
        if ($this->gasto) {
            return $this->gasto->url();
        }

        return $this->page->url();
    }

    private function check_extensions()
    {
        /**
         * Si hay alguna extensión de tipo config y texto no_button_publicar,
         * desactivamos el botón publicar.
         */
        $this->mostrar_boton_publicar = TRUE;
        foreach ($this->extensions as $ext) {
            if ($ext->type == 'config' && $ext->text == 'no_button_publicar') {
                $this->mostrar_boton_publicar = FALSE;
                break;
            }
        }

        /**
         * Si hay atributos, mostramos el tab atributos.
         */
        $this->hay_atributos = FALSE;
        $this->mostrar_tab_atributos = FALSE;
        $atri0 = new atributo();
        if (count($atri0->all()) > 0) {
            $this->mostrar_tab_atributos = TRUE;
            $this->hay_atributos = TRUE;
        }

        /**
         * Si hay alguna extensión de tipo config y texto no_tab_recios,
         * desactivamos la pestaña precios.
         */
        $this->mostrar_tab_precios = TRUE;
        foreach ($this->extensions as $ext) {
            if ($ext->type == 'config' && $ext->text == 'no_tab_precios') {
                $this->mostrar_tab_precios = FALSE;
                break;
            }
        }

        /**
         * Si hay alguna extensión de tipo config y texto no_tab_stock,
         * desactivamos la pestaña stock.
         */
        $this->mostrar_tab_stock = TRUE;
        foreach ($this->extensions as $ext) {
            if ($ext->type == 'config' && $ext->text == 'no_tab_stock') {
                $this->mostrar_tab_stock = FALSE;
                break;
            }
        }
    }

    /**
     * Decide qué modificación hacer en función de los parametros del formulario.
     */
    private function modificar()
    {
        if (isset($_POST['pvpiva'])) {
            $this->edit_precio();
        } else if (isset($_POST['almacen'])) {
            $this->edit_stock();
        } else if (isset($_GET['deletereg'])) {
            $this->eliminar_regulacion();
        } else if (isset($_POST['imagen'])) {
            $this->edit_imagen();
        } else if (isset($_GET['delete_img'])) {
            $this->eliminar_imagen();
        } else if (isset($_POST['referencia'])) {
            $this->modificar_gasto();
        } else if (isset($_GET['recalcular_stock'])) {
            $this->calcular_stock_real();
        } else if (isset($_POST['nueva_combi'])) {
            $this->nueva_combinacion();
        } else if (isset($_POST['editar_combi'])) {
            $this->edit_combinacion();
        } else if (isset($_GET['delete_combi'])) {
            $this->eliminar_combinacion();
        }
    }

    private function edit_precio()
    {
        $this->gasto->set_impuesto($_POST['codimpuesto']);
        $this->gasto->set_pvp_iva(floatval($_POST['pvpiva']));

        if (isset($_POST['preciocoste'])) {
            $this->gasto->preciocoste = floatval($_POST['preciocoste']);
            $this->gasto->preciocombo = floatval($_POST['preciocombo']);
        }

        if ($this->gasto->save()) {

            $auditar = new auditoriapreciogasto();
            $auditar->costo =  $this->gasto->preciocoste ;
            $auditar->preciocombo =  $this->gasto->preciocombo;
            $auditar->costoanterior =  $_POST['costoanterior'] ;
            $auditar->preciocomboanterior =  $_POST['preciocomboanterior'];
            $impu = new impuesto();
            $impu = $impu->get($_POST['codimpuesto']);
            $auditar->iva =  $impu->iva;
            $impu = $impu->get($_POST['ivaanterior']);
            $auditar->ivaanterior =  $impu->iva;
            $auditar->precio = $this->gasto->pvp;
            $auditar->precioanterior = $_POST['precioanterior'];
            $auditar->idgasto = $this->gasto->referencia;
            $auditar->ultmod = date("Y-m-d H:i:s");
            $auditar->useredit = $this->user->nick;
            $empleado = new agente();
            $empleado = $empleado->get($this->user->codagente);
            $auditar->nombreempleado = $empleado->nombre." ".$empleado->apellidos;

            $auditar->save();
            
            $this->new_message("Precio modificado correctamente.");
        } else {
            $this->new_error_msg("Error al modificar el precio.");
        }
    }

    private function edit_stock()
    {
        if ($_POST['cantidadini'] == $_POST['cantidad']) {
            /// sin cambios de stock, pero aún así guardamos la ubicación
            $encontrado = FALSE;
            foreach ($this->gasto->get_stock() as $stock) {
                if ($stock->codalmacen == $_POST['almacen']) {
                    /// forzamos que se asigne el nombre del almacén
                    $stock->nombre();

                    $stock->ubicacion = $_POST['ubicacion'];
                    if ($stock->save()) {
                        $this->new_message('Cambios guardados correctamente.');
                    }

                    $encontrado = TRUE;
                    break;
                }
            }

            if (!$encontrado) {
                $nstock = new stock();
                $nstock->referencia = $this->gasto->referencia;
                $nstock->codalmacen = $_POST['almacen'];
                $nstock->ubicacion = $_POST['ubicacion'];
                $nstock->nombre();

                if ($nstock->save()) {
                    $this->new_message('Cambios guardados correctamente.');
                }
            }
        } else if ($this->gasto->set_stock($_POST['almacen'], $_POST['cantidad'])) {
            $this->new_message("Stock guardado correctamente.");

            /// añadimos la regularización
            foreach ($this->gasto->get_stock() as $stock) {
                if ($stock->codalmacen == $_POST['almacen']) {
                    /// forzamos que se asigne el nombre del almacén
                    $stock->nombre();

                    $stock->ubicacion = $_POST['ubicacion'];
                    $stock->save();

                    $regularizacion = new regularizacion_stock();
                    $regularizacion->idstock = $stock->idstock;
                    $regularizacion->cantidadini = floatval($_POST['cantidadini']);
                    $regularizacion->cantidadfin = floatval($_POST['cantidad']);
                    $regularizacion->codalmacendest = $_POST['almacen'];
                    $regularizacion->motivo = $_POST['motivo'];
                    $regularizacion->nick = $this->user->nick;
                    if ($regularizacion->save()) {
                        $this->new_message('Cambios guardados correctamente.');
                    }
                    break;
                }
            }
        } else {
            $this->new_error_msg("Error al guardar el stock.");
        }
    }

    private function eliminar_regulacion()
    {
        $reg = new regularizacion_stock();
        $regularizacion = $reg->get($_GET['deletereg']);
        if ($regularizacion) {
            if (!$this->allow_delete) {
                $this->new_error_msg('No tienes permiso para eliminar en esta página.');
            } else if ($regularizacion->delete()) {
                $this->new_message('Regularización eliminada correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar la regularización.');
            }
        } else {
            $this->new_error_msg('Regularización no encontrada.');
        }
    }

    private function edit_imagen()
    {
        if (is_uploaded_file($_FILES['fimagen']['tmp_name'])) {
            $png = ( substr(strtolower($_FILES['fimagen']['name']), -3) == 'png' );
            $this->gasto->set_imagen(file_get_contents($_FILES['fimagen']['tmp_name']), $png);
            if ($this->gasto->save()) {
                $this->new_message("Imagen del gasto modificada correctamente");
            } else {
                $this->new_error_msg("¡Error al guardar la imagen del gasto!");
            }
        }
    }

    private function eliminar_imagen()
    {
        $this->gasto->set_imagen(NULL);
        if ($this->gasto->save()) {
            $this->new_message("Imagen del gasto eliminada correctamente");
        } else {
            $this->new_error_msg("¡Error al eliminar la imagen del gasto!");
        }
    }

    private function modificar_gasto()
    {
        $this->gasto->descripcion = $_POST['descripcion'];

        $this->gasto->tipo = NULL;
        if ($_POST['tipo'] != '') {
            $this->gasto->tipo = $_POST['tipo'];
        }

        $this->gasto->codfamilia = NULL;
        if ($_POST['codfamilia'] != '') {
            $this->gasto->codfamilia = $_POST['codfamilia'];
        }

        $this->gasto->codfabricante = NULL;
        if ($_POST['codfabricante'] != '') {
            $this->gasto->codfabricante = $_POST['codfabricante'];
        }

        /// ¿Existe ya ese código de barras?
        if ($_POST['codbarras'] != '') {
            $arts = $this->gasto->search_by_codbar($_POST['codbarras']);
            if ($arts) {
                foreach ($arts as $art2) {
                    if ($art2->referencia != $this->gasto->referencia) {
                        $this->new_advice('Ya hay un artículo con este mismo código de barras. '
                            . 'En concreto, el artículo <a href="' . $art2->url() . '">' . $art2->referencia . '</a>.');
                        break;
                    }
                }
            }
        }

        $this->gasto->codbarras = $_POST['codbarras'];
        $this->gasto->partnumber = $_POST['partnumber'];
        $this->gasto->equivalencia = $_POST['equivalencia'];
        $this->gasto->bloqueado = isset($_POST['bloqueado']);
        $this->gasto->controlstock = isset($_POST['controlstock']);
        $this->gasto->nostock = isset($_POST['nostock']);
        $this->gasto->secompra = isset($_POST['secompra']);
        $this->gasto->sevende = isset($_POST['sevende']);
        $this->gasto->publico = isset($_POST['publico']);
        $this->gasto->observaciones = $_POST['observaciones'];
        $this->gasto->stockmin = floatval($_POST['stockmin']);
        $this->gasto->stockmax = floatval($_POST['stockmax']);
        $this->gasto->trazabilidad = isset($_POST['trazabilidad']);
        $this->gasto->servicio = isset($_POST['servicio']);
        $this->gasto->por_liquidacion = $_POST['por_liquidacion'];
        $this->gasto->necesita_gasto = $_POST['necesita_gasto'];
       /* if(!empty($_POST['servicio'])){
            
        }*/

        if ($this->gasto->save()) {
            $this->new_message("Datos del gasto modificados correctamente");

            $img = $this->gasto->imagen_url();
            $this->gasto->set_referencia($_POST['nreferencia']);

            /// ¿Renombramos la imagen?
            if ($img) {
                @rename($img, $this->gasto->imagen_url());
            }

            /**
             * Renombramos la referencia en el resto de tablas: lineasalbaranes, lineasfacturas...
             */
            if ($this->db->table_exists('lineasalbaranescli')) {
                $this->db->exec("UPDATE lineasalbaranescli SET referencia = " . $this->empresa->var2str($_POST['nreferencia'])
                    . " WHERE referencia = " . $this->empresa->var2str($_POST['referencia']) . ";");
            }

            if ($this->db->table_exists('lineasalbaranesprov')) {
                $this->db->exec("UPDATE lineasalbaranesprov SET referencia = " . $this->empresa->var2str($_POST['nreferencia'])
                    . " WHERE referencia = " . $this->empresa->var2str($_POST['referencia']) . ";");
            }

            if ($this->db->table_exists('lineasfacturascli')) {
                $this->db->exec("UPDATE lineasfacturascli SET referencia = " . $this->empresa->var2str($_POST['nreferencia'])
                    . " WHERE referencia = " . $this->empresa->var2str($_POST['referencia']) . ";");
            }

            if ($this->db->table_exists('lineasfacturasprov')) {
                $this->db->exec("UPDATE lineasfacturasprov SET referencia = " . $this->empresa->var2str($_POST['nreferencia'])
                    . " WHERE referencia = " . $this->empresa->var2str($_POST['referencia']) . ";");
            }

            /// esto es una personalización del plugin producción, será eliminado este código en futuras versiones.
            if ($this->db->table_exists('lineasfabricados')) {
                $this->db->exec("UPDATE lineasfabricados SET referencia = " . $this->empresa->var2str($_POST['nreferencia'])
                    . " WHERE referencia = " . $this->empresa->var2str($_POST['referencia']) . ";");
            }
        } else {
            $this->new_error_msg("¡Error al guardar el gasto!");
        }
    }

    private function nueva_combinacion()
    {
        $comb1 = new gasto_combinacion();
        $comb1->referencia = $this->gasto->referencia;
        $comb1->impactoprecio = floatval($_POST['impactoprecio']);

        if ($_POST['refcombinacion']) {
            $comb1->refcombinacion = $_POST['refcombinacion'];
        }

        if ($_POST['codbarras']) {
            $comb1->codbarras = $_POST['codbarras'];
        }

        $error = TRUE;
        $valor0 = new atributo_valor();
        for ($i = 0; $i < 10; $i++) {
            if (isset($_POST['idvalor_' . $i])) {
                if ($_POST['idvalor_' . $i]) {
                    $valor = $valor0->get($_POST['idvalor_' . $i]);
                    if ($valor) {
                        $comb1->id = NULL;
                        $comb1->idvalor = $valor->id;
                        $comb1->nombreatributo = $valor->nombre();
                        $comb1->valor = $valor->valor;
                        $error = !$comb1->save();
                    }
                }
            } else {
                break;
            }
        }

        if ($error) {
            $this->new_error_msg('Error al guardar la combinación.');
        } else {
            $this->new_message('Combinación guardada correctamente.');
        }
    }

    private function edit_combinacion()
    {
        $comb1 = new gasto_combinacion();
        foreach ($comb1->all_from_codigo($_POST['editar_combi']) as $com) {
            $com->refcombinacion = NULL;
            if ($_POST['refcombinacion']) {
                $com->refcombinacion = $_POST['refcombinacion'];
            }

            $com->codbarras = NULL;
            if ($_POST['codbarras']) {
                $com->codbarras = $_POST['codbarras'];
            }

            $com->impactoprecio = floatval($_POST['impactoprecio']);
            $com->stockfis = floatval($_POST['stockcombinacion']);
            $com->save();
        }

        $this->new_message('Combinación modificada.');
    }

    private function eliminar_combinacion()
    {
        $comb1 = new gasto_combinacion();
        foreach ($comb1->all_from_codigo($_GET['delete_combi']) as $com) {
            $com->delete();
        }

        $this->new_message('Combinación eliminada.');
    }

    public function get_tarifas()
    {
        $tarlist = array();
        $tarifa = new tarifa();

        foreach ($tarifa->all() as $tar) {
            $gasto = $this->gasto->get($this->gasto->referencia);
            if ($gasto) {
                $gasto->dtopor = 0;
                $aux = array($gasto);
                $tar->set_precios($aux);
                $tarlist[] = $aux[0];
            }
        }

        return $tarlist;
    }

    public function get_gasto_proveedores()
    {
        $artprov = new gasto_proveedor();
        $alist = $artprov->all_from_ref($this->gasto->referencia);

        /// revismos el impuesto y la descripción
        foreach ($alist as $i => $value) {
            $guardar = FALSE;
            if (is_null($value->codimpuesto)) {
                $alist[$i]->codimpuesto = $this->gasto->codimpuesto;
                $guardar = TRUE;
            }

            if (is_null($value->descripcion)) {
                $alist[$i]->descripcion = $this->gasto->descripcion;
                $guardar = TRUE;
            }

            if ($guardar) {
                $alist[$i]->save();
            }
        }

        return $alist;
    }

    /**
     * Devuelve un array con los movimientos de stock del artículo.
     * @return array
     */
    public function get_movimientos($codalmacen)
    {
        $recstock = new recalcular_stock();
        $mlist = $recstock->get_movimientos($this->gasto->referencia, $codalmacen);

        if ($this->agrupar_stock_fecha) {
            foreach ($mlist as $item) {
                if (!isset($this->mgrupo[$item['fecha']])) {
                    $this->mgrupo[$item['fecha']]['ingreso'] = FALSE;
                    $this->mgrupo[$item['fecha']]['salida'] = FALSE;
                }

                if ($item['movimiento'] > 0) {
                    $this->mgrupo[$item['fecha']]['ingreso'] += $item['movimiento'];
                } else if ($item['movimiento'] < 0) {
                    $this->mgrupo[$item['fecha']]['salida'] += $item['movimiento'];
                }
            }
        }

        return $mlist;
    }

    /**
     * Calcula el stock real del artículo en función de los movimientos y regularizaciones
     */
    private function calcular_stock_real()
    {
        $almacenes = $this->almacen->all();
        foreach ($almacenes as $alm) {
            $total = 0;
            $movimientos = $this->get_movimientos($alm->codalmacen);
            foreach ($movimientos as $mov) {
                if ($mov['codalmacen'] == $alm->codalmacen) {
                    $total += $mov['movimiento'];
                }
            }

            if ($this->gasto->set_stock($alm->codalmacen, $total)) {
                $this->new_message('Recarculado el stock del almacén ' . $alm->codalmacen . '.');
            } else {
                $this->new_error_msg('Error al recarcular el stock del almacén ' . $alm->codalmacen . '.');
            }
        }

        $this->new_advice("Puedes recalcular el stock de todos los artículos desde"
            . " el menú <b>Informes &gt; Artículos &gt; Stock</b>");
    }

    public function combinaciones()
    {
        $lista = array();

        $comb1 = new gasto_combinacion();
        foreach ($comb1->all_from_ref($this->gasto->referencia) as $com) {
            if (isset($lista[$com->codigo])) {
                $lista[$com->codigo]->txt .= ', ' . $com->nombreatributo . ' - ' . $com->valor;
            } else {
                $com->txt = $com->nombreatributo . ' - ' . $com->valor;
                $lista[$com->codigo] = $com;
            }
        }

        return $lista;
    }

    public function atributos()
    {
        $atri0 = new atributo();
        return $atri0->all();
    }
}
