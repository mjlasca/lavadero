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

require_model('colombia_certificado.php');

class colombia_certificados extends fs_controller
{
   public $certificado;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Certificados DIAN', 'contabilidad', FALSE, TRUE);
   }
   
   protected function private_core()
   {
      $this->custom_search = TRUE;
      $this->certificado = new colombia_certificado();
      
      if( isset($_POST['numero']) )
      {
         /// si tenemos el id, buscamos el certificado y así lo modificamos
         if( isset($_POST['idcertificado']) )
         {
            $cert0 = $this->certificado->get($_POST['idcertificado']);
         }
         else /// si no está el id, seguimos como si fuese nuevo
         {    
            $cert0 = new colombia_certificado();
            $cert0->idcertificado = $this->certificado->nuevo_numero();
         }
         
         $cert0->numero = $_POST['numero'];
         $cert0->fecha_inicio = $_POST['fecha_inicio'];
         if($_POST['fecha_fin'] != '')
         {
            $cert0->fecha_fin = $_POST['fecha_fin'];
         }
         
         $cert0->contador_inicial = intval($_POST['contador_inicial']);
         $cert0->contador_final = intval($_POST['contador_final']);
         
         if( $cert0->save() )
         {
            $this->new_message('Datos guardados correctamente.');
         }
         else
         {
            $this->new_error_msg('Imposible guardar los datos.');
         }
      }
      else if( isset($_GET['delete']) )
      {
         $cert0 = $this->certificado->get($_GET['delete']);
         if($cert0)
         {
            if( $cert0->delete() )
            {
               $this->new_message('Identificador '. $_GET['delete'] .' eliminado correctamente.');
            }
            else
            {
               $this->new_error_msg('Imposible eliminar los datos.');
            }
         }
      }
   }
   
   public function listar_certificados()
   {
      if($this->query != '')
      {
         return $this->certificado->buscar($this->query);
      }
      else
      {
         return $this->certificado->listar();
      }
   }
}