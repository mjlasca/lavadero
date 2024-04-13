<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of servicios_lav
 *
 * @author LASCA
 */
class servicios_lav extends fs_controller {
    //put your code here
    
    public $id_com;
    
    public $desc;
    
    public $ad1;
    
    public $ad2;
    
    public $ad3;
    
    public $ad4;
    
    public $ad5;
    
    public $ad6;
    
    
    public $allow_delete;
    
    public $adi1;
    public $adi2;
    public $adi3;
    
    public $art1;
    public $art2;
    public $art3;
    
    public $fecha;
 
    
    public $datos;
    
    public $datos1;
    
    public $servicios;
    
    public $articulos;
    
    public $consulta_combos;
    
    public $consulta_ind;
    
    public $aprobar;
    
    public $cont_lav;
    
    public $actualiza_ban;
    
    public $conjunto_combo_cant;
    
    public $consulta_combos_conjunto;
    
    public $bandera_aviso;
    



    public function __construct() {
        parent::__construct(__CLASS__, 'Combos', 'ventas');
        $this->datos = array();
        $this->aprobar = false;
        $this->cont_lav = 1;
        
    }
    

    
    protected function private_core()
    {
     //   $this->template = FALSE;
        $this->bandera_aviso = 0;
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->admin;

        if ( !empty($_POST['cant_conjunto'] ) ) {
            if($_POST['cant_conjunto'] > 1){
                $this->conjunto_combo_cant = $_POST['cant_conjunto'];
            }
        }
        
        if ( !empty($_GET['ref'] ) ) {
            $this->actualiza_ban = $_GET["ref"];
            $this->consulta_combos_conjunto = $this->db->select("SELECT * FROM conjunto_combos WHERE codigo_estado=1 AND idcombo='".$_GET['ref']."' ");
            $this->conjunto_combo_cant = count($this->consulta_combos_conjunto) + 1;
        }
        
        if(isset($_REQUEST["eliminar"])){
            $this->eliminar_combo($_REQUEST["eliminar"]);
        }
        
        if ( !empty($_POST['des_servicio'] ) ) {
            
            
            if (isset($_POST['cod_servicio'])) {
                $this->id_com = $_POST['cod_servicio'];
            }
            if (isset($_POST['des_servicio'])) {
                $this->desc = $_POST['des_servicio'];
            }

            $this->fecha = $_POST['fecha']." ".$_POST['hora'];
            if(self::ver_id() == 1){
                self::insertar_servicio($this->fecha);
            }

        }
        
        
        if ( !empty($_POST['actualizar_lav'] ) ) {
            $this->fecha = $_POST['fecha']." ".$_POST['hora'];
            self::actualizar_servicio($this->fecha);
        }
        

        self::ver_servicios();
        self::ver_articulos();
        self::consultar_combos();

    }
    
    private function eliminar_combo($id){
        
        $sql = "UPDATE combos_lav SET codigo_estado = 0 WHERE idcombo = '".$id."' ";
        
        if(!$this->db->exec($sql))
            $this->new_error_msg ("1Error... No se pudo eliminar el registro");
        else{
            $sql = "UPDATE articulos SET cod_estado = 0 WHERE referencia = 'COMBO".$id."' ";
            if(!$this->db->exec($sql))
                $this->new_error_msg ("2Error... No se pudo eliminar el registro ");
            
        }
        
        
    }

    
    private function crearTablaServicio()
    {
        
        //$this->inventario = array("ciudad", "estado", "otro");

        /*$consulta = "CREATE TABLE servicios_lav (
                id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
                firstname VARCHAR(30) NOT NULL,
                lastname VARCHAR(30) NOT NULL,
                email VARCHAR(50),
                reg_date TIMESTAMP
                )";
        $tabla = $this->db->select($consulta);*/
    }
    
    
    
    public function conjunto_combo(){
        
        $sql = "";
        
        for($i=2; $i <= $this->conjunto_combo_cant ; $i++){
            //echo '</br>---> '.$i." - > ".$sql;
            if(!empty($_POST['ad'.$i])){
				
				$consul_articulo = $this->db->select("SELECT referencia FROM articulos WHERE descripcion='".$_POST['ad'.$i]."' ");
				
                $sql = "INSERT INTO conjunto_combos"
                . "( idcombo, articulo, codigo_estado )"
                . " VALUES('".$this->id_com."', '".$consul_articulo[0]["referencia"]."', '1' )";
                
                //echo '</br>ad'.$i." - > ".$sql;
                
                $this->db->exec($sql);
            }
        }
    }
    
