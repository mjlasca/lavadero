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

class ventas_gastos extends fbase_controller
{

    public $almacenes;
    public $b_bloqueados;
    public $b_codalmacen;
    public $b_codfabricante;
    public $b_codfamilia;
    public $b_codtarifa;
    public $b_constock;
    public $b_orden;
    public $b_publicos;
    public $b_subfamilias;
    public $b_url;
    public $familia;
    public $fabricante;
    public $impuesto;
    public $mostrar_tab_tarifas;
    public $mostrar_tab_filtros;
    public $offset;
    public $resultados;
    public $total_resultados;
    public $tarifa;
    public $transferencia_stock;
    public $consulta_gastos;
    public $total_gastos;
    public $desde;
    public $hasta;
    public $filtro_b;
    public $estado_factura;
    public $consolidado;
    public $info_provee;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'gastos', 'gastos');
    }

    protected function private_core()
    {
        parent::private_core();
        $this->consolidado = 0;
        $almacen = new almacen();
        $this->almacenes = $almacen->all();
        $gasto = new gasto();
        $this->familia = new familia();
        $this->fabricante = new fabricante();
        $this->impuesto = new impuesto();
        $this->tarifa = new tarifa();
        $this->transferencia_stock = new transferencia_stock();

        /**
         * Si hay alguna extensión de tipo config y texto no_tab_tarifas,
         * desactivamos la pestaña tarifas.
         */
        $this->mostrar_tab_tarifas = TRUE;
        
        foreach ($this->extensions as $ext) {
            if ($ext->type == 'config' && $ext->text == 'no_tab_tarifas') {
                $this->mostrar_tab_tarifas = FALSE;
                break;
            }
        }

        if(isset($_POST["form_filtros"])){
            $this->generar_consulta_filtros();
        }
        if (isset($_POST['codtarifa'])) {
            $this->edit_tarifa();
        } else if (isset($_GET['delete_tarifa'])) {
            $this->delete_tarifa();
        } else if (isset($_POST['descripcion']) && isset($_POST['pvp'])) {
            $this->new_gasto($gasto);
        } else if (isset($_GET['delete'])) {
            $this->delete_gasto($gasto);
        } else if (isset($_POST['origen'])) {
            $this->new_transferencia();
        } else if (isset($_GET['delete_transf'])) {
            $this->delete_transferencia($gasto);
        }

        $this->ini_filters();
        $this->search_gastos();
    }

    private function ini_filters()
    {
        $this->offset = 0;
        if (isset($_REQUEST['offset'])) {
            $this->offset = intval($_REQUEST['offset']);
        }

        $this->b_codalmacen = '';
        if (isset($_REQUEST['b_codalmacen'])) {
            $this->b_codalmacen = $_REQUEST['b_codalmacen'];
        }

        $this->b_codfamilia = '';
        $this->b_subfamilias = FALSE;
        if (isset($_REQUEST['b_codfamilia'])) {
            $this->b_codfamilia = $_REQUEST['b_codfamilia'];
            if ($_REQUEST['b_codfamilia']) {
                $this->b_subfamilias = isset($_REQUEST['b_subfamilias']);
            }
        }

        $this->b_codfabricante = '';
        if (isset($_REQUEST['b_codfabricante'])) {
            $this->b_codfabricante = $_REQUEST['b_codfabricante'];
        }

        $this->b_constock = isset($_REQUEST['b_constock']);
        $this->b_bloqueados = isset($_REQUEST['b_bloqueados']);
        $this->b_publicos = isset($_REQUEST['b_publicos']);

        $this->b_codtarifa = '';
        if (isset($_POST['b_codtarifa'])) {
            $this->b_codtarifa = $_POST['b_codtarifa'];
            setcookie('b_codtarifa', $this->b_codtarifa, time() + FS_COOKIES_EXPIRE);
        } else if (isset($_GET['b_codtarifa'])) {
            $this->b_codtarifa = $_GET['b_codtarifa'];
            setcookie('b_codtarifa', $this->b_codtarifa, time() + FS_COOKIES_EXPIRE);
        } else if (isset($_COOKIE['b_codtarifa'])) {
            $this->b_codtarifa = $_COOKIE['b_codtarifa'];
        }

        $this->b_orden = 'refmin';
        if (isset($_REQUEST['b_orden'])) {
            $this->b_orden = $_REQUEST['b_orden'];
            setcookie('ventas_gastos_orden', $this->b_orden, time() + FS_COOKIES_EXPIRE);
        } else if (isset($_COOKIE['ventas_gastos_orden'])) {
            $this->b_orden = $_COOKIE['ventas_gastos_orden'];
        }

        $this->b_url = $this->url() . "&query=" . $this->query
            . "&b_codfabricante=" . $this->b_codfabricante
            . "&b_codalmacen=" . $this->b_codalmacen
            . "&b_codfamilia=" . $this->b_codfamilia
            . "&b_codtarifa=" . $this->b_codtarifa;

        if ($this->b_subfamilias) {
            $this->b_url .= '&b_subfamilias=TRUE';
        }

        if ($this->b_constock) {
            $this->b_url .= '&b_constock=TRUE';
        }

        if ($this->b_bloqueados) {
            $this->b_url .= '&b_bloqueados=TRUE';
        }

        if ($this->b_publicos) {
            $this->b_url .= '&b_publicos=TRUE';
        }
    }

    /**
     * Con ésta función se va a consultar y mostrar en pantalla
     * los registros de gastos en facturas de cierto rango y/o con otros
     * filtros 
     */

    public function generar_consulta_filtros(){

        $fecha1 = date("Y-m-")."01"; 
        $fecha2 = date("Y-m-")."30";
        $familia = "";
        $estado_factura = "";

        $prov_servicio = "";

        if(isset($_POST["b_codfamilia"])){
            if($_POST["b_codfamilia"] != "")
                $familia = " AND  t3.codfamilia = '".$_POST["b_codfamilia"]."' ";
        }

        if(isset($_POST["info_provee"])){
            if($_POST["info_provee"] != "")
                $prov_servicio = " AND  t2.proveedor_servicio = '".$_POST["info_provee"]."' ";
        }

        if(isset($_POST["desde"])){
            if($_POST["desde"] != ""){
                $fecha1 = substr($_POST["desde"], 6, 4) ."-". substr($_POST["desde"], 3, 2) . "-" .substr($_POST["desde"], 0, 2);
                $this->desde = $_POST["desde"];
            }
            
        }
        if(isset($_POST["hasta"])){
            if($_POST["hasta"] != ""){
                $fecha2 = substr($_POST["hasta"], 6, 4) ."-". substr($_POST["hasta"], 3, 2) . "-" .substr($_POST["hasta"], 0, 2);
                $this->hasta = $_POST["hasta"];
            }
        }

        if(isset($_POST["query"])){
            $this->filtro_b = $_POST["query"];
        }
         
        if(isset($_POST["estado"])){
            if($_POST["estado"] != ""){
                $estado_factura = " AND t2.".$_POST["estado"]." = 1 ";
                if($_POST["estado"] == "pagada0")   
                    $estado_factura = " AND t2.pagada = 0 ";
            }

            $this->estado_factura = $_POST["estado"];
            $this->consolidado = $_POST["consolidado"];
        }
        


        $sql = "SELECT t2.codigo, t2.idfactura, t2.proveedor_servicio, t2.fecha, t1.referencia, t1.descripcion, t2.codagente, t3.codfamilia, t2.nombrecliente, t1.cantidad, t1.pvptotal 
        FROM lineasfacturascli t1 INNER JOIN facturascli t2 INNER JOIN gastos t3 ON t3.referencia = t1.referencia ".$familia."  AND t1.idfactura = t2.idfactura AND t2.fecha BETWEEN '".$fecha1."' AND '".$fecha2."' 
        ".$prov_servicio." AND (lower(t1.descripcion) LIKE lower('%".$this->filtro_b."%') OR lower(t1.referencia) LIKE lower('%".$this->filtro_b."%')  ) ".$estado_factura." ORDER BY t2.fecha ASC ";

        if(isset($_POST["consolidado"])){
            if($_POST["consolidado"] == 1){

                $familia_add = " '--*--' as codfamilia";
                $lavador = " '--*--' as proveedor_servicio";
                
                if($_POST["b_codfamilia"] != "")
                    $familia_add = " t3.codfamilia as codfamilia";    
                if($_POST["info_provee"] != "")
                    $lavador = " t2.proveedor_servicio as proveedor_servicio";
                    

                $sql = "SELECT SUM(t1.pvptotal) as pvptotal,  SUM(t1.cantidad) as cantidad, t2.codigo, t2.idfactura, ".$lavador.",  t2.fecha, t1.referencia, t1.descripcion, '--*--' as codagente, ".$familia_add.", '--*--' as nombrecliente 
                FROM lineasfacturascli t1 INNER JOIN facturascli t2 INNER JOIN gastos t3 ON t3.referencia = t1.referencia ".$familia."  AND t1.idfactura = t2.idfactura AND t2.fecha BETWEEN '".$fecha1."' AND '".$fecha2."' 
                ".$prov_servicio."  AND (lower(t1.descripcion) LIKE lower('%".$this->filtro_b."%') OR lower(t1.referencia) LIKE lower('%".$this->filtro_b."%')  ) ".$estado_factura." ORDER BY t2.fecha ASC ";
            }
        }

        $this->consulta_gastos = $this->db->select($sql);

        $sql = "SELECT SUM(t1.pvptotal) as total,  SUM(t1.cantidad) as cantidad
        FROM lineasfacturascli t1 INNER JOIN facturascli t2 INNER JOIN gastos t3 ON t3.referencia = t1.referencia ".$familia."  AND t1.idfactura = t2.idfactura AND t2.fecha BETWEEN '".$fecha1."' AND '".$fecha2."' 
        ".$prov_servicio." AND (lower(t1.descripcion) LIKE lower('%".$this->filtro_b."%') OR lower(t1.referencia) LIKE lower('%".$this->filtro_b."%')  ) ".$estado_factura." ORDER BY t2.fecha ASC ";

        $this->total_gastos = $this->db->select($sql)[0];
        $this->consolidado = $_POST["consolidado"];
        $this->info_provee = $_POST["info_provee"];
    }

    public function personas_lav_informe(){
        $sql = "SELECT * FROM proveedores WHERE  personafisica = 1 ORDER BY nombre ASC ";

        $tempo = $this->db->select($sql);
        //$tempo1 = $this->db->select($sql1);
        $datos = array();

        for($i=0; $i < count($tempo) ; $i++){
            $datos[] = $tempo[$i]['nombre'];
        }

        /*for($i=0; $i < count($tempo1) ; $i++){
            $datos[] = $tempo1[$i]['nick'];
        }*/


        return $datos;
        
    }


    private function edit_tarifa()
    {
        $tar0 = $this->tarifa->get($_POST['codtarifa']);
        if (!$tar0) {
            $tar0 = new tarifa();
            $tar0->codtarifa = $_POST['codtarifa'];
        }
        $tar0->nombre = $_POST['nombre'];
        $tar0->aplicar_a = $_POST['aplicar_a'];
        $tar0->set_x(floatval($_POST['dtopor']));
        $tar0->set_y(floatval($_POST['inclineal']));
        $tar0->mincoste = isset($_POST['mincoste']);
        $tar0->maxpvp = isset($_POST['maxpvp']);
        if ($tar0->save()) {
            $this->new_message("Tarifa guardada correctamente.");
        } else {
            $this->new_error_msg("¡Imposible guardar la tarifa!");
        }
    }

    private function delete_tarifa()
    {
        $tar0 = $this->tarifa->get($_GET['delete_tarifa']);
        if ($tar0) {
            if (!$this->allow_delete) {
                $this->new_error_msg('No tienes permiso para eliminar en esta página.');
            } else if ($tar0->delete()) {
                $this->new_message("Tarifa " . $tar0->codtarifa . " eliminada correctamente.", TRUE);
            } else {
                $this->new_error_msg("¡Imposible eliminar la tarifa!");
            }
        } else {
            $this->new_error_msg("¡La tarifa no existe!");
        }
    }

    private function new_gasto(&$gasto)
    {
        //$this->save_codimpuesto($_POST['codimpuesto']);
        
        if ($_POST['referencia'] == '') {
            $referencia = $gasto->get_new_referencia();
        } else {
            $referencia = $_POST['referencia'];
        }

        $art0 = $gasto->get($referencia);
        if ($art0) {
            $this->new_error_msg('Ya existe el gasto <a href="' . $art0->url() . '">' . $art0->referencia . '</a>');
        } else {
            $gasto->referencia = $referencia;
            $gasto->descripcion = $_POST['descripcion'];
            $gasto->nostock = isset($_POST['nostock']);

            /*if ($_POST['codfamilia'] != '') {
                $gasto->codfamilia = $_POST['codfamilia'];
            }

            if ($_POST['codfabricante'] != '') {
                $gasto->codfabricante = $_POST['codfabricante'];
            }*/

            //$gasto->set_impuesto($_POST['codimpuesto']);

            $pvp = floatval(str_replace(',', '.', $_POST['pvp']));
            if (isset($_POST['coniva'])) {
                $gasto->set_pvp_iva($pvp);
            } else {
                $gasto->set_pvp($pvp);
            }
            
            if ($gasto->save()) {
                header('location: ' . $gasto->url());
            } else {
                $this->new_error_msg("¡Error al crear el gasto!");
            }
        }
    }

    private function delete_gasto(&$gasto)
    {
        $art = $gasto->get($_GET['delete']);
        if ($art) {
            if (!$this->allow_delete) {
                $this->new_error_msg('No tienes permiso para eliminar en esta página.');
            } else if ($art->delete()) {
                $this->new_message("gasto " . $art->referencia . " eliminado correctamente.", TRUE);
            } else {
                $this->new_error_msg("¡Error al eliminarl el gasto!");
            }
        } else {
            $this->new_error_msg("gasto no encontrado.");
        }
    }

    private function new_transferencia()
    {
        $this->transferencia_stock->usuario = $this->user->nick;
        $this->transferencia_stock->codalmaorigen = $_POST['origen'];
        $this->transferencia_stock->codalmadestino = $_POST['destino'];

        if ($this->transferencia_stock->save()) {
            $this->new_message('Datos guardados correctamente.');
            header('Location: ' . $this->transferencia_stock->url());
        } else {
            $this->new_error_msg('Error al guardar los datos.');
        }
    }

    private function delete_transferencia(&$gasto)
    {
        $transf = $this->transferencia_stock->get($_GET['delete_transf']);

        if (!$this->allow_delete) {
            $this->new_error_msg('No tienes permiso para eliminar en esta página.');
        } else if ($transf) {
            $ok = TRUE;

            /// eliminamos las líneas
            $ltf = new linea_transferencia_stock();
            foreach ($ltf->all_from_transferencia($transf->idtrans) as $lin) {
                if ($lin->delete()) {
                    /// movemos el stock
                    $art = $gasto->get($lin->referencia);
                    if ($art) {
                        $art->sum_stock($transf->codalmadestino, 0 - $lin->cantidad);
                        $art->sum_stock($transf->codalmaorigen, $lin->cantidad);
                    }
                } else {
                    $this->new_error_msg('Error al eliminar la línea con referencia ' . $lin->referencia);
                    $ok = FALSE;
                }
            }

            if ($ok) {
                if ($transf->delete()) {
                    $this->new_message('Transferencia eliminada correctamente.');
                } else {
                    $this->new_error_msg('Error al eliminar la transferencia.');
                }
            }
        } else {
            $this->new_error_msg('Transferencia no encontrada.');
        }
    }

    private function search_gastos()
    {
        $this->resultados = array();
        $this->num_resultados = 0;
        $sql = ' FROM gastos ';
        $where = ' WHERE ';

        if ($this->query) {
            $query = $this->empresa->no_html(mb_strtolower($this->query, 'UTF8'));
            $sql .= $where;
            if (is_numeric($query)) {
                /// ¿La búsqueda son números?
                $sql .= "(referencia = " . $this->empresa->var2str($query)
                    . " OR referencia LIKE '%" . $query . "%'"
                    . " OR partnumber LIKE '%" . $query . "%'"
                    . " OR equivalencia LIKE '%" . $query . "%'"
                    . " OR descripcion LIKE '%" . $query . "%'"
                    . " OR codbarras = " . $this->empresa->var2str($query) . ")";
            } else {
                /// ¿La búsqueda son varias palabras?
                $palabras = explode(' ', $query);
                if (count($palabras) > 1) {
                    $sql .= "(lower(referencia) = " . $this->empresa->var2str($query)
                        . " OR lower(referencia) LIKE '%" . $query . "%'"
                        . " OR lower(partnumber) LIKE '%" . $query . "%'"
                        . " OR lower(equivalencia) LIKE '%" . $query . "%'"
                        . " OR (";

                    foreach ($palabras as $i => $pal) {
                        if ($i == 0) {
                            $sql .= "lower(descripcion) LIKE '%" . $pal . "%'";
                        } else {
                            $sql .= " AND lower(descripcion) LIKE '%" . $pal . "%'";
                        }
                    }

                    $sql .= "))";
                } else {
                    $sql .= "(lower(referencia) = " . $this->empresa->var2str($query)
                        . " OR lower(referencia) LIKE '%" . $query . "%'"
                        . " OR lower(partnumber) LIKE '%" . $query . "%'"
                        . " OR lower(equivalencia) LIKE '%" . $query . "%'"
                        . " OR lower(codbarras) = " . $this->empresa->var2str($query)
                        . " OR lower(descripcion) LIKE '%" . $query . "%')";
                }
            }
            $where = ' AND ';
        }

        if ($this->b_codfamilia) {
            if ($this->b_subfamilias) {
                $sql .= $where . "codfamilia IN (";
                $coma = '';
                foreach ($this->get_subfamilias($this->b_codfamilia) as $fam) {
                    $sql .= $coma . $this->empresa->var2str($fam);
                    $coma = ',';
                }
                $sql .= ")";
            } else {
                $sql .= $where . "codfamilia = " . $this->empresa->var2str($this->b_codfamilia);
            }
            $where = ' AND ';
        }

        if ($this->b_codfabricante) {
            $sql .= $where . "codfabricante = " . $this->empresa->var2str($this->b_codfabricante);
            $where = ' AND ';
        }

        if ($this->b_constock) {
            if ($this->b_codalmacen == '') {
                $sql .= $where . "stockfis > 0";
            } else {
                $sql .= $where . "referencia IN (SELECT referencia FROM stocks WHERE cantidad > 0"
                    . " AND codalmacen = " . $this->empresa->var2str($this->b_codalmacen) . ')';
            }
            $where = ' AND ';
        }

        if ($this->b_publicos) {
            $sql .= $where . "publico = TRUE";
            $where = ' AND ';
        }

        if ($this->b_bloqueados) {
            $sql .= $where . "bloqueado = TRUE";
            $where = ' AND ';
        } else {
            $sql .= $where . "bloqueado = FALSE";
            $where = ' AND ';
        }

        $order = 'referencia DESC';
        switch ($this->b_orden) {
            case 'stockmin':
                $order = 'stockfis ASC';
                break;

            case 'stockmax':
                $order = 'stockfis DESC';
                break;

            case 'refmax':
                if (strtolower(FS_DB_TYPE) == 'postgresql') {
                    $order = 'referencia DESC';
                } else {
                    $order = 'lower(referencia) DESC';
                }
                break;

            case 'descmin':
                $order = 'descripcion ASC';
                break;

            case 'descmax':
                $order = 'descripcion DESC';
                break;

            case 'preciomin':
                $order = 'pvp ASC';
                break;

            case 'preciomax':
                $order = 'pvp DESC';
                break;

            default:
            case 'refmin':
                if (strtolower(FS_DB_TYPE) == 'postgresql') {
                    $order = 'referencia ASC';
                } else {
                    $order = 'lower(referencia) ASC';
                }
                break;
        }

        $data = $this->db->select("SELECT COUNT(referencia) as total" . $sql);
        if ($data) {
            $this->total_resultados = intval($data[0]['total']);

            /// ¿Descargar o mostrar en pantalla?
            if (isset($_GET['download'])) {
                $this->download_resultados($sql, $order);
            } else {
                $data2 = $this->db->select_limit("SELECT *" . $sql . " ORDER BY " . $order, FS_ITEM_LIMIT, $this->offset);
                if ($data2) {
                    foreach ($data2 as $i) {
                        $this->resultados[] = new gasto($i);
                    }

                    if ($this->b_codalmacen != '') {
                        /// obtenemos el stock correcto
                        foreach ($this->resultados as $i => $value) {
                            $this->resultados[$i]->stockfis = 0;
                            foreach ($value->get_stock() as $s) {
                                if ($s->codalmacen == $this->b_codalmacen) {
                                    $this->resultados[$i]->stockfis = $s->cantidad;
                                }
                            }
                        }
                    }

                    if ($this->b_codtarifa != '') {
                        /// aplicamos la tarifa
                        $tarifa = $this->tarifa->get($this->b_codtarifa);
                        if ($tarifa) {
                            $tarifa->set_precios($this->resultados);

                            /// si la tarifa añade descuento, lo aplicamos al precio
                            foreach ($this->resultados as $i => $value) {
                                $this->resultados[$i]->pvp -= $value->pvp * $value->dtopor / 100;
                            }
                        }
                    }
                }
            }
        }
    }

    private function download_resultados($sql, $order)
    {
        /// desactivamos el motor de plantillas
        $this->template = FALSE;

        header("content-type:application/csv;charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"gastos.csv\"");
        echo "referencia;codfamilia;codfabricante;descripcion;pvp;iva;codbarras;stock;coste\n";

        $offset2 = 0;
        $data2 = $this->db->select_limit("SELECT *" . $sql . " ORDER BY " . $order, 1000, $offset2);
        while ($data2) {
            $resultados = array();
            foreach ($data2 as $i) {
                $resultados[] = new gasto($i);
            }

            if ($this->b_codalmacen != '') {
                /// obtenemos el stock correcto
                foreach ($resultados as $i => $value) {
                    $resultados[$i]->stockfis = 0;
                    foreach ($value->get_stock() as $s) {
                        if ($s->codalmacen == $this->b_codalmacen) {
                            $resultados[$i]->stockfis = $s->cantidad;
                        }
                    }
                }
            }

            if ($this->b_codtarifa != '') {
                /// aplicamos la tarifa
                $tarifa = $this->tarifa->get($this->b_codtarifa);
                if ($tarifa) {
                    $tarifa->set_precios($resultados);

                    /// si la tarifa añade descuento, lo aplicamos al precio
                    foreach ($resultados as $i => $value) {
                        $resultados[$i]->pvp -= $value->pvp * $value->dtopor / 100;
                    }
                }
            }

            /**
             * libreoffice y excel toman el punto y 3 decimales como millares,
             * así que si el usuario ha elegido 3 decimales, mejor usamos 4.
             */
            $nf0 = FS_NF0_ART;
            if ($nf0 == 3) {
                $nf0 = 4;
            }

            /// escribimos los datos de los gastos
            foreach ($resultados as $art) {
                echo $art->referencia . ';';
                echo $art->codfamilia . ';';
                echo $art->codfabricante . ';';
                echo fs_fix_html(preg_replace('~[\r\n]+~', ' ', $art->descripcion)) . ';';
                echo number_format($art->pvp, $nf0, FS_NF1, '') . ';';
                echo number_format($art->get_iva(), 2, FS_NF1, '') . ';';
                echo trim($art->codbarras) . ';';
                echo number_format($art->stockfis, 2, FS_NF1, '') . ';';
                echo number_format($art->preciocoste(), $nf0, FS_NF1, '') . "\n";

                $offset2++;
            }

            $data2 = $this->db->select_limit("SELECT *" . $sql . " ORDER BY " . $order, 1000, $offset2);
        }
    }

    public function paginas()
    {
        $url = $this->b_url . '&b_orden=' . $this->b_orden;
        return $this->fbase_paginas($url, $this->total_resultados, $this->offset);
    }

    private function get_subfamilias($cod)
    {
        $familias = array($cod);

        $data = $this->db->select("SELECT codfamilia,madre FROM familias WHERE madre = " . $this->empresa->var2str($cod) . ";");
        if ($data) {
            foreach ($data as $d) {
                foreach ($this->get_subfamilias($d['codfamilia']) as $subf) {
                    $familias[] = $subf;
                }
            }
        }

        return $familias;
    }
}
