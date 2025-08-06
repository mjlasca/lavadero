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

use FacturaScripts\model\registro_gasto;

require_once 'plugins/facturacion_base/extras/fbase_controller.php';

class tpv_caja extends fbase_controller
{

    public $almacen;
    public $caja;
    public $offset;
    public $resultados;
    public $serie;
    public $terminal;
    public $terminales;
    public $datos_cierre_caja;
    public $agente;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Arqueos y terminales', 'TPV');
    }

    protected function private_core()
    {
        parent::private_core();
        
        $this->share_extensions();
        
        
        
        
        $this->almacen = new almacen();
        $this->caja = new caja();
        $this->serie = new serie();
        
        $terminal = new terminal_caja();

        if (isset($_POST['nuevot'])) { /// nuevo terminal
            $this->nuevo_terminal($terminal);
        } else if (isset($_POST['idt'])) { /// editar terminal
            $this->editar_terminal($terminal);
        } else if (isset($_GET['deletet'])) { /// eliminar terminal
            $this->eliminar_terminal($terminal);
        } else if (isset($_GET['delete'])) { /// eliminar caja
            $this->eliminar_caja();
        } else if (isset($_GET['cerrar'])) {
            $sql = "SELECT * FROM facturascli WHERE anulada=0 AND id_arqueo='".$_GET['cerrar']."' AND pagada<1  AND codpago='CONT'";
            
            if($this->db->select($sql))
                $this->new_error_msg ("No se puede cerrar el arqueo si no ha pagado todas las facturas contado");
            else
                $this->cerrar_caja();
        } else if(isset($_REQUEST['arqueo1'])){
            if($_REQUEST['arqueo1'] == $_REQUEST['arqueo2']){
                $this->new_error_msg("No puede ser el mismo arqueo");
            }else{
                $this->volcandoarqueo($_REQUEST['arqueo1'],$_REQUEST['arqueo2']);
            }
            
            
        }

        $this->offset = 0;
        if (isset($_GET['offset'])) {
            $this->offset = intval($_GET['offset']);
        }

        $this->resultados = $this->caja->all($this->offset);
        //$this->datos_cierre_caja($identificador);
        $this->terminales = $terminal->all();
        
        
        
        if (isset($_GET['imprimir_caja'])) {
                        $this->terminal = new terminal_caja();
                        $t = new terminal_caja();
                        $t = $this->terminal->get_cod("TT");
                        $caja2 = new caja();
                        $this->agente = new agente();
                        
                        $this->cerrar_caja1($caja2->get_id($_GET['imprimir_caja']), $t);
                        
        }
        
        
    }
    
    
    public  function datos_cierre_caja($identificador, $fecha1="", $fecha2="", $arqueo){
       /* $sql="SELECT  t1.codagente, t2.id, "
                . " SUM(t1.total) as total "
                . "  FROM facturascli t1 INNER JOIN cajas t2 ON "
                . " IF(t2.f_fin=null, t1.horaas > t2.f_inicio, (t1.horaas BETWEEN t2.f_inicio AND t2.f_fin)) AND  t1.anulada=0  group by t2.id ";*/
        
        //$sql="SELECT  t1.codagente, t2.id, SUM(t1.total) as total "
          //      . " FROM facturascli t1 INNER JOIN cajas t2 ON (t1.horaas BETWEEN t2.f_inicio AND t2.f_fin) AND t1.codagente= '".$identificador."' AND   t1.pagada='1'    group by t2.id ";
        

        //if($arqueo > 73){
            $sql="SELECT  SUM(total) as total FROM facturascli WHERE id_arqueo = '".$arqueo."' AND  codpago='CONT' AND pagada=1 AND anulada = 0 AND codagente= '".$identificador."'  ";

            
        /*}else{
        $sql="SELECT  t1.codagente, t2.id, SUM(t1.total) as total, DATE(t1.horaas) as horaas "
              . " FROM facturascli t1 INNER JOIN cajas t2 "
                . " ON  ( TIME(t1.horaas)  BETWEEN '".substr($fecha1, -8)."' AND  '".substr($fecha2, -8)."' ) "
                . " AND DATE(t1.horaas) = DATE(t2.f_inicio) AND t1.codagente= '".$identificador."' AND "
                . "  t1.pagada=1 AND t1.anulada = 0   group by t2.id ";
        
        }*/
      
        
        
        $this->datos_cierre_caja = $this->db->select($sql);
        
        
       
        
        if($this->datos_cierre_caja) {
             /*$sql = "UPDATE facturascli SET id_arqueo='".$arqueo."' WHERE ( TIME(horaas)  BETWEEN '".substr($fecha1, -8)."' AND  '".substr($fecha2, -8)."'  )"
                . "AND DATE(horaas) = '".$this->datos_cierre_caja[0]["horaas"]."' "
                . " AND codagente = '".$identificador."' ";
        
             $this->db->exec($sql);
            echo "</br></br>".$sql;*/
            $this->datos_cierre_caja = $this->datos_cierre_caja[0];
        }
        else{
            $this->datos_cierre_caja["total"] = 0;
        }
        
        
    }

    /*
    * Función para seleccionar arqueos hasta 6 meses antes de la fecha actual
    */
    public function get_arqueos(){
        $fecha_actual = date('Y-m-d');
        $fecha = date("d-m-Y",strtotime($fecha_actual."- 6 month"));
        $sql = "SELECT id  FROM cajas WHERE DATE(f_inicio) > '".$fecha."' ORDER BY id DESC ";

        return $this->db->select($sql);
    }


    /**
     * Función para volcar todo un arqueo en otro
     */
    private function volcandoarqueo($arqueo1, $arqueo2){
        
        //Arqueo uno
        $get_arqueo1 = new caja;
        $get_arqueo1 = $get_arqueo1->get($arqueo1);

        //Arqueo dos
        $get_arqueo2 = new caja;
        $get_arqueo2 = $get_arqueo2->get($arqueo2);

        if($get_arqueo1->fecha_fin){
            $hora = substr($get_arqueo2->fecha_inicial, 11,8);
            $get_arqueo2->fecha_inicial = substr($get_arqueo2->fecha_inicial, 6,4)."-".substr($get_arqueo2->fecha_inicial, 3,2)."-".substr($get_arqueo2->fecha_inicial, 0,2);

            $sql = "UPDATE cajas SET tickets = '".$get_arqueo1->tickets."' + tickets, d_fin = '".$get_arqueo1->dinero_fin."' + d_fin - '".$get_arqueo1->dinero_inicial."' WHERE id = '".$get_arqueo2->id."' ";

            $sql1 = "UPDATE facturascli SET 
            id_arqueo = '".$get_arqueo2->id."', codagente = '".$get_arqueo2->codagente."',
            fecha = '".$get_arqueo2->fecha_inicial."', hora = '".$hora."'
            WHERE id_arqueo = '".$get_arqueo1->id."' ";

            if(!$this->db->exec($sql)){
                $this->new_error_msg("Hubo un error al tratar de volcar los datos en 1ra. parte");
            }
            if(!$this->db->exec($sql1)){
                $this->new_error_msg("Hubo un error al tratar de volcar los datos en 2da. parte");
            }else{
                if(!$get_arqueo2->fecha_fin){
                    $this->pagar_facturas_all($get_arqueo2->id);
                    $caja2 = $this->caja->get($get_arqueo2->id);
                
                    if ($caja2) {
                        $caja2->fecha_fin = Date('d-m-Y H:i:s');
                        $caja2->cierremanual = $get_arqueo2->dinero_fin;
                        if (!$caja2->save()) 
                            $this->new_error_msg("¡Imposible cerrar el arqueo!");
                    } else
                        $this->new_error_msg("Arqueo no encontrado.");
                }
                $this->new_message("Se han volcado los datos correctamente al arqueo No. ".$get_arqueo2->id);
                $get_arqueo1->delete();
            }
        }else{
            $this->new_error_msg("El arqueo ".$get_arqueo1->id." debe estar cerrado");
        }

        

        /*echo "</br></br></br>";
        print_r($get_arqueo2);
        echo "--> ".$sql;
        echo "</br>";
        echo "--> ".$sql1;*/

        
    }


    private function pagar_facturas_all($id){
        $sql = "UPDATE facturascli SET pagada = 1 WHERE codpago = 'CONT' AND id_arqueo = '".$id."' ";
        if(!$this->db->exec($sql))
            $this->new_message ("Hubo un error al tratar de pagar todas las facutas contado del arqueo ".$id);
        
    }

    private function nuevo_terminal(&$terminal)
    {
        $terminal->codalmacen = $_POST['codalmacen'];
        $terminal->codserie = $_POST['codserie'];

        $terminal->codcliente = NULL;
        if ($_POST['codcliente'] != '') {
            $terminal->codcliente = $_POST['codcliente'];
        }

        $terminal->anchopapel = intval($_POST['anchopapel']);
        $terminal->comandoapertura = $_POST['comandoapertura'];
        $terminal->comandocorte = $_POST['comandocorte'];
        $terminal->num_tickets = intval($_POST['num_tickets']);
        $terminal->sin_comandos = isset($_POST['sin_comandos']);

        if ($terminal->save()) {
            $this->new_message('Terminal añadido correctamente.');
            header('Location: index.php?page=tpv_recambios');
        } else
            $this->new_error_msg('Error al guardar los datos.');
    }

    private function editar_terminal(&$terminal)
    {
        $t2 = $terminal->get($_POST['idt']);
        if ($t2) {
            $t2->codalmacen = $_POST['codalmacen'];
            $t2->codserie = $_POST['codserie'];

            $t2->codcliente = NULL;
            if ($_POST['codcliente'] != '') {
                $t2->codcliente = $_POST['codcliente'];
            }

            $t2->anchopapel = intval($_POST['anchopapel']);
            $t2->comandoapertura = $_POST['comandoapertura'];
            $t2->comandocorte = $_POST['comandocorte'];
            $t2->num_tickets = intval($_POST['num_tickets']);
            $t2->sin_comandos = isset($_POST['sin_comandos']);

            if ($t2->save()) {
                $this->new_message('Datos guardados correctamente.');
            } else
                $this->new_error_msg('Error al guardar los datos.');
        } else
            $this->new_error_msg('Terminal no encontrado.');
    }

    private function eliminar_terminal(&$terminal)
    {
        if ($this->user->admin) {
            $t2 = $terminal->get($_GET['deletet']);
            if ($t2) {
                if ($t2->delete()) {
                    $this->new_message('Terminal eliminado correctamente.');
                } else
                    $this->new_error_msg('Error al eliminar el terminal.');
            } else
                $this->new_error_msg('Terminal no encontrado.');
        } else
            $this->new_error_msg("Solamente un administrador puede eliminar terminales.");
    }

    private function eliminar_caja()
    {
        if ($this->user->admin) {
            $caja2 = $this->caja->get($_GET['delete']);
            if ($caja2) {
                if ($caja2->delete()) {
                    $this->new_message("Arqueo eliminado correctamente.");
                } else
                    $this->new_error_msg("¡Imposible eliminar el arqueo!");
            } else
                $this->new_error_msg("Arqueo no encontrado.");
        } else
            $this->new_error_msg("Solamente un administrador puede eliminar arqueos.");
    }

    private function cerrar_caja()
    {
        if ($this->user->admin) {
            $caja2 = $this->caja->get($_GET['cerrar']);
            
            if ($caja2) {
                $caja2->fecha_fin = Date('d-m-Y H:i:s');
                $caja2->cierremanual = $_REQUEST["dinero_caja"];
                if ($caja2->save()) {
                    $this->new_message("Arqueo cerrado correctamente.");
                } else
                    $this->new_error_msg("¡Imposible cerrar el arqueo!");
            } else
                $this->new_error_msg("Arqueo no encontrado.");
        }
        else {
            $this->new_error_msg("El procedimiento normal es cerrar el arqueo desde el propio TPV, pulsando el botón"
                . " <b>cerrar caja</b>. Para forzar el cierre desde esta pantalla debes ser administrador.");
        }
    }

    public function anterior_url()
    {
        $url = '';

        if ($this->offset > 0) {
            $url = $this->url() . "&offset=" . ($this->offset - FS_ITEM_LIMIT);
        }

        return $url;
    }

    public function siguiente_url()
    {
        $url = '';

        if (count($this->resultados) == FS_ITEM_LIMIT) {
            $url = $this->url() . "&offset=" . ($this->offset + FS_ITEM_LIMIT);
        }

        return $url;
    }
    
     /*
     * Se consulta las facturas para saber cuánto ha generado por lavador o por 
      * artículos, es decir, cuando la factura no tiene ningún lavador asignado
     */
    private function desgloce_lavador($empleado, $id_arqueo){
        
        $sql = "SELECT SUM(t2.pvptotal * (1+ (t2.iva/100))) as total, t2.proveedor_lav as proveedor_servicio FROM facturascli t1 INNER JOIN lineasfacturascli t2 WHERE   t2.proveedor_lav != '' AND t1.idfactura = t2.idfactura AND  t1.codagente = '".$empleado."' AND t1.id_arqueo='".$id_arqueo."' AND t1.anulada='0' GROUP BY t2.proveedor_lav";
        $consulta = $this->db->select($sql);
        return $consulta;
    }
    
    
    /*
     * Se consulta las facturas crédito que se hicieron en el arqueo con id $id
     * y el empleado
     */
    private function facturas_credito($empleado, $id_arqueo){
        
        $sql = "SELECT SUM(total) as total FROM facturascli WHERE codpago != 'CONT' AND anulada = 0 AND   codagente = '".$empleado."' AND id_arqueo='".$id_arqueo."'";
        
        $consulta = $this->db->select($sql);
        
        if($consulta)
            return $consulta[0]["total"];
        else
            return 0;
        
    }
    
    
    /*
     * Se consulta las facturas contado que se hicieron en el arqueo con id $id
     * y el empleado
     */
    private function facturas_contado($empleado, $id_arqueo){
        
        $sql = "SELECT SUM(total) as total FROM facturascli WHERE pagada=1 AND anulada = 0 AND  codagente = '".$empleado."' AND id_arqueo='".$id_arqueo."'";
        
        $consulta = $this->db->select($sql);
        
        if($consulta)
            return $consulta[0]["total"];
        else
            return 0;
        
    }

    function getMetodosPagoVal($idarqueo) : array {
        $result = [];
        $metodopago = new metodo_pago();
        
        return $metodopago->getTotalCash($idarqueo) ?? [];
    }
    
    private function cerrar_caja1($caja_im, $terminal)
    {
        $registrogasto = new registro_gasto();
            if ($this->terminal) {
                
                $this->terminal->cod_letra_tickect();
                $this->terminal->anchopapel = 28;
                $this->terminal->add_linea_big("\nCIERRE DE CAJA:\n\n");
                //$this->terminal->add_linea("Empleado: " . $this->user->codagente . " " . $this->agente->get_fullname() . "\n");
                
                $this->terminal->add_linea("Caja: " . $caja_im[0]["fs_id"] ."  ID Arqueo: ".$caja_im[0]["id"]." \n");
                $this->terminal->add_linea("Fecha Arqueo: \n" . date("Y-m-d H:i:s"). "\n");
                $this->terminal->add_linea("Fecha ini: \n" . $caja_im[0]["f_inicio"] . "\n");
                $this->terminal->add_linea("Fecha fin: \n" . $caja_im[0]["f_fin"] . "\n");
                $this->terminal->add_linea("Diferencia: $ " . $this->formato_moneda($caja_im[0]["d_fin"] - $caja_im[0]["d_inicio"]) . "\n");
                $this->terminal->add_linea("Tickets: " . $caja_im[0]["tickets"] . "\n\n");
                
                $this->terminal->add_linea_big("Arqueo caja: \n\n");
                
                $this->terminal->add_linea_big("Métodos de pago: \n");
                foreach ($this->getMetodosPagoVal($caja_im[0]["id"]) as $meth) {
                    $this->terminal->add_linea($meth['nombre']."      $" .  sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($meth['total']) . "\n" ));
                }
                $this->terminal->add_linea("\nGastos      $" .  sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($registrogasto->get_sum_idarqueo($caja_im[0]["id"])) . "\n" ));
                $this->terminal->add_linea_big("\n\n");
                /*
                 * sprintf sirve para alinear la impresión a la derecha, el número en $this->terminal->anchopapel - 16 determina los espacios después del título
                 * y así acomoda cada resultado alineado a la derecha
                 */
                $this->terminal->add_linea("Dinero inic $" . sprintf("%" . ($this->terminal->anchopapel - 13) . "s",$this->formato_moneda($caja_im[0]["d_inicio"]) ) . "\n");
                $this->terminal->add_linea("Fact. Ctado $". sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($this->facturas_contado($caja_im[0]["codagente"], $caja_im[0]["id"])). "\n") );
                $this->terminal->add_linea("Fact. Cdito $". sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($this->facturas_credito($caja_im[0]["codagente"], $caja_im[0]["id"])). "\n") );
                $this->terminal->add_linea("Dnero Manua $" .  sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($caja_im[0]["cierremanual"]) . "\n" ));
                $this->terminal->add_linea("Dnero final $" .  sprintf("%" . ($this->terminal->anchopapel - 11) . "s", $this->formato_moneda($caja_im[0]["d_fin"] - $this->facturas_credito($caja_im[0]["codagente"], $caja_im[0]["id"])) . "\n\n" ));
                
                $this->terminal->add_linea_big("Por Artículo \n\n");
                
                $articulos = $this->familiasCierre($caja_im[0]["id"]);
                $sumaArticulos = 0;
                if($articulos){
                    foreach($articulos as $art){
                        $refe = $art["referencia"];
                        while(strlen($refe)<=11){
                            $refe .= " ";
                        }
                        $refe .= "$";
                        $this->terminal->add_linea($refe .sprintf("%" . ($this->terminal->anchopapel - 12) . "s", "(".$art["cantidad"].")".$this->formato_moneda($art["sumita"]) . "\n"));  
                        $sumaArticulos += $art["sumita"];
                    }
                }
                
                $this->terminal->add_linea("\nTOTAL:      $" . sprintf("%" . ($this->terminal->anchopapel - 11) . "s", $this->formato_moneda($sumaArticulos) . "\n\n"));

                $this->terminal->add_linea_big("\nPor Lavador \n\n");
                $total_proveedores = 0;
                if($this->desgloce_lavador($caja_im[0]["codagente"], $caja_im[0]["id"])){
                    $consulta = $this->desgloce_lavador($caja_im[0]["codagente"], $caja_im[0]["id"]);
                    for($i=0 ; $i < count($consulta) ; $i++){
                        //if($consulta[$i]["proveedor_servicio"] == "0")
                          //  $this->terminal->add_linea("ARTICULOS:  $" .sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($consulta[$i]["total"]) . "\n"));
                        if($consulta[$i]["proveedor_servicio"] != "0"){
                            $proveedor_temp = substr($consulta[$i]["proveedor_servicio"], 0, 11);
                            
                            if(strlen($consulta[$i]["proveedor_servicio"]) > 11){
                                $proveedor_temp .= ".$";
                            }else{
                                while(strlen($proveedor_temp)<=11){
                                    $proveedor_temp .= " ";
                                }
                                $proveedor_temp .= "$";
                                
                            }
                            $this->terminal->add_linea($proveedor_temp. sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($consulta[$i]["total"]) . "\n"));
                            if($consulta[$i]["total"] != "")
                                $total_proveedores += $consulta[$i]["total"];
                        }
                        
                    }
                    $this->terminal->add_linea("\nTOTAL:      $" . sprintf("%" . ($this->terminal->anchopapel - 11) . "s", $this->formato_moneda($total_proveedores) . "\n\n"));
                }
                $this->terminal->add_linea("Observaciones:\n\n\n\n");
                $this->terminal->add_linea("Firma:\n\n\n\n\n\n\n\n\n\n");
                $this->terminal->cortar_papel();
                $this->terminal->codserie = "TT";
                $this->terminal->id = $terminal->id;
                
                //print_r($this->terminal);

                $this->terminal->save();
                /*
                /// recargamos la página
                header('location: ' . $this->url() . '&terminal=' . $this->terminal->id);
            } else {
                /// recargamos la página
                header('location: ' . $this->url());*/
            }
            
        
    }

    public function familiasCierre($idarqueo){
        
        $sql = "SELECT t1.referencia, SUM(t1.pvptotal * (1+ (t1.iva/100))) as sumita, SUM(t1.cantidad) as cantidad  FROM lineasfacturascli t1 INNER JOIN facturascli t2 ON t2.anulada = 0 AND t2.id_arqueo = '".$idarqueo."' AND t1.idfactura = t2.idfactura AND ( t1.proveedor_lav IS NULL OR  t1.proveedor_lav = '' OR  t1.proveedor_lav = '0' )    GROUP BY t1.referencia";

        return $this->db->select($sql);

    }
    
    public function formato_moneda($number){
            $text =number_format($number, 2); 
            return  $text;
        
        }
    private function share_extensions()
    {
        $fsext = new fs_extension();
        $fsext->name = 'api_remote_printer';
        $fsext->from = __CLASS__;
        $fsext->type = 'api';
        $fsext->text = 'remote_printer';
        $fsext->save();
    }
}