    public function insertar_servicio($fecha){
        
        //$id, $desc, $ad1, $ad2, $ad3, $ad4, $fecha, $estado
        
        $fecha = date("Y-m-d");
        
        $sql = "INSERT INTO combos_lav"
                . "(descripcion, fecha, codigo_estado, usuario )"
                . " VALUES('".$this->desc."', '".$fecha."', '1', '".$this->user->nick."' )";
        
        
		//SI se inserta correctamente el registro o el combo, también se debe crear el artículo con el nombre del combo
		//para luego reconocerlo en TPV o facturas
        if($this->db->exec($sql))
        {
            
            $temp = $this->db->select("SELECT MAX(idcombo) as idcombo FROM combos_lav");
        
            $this->id_com = $temp[0]['idcombo'];
            
                $refi = "COMBO".$this->id_com;

                $this->db->exec("INSERT INTO articulos(referencia, descripcion, codfamilia, es_combo) VALUES('".$refi."', '".strtoupper($this->desc)."', 'COMBO', '1')");
                $this->bandera_aviso = 1;
                self::conjunto_combo();
        }else
            $this->bandera_aviso = 2;
			

        
        
    }
    
    public function actualizar_servicio($fecha){

        $sql = "UPDATE combos_lav SET "
                ." descripcion ='".strtoupper($this->desc)."', fecha = '".$fecha."' WHERE idcombo = '".$this->id_com."' ";
        $this->db->exec($sql);
        
        $sql = "UPDATE conjunto_combos SET "
                ." codigo_estado = 0 WHERE idcombo = '".$this->id_com."' ";
        $this->db->exec($sql);
        
        self::conjunto_combo();
        
    }
    
    private function ver_id(){
        $result = 1;
        $dato = $this->db->select("SELECT * FROM combos_lav WHERE descripcion='".$this->desc."'");

        if($dato)
            $result = 0;
        
        return $result;
    }
    
    public function ver_servicios()
    {
        $this->adi1 = $this->db->select("SELECT referencia, descripcion FROM articulos WHERE servicio=1 ORDER BY descripcion ASC");
        
    }
    
    public function ver_articulos()
    {
        $this->art1 = $this->db->select("SELECT referencia, descripcion FROM articulos  WHERE  	servicio != 1 AND es_combo=0 ORDER BY descripcion ASC ");

    }
    
    public function consultar_combos()
    {
            
            $this->consulta_combos = $this->db->select("SELECT * FROM combos_lav WHERE codigo_estado=1 ");

    }
	
	
	public function busq_articulo($datico)
	{
		$consul_articulo = $this->db->select("SELECT descripcion FROM articulos WHERE referencia='".$datico."' ");
		return $consul_articulo[0]['descripcion'];
	}
	
    
     public function valor_combo($id_combo){
         $sql = "SELECT SUM(t1.preciocombo) as total FROM articulos t1 INNER JOIN conjunto_combos t2 ON "
                 . "t2.idcombo='".$id_combo."' AND t2.articulo=t1.referencia AND t2.codigo_estado = 1 ";
         
         //echo "</br></br>".$sql;
         $total_combo = $this->db->select($sql);
         
         if($total_combo)
             return $total_combo[0]["total"];
         else
             return 0;
     }
     
     public function formato_moneda($number){
            $text ="$". number_format($number, 2); 
            return  $text;
        
     }
        
        
    public function conjunto_consulta($regis){
        
        $temp = "";
        $temporal = $this->db->select("SELECT * FROM conjunto_combos WHERE codigo_estado=1 AND idcombo='".$regis."' ");
        for($i=0; $i < count($temporal  ) ; $i++){
            
				
                if(count( $temporal )>1 &&  $i < count($temporal)-1 ){
                    $temp .= "<a href='index.php?page=ventas_articulo&ref=".$temporal[$i]['articulo']."'>".$this->busq_articulo($temporal[$i]['articulo'])."</a>, ";
				}
                else {
                    $temp .= "<a href='index.php?page=ventas_articulo&ref=".$temporal[$i]['articulo']."'>".$this->busq_articulo($temporal[$i]['articulo'])."</a>";
                }

                $this->datos[$temporal[$i]['idcombo']] = $temp;
                 
        }
        
        return $temp;
    }
    
    
    public function consultar_indi($campo)
    {
           if ( !empty($_GET['ref'] ) ) {
            //$this->consulta_ind = self::consultar_indi($_GET['ref']);

                $this->consulta_ind = $this->db->select("SELECT * FROM combos_lav WHERE codigo_estado=1 AND idcombo='".$_GET['ref']."' ");
                return $this->consulta_ind[0][$campo];
            
            }
    }
    
    
    
    

}
