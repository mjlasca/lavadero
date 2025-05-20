<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use FacturaScripts\model\metodo_pago;

/**
 * Description of servicios_lav
 *
 * @author LASCA
 */
class informes_lav extends fs_controller {
    //put your code here
    
	public $fecha_inicio;
	public $fecha_final;
	public $hora_inicio;
	public $hora_final;
	
	public $info_lavaderos;
	public $info_lavaderos_total;
        
        public $proveedores;
        
        public $informe_aspiradores; 
	public $info_aspiradores_total;
        
        public $informe_jefes;
        public $info_jefes_total;
        
	public $info_provee;
	public $nav_activo;
    public $metodo_pago;
        
        public $consolidado;
        public $terminal;
        public $reciboSI;
        public $num_servicios;
        public $deducciones_prov;
        public $registros_lavador_comsion;
        public $ano;
        public $consecutivo;
        public $total_a_liquidar;
        public $titulo_proveedor;
        public $tip_lavador;
        public $buscar_empleado;
        public $vista_cambio_valores;

    public function __construct() {
        parent::__construct(__CLASS__, 'Informes Lavadero', 'informes');
        
        
    }
    

    
    protected function private_core()
    {
                $this->share_extensions();
                $this->allow_delete = $this->user->admin;
                
		 //   $this->template = FALSE;
		 $this->fecha_inicio = date("d-m-Y");
		 $this->fecha_final = date("d-m-Y");
		 $this->info_provee = "";
		 $this->info_lavaderos_total=0;
         $this->metodo_pago = new metodo_pago();
                 $this->nav_activo = 1;
                 $this->vista_cambio_valores = FALSE;
                 
                 
                 $this->consolidado = isset($_POST['consolidado']);
                 
                 
		 $this->hora_inicio = "00:00:00";
		 $this->hora_final = "23:59:00";
                 
                 $this->deducciones_prov = 0;
                 
                 
                 $this->ano  = array();
                 
                 $this->ano[] = date("Y");
                 $this->ano[] = date("Y") - 1;



                 $this->consecutivo = "";
                 
	 
		if (isset($_POST['info_provee'])) {
			
			$this->fecha_final = substr($_POST["fec_hasta"], -4). "-" .  substr($_POST["fec_hasta"], 3, 2) . "-" .  substr($_POST["fec_hasta"], 0, 2);
			$this->fecha_inicio = substr($_POST["fec_desde"], -4). "-" .  substr($_POST["fec_desde"], 3, 2). "-" .  substr($_POST["fec_desde"], 0, 2);
			$this->hora_inicio = $_POST["hora_desde"];
			$this->hora_final = $_POST["hora_hasta"];
			$this->info_provee = $_POST["info_provee"];
                        
			$this->nav_activo = $_POST["nav_activo"];
                        
                        $this->proveedores = $_POST['info_provee'];
                        
                        
                    $this->deducciones_prov();
                    //$this->consolidado();
                        
                    //echo "AJA ".isset($_POST['fec_desde']);
                    if($this->nav_activo == 1){
                        $this->titulo_proveedor = "LAVADOR";
                        $this->informe_lavaderos();
                    }
                    if($this->nav_activo == 2){
                        $this->titulo_proveedor = "ASPIRADOR";
                        $this->informe_aspiradores();
                    }
                    
                    if($this->nav_activo == 3){
                        $this->titulo_proveedor = "JEFE PATIO";
                        $this->informe_jefes();
                    }
                    
                    $this->fecha_inicio =  $_POST["fec_desde"];
                    $this->fecha_final = $_POST["fec_hasta"];

		}
                
                if(isset($_REQUEST["nav_activo"])){
                    if($_REQUEST["nav_activo"] == 1){
                        $this->titulo_proveedor = "LAVADOR   ";
                    }
                    if($_REQUEST["nav_activo"] == 2){
                        $this->titulo_proveedor = "ASPIRADOR ";
                    }

                    if($_REQUEST["nav_activo"] == 3){
                        $this->titulo_proveedor = "JEFE PATIO";
                    }
                }

                
                 /*
                   * IMPRESIÓN DE RECIBOS DE LOS LAVADEROS EN IMPRESORA POS
                     * Si no existe el terminal, se creará el terminal con codserie = R
                     */
		 if(isset($_REQUEST["imprime_recibo"])){
                     
                    $this->terminal = new terminal_caja();
                    
                    $sql = "SELECT id FROM cajas_terminales WHERE codserie='R'";
                    $id_terminal_impresion = $this->db->select($sql);
                    
                        if(!$id_terminal_impresion){
                            $sql = "INSERT INTO cajas_terminales(codalmacen,codserie,anchopapel,comandocorte,comandoapertura,num_tickets,sin_comandos) "
                                    . "VALUES('ALG','R','40','27.105','27.112.48','1','-1') ";
                           $this->db->exec($sql);
                           $sql = "SELECT id FROM cajas_terminales WHERE codserie='R'";
                           $id_terminal_impresion = $this->db->select($sql);
                        }

                        if($id_terminal_impresion){
                            $this->terminal = $this->terminal->get($id_terminal_impresion[0]["id"]);

                            if(isset($_REQUEST["reporte_comision"])){
                                if($this->generar_pago_comisiones($_REQUEST["lavador_imp"],$_REQUEST["nav_activo"])){
                                    $this->imprimir_recibo();
                                }
                            }else{
                                $this->imprimir_recibo();
                            }
                            
                            
                            
                        }
                        else{
                            $this->new_error_msg("No se encuentra el terminal para iniciar la impresión del recibo");
                        }
                        
                        
                    $this->info_provee = $_REQUEST["lavador_imp"];
                    $this->fecha_inicio =  $_POST["fec_desde"];
                    $this->fecha_final = $_POST["fec_hasta"];
                     
                     
                 }
                
                $this->comisiones_pestana();

        }//FIN CORE
        
        
        public function comisiones_pestana(){
            if(isset($_REQUEST["tip_lavador"])){
            
                $sql = "SELECT * FROM comision_empleados WHERE "
                        . " ( nombre_empleado LIKE '%".$_REQUEST["buscar_empleado"]."%' OR  reg LIKE '%".$_REQUEST["buscar_empleado"]."%' )"
                        . "AND YEAR(ultmod) = '".$_REQUEST["ano_con"]."'"
                        . " AND tipo_empleado = '".$_REQUEST["tip_lavador"]."'";
                $this->tip_lavador = $_REQUEST["tip_lavador"];
                $this->buscar_empleado = $_REQUEST["buscar_empleado"];
                $this->registros_lavador_comsion = $this->db->select($sql);
                $this->nav_activo = 4;
            }
        }
        
        
        
