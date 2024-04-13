<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015  Carlos Garcia Gomez  neorazorx@gmail.com
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

class colombia_certificado extends fs_model
{
   public $idcertificado;
   public $numero;
   public $fecha_inicio;
   public $fecha_fin;
   public $contador_inicial;
   public $contador_final;
   
   public function __construct($g = FALSE)
   {
      parent::__construct('colombia_certificados', 'plugins/colombia/');
      
      if($g)
      {
         $this->idcertificado = $g['idcertificado'];
         $this->numero = $g['numero'];
         
         $this->fecha_inicio = date('d-m-Y', strtotime($g['fecha_inicio']));
         $this->fecha_fin = NULL;
         if( !is_null($g['fecha_fin']) )
            $this->fecha_fin = date('d-m-Y', strtotime($g['fecha_fin']));
         
         $this->contador_inicial = intval($g['contador_inicial']);
         $this->contador_final = intval($g['contador_final']);
      }
      else
      {
         $this->idcertificado = NULL;
         $this->numero = '';
         $this->fecha_inicio = date('d-m-Y');
         $this->fecha_fin = NULL;
         $this->contador_inicial = 0;
         $this->contador_final = 0;
      }
   }
   
   protected function install() {
      ;
   }
   
   public function get($id)
   {
      $data = $this->db->select("select * from colombia_certificados where idcertificado = ".$this->var2str($id).";");
      if($data)
      {
         return new colombia_certificado($data[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->idcertificado) )
      {
         return FALSE;
      }
      else
      {
         return $this->db->select("select * from colombia_certificados where idcertificado = ".$this->var2str($this->idcertificado).";");
      }
   }
   
   public function test() {
      ;
   }
   
   public function nuevo_numero()
   {
      $data = $this->db->select("select max(idcertificado) as num from colombia_certificados;");
      if($data)
         return intval($data[0]['num']) + 1;
      else
         return 1;
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE colombia_certificados set numero = ".$this->var2str($this->numero).
                 ", fecha_inicio = ".$this->var2str($this->fecha_inicio).
                 ", fecha_fin = ".$this->var2str($this->fecha_fin).
                 ", contador_inicial = ".$this->var2str($this->contador_inicial).
                 ", contador_final = ".$this->var2str($this->contador_final). 
                 " where idcertificado = ".$this->var2str($this->idcertificado).";";
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT into colombia_certificados (idcertificado,numero,fecha_inicio,fecha_fin,contador_inicial,contador_final) VALUES ("
                 .$this->var2str($this->idcertificado).","
                 .$this->var2str($this->numero).","
                 .$this->var2str($this->fecha_inicio).","
                 .$this->var2str($this->fecha_fin).","
                 .$this->var2str($this->contador_inicial).","
                 .$this->var2str($this->contador_final).");";
         
         if( $this->db->exec($sql) )
         {
            $this->idcertificado = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("delete from colombia_certificados where idcertificado = ".$this->var2str($this->idcertificado).";");
   }
   
   public function listar()
   {
      $listag = array();
      
      $data = $this->db->select("select * from colombia_certificados;");
      if($data)
      {
         foreach($data as $d)
         {
            $listag[] = new colombia_certificado($d);
         }
      }
      
      return $listag;
   }
   
   public function buscar($texto = '')
   {
      $listag = array();
      
      $data = $this->db->select("select * from colombia_certificados where numero like '%".$texto."%';");
      if($data)
      {
         foreach($data as $d)
         {
            $listag[] = new colombia_certificado($d);
         }
      }
      
      return $listag;
   }
}
