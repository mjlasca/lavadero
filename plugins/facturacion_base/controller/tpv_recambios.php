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

use FacturaScripts\model\factura_cliente;

require_once 'plugins/facturacion_base/extras/fbase_controller.php';

class tpv_recambios extends fbase_controller
{

    public $agente;
    public $almacen;
    public $articulo;
    public $caja;
    public $cliente;
    public $cliente_s;
    public $divisa;
    public $ejercicio;
    public $equivalentes;
    public $fabricante;
    public $familia;
    public $forma_pago;
    public $imprimir_descripciones;
    public $imprimir_observaciones;
    public $impuesto;
    public $results;
    public $serie;
    public $terminal;
    public $ultimas_compras;
    public $ultimas_ventas;
    public $metodo_pago;
    
    public $current_invoice;
    
    public $aviso_ne;
    
    
    public $factura_editar;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'TPV Genérico', 'TPV');
    }

    protected function private_core()
    {
        parent::private_core();
        
        //$this->cambiar_();
        $this->jefeopro = "Proveedor de servicio";
        $this->share_extensions();

        $this->articulo = new articulo();
        $this->cliente = new cliente();
        $this->cliente_s = FALSE;
        $this->fabricante = new fabricante();
        $this->familia = new familia();
        $this->impuesto = new impuesto();
        $this->results = array();
        $this->metodo_pago = new metodo_pago();
        $this->factura_editar = -1;
        $fact_ = new factura_cliente();
        $this->current_invoice = FALSE;
        
        if (isset($_REQUEST['factura_editar'])) {
            $this->factura_editar = $_REQUEST['factura_editar'];
            $this->current_invoice = $fact_->get($this->factura_editar);
        }
        
        if (isset($_REQUEST['editando_lineas'])) {
            $this->factura_editar = $_REQUEST['editando_lineas'];
            $this->lineas_factura_editar();
        }
        
        if ( isset($_GET['ticketfact']) ) {
            $this->reimprimir_ticket();
        }
        
        
        if (isset($_REQUEST['buscar_cliente'])) {
            $this->fbase_buscar_cliente($_REQUEST['buscar_cliente']);
        } else if (isset($_REQUEST['datoscliente'])) {
            $this->datos_cliente();
        } else if (isset($_REQUEST['placa'])) {
            $this->datos_cliente_placa();
        }else if (isset($_REQUEST['combo_seleccionado'])) {
            $this->datos_combo($_REQUEST['combo_seleccionado']);
        } else if (isset($_REQUEST['crear_cliente'])) {
            $this->crear_cliente($_REQUEST['crear_cliente']);
        } else if (isset($_REQUEST['clientes_db'])) {
            $this->clientes_db();
        } else if ($this->query != '') {
            $this->new_search();
        } else if (isset($_REQUEST['referencia4precios'])) {
            $this->get_precios_articulo();
        } else if (isset($_POST['referencia4combi'])) {
            $this->get_combinaciones_articulo();
        } else {
            $this->agente = $this->user->get_agente();
            $this->almacen = new almacen();
            $this->divisa = new divisa();
            $this->ejercicio = new ejercicio();
            $this->forma_pago = new forma_pago();
            $this->serie = new serie();

            $this->comprobar_opciones();

            if ($this->agente) {
                $this->caja = FALSE;
                $this->terminal = FALSE;
                $caja = new caja();
                
                $terminal0 = new terminal_caja();
                foreach ($caja->all_by_agente($this->agente->codagente) as $cj) {
                    if ($cj->abierta()) {
                        $this->caja = $cj;
                        $this->terminal = $terminal0->get($cj->fs_id);
                        break;
                    }
                }
                

                if (!$this->caja) {
                    
                    if (isset($_POST['terminal'])) {
                        $this->terminal = $terminal0->get($_POST['terminal']);
                        if (!$this->terminal) {
                            $this->new_error_msg('Terminal no encontrado.');
                        } else if ($this->terminal->disponible()) {
                            $this->caja = new caja();
                            $this->caja->fs_id = $this->terminal->id;
                            $this->caja->codagente = $this->agente->codagente;
                            $this->caja->dinero_inicial = floatval($_POST['d_inicial']);
                            $this->caja->dinero_fin = floatval($_POST['d_inicial']);
                            
                            
                            if ($this->caja->save()) {
                                $this->new_message("Caja iniciada con " . $this->show_precio($this->caja->dinero_inicial));
                                /*
                                    *** FRAGMENTO PARA ASIGNAR EL REGISTRO DE USUARIO JEFES DE PATIO Y PROV SERVICIOS**
                                 *  se hace para poder identificar en la tabla cajas las facturas que se hicieron en ese turno, tengan la fecha que tengan
                                 *  ya que al tener un identificador REG que se encuentra en cada una de las facturas elaboradas
                                 *                                  
                                $sql = "select reg FROM personas_lav WHERE usario_asociado = '".$this->user->nick."' AND codigo_estado=1 " ;
                                $consulta_registro = $this->db->select($sql);*/
                                //$sql = "UPDATE cajas SET totalRealCaja = '".$consulta_registro[0]["reg"]."' WHERE id = '".$this->caja->id."'";
                                //$this->db->exec($sql);
                                
                            } else {
                                $this->new_error_msg("¡Imposible guardar los datos de caja!");
                            }
                        } else {
                            $this->new_error_msg('El terminal ya no está disponible.');
                        }
                    } else if (isset($_GET['terminal'])) {
                        $this->terminal = $terminal0->get($_GET['terminal']);
                        if ($this->terminal) {
                            //$this->terminal->abrir_cajon();
                            $this->terminal->save();
                        } else {
                            $this->new_error_msg('Terminal no encontrado.');
                        }
                    }
                }

                if ($this->caja) {
                    
                    if (isset($_POST['cliente'])) {
                        $this->cliente_s = $this->cliente->get($_POST['cliente']);
                    } else if ($this->terminal) {
                        $this->cliente_s = $this->cliente->get($this->terminal->codcliente);
                    }

                    if (!$this->cliente_s) {
                        foreach ($this->cliente->all() as $cli) {
                            $this->cliente_s = $cli;
                            break;
                        }
                    }

                    if (isset($_GET['abrir_caja'])) {
                        $this->abrir_caja();
                    } else if (isset($_GET['cerrar_caja'])) {
                        
                        $sql = "SELECT * FROM facturascli WHERE id_arqueo='".$this->caja->id."' AND pagada<1  AND codpago='CONT'";
                        
                        if($this->db->select($sql))
                            $this->new_error_msg ("No se puede cerrar el arqueo si no ha pagado todas las facturas contado");
                        else
                            $this->cerrar_caja();
                        
                    } else if (isset($_POST['cliente'])) {
                        if (intval($_POST['numlineas']) > 0) {
                            $this->nueva_factura_cliente();
                            $this->aviso_ne = $this->factura_editar;
                        }
                    } else if (isset($_GET['reticket'])) {
                        $this->reimprimir_ticket();
                    }
                } else {
                    $this->results = $terminal0->disponibles();
                }
            } else {
                $this->new_error_msg('No tienes un <a href="' . $this->user->url() . '">agente asociado</a>
               a tu usuario, y por tanto no puedes hacer tickets.');
            }

            
            
            if(isset($_REQUEST["familia_mostrar"])){
                $this->mostrar_cinta_articulos($_REQUEST["familia_mostrar"]);
            }
            
            
            
            
            
            

            
        }
        
        
        
        
        
    }

    
    public function today1(){
        return date('Y-m-d');
    }


    private function comprobar_opciones()
    {
        $fsvar = new fs_var();

        $this->imprimir_descripciones = ($fsvar->simple_get('tpv_gen_descripcion') == '1');
        $this->imprimir_observaciones = ($fsvar->simple_get('tpv_gen_observaciones') == '1');

        /**
         * Si se detectan datos por post de que se está creando una factura, modificamos las opciones
         */
        if (isset($_POST['cliente'])) {
            if (isset($_POST['imprimir_desc'])) {
                $this->imprimir_descripciones = TRUE;
                $fsvar->simple_save('tpv_gen_descripcion', '1');
            } else {
                $this->imprimir_descripciones = FALSE;
                $fsvar->simple_delete('tpv_gen_descripcion');
            }

            if (isset($_POST['imprimir_obs'])) {
                $this->imprimir_observaciones = TRUE;
                $fsvar->simple_save('tpv_gen_observaciones', '1');
            } else {
                $this->imprimir_observaciones = FALSE;
                $fsvar->simple_delete('tpv_gen_observaciones');
            }
        }
    }

    private function datos_cliente()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;

        header('Content-Type: application/json');
        echo json_encode($this->cliente->get($_REQUEST['datoscliente']));
    }

    private function datos_cliente_placa()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;

        header('Content-Type: application/json');
        echo json_encode($this->cliente->get_placa($_REQUEST['placa']));
    }
    
    
    private function lineas_factura_editar()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;

         $sql = "SELECT t1.referencia, t1.codcombinacion, t1.iva, t1.recargo, t1.irpf, t1.descripcion, t1.cantidad, t1.pvpunitario, t1.proveedor_lav, t2.proveedor_servicio, t2.nombrecliente, t2.fecha "
                 . "FROM lineasfacturascli t1 INNER JOIN facturascli t2 ON t2.pagada = 0 AND t1.idfactura = '".$this->factura_editar."'  AND t1.idfactura = t2.idfactura ORDER BY t1.idlinea DESC";
        
        $lineas_fact = $this->db->select($sql);
        
        //fechatemporal = json[0].fecha .substr(8, 2) +"-"+ json[0].fecha .substr(5, 2) +"-"+ json[0].fecha .substr(0, 4);
        
        
        header('Content-Type: application/json');
        echo json_encode($lineas_fact);
    }
    
    
    private function clientes_db()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;
        $sql = "SELECT nombre FROM clientes ";
        $ten = $this->db->select($sql);
        header('Content-Type: application/json');
        echo json_encode($ten);
    }
	
	
    private function datos_combo($dato)
    {
        /// desactivamos la plantilla HTML

        $this->template = FALSE;
		$sql = "SELECT t3.referencia, t3.descripcion, t3.pvp, t3.codimpuesto, t3.servicio,t3.preciocombo, t3.necesita_articulo  FROM combos_lav t1 INNER JOIN conjunto_combos t2 INNER JOIN articulos t3 ON  t3.referencia=t2.articulo AND t1.idcombo = '".$dato."' AND t1.codigo_estado=1 AND t1.idcombo=t2.idcombo AND t2.codigo_estado=1 ";
		$resultadito = $this->db->select($sql);
		
        header('Content-Type: application/json');
        echo json_encode($resultadito);
    }
    
    private function crear_cliente($dato)
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;
        $resultadito = false;
        
        $cliente_nuevo = new cliente();
        
        
        $cliente_nuevo->coddivisa = "COP";
        $cliente_nuevo->codpago = "CONT";
        $cliente_nuevo->fechaalta = date("Y-m-d");
        $cliente_nuevo->nombre = $dato;
        if(isset($_REQUEST['cod']))
            $cliente_nuevo->codcliente = $_REQUEST['cod'];
        if(isset($_REQUEST['razonsocial']) && $_REQUEST['razonsocial'] != "")
            $cliente_nuevo->razonsocial = $_REQUEST['razonsocial'];
        if(isset($_REQUEST['nombre2']))
            $cliente_nuevo->nombre2 = $_REQUEST['nombre2'];
        if(isset($_REQUEST['telefono1']))
            $cliente_nuevo->telefono1 = $_REQUEST['telefono1'];
        if(isset($_REQUEST['email']))
            $cliente_nuevo->email = $_REQUEST['email'];
        if(isset($_REQUEST['cifnif']))
            $cliente_nuevo->cifnif = $_REQUEST['cifnif'];
        if(isset($_REQUEST['tipo_cifnif']))
            $cliente_nuevo->tipoidfiscal = $_REQUEST['tipo_cifnif'];
        $cliente_nuevo->regimeniva = "General";
        $cliente_nuevo->personafisica = 1;
        
        if($cliente_nuevo->save()){
            $resultadito["exito"] = true;
            $resultadito["cod"] = $cliente_nuevo->get_by_cifnif($cliente_nuevo->cifnif);
        }
        
		//$resultadito["hola"] = $sql;
        header('Content-Type: application/json');
        echo json_encode($resultadito);
    }
    
    
    private function mostrar_cinta_articulos($familia)
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;
        
        if($familia == "0")
            $sql = "SELECT *, SUM(t1.cantidad) as suma FROM lineasfacturascli t1 INNER JOIN articulos t2  ON  t2.cod_estado = 1 AND   t1.referencia = t2.referencia  GROUP BY t2.referencia  ";
        else
            $sql = "SELECT * FROM lineasfacturascli t1 INNER JOIN articulos t2  ON  t2.cod_estado = 1 AND  t2.codfamilia = '".$familia."' ";
        
        $consulta = $this->db->select_limit($sql, 30);

        header('Content-Type: application/json');
        echo json_encode($consulta);
    }

    private function new_search()
    {
        /// desactivamos la plantilla HTML
        $this->template = FALSE;

        $codfamilia = '';
        if (isset($_REQUEST['codfamilia'])) {
            $codfamilia = $_REQUEST['codfamilia'];
        }
        $codfabricante = '';
        if (isset($_REQUEST['codfabricante'])) {
            $codfabricante = $_REQUEST['codfabricante'];
        }
        $con_stock = isset($_REQUEST['con_stock']);
        $this->results = $this->articulo->search($this->query, 0, $codfamilia, $con_stock, $codfabricante);

        /// buscamos por código de barras de la combinación
        $combi0 = new articulo_combinacion();
        foreach ($combi0->search($this->query) as $combi) {
            $articulo = $this->articulo->get($combi->referencia);
            if ($articulo) {
                $articulo->codbarras = $combi->codbarras;
                $this->results[] = $articulo;
            }
        }

        /// ejecutamos las funciones de las extensiones
        foreach ($this->extensions as $ext) {
            if ($ext->type == 'function' && $ext->params == 'new_search') {
                $name = $ext->text;
                $name($this->db, $this->results);
            }
        }

        $this->new_search_postprocess();

        header('Content-Type: application/json');
        echo json_encode($this->results);
    }

    private function new_search_postprocess()
    {
        $stock = new stock();

        /// añadimos el descuento y la cantidad
        foreach ($this->results as $i => $value) {
            $this->results[$i]->query = $this->query;
            $this->results[$i]->dtopor = 0;
            $this->results[$i]->cantidad = 1;

            $this->results[$i]->stockalm = $value->stockfis;
            if ($this->multi_almacen && isset($_REQUEST['codalmacen'])) {
                $this->results[$i]->stockalm = $stock->total_from_articulo($this->results[$i]->referencia, $_REQUEST['codalmacen']);
            }
        }

        if (isset($_REQUEST['codcliente'])) {
            $cliente = $this->cliente->get($_REQUEST['codcliente']);
            $tarifa0 = new tarifa();

            if ($cliente && $cliente->codtarifa) {
                $tarifa = $tarifa0->get($cliente->codtarifa);
                if ($tarifa) {
                    $tarifa->set_precios($this->results);
                }
            } else if ($cliente && $cliente->codgrupo) {
                $grupo0 = new grupo_clientes();

                $grupo = $grupo0->get($cliente->codgrupo);
                if ($grupo) {
                    $tarifa = $tarifa0->get($grupo->codtarifa);
                    if ($tarifa) {
                        $tarifa->set_precios($this->results);
                    }
                }
            }
        }
    }
    //ESTA FUNCIÓN SE USÓ PARA MODIFICAR LAS LIQUIDACIONES PORQUE COMO SE INGRESABAN CON UNA FECHA
    //ATÍPICA NO SE ESTABA COGIENDO REALMENTE EL DÍA CORRECTO DE LA SEMANA. LA IDEA ES USAR EL ALGORITMO UNA SOLA VEZ
    private function cambiar_(){
        
       $sql = "SELECT * FROM lineasfacturascli t1 INNER JOIN facturascli t2 ON t1.idfactura = t2.idfactura AND YEAR(fecha) = '2019' "; 
       $res = $this->db->select($sql);
       $val_liq = 0;
       if($res){
           for($i=0 ; $i < count($res) ; $i++){
                
               //echo "</br></br></br> ".$res[$i]["pvptotal"]. ", ".$res[$i]["referencia"]."1,". $res[$i]["idfactura"]."</br> ".$this->liquidacion_servicios($res[$i]["pvptotal"], $res[$i]["referencia"], 1, $res[$i]["idfactura"]);
               $val_liq = $this->liquidacion_servicios($res[$i]["pvptotal"], $res[$i]["referencia"], 1, $res[$i]["idfactura"]);
               if($val_liq == 0){
                    $val_liq = $this->liquidacion_servicios($res[$i]["pvptotal"], $res[$i]["referencia"], 2, $res[$i]["idfactura"]);
               }
               
               $sql = "UPDATE lineasfacturascli SET val_liquidacion='".$val_liq."' WHERE  idlinea ='".$res[$i]["idlinea"]."' ";
               $this->db->exec($sql);
           }
       }
       
       
        
        
    }

    private function get_precios_articulo()
    {
        /// cambiamos la plantilla HTML
        $this->template = 'ajax/tpv_recambios_precios';

        $this->articulo = $this->articulo->get($_REQUEST['referencia4precios']);
    }

    private function get_combinaciones_articulo()
    {
        /// cambiamos la plantilla HTML
        $this->template = 'ajax/tpv_recambios_combinaciones';

        $impuestos = $this->impuesto->all();

        $this->results = array();
        $comb1 = new articulo_combinacion();
        foreach ($comb1->all_from_ref($_POST['referencia4combi']) as $com) {
            if (isset($this->results[$com->codigo])) {
                $this->results[$com->codigo]['desc'] .= ', ' . $com->nombreatributo . ' - ' . $com->valor;
                $this->results[$com->codigo]['txt'] .= ', ' . $com->nombreatributo . ' - ' . $com->valor;
            } else {
                $iva = 0;
                foreach ($impuestos as $imp) {
                    if ($imp->codimpuesto == $_POST['codimpuesto']) {
                        $iva = $imp->iva;
                        break;
                    }
                }

                $this->results[$com->codigo] = array(
                    'ref' => $_POST['referencia4combi'],
                    'desc' => base64_decode($_POST['desc']) . "\n" . $com->nombreatributo . ' - ' . $com->valor,
                    'pvp' => floatval($_POST['pvp']) + $com->impactoprecio,
                    'dto' => floatval($_POST['dto']),
                    'codimpuesto' => $_POST['codimpuesto'],
                    'iva' => $iva,
                    'cantidad' => floatval($_POST['cantidad']),
                    'txt' => $com->nombreatributo . ' - ' . $com->valor,
                    'codigo' => $com->codigo,
                    'stockfis' => $com->stockfis,
                );
            }
        }
    }

    public function get_tarifas_articulo($ref)
    {
        $tarlist = array();
        $articulo = new articulo();
        $tarifa = new tarifa();

        foreach ($tarifa->all() as $tar) {
            $art = $articulo->get($ref);
            if ($art) {
                $art->dtopor = 0;
                $aux = array($art);
                $tar->set_precios($aux);
                $tarlist[] = $aux[0];
            }
        }

        return $tarlist;
    }

    private function nueva_factura_cliente()
    {
        $continuar = TRUE;

        $ejercicio = $this->ejercicio->get_by_fecha($_POST['fecha']);
        if (!$ejercicio) {
            $this->new_error_msg('Ejercicio no encontrado o está cerrado.');
            $continuar = FALSE;
        }

        $serie = $this->serie->get($_POST['serie']);
        if (!$serie) {
            $this->new_error_msg('Serie no encontrada.');
            $continuar = FALSE;
        }

        $forma_pago = $this->forma_pago->get($_POST['forma_pago']);
        if ($forma_pago) {
            $this->save_codpago($_POST['forma_pago']);
        } else {
            $this->new_error_msg('Forma de pago no encontrada.');
            $continuar = FALSE;
        }

        $divisa = $this->divisa->get($_POST['divisa']);
        if (!$divisa) {
            $this->new_error_msg('Divisa no encontrada.');
            $continuar = FALSE;
        }

        $factura = new factura_cliente();

        if ($this->duplicated_petition($_POST['petition_id'])) {
            $this->new_error_msg('Petición duplicada. Has hecho doble clic sobre el botón Guardar
               y se han enviado dos peticiones. Mira en <a href="' . $factura->url() . '">Facturas</a>
               para ver si la factura se ha guardado correctamente.');
            $continuar = FALSE;
        }

        if ($continuar) {
			
			$ssql = "";
			$factura->idmetodopago = $_POST['metodo_pago'] ?? NULL;
            $factura->codejercicio = $ejercicio->codejercicio;
            $factura->codserie = $serie->codserie;
            
            
            //CONDICIÓN PARA NO CAMBIAR LA FECHA DE LA FACTURA
            /*if($this->user->admin)
                $factura->fecha = $_POST['fecha'];    
            else
                $factura->set_fecha_hora($_POST['fecha'], $factura->hora);*/
            
            $factura->fecha = $_POST['fecha'];    
            $factura->codalmacen = $_POST['almacen'];
            $factura->codpago = $forma_pago->codpago;
            $factura->coddivisa = $divisa->coddivisa;
            $factura->tasaconv = $divisa->tasaconv;
			
			
			
            if ($_POST['tasaconv'] != '') {
                $factura->tasaconv = floatval($_POST['tasaconv']);
            }

            $factura->codagente = $this->agente->codagente;
            $factura->id_arqueo = $this->caja->id;
            $factura->observaciones = $_POST['observaciones'];
            $factura->porcomision = $this->agente->porcomision;

            if ($forma_pago->genrecibos == 'Pagados') {
                $factura->pagada = TRUE;
            }

            $factura->vencimiento = $forma_pago->calcular_vencimiento($factura->fecha, $this->cliente_s->diaspago);

            $factura->codcliente = $this->cliente_s->codcliente;
            $factura->cifnif = $_POST['cifnif'];
            $factura->nombrecliente = $_POST['nombrecliente'];
            $factura->ciudad = $this->empresa->ciudad;
            $factura->codpais = $this->empresa->codpais;
            $factura->codpostal = $this->empresa->codpostal;
            $factura->provincia = $this->empresa->provincia;
			

            foreach ($this->cliente_s->get_direcciones() as $d) {
                if ($d->domfacturacion) {
                    $factura->apartado = $d->apartado;
                    $factura->ciudad = $d->ciudad;
                    $factura->coddir = $d->id;
                    $factura->codpais = $d->codpais;
                    $factura->codpostal = $d->codpostal;
                    $factura->direccion = $d->direccion;
                    $factura->provincia = $d->provincia;
                    break;
                }
            }

            /// función auxiliar para implementar en los plugins que lo necesiten
            if (!fs_generar_numero2($factura)) {
                $factura->numero2 = $_POST['numero2'] ?? null;
            }
            
            //En ésta línea se hace una condición para que la factura sea editada y no cree una nueva
            //Se actualiza con el idfactura de factura_editar
            if($this->factura_editar != -1){
                $factura->idfactura = $this->factura_editar;
                $edita_factura = $factura->search_id($factura->idfactura);
                $factura->codigo =  $edita_factura[0]["codigo"];
                $factura->numero =  $edita_factura[0]["numero"];
                $this->caja->dinero_fin = $this->caja->dinero_fin - $edita_factura[0]["total"];
            }
            
            
            $regularizacion = new regularizacion_iva();
            
            
            if ($regularizacion->get_fecha_inside($factura->fecha)) {
                $this->new_error_msg("El " . FS_IVA . " de ese periodo ya ha sido regularizado."
                    . " No se pueden añadir más facturas en esa fecha.");
                
            } else if ($factura->save()) {
                 
            
                //$cons_usuario = $this->db->select("SELECT reg FROM personas_lav WHERE usario_asociado='".$this->user->nick."' AND codigo_estado = 1 ");
                $cons_usuario = $this->bandera_lav;
                $provAgregar = "0";
                
                $linea_ = new linea_factura_cliente();
                if($this->factura_editar != -1){
                    $factura->idfactura = $this->factura_editar;
                    $linea_->delete_lineas($factura->idfactura);
                    $linea_iva = new linea_iva_factura_cliente;
                    $linea_iva->delete_fact($factura->idfactura);
                    $this->factura_editar    = -1;
                }
                
                
                $trazabilidad = FALSE;
                $n = floatval($_POST['numlineas']);
                for ($i = 1; $i <= $n; $i++) {
                    if (isset($_POST['referencia_' . $i])) {
                        $articulo = $this->articulo->get($_POST['referencia_' . $i]);
                        if ($articulo) {
                            $linea = new linea_factura_cliente();
                            $linea->idfactura = $factura->idfactura;
                            $linea->referencia = $articulo->referencia;
                            $linea->descripcion = $_POST['desc_' . $i];
                            $linea->proveedor_lav = $_POST['prov_' . $i];
                            
                                

                            if (!$serie->siniva || $this->cliente_s->regimeniva != 'Exento') {
                                $linea->codimpuesto = $articulo->codimpuesto;
                                $linea->iva = floatval($_POST['iva_' . $i]);
                                $linea->recargo = floatval($_POST['recargo_' . $i]);
                            }

                            $linea->irpf = floatval($_POST['irpf_' . $i]);
                            $linea->pvpunitario = floatval($_POST['pvp_' . $i]);
                            $linea->cantidad = floatval($_POST['cantidad_' . $i]);
                            $linea->dtopor = floatval($_POST['dto_' . $i]);
                            $linea->pvpsindto = $linea->pvpunitario * $linea->cantidad;
                            $linea->pvptotal = $linea->cantidad * $linea->pvpunitario * (100 - $linea->dtopor) / 100;
                            
                            
                            $linea->val_liquidacion_jefes = $this->liquidacion_jefes($linea->pvptotal / $linea->cantidad, $linea->referencia, 2, $linea->idfactura);
                            
                            if(!empty($_POST['prov_' . $i]))
                            {
                                    $provAgregar = $_POST['prov_' . $i];
                                    $linea->val_liquidacion = $this->liquidacion_servicios($linea->pvptotal / $linea->cantidad, $linea->referencia, 1, $linea->idfactura);
                                    if($linea->val_liquidacion == 0)
                                        $linea->val_liquidacion = $this->liquidacion_servicios($linea->pvptotal / $linea->cantidad, $linea->referencia, 2, $linea->idfactura);
                                    
                            }
                            
                            
                            
                            /*
                             * Se multiplica por la cantidad los valores de liquidación.
                             */
                            $linea->val_liquidacion = $linea->cantidad * $linea->val_liquidacion;
                            $linea->val_liquidacion_instalados = $linea->cantidad * $linea->val_liquidacion_instalados;
                            $linea->val_liquidacion_jefes = $linea->cantidad * $linea->val_liquidacion_jefes;
                            
                            if ($articulo) {
                                if ($articulo->trazabilidad) {
                                    $trazabilidad = TRUE;
                                }

                                if ($_POST['codcombinacion_' . $i]) {
                                    $linea->codcombinacion = $_POST['codcombinacion_' . $i];
                                }
                            }

                            if ($linea->save()) {
                                /// descontamos del stock
                                $articulo->sum_stock($factura->codalmacen, 0 - $linea->cantidad, FALSE, $linea->codcombinacion);

                                if ($linea->irpf > $factura->irpf) {
                                    $factura->irpf = $linea->irpf;
                                }
                            } else {
                                $this->new_error_msg("¡Imposible guardar la linea con referencia: " . $linea->referencia);
                                $continuar = FALSE;
                            }
                        } else {
                            $this->new_error_msg("Artículo no encontrado: " . $_POST['referencia_' . $i]);
                            $continuar = FALSE;
                        }
                    }
                }
				
				

                if ($continuar) {
                    /// obtenemos los subtotales por impuesto
                    foreach ($this->fbase_get_subtotales_documento($factura->get_lineas()) as $subt) {
                        $factura->netosindto += $subt['netosindto'];
                        $factura->neto += $subt['neto'];
                        $factura->totaliva += $subt['iva'];
                        $factura->totalirpf += $subt['irpf'];
                        $factura->totalrecargo += $subt['recargo'];
                    }

                    $factura->total = round($factura->neto + $factura->totaliva - $factura->totalirpf + $factura->totalrecargo, FS_NF0);
                    //if (abs(floatval($_POST['tpv_total2']) - $factura->total) >= .02) {
                    if (abs(floatval($_POST['tpv_total2']) - $factura->total) >= .02) {
                        $this->new_error_msg("El total difiere entre la vista y el controlador (" . $_POST['tpv_total2'] .
                            " frente a " . $factura->total . "). Debes informar del error.");
                        $factura->delete();
                    } else if ($factura->save()) {
                        $this->generar_asiento($factura);
						
						

                        /// Función de ejecución de tareas post guardado correcto de la factura
                        fs_documento_post_save($factura);
						
						

                        $this->new_message("<a href='" . $factura->url() . "'>Factura</a> guardada correctamente.");
                        if ($_POST['regalo'] == 'TRUE') {
                            $this->imprimir_ticket_regalo($factura);
                        } else {
                            $this->imprimir_ticket($factura, floatval($_POST['num_tickets']));
                        }
						
                        $ssql = "UPDATE facturascli SET proveedor_servicio='".$provAgregar."', cod_persona='".$cons_usuario[0]["reg"]."' WHERE idfactura='".$factura->idfactura."'";
                        //SE GUARDA EL PROVEEDOR DE SERVICIO DE LA TABLA FACTURASCLI
                        $this->db->exec($ssql);

                        /// actualizamos la caja
                        
                        $this->caja->dinero_fin += $factura->total;
                        $this->caja->tickets += 1;
                        $this->caja->ip = $_SERVER['REMOTE_ADDR'];
                        if ($this->caja->save()) {
                            if ($trazabilidad) {
                                header('Location: index.php?page=ventas_trazabilidad&doc=factura&id=' . $factura->idfactura
                                    . '&volver=' . urlencode($this->url()));
                            }
                        } else {
                            $this->new_error_msg("¡Imposible actualizar la caja!");
                        }
                    } else {
                        $this->new_error_msg("¡Imposible actualizar la <a href='" . $factura->url() . "'>factura</a>!");
                    }
                } else if ($factura->delete()) {
                    $this->new_message("Factura eliminada correctamente.");
                } else {
                    $this->new_error_msg("¡Imposible eliminar la <a href='" . $factura->url() . "'>factura</a>!");
                }
            } else {
                $this->new_error_msg("¡Imposible guardar la factura!");
            }
        }
    }
  
            

    private function abrir_caja()
    {
        if ($this->terminal) {
            $this->terminal->abrir_cajon();
            $this->terminal->save();
            
        } else {
            $this->new_error_msg('Terminal no encontrado.');
        }
    }
    
    
    private function cerrar_caja()
    {
        
        $this->caja->fecha_fin = Date('d-m-Y H:i:s');
        if(isset($_REQUEST["dinero_caja"]))
            $this->caja->cierremanual = $_REQUEST["dinero_caja"];
        
        if ($this->caja->save()) {
            if ($this->terminal) {
                $this->terminal->cod_letra_tickect();
                $this->terminal->anchopapel = 28;
                $this->terminal->add_linea_big("\nCIERRE DE CAJA:\n\n");
                $this->terminal->add_linea("Gestor: " . $this->user->codagente . " \n " . $this->agente->get_fullname() . "\n");
                $this->terminal->add_linea("Caja: " . $this->caja->fs_id ."  ID Arqueo: ".$this->caja->id." \n");
                $this->terminal->add_linea("Fecha Arqueo: \n" . date("Y-m-d H:i:s"). "\n");
                $this->terminal->add_linea("Fecha ini: \n" . $this->caja->fecha_inicial . "\n");
                $this->terminal->add_linea("Fecha fin: \n" . $this->caja->show_fecha_fin() . "\n");
                $this->terminal->add_linea("Diferencia: $ " . $this->formato_moneda($this->caja->diferencia()) . "\n");
                $this->terminal->add_linea("Tickets: " . $this->caja->tickets . "\n\n");
                $this->terminal->add_linea_big("Arqueo caja: \n\n");
                
                
                $this->terminal->add_linea("Dinero inic $" . sprintf("%" . ($this->terminal->anchopapel - 12) . "s",$this->formato_moneda($this->caja->dinero_inicial) . "\n"));
                $this->terminal->add_linea("Fact. Ctado $". sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($this->facturas_contado($this->user->codagente, $this->caja->id)). "\n") );
                $this->terminal->add_linea("Fact. Cdito $". sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($this->facturas_credito($this->user->codagente, $this->caja->id)). "\n") );
                $this->terminal->add_linea("Dnero Manua $" .  sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($this->caja->cierremanual) . "\n" ));
                $this->terminal->add_linea("Dnero final $" .  sprintf("%" . ($this->terminal->anchopapel - 11) . "s", $this->formato_moneda($this->caja->dinero_fin - $this->facturas_credito($this->user->codagente, $this->caja->id) ) . "\n\n" ));
                
                /*$this->terminal->add_linea("Dinero inicial:  " .$espacio. $this->formato_moneda($this->caja->dinero_inicial) . "\n");
                $this->terminal->add_linea("Facturas Contado: ".$this->formato_moneda($this->facturas_contado($this->user->codagente, $this->caja->id)). "\n");
                $this->terminal->add_linea("Facturas Crédito: ".$this->formato_moneda($this->facturas_credito($this->user->codagente, $this->caja->id)). "\n");
                $this->terminal->add_linea("Dinero Manual:   " .$espacio. $this->formato_moneda($this->caja->cierremanual) . "\n");
                $this->terminal->add_linea("Dinero final:    " .$espacio. $this->formato_moneda($this->caja->dinero_fin) . "\n\n");*/
                
                $this->terminal->add_linea_big("Por Artículo \n\n");

                $articulos = $this->familiasCierre($this->caja->id);
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

                $this->terminal->add_linea_big("Por Lavador \n\n");

                $total_proveedores = 0;
                if($this->desgloce_lavador($this->user->codagente, $this->caja->id)){
                    $consulta = $this->desgloce_lavador($this->user->codagente, $this->caja->id);
                    for($i=0 ; $i < count($consulta) ; $i++){
                        //if($consulta[$i]["proveedor_servicio"] == "0")
                            //$this->terminal->add_linea("ARTICULOS:  $" .sprintf("%" . ($this->terminal->anchopapel - 12) . "s", $this->formato_moneda($consulta[$i]["total"]) . "\n"));                            
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
                            $total_proveedores += $consulta[$i]["total"];
                        }
                        
                    }
                    $this->terminal->add_linea("\nTOTAL:      $" . sprintf("%" . ($this->terminal->anchopapel - 11) . "s", $this->formato_moneda( $total_proveedores ) . "\n\n"));
                }
                $this->terminal->add_linea("Observaciones:\n\n\n\n");
                $this->terminal->add_linea("Firma:\n\n\n\n\n\n\n\n\n\n");
                $this->terminal->cortar_papel();
                //$this->terminal->abrir_cajon();
                $this->terminal->save();

                /// recargamos la página
                header('location: ' . $this->url() . '&terminal=' . $this->terminal->id);
            } else {
                /// recargamos la página
                header('location: ' . $this->url());
            }
        } else {
            $this->new_error_msg("¡Imposible cerrar la caja!");
        }
    }


    public function familiasCierre($idarqueo){
        
        $sql = "SELECT t1.referencia, SUM(t1.pvptotal  * (1+ (t1.iva/100))) as sumita, SUM(t1.cantidad) as cantidad  FROM lineasfacturascli t1 INNER JOIN facturascli t2 ON t2.anulada = 0 AND t2.id_arqueo = '".$idarqueo."' AND t1.idfactura = t2.idfactura AND ( t1.proveedor_lav IS NULL OR  t1.proveedor_lav = '' OR  t1.proveedor_lav = '0' )    GROUP BY t1.referencia";

        return $this->db->select($sql);

    }

    
    
    public function formato_moneda($number){
            $text = number_format($number, 2); 
            return  $text;
        
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
        
        $sql = "SELECT SUM(total) as total FROM facturascli WHERE pagada=0 AND  codagente = '".$empleado."' AND id_arqueo='".$id_arqueo."'   AND anulada='0' " ;
        
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
        
        $sql = "SELECT SUM(total) as total FROM facturascli WHERE pagada=1 AND  codagente = '".$empleado."' AND id_arqueo='".$id_arqueo."' AND anulada='0' ";
        
        $consulta = $this->db->select($sql);
        
        if($consulta)
            return $consulta[0]["total"];
        else
            return 0;
        
    }

    private function reimprimir_ticket()
    {
        $factura = new factura_cliente();
        $fac0 = FALSE;

        if ($_GET['reticket'] == '') {
            foreach ($factura->all(0, FS_ITEM_LIMIT, 'idfactura DESC') as $fac) {
                $fac0 = $fac;
                break;
            }
        } else {
            $fac0 = $factura->get_by_codigo($_GET['reticket']);
        }

        if ($fac0) {
            $this->imprimir_ticket($fac0, 1, FALSE);
        } else {
            $this->new_error_msg("Ticket no encontrado.");
        }
    }

    /**
     * Añade el ticket a la cola de impresión.
     * @param factura_cliente $factura
     * @param type $num_tickets
     * @param type $cajon
     */
    private function imprimir_ticket($factura, $num_tickets = 1, $cajon = TRUE)
    {
        if ($this->terminal) {
            if ($cajon) {
                //$this->terminal->abrir_cajon();
            }

            while ($num_tickets > 0) {
                $this->terminal->anchopapel = 54;
                $this->terminal->imprimir_ticket($factura, $this->empresa, $this->imprimir_descripciones, $this->imprimir_observaciones);
                $num_tickets--;
            }

            $this->terminal->save();
            $this->new_message('<a href="#" data-toggle="modal" data-target="#modal_ayuda_ticket">¿No se imprime el ticket?</a>');
        } else {
            $this->new_error_msg('Terminal no encontrado.');
        }
    }

    /**
     * Imprime uno o varios tickets de la factura.
     * @param factura_cliente $factura
     * @param type $num_tickets
     * @param type $cajon
     */
    private function imprimir_ticket_regalo($factura, $num_tickets = 1, $cajon = TRUE)
    {
        if ($this->terminal) {
            if ($cajon) {
                //$this->terminal->abrir_cajon();
            }

            while ($num_tickets > 0) {
                $this->terminal->imprimir_ticket_regalo($factura, $this->empresa, $this->imprimir_descripciones, $this->imprimir_observaciones);
                $num_tickets--;
            }

            $this->terminal->save();
        }
    }

    /**
     * Genera el asiento para la factura, si procede
     * @param factura_cliente $factura
     */
    private function generar_asiento(&$factura)
    {
        if ($this->empresa->contintegrada) {
            $asiento_factura = new asiento_factura();
            $asiento_factura->generar_asiento_venta($factura);

            foreach ($asiento_factura->errors as $err) {
                $this->new_error_msg($err);
            }

            foreach ($asiento_factura->messages as $msg) {
                $this->new_message($msg);
            }
        } else {
            /// de todas formas forzamos la generación de las líneas de iva
            $factura->get_lineas_iva();
        }
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