        /*
         * Función para generar registro de comisión, el tipo de empleado se refiere a si es lavador, aspirador o jefe de patio
         */
        public  function generar_pago_comisiones($empleado, $tipo_empleado){
            
            
            
            //$final_mes = cal_days_in_month(CAL_GREGORIAN, $mes, $ano); // 31
            $fecha_final = substr($_POST["fec_hasta"], -4). "-" .  substr($_POST["fec_hasta"], 3, 2) . "-" .  substr($_POST["fec_hasta"], 0, 2);
            $fecha_inicio = substr($_POST["fec_desde"], -4). "-" .  substr($_POST["fec_desde"], 3, 2). "-" .  substr($_POST["fec_desde"], 0, 2);
            
            
            $sql = "SELECT reg,fecha_inicial,fecha_final FROM comision_empleados "
                    . " WHERE nombre_empleado = '".$_REQUEST["lavador_imp"]."' AND tipo_empleado = '".$tipo_empleado."' AND "
                    . " ( fecha_inicial BETWEEN '".$fecha_inicio."' AND  '".$fecha_final."' OR fecha_final BETWEEN '".$fecha_inicio."' AND '".$fecha_final."' || "
                    . " ( fecha_inicial BETWEEN '".$fecha_inicio."' AND  '".$fecha_final."' AND fecha_final BETWEEN '".$fecha_inicio."' AND '".$fecha_final."' )  )";
            //Esta consulta revisa si ya se hizo una comisión para éste empleado en ese rango de fechas y 
            $consulta = $this->db->select($sql);
            if(!$consulta){
                if($_REQUEST["comision_imp_aux"] > 0){
                    $sql = "INSERT INTO comision_empleados(tipo_empleado,nombre_empleado,fecha_inicial,fecha_final,comision, deducciones, total, ultMod, user_responsable)"
                        . "VAlUES( '".$tipo_empleado."','".$empleado."','".$fecha_inicio."','".$fecha_final."', '".$_REQUEST["comision_imp"]."', '".($_REQUEST["comision_imp"] - $_REQUEST["comision_imp_aux"])."', '".($_REQUEST["comision_imp_aux"])."', '".date("Y-m-d H:i:s")."', '".$this->user->nick."'  ) ";
                }else{
                    $sql = "INSERT INTO comision_empleados(tipo_empleado,nombre_empleado,fecha_inicial,fecha_final,comision, deducciones, total, ultMod, user_responsable,idmetodopago)"
                        . "VAlUES( '".$tipo_empleado."','".$empleado."','".$fecha_inicio."','".$fecha_final."', '".$_REQUEST["comision_imp"]."', '".($_REQUEST["comision_imp_aux"])."', '".($_REQUEST["comision_imp"])."', '".date("Y-m-d H:i:s")."', '".$this->user->nick."', '".$_POST['metodo_pago']."'  ) ";
                }
                if($this->db->exec($sql)){
                    $this->pagar_creditos_lavadores($_REQUEST["abono_deducciones"], $_REQUEST["lavador_imp"]);
                    $sql = "SELECT MAX(reg) as reg FROM comision_empleados WHERE nombre_empleado='".$empleado."'";
                    $this->consecutivo = $this->db->select($sql)[0]["reg"];
                    $this->new_message("La liquidación del ".$this->titulo_proveedor.$_REQUEST["lavador_imp"]." se ha generado con éxito");
                    return TRUE;
                }
                
            }else{
                $this->new_error_msg("No se puede generar el pago de comisión, el rango de fechas ya se ha generado parcial o totalmente. Recibo No.".$consulta[0]["reg"]." de ".$consulta[0]["fecha_inicial"]." a ".$consulta[0]["fecha_final"]);
                return FALSE;
            }
            

        }

