<?php
/*
 * This file is part of FacturaScripts
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

require_once 'base/fs_core_log.php';
require_once 'base/fs_cache.php';
require_once 'base/fs_db2.php';
require_once 'base/fs_default_items.php';
require_once 'base/fs_model.php';
require_once 'base/fs_login.php';
require_once 'base/fs_divisa_tools.php';
require_once 'base/festivos.php';

require_all_models();

/**
 * La clase principal de la que deben heredar todos los controladores
 * (las páginas) de FacturaScripts.
 * 
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class fs_controller
{

    /**
     * Este objeto permite acceso directo a la base de datos.
     * @var fs_db2
     */
    protected $db;

    /**
     * Este objeto permite interactuar con memcache
     * @var fs_cache
     */
    protected $cache;

    /**
     * Este objeto contiene los mensajes, errores y consejos volcados por controladores,
     * modelos y base de datos.
     * @var fs_core_log 
     */
    protected $core_log;

    /**
     * Nombre del controlador (lo utilizamos en lugar de __CLASS__ porque __CLASS__
     * en las funciones de la clase padre es el nombre de la clase padre).
     * @var string 
     */
    protected $class_name;

    /**
     *
     * @var fs_divisa_tools
     */
    protected $divisa_tools;

    /**
     * Permite calcular cuanto tarda en procesarse la página.
     * @var string 
     */
    private $uptime;

    /**
     * Listado con los últimos cambios en documentos.
     * @var array 
     */
    private $last_changes;

    /**
     *
     * @var fs_login
     */
    private $login_tools;

    /**
     * Indica si FacturaScripts está actualizado o no.
     * @var boolean 
     */
    private $fs_updated;

    /**
     * El usuario que ha hecho login
     * @var fs_user
     */
    public $user;

    /**
     * El elemento del menú de esta página
     * @var fs_page
     */
    public $page;

    /**
     * Contiene el menú de FacturaScripts
     * @var array
     */
    protected $menu;

    /**
     * Indica que archivo HTML hay que cargar
     * @var string|false 
     */
    public $template;

    /**
     * Esta variable contiene el texto enviado como parámetro query por cualquier formulario,
     * es decir, se corresponde con $_REQUEST['query']
     * @var string|boolean
     */
    public $query;

    /**
     * La empresa
     * @var empresa
     */
    public $empresa;

    /**
     * Permite consultar los parámetros predeterminados para series, divisas, forma de pago, etc...
     * @var fs_default_items 
     */
    public $default_items;

    /**
     * Listado de extensiones de la página
     * @var array 
     */
    public $extensions;
    
    
    public $bandera_lav;
    
    public $bandera;
    
    public $adminAbierto;
    
    public $jefeopro;







    /**
     * @param string $name sustituir por __CLASS__
     * @param string $title es el título de la página, y el texto que aparecerá en el menú
     * @param string $folder es el menú dónde quieres colocar el acceso directo
     * @param boolean $admin OBSOLETO
     * @param boolean $shmenu debe ser TRUE si quieres añadir el acceso directo en el menú
     * @param boolean $important debe ser TRUE si quieres que aparezca en el menú de destacado
     */
    public function __construct($name = __CLASS__, $title = 'home', $folder = '', $admin = FALSE, $shmenu = TRUE, $important = FALSE)
    {
        date_default_timezone_set('America/Bogota');
        $tiempo = explode(' ', microtime());
        $this->uptime = $tiempo[1] + $tiempo[0];
        $this->extensions = array();

        $this->class_name = $name;
        $this->core_log = new fs_core_log($name);
        $this->cache = new fs_cache();
        $this->db = new fs_db2();
        
        
        
        
        

        if ($this->db->connect()) {
            $this->user = new fs_user();
            $this->check_fs_page($name, $title, $folder, $shmenu, $important);

            $this->empresa = new empresa();
            $this->default_items = new fs_default_items();
            $this->login_tools = new fs_login();
            $this->divisa_tools = new fs_divisa_tools($this->empresa);

            /// cargamos las extensiones
            $fsext = new fs_extension();
            foreach ($fsext->all() as $ext) {
                /// Cargamos las extensiones para este controlador o para todos
                if (in_array($ext->to, array(NULL, $name))) {
                    $this->extensions[] = $ext;
                }
            }

            if (filter_input(INPUT_GET, 'logout')) {
                self::sesion_cerrada();
                $this->template = 'login/default';
                $this->login_tools->log_out();
            } else if (filter_input(INPUT_POST, 'new_password') && filter_input(INPUT_POST, 'new_password2') && filter_input(INPUT_POST, 'user')) {
                $this->login_tools->change_user_passwd();
                $this->template = 'login/default';
            } else if (!$this->log_in()) {
                $this->template = 'login/default';
                $this->public_core();
            } else if ($this->user->have_access_to($this->page->name)) {
                if ($name == __CLASS__) {
                    $this->template = 'index';
                } else {
                    $this->template = $name;
                    $this->set_default_items();
                    $this->pre_private_core();
                    $this->private_core();
                }
            } else if ($name == '') {
                $this->template = 'index';
            } else {
                $this->template = 'access_denied';
                $this->user->clean_cache(TRUE);
                $this->empresa->clean_cache();
            }
        } else {
            $this->template = 'no_db';
            $this->new_error_msg('¡Imposible conectar con la base de datos <b>' . FS_DB_NAME . '</b>!');
        }
        //$this->bandera_lav = $this->db->select("SELECT * FROM personas_lav  WHERE usario_asociado='".$this->user->nick."'  AND codigo_estado=1");
        
        
          //  $this->bandera = count($this->bandera_lav);
        
    }

    private function pre_private_core()
    {
        $this->jefeopro = "Jefe de patio";
        $this->adminAbierto = "";
        
        
        
        $this->sesion_personasLav();
                
        $this->query = fs_filter_input_req('query');

        /// quitamos extensiones de páginas a las que el usuario no tenga acceso
        foreach ($this->extensions as $i => $value) {
            if ($value->type != 'config' && !$this->user->have_access_to($value->from)) {
                unset($this->extensions[$i]);
            }
        }
    }
    
    
    /*
     * Esta función registra los jefes de patio y los aspiradoes para el turno de caja,
     * si el usuario es admin, entonces no tiene la necesidad de ingresar jefes y aspiradores
     */
    private function sesion_personasLav(){
        
        //Consulta para saber si el usuario que intenta ingresar tiene una sesión de jefes y aspiradores abierta
        $this->bandera_lav = $this->db->select("SELECT * FROM personas_lav  WHERE usario_asociado='".$this->user->nick."'  AND codigo_estado=1");
        
        $jefes_agregados = -1;
        
        if(isset($_POST["jefes_agregados"])){
            $jefes_agregados = $_POST["jefes_agregados"];
        }
        
        //Si la sesión de personaLAV(con jefes y aspiradores) no está abierta, entonces vamos a recibir los datos para hacerlo
        if(!$this->bandera_lav && ($jefes_agregados>0 || ($jefes_agregados==0 && ($this->user->admin || $this->user->user_admin )) ) ){
            
            //Ingreso de datos jefes y aspiradores
            $this->bandera_lava();
            
            $this->bandera_lav = $this->db->select("SELECT * FROM personas_lav WHERE usario_asociado='".$this->user->nick."' AND codigo_estado=1");
            if($this->bandera_lav){
                $this->bandera = 1;
            }
            
            
        }
    }
    
    
    private function sesionAdmin()
    {
        $fecha = date("Y-m-d H:i:s");
        $sql = "INSERT INTO personas_lav"
                . "(usario_asociado, fecha_inicial, fecha_final, ip_equipo, codigo_estado )"
                . " VALUES('".$this->user->nick."', '".$fecha."', '".$fecha."', '".$ip."', '1' )";
        
        $this->db->exec($sql);
    }
    
    
            
    
    public function  sesion_cerrada(){
        
        $usuario = "";
        
        if(!empty($_GET["user_lav"])){
                $usuario = $_GET["user_lav"];
        }
        
        $fecha = date("Y-m-d H:i:s"); 
        
        $consUltimo = $this->db->select("SELECT reg FROM personas_lav WHERE usario_asociado = '".$usuario."' AND codigo_estado = 1 ");
        
        $sql="UPDATE personas_lav SET codigo_estado = '0', fecha_final='".$fecha."' WHERE  usario_asociado='".$usuario."' AND codigo_estado = 1";
        $this->db->exec($sql);
        if($consUltimo){
            $sql="UPDATE jefes_patio SET cod_estado = '0' WHERE  id_persona='".$consUltimo[0]["reg"]."' AND cod_estado = 1";
            $this->db->exec($sql);

            $sql="UPDATE aspiradores_lav SET cod_estado = '0' WHERE  id_persona='".$consUltimo[0]["reg"]."' AND cod_estado = 1";
            $this->db->exec($sql);
        }
        
        
        
    }

    /*
     * Función que inserta los jefes y los aspiradores  que seleccionó el usuario
     */
    public function bandera_lava(){
        
        $fecha = date("Y-m-d H:i:s"); 

        
             $sql = "INSERT INTO personas_lav"
                . "(usario_asociado, fecha_inicial, fecha_final, codigo_estado )"
                . " VALUES('".$this->user->nick."', '".$fecha."', '".$fecha."', '1' )";            

        $this->db->exec($sql);
        
        $consUltimo = $this->db->select("SELECT reg FROM personas_lav WHERE usario_asociado = '".$this->user->nick."' AND codigo_estado = 1 ");
        
        
        
        for($i=1; $i < 20 ; $i++){
            if(!empty($_POST["ad".$i])){
             $sql = "INSERT INTO  jefes_patio"
                . "(id_persona,jefe_adicionado,cod_estado)"
                . " VALUES('".$consUltimo[0]["reg"]."','".$_POST["ad".$i]."', '1' )";
             $this->db->exec($sql);
            }
        }
        
        for($i=1; $i < 20 ; $i++){
            if(!empty($_POST["asp".$i])){
             $sql = "INSERT INTO  aspiradores_lav"
                . "(id_persona,aspirador_add,cod_estado)"
                . " VALUES('".$consUltimo[0]["reg"]."','".$_POST["asp".$i]."', '1' )";
             $this->db->exec($sql);   
            }
        }
        
        
    }
    
    
    public function jefes_patio_inicio_sesion(){
        
        $sql = "SELECT * FROM proveedores WHERE jefe_patio=1  ORDER BY nombre ASC ";
        
        $consulta = $this->db->select($sql);
        
        if($consulta)
        {
            return $consulta;
        }else
            return FALSE;
           
    }


    
    /**
     * Función para llenar el select de inicio con los proveedores de servicio  Los proveedores de servicio, serán personafisica en la tabla proveedores
     * @param sin parámetros
      */
    public function  personas_lav(){
        
        
        if($this->bandera == 1)
            $sql = "SELECT * FROM proveedores WHERE personafisica=1  ORDER BY nombre ASC";
        else
            $sql = "SELECT * FROM proveedores WHERE jefe_patio=1  ORDER BY nombre ASC ";
        //$sql1 = "SELECT * FROM fs_users ";
        $tempo = $this->db->select($sql);
        //$tempo1 = $this->db->select($sql1);
        
        $datos = array();
        
        echo "</br>";
        print_r($datos);
        
        for($i=0; $i < count($tempo) ; $i++){
            $datos[] = $tempo[$i]['nombre'];
        }
        
        /*for($i=0; $i < count($tempo1) ; $i++){
            $datos[] = $tempo1[$i]['nick'];
        }*/
        
        
        return $datos;
    }
    
    public function aspiradoresInicio(){
        
            $sql = "SELECT * FROM proveedores WHERE personafisica=1 ORDER BY nombre ASC  ";

        //$sql1 = "SELECT * FROM fs_users ";
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
    
    /**
     * Función que liquida los servicios que se facturan, según parámetros modificados por el usuario
     * @param  double valor a liquidar $val
     * @param  varchar referencia artículo $ref
     */
    public function liquidacion_servicios($val, $ref, $tipo, $id_factura){
       
        
        $devolver = 0; //valor a devolver por defecto
        
        //tipo es para saber si estoy buscando el servicio o el producto instalado
        if($tipo == 1){
            $festivito = new festivos();

            $sql = "SELECT * FROM  facturascli WHERE idfactura = '".$id_factura."' ";
            $fact_fecha = $this->db->select($sql);
            if($fact_fecha){
                $fechita = $fact_fecha[0]["fecha"];
                $diaSemana = date("N", strtotime($fechita));
                if($festivito->esFestivo(date("d", strtotime($fechita)), date("m", strtotime($fechita))))
                    $diaSemana = 8;
                //echo "</br></br></br>".$fechita . " // ".date("N", strtotime($fechita));
            }else{
                $diaSemana = date("N");
                if($festivito->esFestivo(date("d"), date("m")))
                    $diaSemana = 8;
            }
            
            
            

            if($diaSemana >= 1 && $diaSemana <= 5)
                $diaSemana = 1;
            if($diaSemana >= 6 && $diaSemana <= 7)
                $diaSemana = 2;
            if($diaSemana == 8 )
                $diaSemana = 3;


            

            $sql = "SELECT * FROM  liquidacion_servicios_lav WHERE referencia = '".$ref."' ";
            $result = $this->db->select($sql);
            
            //echo "</br></br>".$sql;
            if($result){

                
                if($result[0]["porcentaje".$diaSemana]>0){
                    $devolver = ($val * $result[0]["porcentaje".$diaSemana]) / 100;
                }
                if($result[0]["valor_fijo".$diaSemana]>0){
                    $devolver = $result[0]["valor_fijo".$diaSemana];
                }
            }

            //Se cambia la forma como se van a liquidar los aspiradores para que se busque "aspirada" en el servicio
            //de esa forma cualquier ITEM con descripción aspirada será liquidada para los aspiradores
            $sql = "SELECT * FROM  liquidacion_servicios_lav t1 INNER JOIN articulos t2 ON  
             t1.referencia = '".$ref."' AND t1.referencia = t2.referencia AND  ( t2.descripcion LIKE '%ASPIRADA%' OR  t2.descripcion LIKE '%aspirada%') ";
            $result = $this->db->select($sql);
            if($result){
                if($result[0]["porcentaje".$diaSemana]>0){
                    $devolver = ($val * $result[0]["porcentaje".$diaSemana]) / 100;
                }
                if($result[0]["valor_fijo".$diaSemana]>0){
                    $devolver = $result[0]["valor_fijo".$diaSemana];
                }

                $cant_aspiradores = $this->db->select("SELECT COUNT(t1.reg) as cant FROM aspiradores_lav t1 INNER JOIN personas_lav t2 ON t2.usario_asociado = '".$this->user->nick."' AND t2.codigo_estado = 1 AND t2.reg = t1.id_persona ");
                if($cant_aspiradores){
                    if( (float)$cant_aspiradores[0]["cant"] == 0)
                        $devolver = $devolver;
                    else
                        $devolver = ($devolver) / (float)$cant_aspiradores[0]["cant"];
                }
                else
                    $devolver = 0;
            }
            /*
            if($ref == 6){

                $cant_aspiradores = $this->db->select("SELECT COUNT(t1.reg) as cant FROM aspiradores_lav t1 INNER JOIN personas_lav t2 ON t2.usario_asociado = '".$this->user->nick."' AND t2.codigo_estado = 1 AND t2.reg = t1.id_persona ");
                if($cant_aspiradores){
                    if( (float)$cant_aspiradores[0]["cant"] == 0)
                        $devolver = $val/2;
                    else
                        $devolver = ($val/2) / (float)$cant_aspiradores[0]["cant"];
                }
                else
                    $devolver = 0;
            }*/
            
            

        
        }else{
            $sql = "SELECT necesita_articulo FROM  articulos WHERE referencia = '".$ref."' ";
            $result = $this->db->select($sql);

            if($result){
                $devolver = ($val * (float)$result[0]["necesita_articulo"])  / 100 ; 
            }
        }
        
        //echo "</br></br> ".$ref." DEVOLVER ".$devolver;
        return $devolver;
        
    }

    
    /**
     * Función que liquida los servicios que se facturan, según parámetros modificados por el usuario
     * @param  double valor a liquidar $val
     * @param  varchar referencia artículo $ref
     */
    public function liquidacion_jefes($val, $ref, $tipo, $id_factura){
       
        
        $devolver = 0; //valor a devolver por defecto
        
         $sql = "SELECT por_liquidacion FROM  articulos WHERE referencia = '".$ref."'";
            $result = $this->db->select($sql);
            
 

            if($result){
                $devolver = ($val * (float)$result[0]["por_liquidacion"])  / 100 ; 
            }
        
        
        return $devolver;
        
    }


    /**
     * Procesa los datos de la página o entrada en el menú
     * @param string $name
     * @param string $title
     * @param string $folder
     * @param boolean $shmenu
     * @param boolean $important
     */
    private function check_fs_page($name, $title, $folder, $shmenu, $important)
    {
        /// cargamos los datos de la página o entrada del menú actual
        $this->page = new fs_page(
            array(
            'name' => $name,
            'title' => $title,
            'folder' => $folder,
            'version' => $this->version(),
            'show_on_menu' => $shmenu,
            'important' => $important,
            'orden' => 100
            )
        );

        /// ahora debemos comprobar si guardar o no
        if ($name !== 'fs_controller') {
            $page = $this->page->get($name);
            if ($page) {
                /// la página ya existe ¿Actualizamos?
                if ($page->title != $title || $page->folder != $folder || $page->show_on_menu != $shmenu || $page->important != $important) {
                    $page->title = $title;
                    $page->folder = $folder;
                    $page->show_on_menu = $shmenu;
                    $page->important = $important;
                    $page->save();
                }

                $this->page = $page;
            } else {
                /// la página no existe, guardamos.
                $this->page->save();
            }
        }
    }

    /**
     * Devuelve la versión de FacturaScripts
     * @return string versión de FacturaScripts
     */
    public function version()
    {
        if (file_exists('VERSION')) {
            return trim(file_get_contents('VERSION'));
        }

        return '0';
    }

    /**
     * Cierra la conexión con la base de datos.
     */
    public function close()
    {
        $this->db->close();
    }

    private function new_log_msg($tipo, $detalle, $alerta = FALSE)
    {
        $fslog = new fs_log();
        $fslog->tipo = $tipo;
        $fslog->detalle = $detalle;
        $fslog->ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        $fslog->alerta = $alerta;
        if ($this->user) {
            $fslog->usuario = $this->user->nick;
        }

        $fslog->save();
    }

    /**
     * Muestra al usuario un mensaje de error
     * @param string $msg el mensaje a mostrar
     */
    public function new_error_msg($msg, $tipo = 'error', $alerta = FALSE, $guardar = TRUE)
    {
        if ($this->class_name == $this->core_log->controller_name()) {
            /// solamente nos interesa mostrar los mensajes del controlador que inicia todo
            $this->core_log->new_error($msg);
        }

        if ($guardar) {
            $this->new_log_msg($tipo, $msg, $alerta);
        }
    }

    /**
     * Devuelve la lista de errores
     * @return array lista de errores
     */
    public function get_errors()
    {
        return $this->core_log->get_errors();
    }

    /**
     * Muestra un mensaje al usuario
     * @param string $msg
     * @param boolean $save
     * @param string $tipo
     * @param boolean $alerta
     */
    public function new_message($msg, $save = FALSE, $tipo = 'msg', $alerta = FALSE)
    {
        if ($this->class_name == $this->core_log->controller_name()) {
            /// solamente nos interesa mostrar los mensajes del controlador que inicia todo
            $this->core_log->new_message($msg);
        }

        if ($save) {
            $this->new_log_msg($tipo, $msg, $alerta);
        }
    }

    /**
     * Devuelve la lista de mensajes
     * @return array lista de mensajes
     */
    public function get_messages()
    {
        return $this->core_log->get_messages();
    }

    /**
     * Muestra un consejo al usuario
     * @param string $msg el consejo a mostrar
     */
    public function new_advice($msg)
    {
        if ($this->class_name == $this->core_log->controller_name()) {
            /// solamente nos interesa mostrar los mensajes del controlador que inicia todo
            $this->core_log->new_advice($msg);
        }
    }

    /**
     * Devuelve la lista de consejos
     * @return array lista de consejos
     */
    public function get_advices()
    {
        return $this->core_log->get_advices();
    }

    /**
     * Devuelve la URL de esta página (index.php?page=LO-QUE-SEA)
     * @return string
     */
    public function url()
    {
        return $this->page->url();
    }

    /**
     * Devuelve TRUE si el usuario realmente tiene acceso a esta página
     * @return boolean
     */
    private function log_in()
    {
        $this->login_tools->log_in($this->user);
        if ($this->user->logged_on) {
            $this->load_menu();
        }

        return $this->user->logged_on;
    }

    /**
     * Devuelve la duración de la ejecución de la página
     * @return string un string con la duración de la ejecución
     */
    public function duration()
    {
        $tiempo = explode(" ", microtime());
        return (number_format($tiempo[1] + $tiempo[0] - $this->uptime, 3) . ' s');
    }

    /**
     * Devuelve el número de consultas SQL (SELECT) que se han ejecutado
     * @return integer
     */
    public function selects()
    {
        return $this->db->get_selects();
    }

    /**
     * Devuleve el número de transacciones SQL que se han ejecutado
     * @return integer
     */
    public function transactions()
    {
        return $this->db->get_transactions();
    }

    /**
     * Devuelve el listado de consultas SQL que se han ejecutados
     * @return array lista de consultas SQL
     */
    public function get_db_history()
    {
        return $this->core_log->get_sql_history();
    }

    /**
     * Carga el menú de facturaScripts
     * @param boolean $reload TRUE si quieres recargar
     */
    protected function load_menu($reload = FALSE)
    {
        $this->menu = $this->user->get_menu($reload);
    }

    /**
     * Devuelve la lista de menús
     * @return array lista de menús
     */
    public function folders()
    {
        $folders = array();
        foreach ($this->menu as $m) {
            if ($m->folder != '' && $m->show_on_menu && !in_array($m->folder, $folders)) {
                $folders[] = $m->folder;
            }
        }
        return $folders;
    }

    /**
     * Devuelve la lista de elementos de un menú seleccionado
     * @param string $folder el menú seleccionado
     * @return array lista de elementos del menú
     */
    public function pages($folder = '')
    {
        $pages = array();
        foreach ($this->menu as $page) {
            if ($folder == $page->folder && $page->show_on_menu && !in_array($page, $pages)) {
                $pages[] = $page;
            }
        }
        return $pages;
    }

    /**
     * Función que se ejecuta si el usuario no ha hecho login
     */
    protected function public_core()
    {
        
    }

    /**
     * Esta es la función principal que se ejecuta cuando el usuario ha hecho login
     */
    protected function private_core()
    {
        
    }

    /**
     * Redirecciona a la página predeterminada para el usuario
     */
    public function select_default_page()
    {
        if ($this->db->connected() && $this->user->logged_on) {
            if (is_null($this->user->fs_page)) {
                $page = 'admin_home';

                /*
                 * Cuando un usuario no tiene asignada una página por defecto,
                 * se selecciona la primera página del menú.
                 */
                foreach ($this->menu as $p) {
                    if ($p->show_on_menu) {
                        $page = $p->name;
                        if ($p->important) {
                            break;
                        }
                    }
                }
            } else {
                $page = $this->user->fs_page;
            }

            header('Location: index.php?page=' . $page);
        }
    }

    /**
     * Establecemos los elementos por defecto, pero no se guardan.
     * Para guardarlos hay que usar las funciones fs_controller::save_lo_que_sea().
     * La clase fs_default_items sólo se usa para indicar valores
     * por defecto a los modelos.
     */
    private function set_default_items()
    {
        /// gestionamos la página de inicio
        if (filter_input(INPUT_GET, 'default_page')) {
            if (filter_input(INPUT_GET, 'default_page') == 'FALSE') {
                $this->default_items->set_default_page(NULL);
                $this->user->fs_page = NULL;
            } else {
                $this->default_items->set_default_page($this->page->name);
                $this->user->fs_page = $this->page->name;
            }

            $this->user->save();
        } else if (is_null($this->default_items->default_page())) {
            $this->default_items->set_default_page($this->user->fs_page);
        }

        if (is_null($this->default_items->showing_page())) {
            $this->default_items->set_showing_page($this->page->name);
        }

        $this->default_items->set_codejercicio($this->empresa->codejercicio);

        if (filter_input(INPUT_COOKIE, 'default_almacen')) {
            $this->default_items->set_codalmacen(filter_input(INPUT_COOKIE, 'default_almacen'));
        } else {
            $this->default_items->set_codalmacen($this->empresa->codalmacen);
        }

        if (filter_input(INPUT_COOKIE, 'default_formapago')) {
            $this->default_items->set_codpago(filter_input(INPUT_COOKIE, 'default_formapago'));
        } else {
            $this->default_items->set_codpago($this->empresa->codpago);
        }

        if (filter_input(INPUT_COOKIE, 'default_impuesto')) {
            $this->default_items->set_codimpuesto(filter_input(INPUT_COOKIE, 'default_impuesto'));
        }

        $this->default_items->set_codpais($this->empresa->codpais);
        $this->default_items->set_codserie($this->empresa->codserie);
        $this->default_items->set_coddivisa($this->empresa->coddivisa);
    }

    /**
     * Establece un almacén como predeterminado para este usuario.
     * @param string $cod el código del almacén
     */
    protected function save_codalmacen($cod)
    {
        setcookie('default_almacen', $cod, time() + FS_COOKIES_EXPIRE);
        $this->default_items->set_codalmacen($cod);
    }

    /**
     * Establece una forma de pago como predeterminada para este usuario.
     * @param string $cod el código de la forma de pago
     */
    protected function save_codpago($cod)
    {
        setcookie('default_formapago', $cod, time() + FS_COOKIES_EXPIRE);
        $this->default_items->set_codpago($cod);
    }

    /**
     * Establece un impuesto (IVA) como predeterminado para este usuario.
     * @param string $cod el código del impuesto
     */
    protected function save_codimpuesto($cod)
    {
        setcookie('default_impuesto', $cod, time() + FS_COOKIES_EXPIRE);
        $this->default_items->set_codimpuesto($cod);
    }

    /**
     * Devuelve la fecha actual
     * @return string la fecha en formato día-mes-año
     */
    public function today()
    {
         date_default_timezone_set('America/Bogota');
        return date('d-m-Y');
    }

    /**
     * Devuelve la hora actual
     * @return string la hora en formato hora:minutos:segundos
     */
    public function hour()
    {
        return Date('H:i:s');
    }

    /**
     * Devuelve un string aleatorio de longitud $length
     * @param integer $length la longitud del string
     * @return string la cadena aleatoria
     */
    public function random_string($length = 30)
    {
        return mb_substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
    }

    /**
     * He detectado que algunos navegadores, en algunos casos, envían varias veces la
     * misma petición del formulario. En consecuencia se crean varios modelos (asientos,
     * albaranes, etc...) con los mismos datos, es decir, duplicados.
     * Para solucionarlo añado al formulario un campo petition_id con una cadena
     * de texto aleatoria. Al llamar a esta función se comprueba si esa cadena
     * ya ha sido almacenada, de ser así devuelve TRUE, así no hay que gabar los datos,
     * si no, se almacena el ID y se devuelve FALSE.
     * @param string $pid el identificador de la petición
     * @return boolean TRUE si la petición está duplicada
     */
    protected function duplicated_petition($pid)
    {
        $ids = $this->cache->get_array('petition_ids');
        if (in_array($pid, $ids)) {
            return TRUE;
        }

        $ids[] = $pid;
        $this->cache->set('petition_ids', $ids, 300);
        return FALSE;
    }

    /**
     * Devuelve información del sistema para el informe de errores
     * @return string la información del sistema
     */
    public function system_info()
    {
        $txt = 'facturascripts: ' . $this->version() . "\n";

        if ($this->db->connected()) {
            if ($this->user->logged_on) {
                $txt .= 'os: ' . php_uname() . "\n";
                $txt .= 'php: ' . phpversion() . "\n";
                $txt .= 'database type: ' . FS_DB_TYPE . "\n";
                $txt .= 'database version: ' . $this->db->version() . "\n";

                if (FS_FOREIGN_KEYS == 0) {
                    $txt .= "foreign keys: NO\n";
                }

                if ($this->cache->connected()) {
                    $txt .= "memcache: YES\n";
                    $txt .= 'memcache version: ' . $this->cache->version() . "\n";
                } else {
                    $txt .= "memcache: NO\n";
                }

                if (function_exists('curl_init')) {
                    $txt .= "curl: YES\n";
                } else {
                    $txt .= "curl: NO\n";
                }

                $txt .= "max input vars: " . ini_get('max_input_vars') . "\n";

                $txt .= 'plugins: ' . join(',', $GLOBALS['plugins']) . "\n";

                if ($this->check_for_updates()) {
                    $txt .= "updated: NO\n";
                }

                if (filter_input(INPUT_SERVER, 'REQUEST_URI')) {
                    $txt .= 'url: ' . filter_input(INPUT_SERVER, 'REQUEST_URI') . "\n------";
                }
            }
        } else {
            $txt .= 'os: ' . php_uname() . "\n";
            $txt .= 'php: ' . phpversion() . "\n";
            $txt .= 'database type: ' . FS_DB_TYPE . "\n";
        }

        foreach ($this->get_errors() as $e) {
            $txt .= "\n" . $e;
        }

        return str_replace('"', "'", $txt);
    }

    /**
     * Devuelve el símbolo de divisa predeterminado
     * o bien el símbolo de la divisa seleccionada.
     * @param string $coddivisa
     * @return string
     */
    public function simbolo_divisa($coddivisa = FALSE)
    {
        return $this->divisa_tools->simbolo_divisa($coddivisa);
    }

    /**
     * Devuelve un string con el precio en el formato predefinido y con la
     * divisa seleccionada (o la predeterminada).
     * @param float $precio
     * @param string $coddivisa
     * @param string $simbolo
     * @param integer $dec nº de decimales
     * @return string
     */
    public function show_precio($precio = 0, $coddivisa = FALSE, $simbolo = TRUE, $dec = FS_NF0)
    {
        return $this->divisa_tools->show_precio($precio, $coddivisa, $simbolo, $dec);
    }

    /**
     * Devuelve un string con el número en el formato de número predeterminado.
     * @param float $num
     * @param integer $decimales
     * @param boolean $js
     * @return string
     */
    public function show_numero($num = 0, $decimales = FS_NF0, $js = FALSE)
    {
        return $this->divisa_tools->show_numero($num, $decimales, $js);
    }

    /**
     * Convierte el precio en euros a la divisa preterminada de la empresa.
     * Por defecto usa las tasas de conversión actuales, pero si se especifica
     * coddivisa y tasaconv las usará.
     * @param float $precio
     * @param string $coddivisa
     * @param float $tasaconv
     * @return float
     */
    public function euro_convert($precio, $coddivisa = NULL, $tasaconv = NULL)
    {
        return $this->divisa_tools->euro_convert($precio, $coddivisa, $tasaconv);
    }

    /**
     * Convierte un precio de la divisa_desde a la divisa especificada
     * @param float $precio
     * @param string $coddivisa_desde
     * @param string $coddivisa
     * @return float
     */
    public function divisa_convert($precio, $coddivisa_desde, $coddivisa)
    {
        return $this->divisa_tools->divisa_convert($precio, $coddivisa_desde, $coddivisa);
    }

    /**
     * Añade un elemento a la lista de cambios del usuario.
     * @param string $txt texto descriptivo.
     * @param string $url URL del elemento (albarán, factura, artículos...).
     * @param boolean $nuevo TRUE si el elemento es nuevo, FALSE si se ha modificado.
     */
    public function new_change($txt, $url, $nuevo = FALSE)
    {
        $this->get_last_changes();
        if (count($this->last_changes) > 0) {
            if ($this->last_changes[0]['url'] == $url) {
                $this->last_changes[0]['nuevo'] = $nuevo;
            } else {
                array_unshift($this->last_changes, array('texto' => ucfirst($txt), 'url' => $url, 'nuevo' => $nuevo, 'cambio' => date('d-m-Y H:i:s')));
            }
        } else {
            array_unshift($this->last_changes, array('texto' => ucfirst($txt), 'url' => $url, 'nuevo' => $nuevo, 'cambio' => date('d-m-Y H:i:s')));
        }

        /// sólo queremos 10 elementos
        $num = 10;
        foreach ($this->last_changes as $i => $value) {
            if ($num > 0) {
                $num--;
            } else {
                unset($this->last_changes[$i]);
            }
        }

        $this->cache->set('last_changes_' . $this->user->nick, $this->last_changes);
    }

    /**
     * Devuelve la lista con los últimos cambios del usuario.
     * @return array
     */
    public function get_last_changes()
    {
        if (!isset($this->last_changes)) {
            $this->last_changes = $this->cache->get_array('last_changes_' . $this->user->nick);
        }

        return $this->last_changes;
    }

    /**
     * Elimina la lista con los últimos cambios del usuario.
     */
    public function clean_last_changes()
    {
        $this->last_changes = array();
        $this->cache->delete('last_changes_' . $this->user->nick);
    }

    /**
     * Devuelve TRUE si hay actualizaciones pendientes (sólo si eres admin).
     * @return boolean
     */
    public function check_for_updates()
    {
        if (!isset($this->fs_updated)) {
            $this->fs_updated = FALSE;

            if ($this->user->admin) {
                $desactivado = FALSE;
                if (defined('FS_DISABLE_MOD_PLUGINS')) {
                    $desactivado = FS_DISABLE_MOD_PLUGINS;
                }

                if ($desactivado) {
                    $this->fs_updated = FALSE;
                } else {
                    $fsvar = new fs_var();
                    $this->fs_updated = $fsvar->simple_get('updates');
                }
            }
        }

        return $this->fs_updated;
    }

    /**
     * Busca en la lista de plugins activos, en orden inverso de prioridad
     * (el último plugin activo tiene más prioridad que el primero)
     * y nos devuelve la ruta del archivo javascript que le solicitamos.
     * Así usamos el archivo del plugin con mayor prioridad.
     * @param string $filename
     * @return string
     */
    public function get_js_location($filename)
    {
        /// necesitamos un id que se cambie al limpiar la caché
        $idcache = $this->cache->get('fs_idcache');
        if (!$idcache) {
            $idcache = $this->random_string(10);
            $this->cache->set('fs_idcache', $idcache, 86400);
        }

        foreach ($GLOBALS['plugins'] as $plugin) {
            if (file_exists('plugins/' . $plugin . '/view/js/' . $filename)) {
                return FS_PATH . 'plugins/' . $plugin . '/view/js/' . $filename . '?idcache=' . $idcache;
            }
        }

        /// si no está en los plugins estará en el núcleo
        return FS_PATH . 'view/js/' . $filename . '?idcache=' . $idcache;
    }

    /**
     * Devuelve el tamaño máximo permitido para subir archivos.
     * @return integer
     */
    public function get_max_file_upload()
    {
        return fs_get_max_file_upload();
    }
}
