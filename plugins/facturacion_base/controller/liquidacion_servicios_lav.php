<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */



/**
 * Description of facturas_lav
 *
 * @author LASCA
 */
class liquidacion_servicios_lav extends fs_controller {
    public $servicios_lav;
    public $busqueda;
    private $val_fijo1;
    private $porcent1;
    private $val_fijo2;
    private $porcent2;
    private $val_fijo3;
    private $porcent3;
    public $familia;
    


    public function __construct() {
        parent::__construct(__CLASS__, 'Parámetros Lav', 'parámetros', false, true);
    }
    
    protected function private_core()
    {
       $this->busqueda = "";
       $this->familia = new familia();
       
       
       
       if(!empty($_POST["busqueda"]))
            $this->busqueda = strtoupper($_POST["busqueda"]);    
       
       if (isset($_REQUEST['ref'])) {
           $this->val_fijo1 = $_REQUEST['val_fijo1'];
           $this->porcent1 = $_REQUEST['porcentaje1'];
           
           $this->val_fijo2 = $_REQUEST['val_fijo2'];
           $this->porcent2 = $_REQUEST['porcentaje2'];
           
           $this->val_fijo3 = $_REQUEST['val_fijo3'];
           $this->porcent3 = $_REQUEST['porcentaje3'];
           
            $this->actualizar_liquidacion($_REQUEST['ref']);
        } 
        
        if (isset($_REQUEST['porcentaje']) && isset($_REQUEST['tipo']) ) {
            $this->cambiar_masivo($_REQUEST['tipo']);
        }
       
       $this->servicios_lav();
    }

    
    private function servicios_lav(){
        
        $sql = "SELECT * FROM  articulos WHERE 	(descripcion LIKE '%".$this->busqueda."%' OR referencia LIKE '%".$this->busqueda."%') AND servicio=1  ORDER BY descripcion ASC" ;
        $this->servicios_lav = $this->db->select($sql);

    }
    
    
    
    private function cambiar_masivo($tipo){
        /// desactivamos la plantilla HTML
        
        
        
        
        $this->template = FALSE;
        
        $porcen =  ($_REQUEST['porcentaje']/100) + 1 ;
        
        $this->db->exec("TRUNCATE TABLE masivo_temporal");
        
        $sql1  = "";
        
        
        
        if($tipo == 1 ){
            
            $camposModificar = " pvp=ROUND(pvp*".$porcen."), preciocombo = ROUND( preciocombo * ".$porcen." ) ";
            
        }
        
        if($tipo == 2 ){
            
            $camposModificar = " por_liquidacion=".$_REQUEST['porcentaje']." ";
            
        }
            
            $fecha = date("Y-m-d h:i:s");

            if($_REQUEST['fam'] != ""){
                $sql1 = "SELECT referencia, pvp, preciocombo, por_liquidacion FROM articulos WHERE es_combo=0 AND servicio<>1 AND codfamilia = '".$_REQUEST['fam']."' ";
                $condicion = " servicio<>1 AND codfamilia = '".$_REQUEST['fam']."'  ";
            }
            else{
                $sql1 = "SELECT referencia, pvp, preciocombo, por_liquidacion FROM articulos WHERE servicio<>1 AND es_combo=0  ";
                $condicion = " servicio<>1 ";
            }
            
                $articulos_ = $this->db->select($sql1);
                for($i=0; $i< count($articulos_) ; $i++ ){
                
                    
                    $sql2 = "INSERT INTO masivo_temporal(referencia, precio_venta, precio_combo, por_liquidacion, fecha, usuario) "
                            . " VALUES ("
                            . "'".$articulos_[$i]["referencia"]."'"
                            . ",'".$articulos_[$i]["pvp"]."'"
                            . ",'".$articulos_[$i]["preciocombo"]."'"
                            . ",'".$articulos_[$i]["por_liquidacion"]."'"
                            . ",'".$fecha."'"
                            . ",'".$this->user->nick."'"
                            . ")";
                    $sql1 .= "</br> = ".$sql2;
                    $this->db->exec($sql2);
                
                }
                
                $sql3 = "UPDATE articulos SET ".$camposModificar." WHERE ".$condicion;
                $this->db->exec($sql3);
        
        
        
        header('Content-Type: application/json');
        echo json_encode($sql3);
    }




    private function actualizar_liquidacion($ref){
        
        /// desactivamos la plantilla HTML
        $this->template = FALSE;
        $resul = FALSE;
        $fecha = date("Y-m-d h:i:s");
        
        $sql = "UPDATE liquidacion_servicios_lav SET valor_fijo1='".$this->val_fijo1."', porcentaje1='".$this->porcent1."', valor_fijo2='".$this->val_fijo2."', porcentaje2='".$this->porcent2."', valor_fijo3='".$this->val_fijo3."', porcentaje3='".$this->porcent3."', fecha='".$fecha."', usuario='".$this->user->nick."'  WHERE referencia = '".$ref."'" ;
        if($this->db->exec($sql))
            $resul = TRUE;
            
        header('Content-Type: application/json');
        echo json_encode($resul);
            
    }
    
    public function consulta_liquidacion($ref, $tipo)
    {
        $sql = "SELECT * FROM  liquidacion_servicios_lav  WHERE referencia = '".$ref."' " ;
        $resultado = $this->db->select($sql);
        
        if(!$resultado){
            $this->db->exec("INSERT INTO liquidacion_servicios_lav(referencia, usuario) VALUES('".$ref."','".$this->user->nick."') ");
            $resultado = $this->db->select($sql);
        }
        
        return $resultado[0][$tipo];
    }
	
	

}