        /*
         * Consulta de las facturas que se hayan hecho de forma cliente
         * para el proveedor que se está consultando
         */
        public function deducciones_prov(){
            
            $sql = "SELECT SUM(total) as suma FROM facturascli WHERE anulada = 0 AND nombrecliente = '".$this->proveedores."' AND pagada = 0";
            
            $consulta = $this->db->select($sql);

            $this->deducciones_prov = $consulta[0]["suma"];

        }
        
        /*
         * Se consulta las facturas crédito de los proveedores que generan
         * las deducciones, para mostrarlas en un select y hacer pagos parciales o totales
         */
        public function facturas_deducciones(){
            
            $sql = "SELECT idfactura, total FROM facturascli WHERE anulada = 0 AND nombrecliente = '".$this->proveedores."' AND pagada = 0 AND total > 0 ORDER BY idfactura ASC ";
            
            $consulta = $this->db->select($sql);

            $vector  = array();
            if($consulta){
                for($i = 0 ; $i< count($consulta) ; $i++){
                    $vector[$i]["id"] = $consulta[$i]["idfactura"];
                    $vector[$i]["total"] = $consulta[$i]["total"];
                }
            }
            /*if($consulta){
                for($i = 0 ; $i< count($consulta) ; $i++){
                    $vector[$i+count($consulta)]["id"] = $consulta[$i]["idfactura"];
                    if($i+count($consulta) == count($consulta))
                        $vector[$i+count($consulta)]["total"] = $consulta[$i]["total"];
                    else
                        $vector[$i+count($consulta)]["total"] = $consulta[$i]["total"] + $vector[$i+count($consulta)-1]["total"];
                }
            }*/
            
            //print_r($vector);
            
            return $vector;

        }
        
        
        
