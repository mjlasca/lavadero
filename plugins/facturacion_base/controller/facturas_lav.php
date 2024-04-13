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
class facturas_lav extends fs_controller {
    public $enProceso;
    public $cant1;
    public $creditos;
    public $cant2;
    public $id_arqueo;
    
    
    public $buscar;
    
    public $sele_fact;
    
    public function __construct() {
        parent::__construct(__CLASS__, 'Créditos y en Proceso', 'ventas');
    }
    
    protected function private_core()
    {
        
        $arqueo = new \FacturaScripts\model\caja;
        if($arqueo->get_agente($this->user->codagente))
            $this->id_arqueo = $arqueo->get_agente($this->user->codagente)[0]["id"];
        
        
        
        
        
        $this->buscar = "";
        
        
        if(isset($_REQUEST["pagar_facturas"])){
            $this->pagar_facturas($_REQUEST["pagar_facturas"]);
        }
        if(isset($_REQUEST["pagar_facturas_all"])){
            $this->pagar_facturas_all();
        }
        
        
        if(isset($_POST["buscar"])){
            $this->buscar = $_POST["buscar"];
        }
        
        
        $this->sele_fact = "prov";
        if(isset($_POST["sele_fact"])){
            $this->sele_fact = $_POST["sele_fact"];
        }
        
        if($this->sele_fact == "prov")
            $this->consultas_proveedor();
        if($this->sele_fact == "cli")
            $this->consultas_clientes();
        
        

    }
    
    
    private function pagar_facturas_all(){
        $sql = "UPDATE facturascli SET pagada = 1 WHERE codpago = 'CONT' AND id_arqueo = '".$this->id_arqueo."' ";
        if($this->db->exec($sql))
            $this->new_message ("Todas las facturas contado fueron pagadas");
        else
            $this->new_message ("Hubo un error al tratar de pagar todas las facutas contado");
        
    }
    
    private function pagar_facturas($dato){
        
        $sql = "UPDATE facturascli SET pagada = 1 WHERE codpago = 'CONT' AND  id_arqueo = '".$this->id_arqueo."' AND proveedor_servicio = '".$dato."' ";
        if($dato == "0")
            $dato = "ARTÍCULOS";
        if($this->db->exec($sql))
            $this->new_message ("Todas las facturas contado de ".$dato." fueron pagadas");
        else
            $this->new_message ("Hubo un error al tratar de pagar todas las facturas contado de ".$dato."  ");
    }
    
    public function consultas_clientes(){
        //$this->inventario = array("ciudad", "estado", "otro");		
        $this->enProceso = $this->db->select("SELECT DISTINCT(nombrecliente) as nombre FROM facturascli  WHERE  id_arqueo = '".$this->id_arqueo."' AND pagada=0 AND  anulada=0 AND codpago='CONT' AND LOWER(nombrecliente) LIKE LOWER('%".$this->buscar."%')  ORDER BY nombrecliente ASC ");
        $this->cant1 = count($this->enProceso);
        $this->creditos = $this->db->select("SELECT DISTINCT(nombrecliente) as nombre FROM facturascli  WHERE  id_arqueo = '".$this->id_arqueo."' AND pagada=0 AND  anulada=0 AND codpago!='CONT' AND LOWER(nombrecliente) LIKE LOWER('%".$this->buscar."%') ORDER BY nombrecliente ASC");
        $this->cant2 = count($this->creditos);
    }
    
    public function consultas_proveedor(){
        //$this->inventario = array("ciudad", "estado", "otro");		
        $this->enProceso = $this->db->select("SELECT DISTINCT(proveedor_servicio) as nombre FROM facturascli  WHERE  id_arqueo = '".$this->id_arqueo."' AND pagada=0  AND  anulada=0  AND codpago='CONT'  AND LOWER(proveedor_servicio) LIKE LOWER('%".$this->buscar."%') ORDER BY proveedor_servicio ASC ");
        $this->cant1 = count($this->enProceso);
        $this->creditos = $this->db->select("SELECT DISTINCT(proveedor_servicio) as nombre FROM facturascli  WHERE  id_arqueo = '" . $this->id_arqueo . "' AND  pagada=0  AND  anulada=0  AND codpago!='CONT' AND LOWER(proveedor_servicio) LIKE LOWER('%".$this->buscar."%') ORDER BY proveedor_servicio ASC");
        $this->cant2 = count($this->creditos);
    }
    
    
    


    public function listado_facturas($provee, $opcion)
    {
            $arreglin = array();



            if($opcion == 1)//en proceso
                    $sql = "SELECT nombrecliente, codcliente, idfactura, total, observaciones, proveedor_servicio FROM facturascli WHERE   id_arqueo = '".$this->id_arqueo."' AND   anulada=0  AND  ( proveedor_servicio='".$provee."'  OR nombrecliente='".$provee."'  ) AND  pagada=0 AND codpago='CONT'  group by idfactura ORDER BY proveedor_servicio  ASC";
            if($opcion == 2)//en proceso
                    $sql = "SELECT nombrecliente, codcliente, idfactura, total, observaciones, proveedor_servicio FROM facturascli WHERE   id_arqueo = '".$this->id_arqueo."' AND  anulada=0  AND ( proveedor_servicio='".$provee."'  OR nombrecliente='".$provee."'  )  AND  pagada=0  AND codpago!='CONT'  group by idfactura ORDER BY proveedor_servicio ASC";
                    //$sql = "SELECT t1.nombre, t2.codcliente, t2.idfactura, t2.total, t2.observaciones, t2.proveedor_servicio FROM facturascli t2 INNER JOIN clientes t1 ON t2.proveedor_servicio='".$provee."' AND t2.codcliente=t1.codcliente AND  pagada=0 AND codpago='CONT'";
            $arreglin = $this->db->select($sql);


            return $arreglin;
    }
	
	
	

}
