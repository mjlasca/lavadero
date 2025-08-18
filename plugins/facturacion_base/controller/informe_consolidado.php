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

use FacturaScripts\model\metodo_pago;
require_once 'extras/xlsxwriter.class.php';
require_once 'plugins/facturacion_base/extras/fbase_controller.php';

class informe_consolidado extends fbase_controller
{

    public $agente;
    public $almacenes;
    public $articulo;
    public $cliente;
    public $codagente;
    public $codalmacen;
    public $codgrupo;
    public $codpago;
    public $codserie;
    public $desde;
    public $estado;
    public $factura;
    public $forma_pago;
    public $grupo;
    public $hasta;
    public $huecos;
    public $lineas;
    public $mostrar;
    public $num_resultados;
    public $offset;
    public $order;
    public $resultados;
    public $serie;
    public $total_resultados;
    public $total_resultados_comision;
    public $total_resultados_txt;
    public $idarqueo;
    public $metodo_pago;
    public $idmetodopago;
    public $arrPay;
    public $comisiones;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Consolidado', 'informes');
    }

    protected function private_core()
    {
        parent::private_core();
        
        $this->agente = new agente();
        $this->almacenes = new almacen();
        $this->factura = new factura_cliente();
        $this->forma_pago = new forma_pago();
        $this->grupo = new grupo_clientes();
        $this->huecos = array();
        $this->serie = new serie();
        $this->metodo_pago = new metodo_pago();
        $this->desde = date('Y-m-d');
        $this->hasta = date('Y-m-d');
        $this->arrPay[0] = "---";
        foreach ($this->metodo_pago->all() as $k => $pay) {
            $this->arrPay[$pay->id] = $pay->nombre;
        }
        $this->mostrar = 'buscar';
        

        $this->offset = 0;
        if (isset($_REQUEST['offset'])) {
            $this->offset = intval($_REQUEST['offset']);
        }
        
        if(isset($_GET['download'])){
            $this->downloadReport();
        }

        $this->getResultados();
    }
   
    /**
     * Función para agregar los registros y mostrarlos en la tabla
     * @return Array 
     */
    public function getResultados(){
        $this->resultados = [];
        
        $sqlComision = "SELECT  idmetodopago, SUM(total) as total FROM comision_empleados WHERE idmetodopago != '' ";
        $sqlFactcompra = "SELECT  idmetodopago, SUM(total) as total FROM facturasprov WHERE idmetodopago != '' ";
        $sqlGastos = "SELECT  idmetodopago, SUM(total) as total FROM registro_gastos WHERE idmetodopago != '' ";
        $sqlFactcli = "SELECT  idmetodopago, SUM(total) as total FROM facturascli WHERE idmetodopago != '' ";

        if(isset($_REQUEST['metodo_pago']) && $_REQUEST['metodo_pago'] != '' ){
            $this->idmetodopago = $_REQUEST['metodo_pago'];
            $sqlComision .= " AND idmetodopago = '".$_REQUEST['metodo_pago']."' ";
            $sqlFactcompra .= " AND idmetodopago = '".$_REQUEST['metodo_pago']."' ";
            $sqlGastos .= " AND idmetodopago = '".$_REQUEST['metodo_pago']."' ";
            $sqlFactcli .= " AND idmetodopago = '".$_REQUEST['metodo_pago']."' ";
        }
        if(isset($_REQUEST['desde']))
            $this->desde = $_REQUEST['desde'];
        
        $sqlComision .= " AND ultmod >= '".$this->desde." 00:00:01' ";
        $sqlFactcompra .= " AND fecha >= '".$this->desde." 00:00:01' ";
        $sqlGastos .= " AND fecha >= '".$this->desde." 00:00:01' ";
        $sqlFactcli .= " AND fecha >= '".$this->desde." 00:00:01' ";
        
        if(isset($_REQUEST['hasta']))
            $this->hasta = $_REQUEST['hasta'];  

        $sqlComision .= " AND ultmod <= '".$this->hasta." 23:59:59' ";
        $sqlFactcompra .= " AND fecha <= '".$this->hasta." 23:59:59' ";
        $sqlGastos .= " AND fecha <= '".$this->hasta." 23:59:59' ";
        $sqlFactcli .= " AND fecha <= '".$this->hasta." 23:59:59' ";
        
        
        $factcompra_total = $this->db->select($sqlFactcompra);
        $factcompra = $this->db->select($sqlFactcompra." group by idmetodopago ");

        $comisiones_total = $this->db->select($sqlComision);
        $comisiones = $this->db->select($sqlComision." group by idmetodopago ");

        $factcli_total = $this->db->select($sqlFactcli);
        $factcli = $this->db->select($sqlFactcli." group by idmetodopago ");

        $gastos_total = $this->db->select($sqlGastos);
        $gastos = $this->db->select($sqlGastos." group by idmetodopago ");

        $this->resultados['comisiones'] = $comisiones;
        $this->resultados['comisiones_total'] = $comisiones_total[0]['total'];

        $this->resultados['factcompra'] = $factcompra;
        $this->resultados['factcompra_total'] = $factcompra_total[0]['total'];

        $this->resultados['factcli'] = $factcli;
        $this->resultados['factcli_total'] = $factcli_total[0]['total'];

        $this->resultados['gastos'] = $gastos;
        $this->resultados['gastos_total'] = $gastos_total[0]['total'];

    }

    function downloadReport() : void {
        $sqlComision = "SELECT * FROM comision_empleados WHERE idmetodopago != '' ";
        $sqlComision = "SELECT ultmod as date,'COMISIÓN' as module, t1.reg as code, t1.nombre_empleado as detail, (SELECT t0.nombre FROM metodospago t0 WHERE t0.id = t1.idmetodopago limit 1) as method,  t1.user_responsable as user, 'N/A' as cash, 'N/A' as product, 'PAGO COMISIÓN' as description, 'N/A' as observation, 0 as unitsel, 0 as totalup, 0 as ivaup, 'undc' as unitshop, t1.total as totale,  0 as ivae FROM comision_empleados t1 WHERE t1.idmetodopago != '' ";
        $sqlFactcompra = "SELECT CONCAT(t1.fecha,' ',t1.hora) as date,'COMPRA' as module, t1.codigo as code, (SELECT t0.razonsocial FROM proveedores t0 WHERE t0.codproveedor = t1.codproveedor limit 1) as detail, (SELECT t0.nombre FROM metodospago t0 WHERE t0.id = t1.idmetodopago limit 1) as method,  (SELECT t0.nick FROM fs_users t0 WHERE t0.codagente = t1.codagente  limit 1) as user, 'N/A' as cash, t2.referencia as product, t2.descripcion as description, t1.observaciones as observation, 'undve' as unitsel, 0 as totalup, 0 as ivaup, 'undc' as unitshop, t1.total as totale,  t1.totaliva as ivae  FROM lineasfacturasprov t2 INNER JOIN facturasprov t1 ON t1.idfactura = t2.idfactura WHERE t1.idmetodopago != '' ";
        $sqlGastos = "SELECT CONCAT(t1.fecha,' ',t1.hora) as date,'GASTO' as module, t1.codigo as code, (SELECT t0.razonsocial FROM proveedores t0 WHERE t0.codproveedor = t1.codproveedor limit 1) as detail, (SELECT t0.nombre FROM metodospago t0 WHERE t0.id = t1.idmetodopago limit 1) as method,  (SELECT t0.nick FROM fs_users t0 WHERE t0.codagente = t1.codagente  limit 1) as user, t1.idarqueo as cash, t2.referencia as product, t2.descripcion as description, t1.observaciones as observation, 'undve' as unitsel, 0 as totalup, 0 as ivaup, 'undc' as unitshop, t1.total as totale,  t1.totaliva as ivae FROM lineasgastos t2 INNER JOIN registro_gastos t1 ON t1.idfactura = t2.idfactura WHERE t1.idmetodopago != '' ";
        $sqlFactcli = "SELECT CONCAT(t1.fecha,' ',t1.hora) as date,'VENTA' as module, t1.codigo as code, t1.nombrecliente as detail, (SELECT t0.nombre FROM metodospago t0 WHERE t0.id = t1.idmetodopago limit 1) as method,  (SELECT t0.nick FROM fs_users t0 WHERE t0.codagente = t1.codagente  limit 1) as user, t1.id_arqueo as cash, t2.referencia as product, CONCAT(t2.descripcion,' - ',t2.proveedor_lav) as description, t1.observaciones as observation, 'undve' as unitsel, t1.total as totalup, t1.totaliva as ivaup, 'undc' as unitshop, 0 as totale,  0 as ivae FROM lineasfacturascli t2 INNER JOIN facturascli t1 ON t1.idfactura = t2.idfactura WHERE t1.idmetodopago != '' ";

        if(isset($_REQUEST['metodo_pago']) && $_REQUEST['metodo_pago'] != '' ){
            $this->idmetodopago = $_REQUEST['metodo_pago'];
            $sqlComision .= " AND idmetodopago = '".$_REQUEST['metodo_pago']."' ";
            $sqlFactcompra .= " AND idmetodopago = '".$_REQUEST['metodo_pago']."' ";
            $sqlGastos .= " AND idmetodopago = '".$_REQUEST['metodo_pago']."' ";
            $sqlFactcli .= " AND idmetodopago = '".$_REQUEST['metodo_pago']."' ";
        }
        if(isset($_REQUEST['desde']))
            $this->desde = $_REQUEST['desde'];
        
        $sqlComision .= " AND ultmod >= '".$this->desde." 00:00:01' ";
        $sqlFactcompra .= " AND fecha >= '".$this->desde." 00:00:01' ";
        $sqlGastos .= " AND fecha >= '".$this->desde." 00:00:01' ";
        $sqlFactcli .= " AND fecha >= '".$this->desde." 00:00:01' ";
        
        if(isset($_REQUEST['hasta']))
            $this->hasta = $_REQUEST['hasta'];  

        $sqlComision .= " AND ultmod <= '".$this->hasta." 23:59:59' ";
        $sqlFactcompra .= " AND fecha <= '".$this->hasta." 23:59:59' ";
        $sqlGastos .= " AND fecha <= '".$this->hasta." 23:59:59' ";
        $sqlFactcli .= " AND fecha <= '".$this->hasta." 23:59:59' ";
        
        //$factcompra_total = $this->db->select($sqlFactcompra);
        $factcompra = $this->db->select($sqlFactcompra);
        $comisiones_total = $this->db->select($sqlComision);
        $comisiones = $this->db->select($sqlComision);
        //$factcli_total = $this->db->select($sqlFactcli);
        $factcli = $this->db->select($sqlFactcli);
        $gastos_total = $this->db->select($sqlGastos);
        $gastos = $this->db->select($sqlGastos);

        $results = array_merge($factcompra, $comisiones, $factcli, $gastos);
        $this->generar_xls($results);
    }

    protected function generar_xls($results)
    {
        $this->template = FALSE;
        header("Content-Disposition: attachment; filename=\"informe_consolidado_" . time() . ".xls\"");
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $header = array(
            'FECHA' => 'string',
            'MODULO' => 'string',
            'CODIGO' => 'string',
            'CLIENTE/PROVEEDOR/LAVADOR' => 'string',
            'METODO PAGO' => 'string',
            'CAJERO/USER' => 'string',
            'ID ARQUEO' => 'string',
            'PRODUCTO/GASTO' =>  'string',
            'DESCRIPCION' =>  'string',
            'OBSERVACIONES' => 'string',
            'UND VEND' => '#,##0.00;[RED]-#,##0.00',
            'TOTAL INGRESO' => '#,##0.00;[RED]-#,##0.00',
            'IVA INGRESO' => '#,##0.00;[RED]-#,##0.00',
            'UND COMP' => '#,##0.00;[RED]-#,##0.00',
            'TOTAL EGRESO' => '#,##0.00;[RED]-#,##0.00',
            'IVA EGRESO' => '#,##0.00;[RED]-#,##0.00',
        );

        $writter = new XLSXWriter();
        $writter->setAuthor('FacturaScripts');
        $writter->writeSheetHeader("Consolidado", $header);

        foreach ($results as $doc) {
            $linea = array(
                'FECHA' => $doc['date'],
                'MODULO' => $doc['module'],
                'CODIGO' => $doc['code'],
                'CLIENTE/PROVEEDOR/LAVADOR' => $doc['detail'],
                'METODO PAGO' => $doc['method'],
                'CAJERO/USER' => $doc['user'],
                'ID ARQUEO' => $doc['cash'],
                'PRODUCTO/GASTO' =>  $doc['product'],
                'DESCRIPCION' =>  $doc['description'],
                'OBSERVACIONES' => $doc['observation'],
                'UND VEND' => $doc['unitsel'],
                'TOTAL INGRESO' => $doc['totalup'],
                'IVA INGRESO' => $doc['ivaup'],
                'UND COMP' => $doc['unitshop'],
                'TOTAL EGRESO' => $doc['totale'],
                'IVA EGRESO' => $doc['ivae'],
            );

            $writter->writeSheetRow("Consolidado", $linea);
        }

        $writter->writeToStdOut();
    }

    public function url($busqueda = FALSE)
    {
        if ($busqueda) {
            $codcliente = '';
            if ($this->cliente) {
                $codcliente = $this->cliente->codcliente;
            }

            $url = parent::url() . "&mostrar=" . $this->mostrar
                . "&query=" . $this->query
                . "&codagente=" . $this->codagente
                . "&idarqueo=" . $this->idarqueo
                . "&codalmacen=" . $this->codalmacen
                . "&codcliente=" . $codcliente
                . "&codgrupo=" . $this->codgrupo
                . "&codpago=" . $this->codpago
                . "&codserie=" . $this->codserie
                . "&idmetodopago=" . $this->idmetodopago
                . "&desde=" . $this->desde
                . "&estado=" . $this->estado
                . "&hasta=" . $this->hasta;

            return $url;
        }

        return parent::url();
    }

    public function paginas()
    {
        if ($this->mostrar == 'sinpagar') {
            $total = $this->total_sinpagar();
        } else if ($this->mostrar == 'buscar') {
            $total = $this->num_resultados;
        } else {
            $total = $this->total_registros();
        }

        return $this->fbase_paginas($this->url(TRUE), $total, $this->offset);
    }

    public function total_sinpagar()
    {
        return $this->fbase_sql_total('facturascli', 'idfactura', 'WHERE pagada = false');
    }

    private function total_registros()
    {
        return $this->fbase_sql_total('facturascli', 'idfactura');
    }

    private function buscar($order2)
    {
        $this->resultados = array();
        $this->num_resultados = 0;
        $sql = " FROM facturascli ";
        $where = 'WHERE ';

        if ($this->query) {
            $query = $this->agente->no_html(mb_strtolower($this->query, 'UTF8'));
            $sql .= $where;


            if (is_numeric($query)) {
                $sql .= "(codigo LIKE '%" . $query . "%' OR numero2 LIKE '%" . $query . "%' "
                    . "OR observaciones LIKE '%" . $query . "%' OR cifnif LIKE '" . $query . "%' OR lower(nombrecliente) LIKE '%" . $query . "%' )";
            } else {
                $sql .= "(lower(codigo) LIKE '%" . $query . "%'  OR lower(nombrecliente) LIKE '%" . $query . "%'  OR lower(numero2) LIKE '%" . $query . "%' "
                    . "OR lower(cifnif) LIKE '" . $query . "%' "
                    . "OR lower(observaciones) LIKE '%" . str_replace(' ', '%', $query) . "%')";
            }
            $where = ' AND ';
        }

        if ($this->cliente) {
            $sql .= $where . "codcliente = " . $this->agente->var2str($this->cliente->codcliente);
            $where = ' AND ';
        }
        
        if ($this->idarqueo) {
            $sql .= $where . "id_arqueo = '" .$this->idarqueo."'" ;
            $where = ' AND ';
        }

        if ($this->codagente != '') {
            $sql .= $where . "codagente = " . $this->agente->var2str($this->codagente);
            $where = ' AND ';
        }
     

        if ($this->codalmacen != '') {
            $sql .= $where . "codalmacen = " . $this->agente->var2str($this->codalmacen);
            $where = ' AND ';
        }

        if ($this->codgrupo != '') {
            $sql .= $where . "codcliente IN (SELECT codcliente FROM clientes WHERE codgrupo = " . $this->agente->var2str($this->codgrupo) . ")";
            $where = ' AND ';
        }

        if ($this->codpago != '') {
            $sql .= $where . "codpago = " . $this->agente->var2str($this->codpago);
            $where = ' AND ';
        }
        
        if ($this->codserie != '') {
            $sql .= $where . "codserie = " . $this->agente->var2str($this->codserie);
            $where = ' AND ';
        }

        if ($this->idmetodopago != '') {
            $sql .= $where . "idmetodopago = " . $this->agente->var2str($this->idmetodopago);
            $where = ' AND ';
        }

        if ($this->desde) {
            $sql .= $where . "fecha >= " . $this->agente->var2str($this->desde);
            $where = ' AND ';
        }

        if ($this->hasta) {
            $sql .= $where . "fecha <= " . $this->agente->var2str($this->hasta);
            $where = ' AND ';
        }

        if ($this->estado == 'pagadas') {
            $sql .= $where . "pagada";
            $where = ' AND ';
        } else if ($this->estado == 'impagadas') {
            $sql .= $where . "pagada = false";
            $where = ' AND ';
        } else if ($this->estado == 'anuladas') {
            $sql .= $where . "anulada = true";
            $where = ' AND ';
        } else if ($this->estado == 'sinasiento') {
            $sql .= $where . "idasiento IS NULL";
            $where = ' AND ';
        }

        $data = $this->db->select("SELECT COUNT(idfactura) as total" . $sql);
        if ($data) {
            $this->num_resultados = intval($data[0]['total']);

            $data2 = $this->db->select_limit("SELECT *" . $sql . " ORDER BY " . $this->order . $order2, FS_ITEM_LIMIT, $this->offset);
            if ($data2) {
                foreach ($data2 as $d) {
                    $this->resultados[] = new factura_cliente($d);
                }
            }

            $data2 = $this->db->select("SELECT coddivisa,SUM(total) as total" . $sql . " GROUP BY coddivisa");
            if ($data2) {
                $this->total_resultados_txt = 'Suma total de los resultados:';

                foreach ($data2 as $d) {
                    $this->total_resultados[] = array(
                        'coddivisa' => $d['coddivisa'],
                        'total' => floatval($d['total'])
                    );
                }
            }

            if ($this->codagente !== '') {
                /// calculamos la comisión del empleado
                $data2 = $this->db->select("SELECT SUM(neto*porcomision/100) as total" . $sql);
                if ($data2) {
                    $this->total_resultados_comision = floatval($data2[0]['total']);
                }
            }
        }
    }

}