        /*
         * Créditos lavadores funcionará de la siguiente forma:
         * el idfactura será el punto de referencia para actualizar la tabla facturascli
         * en pagada = 0 a = 1 donde se pagará las factuaras menores o iguales a idfactura con el nombrecliente *
         */
        private  function pagar_creditos_lavadores($idfactura,$cliente){
            
            foreach ($idfactura as $id){
                if($id!="0" && $id!=0){
                    $sql = "UPDATE facturascli SET pagada = 1 WHERE anulada = 0 AND nombrecliente = '".$cliente."' AND idfactura = '".$id."'";
                    $this->db->exec($sql);
                }
            }
            
                
            
            
        }




        public function informe_lavaderos()
	{
            
            
            $coincidencia = "t1.proveedor_lav = '".$_POST['info_provee']."' ";
            
            if($_POST['info_provee'] == "" )
                $coincidencia = "t1.proveedor_lav LIKE '%".$_POST['info_provee']."%' ";
            
            $sql = "SELECT t1.idlinea,  t1.proveedor_lav, t1.idfactura, t2.codigo, t1.pvptotal, t1.descripcion, CONCAT(t2.fecha, ' ' , t2.hora) as horaas, t1.referencia, t1.pvpsindto, IF(t3.servicio=0, t1.val_liquidacion_instalados, t1.val_liquidacion) as val_liquidacion "
                    . " FROM lineasfacturascli t1 INNER JOIN facturascli t2 INNER JOIN articulos t3 "
                    . " ON t1.referencia<> 6 AND t1.idfactura=t2.idfactura AND t2.anulada = 0 AND ".$coincidencia
                    . " AND (t2.fecha BETWEEN '".$this->fecha_inicio."'"
                    . " AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."'"
                    . " AND '".$this->hora_final."') AND t1.proveedor_lav != 'NULL'"
                    . " AND t1.proveedor_lav != '' AND t3.referencia=t1.referencia";
            
            if(isset($_POST['consolidado'])>0)
            {
                $sql = "SELECT t1.proveedor_lav, t1.idfactura, t2.codigo, t1.pvptotal, t1.descripcion, CONCAT(t2.fecha, ' ' , t2.hora) as horaas, t1.referencia, t1.pvpsindto, COUNT(t1.proveedor_lav) as contador, SUM(t1.pvptotal) as totalito, t1.val_liquidacion , SUM(t1.val_liquidacion) as totalliqui"
                    . " FROM lineasfacturascli t1 INNER JOIN facturascli t2 "
                    . " ON t1.referencia<> 6 AND  t1.idfactura=t2.idfactura AND t2.anulada = 0 AND ".$coincidencia
                    . " AND (t2.fecha BETWEEN '".$this->fecha_inicio."'"
                    . " AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."'"
                    . " AND '".$this->hora_final."') AND t1.proveedor_lav != 'NULL'"
                    . " AND t1.proveedor_lav != '' GROUP BY t1.proveedor_lav ";
            }

            
            
            
            if($this->allow_delete){
                
                if(isset($_POST['consolidado']) < 1){
                    $this->cambiar_valores_liquidacion($this->db->select($sql));
                }
            }
            
            $this->info_lavaderos = $this->db->select($sql);

            $total_sql =  $this->db->select("SELECT SUM(t1.val_liquidacion) as total FROM "
                    . " lineasfacturascli t1 INNER JOIN facturascli t2 ON t1.referencia<> 6  AND t2.anulada = 0 AND t1.idfactura=t2.idfactura "
                    . " AND  ".$coincidencia." AND t1.proveedor_lav != 'NULL' "
                    . " AND t1.proveedor_lav != '' AND (t2.fecha BETWEEN '".$this->fecha_inicio."' "
                    . " AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."'"
                    . " AND '".$this->hora_final."') "
                    . "");

            $this->info_lavaderos_total = $total_sql[0]["total"];
            if($this->info_lavaderos){
                $this->reciboSI=TRUE;
                $this->total_a_liquidar = $this->info_lavaderos_total;
                if(isset($_POST['consolidado'])>0)
                    $this->num_servicios = $this->info_lavaderos[0]["contador"];
            }

	}
        
        private function cambiar_valores_liquidacion($consulta_lavadores){
            if($consulta_lavadores){
                if($_REQUEST["cambiar_valores_liquidacion"]){
                    //echo "</br></br></br>";
                    for($i=0; $i < count($consulta_lavadores); $i++){
                        $sql = "UPDATE lineasfacturascli SET val_liquidacion = ".$this->liquidacion_servicios(  $consulta_lavadores[$i]["pvptotal"], $consulta_lavadores[$i]["referencia"], 1, $consulta_lavadores[$i]["idfactura"])." WHERE idlinea = '".$consulta_lavadores[$i]["idlinea"]."'";
                        $this->db->exec($sql);
                        //echo "</br>".$sql;
                    }
                }
                $this->vista_cambio_valores = TRUE;
            }
            
                
        }
        
        
        
        public function personas_lav_informe(){
            $sql = "SELECT * FROM proveedores WHERE jefe_patio = 1 OR personafisica = 1 ORDER BY nombre ASC ";
    
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
        
        public function informe_jefes()
	{
            
            $coincidencia = "t1.jefe_adicionado = '".$_POST['info_provee']."' ";
            
            if($_POST['info_provee'] == "" )
                $coincidencia = "t1.jefe_adicionado LIKE '%".$_POST['info_provee']."%' ";
            
            $sql = "SELECT t2.idfactura, t2.codigo, t2.total, CONCAT(t2.fecha, ' ' , t2.hora) as horaas, t3.pvptotal, t3.val_liquidacion_jefes as val_liquidacion, t1.jefe_adicionado, t3.descripcion "
                    . " FROM  jefes_patio t1 "
                    . " INNER JOIN  facturascli t2 "
                    . " INNER JOIN lineasfacturascli t3 "
                    . " INNER JOIN articulos t4 "
                    . " ON  t2.idfactura=t3.idfactura  AND t2.anulada = 0 AND t2.cod_persona=t1.id_persona AND t4.referencia=t3.referencia AND  t3.val_liquidacion_jefes>0  "
                    . " AND (  ".$coincidencia." ) "
                    . " AND (t2.fecha BETWEEN '".$this->fecha_inicio."' AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."' AND '".$this->hora_final."') ";
            
            if(isset($_POST['consolidado'])>0)
            {
                $sql = "SELECT t1.jefe_adicionado, t2.idfactura, t2.codigo, t2.total, t3.descripcion, CONCAT(t2.fecha, ' ' , t2.hora) as horaas, COUNT(t1.jefe_adicionado) as contador, SUM(t3.pvptotal) as pvptotal, SUM(t3.val_liquidacion_jefes) as val_liquidacion "
                    . " FROM  jefes_patio t1 "
                    . " INNER JOIN  facturascli t2 "
                    . " INNER JOIN lineasfacturascli t3 "
                    . " INNER JOIN articulos t4 "
                    . " ON  t2.idfactura=t3.idfactura  AND t2.anulada = 0 AND t2.cod_persona=t1.id_persona AND t4.referencia=t3.referencia AND  t3.val_liquidacion_jefes>0  "
                    . " AND (  ".$coincidencia." ) "
                    . " AND (t2.fecha BETWEEN '".$this->fecha_inicio."' AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."' AND '".$this->hora_final."') "
                    . " GROUP BY DAYOFMONTH(t2.fecha), t1.jefe_adicionado ";
            }
            
            
            
            $this->informe_jefes = $this->db->select($sql);
            
            $total_sql =  $this->db->select("SELECT SUM(t3.val_liquidacion_jefes) as total "
                    . " FROM  jefes_patio t1 "
                    . " INNER JOIN  facturascli t2 "
                    . " INNER JOIN lineasfacturascli t3 "
                    . " INNER JOIN articulos t4 "
                    . " ON  t2.idfactura=t3.idfactura  AND t2.anulada = 0 AND t2.cod_persona=t1.id_persona AND t4.referencia=t3.referencia AND t3.val_liquidacion_jefes>0 "
                    . " AND (   ".$coincidencia."  ) "
                    . " AND (t2.fecha BETWEEN '".$this->fecha_inicio."' AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."' AND '".$this->hora_final."') ");


            

            $this->info_jefes_total = $total_sql[0]["total"];
            
            if($this->informe_jefes){
                $this->reciboSI=TRUE;
                $this->total_a_liquidar = $this->info_jefes_total;
                if(isset($_POST['consolidado'])>0)
                    $this->num_servicios = $this->informe_jefes[0]["contador"];
            }
            

	}
        
        
        public function formato_moneda($number){
            $text = "$0";
            if($number)
                $text ="$". number_format($number, 2); 
            
            return  $text;
        
        }

        
        
   
        public function informe_aspiradores()
        {
            
            $coincidencia = "t1.aspirador_add = '".$_POST['info_provee']."' ";
            
            if($_POST['info_provee'] == "" )
                $coincidencia = "t1.aspirador_add LIKE '%".$_POST['info_provee']."%' ";

            $sql = "SELECT t2.idfactura, t2.codigo, t2.total, CONCAT(t2.fecha, ' ' , t2.hora) as horaas, t3.pvptotal, t3.val_liquidacion, t1.aspirador_add "
                    . " FROM aspiradores_lav t1 "
                    . " INNER JOIN  facturascli t2 "
                    . " INNER JOIN lineasfacturascli t3 "
                    . " ON  t2.idfactura=t3.idfactura  AND t2.anulada = 0 AND t2.cod_persona=t1.id_persona AND t3.referencia = '6' "
                    . " AND (  ".$coincidencia." ) "
                    . " AND (t2.fecha BETWEEN '".$this->fecha_inicio."' AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."' AND '".$this->hora_final."') ";
            
            if(isset($_POST['consolidado'])>0)
            {
                $sql = "SELECT t1.aspirador_add, t2.idfactura, t2.codigo, t2.total, CONCAT(t2.fecha, ' ' , t2.hora) as horaas, SUM(t3.pvptotal) as pvptotal,COUNT(t1.aspirador_add) as contador, SUM(t3.val_liquidacion) as val_liquidacion "
                    . " FROM aspiradores_lav t1 "
                    . " INNER JOIN  facturascli t2 "
                    . " INNER JOIN lineasfacturascli t3 "
                    . " ON  t2.idfactura=t3.idfactura  AND t2.anulada = 0 AND t2.cod_persona=t1.id_persona AND t3.referencia = '6'"
                    . " AND (  ".$coincidencia." ) "
                    . " AND (t2.fecha BETWEEN '".$this->fecha_inicio."' AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."' AND '".$this->hora_final."') "
                    . " GROUP BY DAYOFMONTH(t2.fecha), t1.aspirador_add ";
            }
            
            
            
            $this->informe_aspiradores = $this->db->select($sql);
            
            $total_sql =  $this->db->select("SELECT SUM(t3.val_liquidacion) as total "
                    . " FROM aspiradores_lav t1 "
                    . " INNER JOIN  facturascli t2 "
                    . " INNER JOIN lineasfacturascli t3 "
                    . " ON  t2.idfactura=t3.idfactura  AND t2.anulada = 0 AND t2.cod_persona=t1.id_persona AND t3.referencia = '6' "
                    . " AND (  ".$coincidencia." ) "
                    . " AND (t2.fecha BETWEEN '".$this->fecha_inicio."' AND '".$this->fecha_final."') "
                    . " AND (t2.hora BETWEEN '".$this->hora_inicio."' AND '".$this->hora_final."') ");


            

            $this->info_aspiradores_total = $total_sql[0]["total"];
            
            
            if($this->informe_aspiradores){
                $this->reciboSI=TRUE;
                $this->total_a_liquidar = $this->info_aspiradores_total;
                if(isset($_POST['consolidado'])>0)
                    $this->num_servicios = $this->informe_aspiradores[0]["contador"];
            }

        }
        
        private function imprimir_recibo(){
            if($this->terminal){
                $t = new terminal_caja();
                $empresa = new empresa();
                $t->anchopapel = 50;
                //$sql = "SELECT "
                $medio = $t->anchopapel / 2.5;
                $this->terminal;
                $this->terminal->anchopapel = 28;
                $this->terminal->add_linea_big($t->center_text( $empresa->nombre, $medio)."\n" );

                $this->terminal->add_linea( "      Direccion: ".$empresa->direccion . " " ."\n");
                $this->terminal->add_linea( "      Telefono : ".$empresa->telefono . " " ."\t");
                $this->terminal->add_linea( "Ciudad   : ".$empresa->ciudad . " " ."\n\n");
                $this->terminal->cod_letra_tickect();
                $this->terminal->add_linea("RECIBO No. ".$this->consecutivo." \n");
                $this->terminal->add_linea($this->titulo_proveedor.": ".$_REQUEST["lavador_imp"]."\n");
                $this->terminal->add_linea("FECHA INI : ".$_REQUEST["fec_desde"]."\n");
                $this->terminal->add_linea("FECHA FIN : ".$_REQUEST["fec_hasta"]."\n");
                $this->terminal->add_linea("SERVICIOS : ".$_REQUEST["servicios_imp"]."\n");
                $espacio = "\t   ";
                
                if($this->allow_delete){
                    $this->terminal->add_linea("UTILIDAD NETA".$espacio."DEDUCCIONES\n");
                    $this->terminal->add_linea($this->formato_moneda($_REQUEST["comision_imp"])."   ".$this->formato_moneda($_REQUEST["deduccion_imp"])."\n");
                    if($_REQUEST["comision_imp_aux"] > 0)
                        $this->terminal->add_linea("ABONO     : ".$this->formato_moneda(($_REQUEST["comision_imp"] - $_REQUEST["comision_imp_aux"]))."     ");
                    else
                        $this->terminal->add_linea("ABONO     : ".$this->formato_moneda($_REQUEST["comision_imp_aux"])."     ");
                }else{
                    $this->terminal->add_linea("UTILIDAD NETA  : ");
                    $this->terminal->add_linea($this->formato_moneda($_REQUEST["comision_imp"])."\n");
                }
                $this->terminal->add_linea("\nTOTAL     : ".$this->formato_moneda($_REQUEST["total_imp"])."\n\n");
                $this->terminal->add_linea("FIRMA,  \n\n");
                $lineaiguales = "\n\n\n\n";
                $this->terminal->add_linea($lineaiguales);
                $this->terminal->cortar_papel();
                $this->terminal->save();
            }
            
        }
        
        
        private function share_extensions()
        {
            $fsext = new fs_extension();
            $fsext->name = 'api_remote_printer1';
            $fsext->from = __CLASS__;
            $fsext->type = 'api';
            $fsext->text = 'remote_printer1';
            $fsext->save();
        }

    
}
