<?php
/*
 * This file is part of facturacion_base
 * Copyright (C) 2014-2017  Carlos Garcia Gomez  neorazorx@gmail.com
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
namespace FacturaScripts\model;

/**
 * El auditoriaprecioarticulo contable. Se relaciona con un ejercicio y se compone de partidas.
 * 
 * @author Mario Lasluisa Castaño <mjlasluisa@gmail.com>
 */
class auditoriaprecioarticulo extends \fs_model
{

    /**
     * Clave primaria.
     * @var integer 
     */
    public $reg;
    public $precioanterior;
    public $precio;
    public $costoanterior;
    public $costo;
    public $preciocomboanterior;
    public $preciocombo;
    public $ivaanterior;
    public $iva;
    public $idarticulo;
    public $ultmod;
    public $useredit;
    public $nombreempleado;


    public function __construct($data = FALSE)
    {
        parent::__construct('auditoriaprecioarticulo');
        if ($data) {
            $this->reg = $data['reg'];
            $this->precio = $data['precio'];
            $this->precioanterior = $data['precioanterior'];
            $this->costoanterior = $data['costoanterior'];
            $this->costo = $data['costo'];
            $this->preciocomboanterior = $data['preciocomboanterior'];
            $this->preciocombo = $data['preciocombo'];
            $this->ivaanterior = $data['ivaanterior'];
            $this->iva = $data['iva'];
            $this->idarticulo = $data['idarticulo'];
            $this->ultmod = $data['ultmod'];
            $this->useredit = $data['useredit'];
            $this->nombreempleado = $data['nombreempleado'];
        } else {
            $this->reg = NULL;
            $this->precioanterior = NULL;
            $this->precio = NULL;
            $this->costoanterior = NULL;
            $this->costo = NULL;
            $this->preciocomboanterior = NULL;
            $this->preciocombo = NULL;
            $this->ivaanterior = NULL;
            $this->iva = NULL;
            $this->idarticulo = NULL;
            $this->ultmod = date("Y-m-d H:i:s");
            $this->useredit = NULL;
            $this->nombreempleado = NULL;
        }
    }

    protected function install()
    {
        return '';
    }

 
    public function delete()
    {
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE reg = " . $this->var2str($this->reg) . ";");
    }
 

    /**
     * Devuelve el auditoriaprecioarticulo con el $id solicitado.
     * @param string $id
     * @return \auditoriaprecioarticulo|boolean
     */
    public function get($id)
    {
        if (isset($id)) {
            $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idauditoriaprecioarticulo = " . $this->var2str($id) . ";");
            if ($data) {
                return new \auditoriaprecioarticulo($data[0]);
            }
        }

        return FALSE;
    }

    public function exists()
    {
        if (is_null($this->reg)) {
            return FALSE;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE reg = " . $this->var2str($this->reg) . ";");
    }

   

    public function test()
    {
        return TRUE;
    }

    
    public function all()
    {
        $balist = array();

        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE idarticulo = '".$this->idarticulo."' ORDER BY reg DESC;");
        if ($data) {
            foreach ($data as $b) {
                $balist[] = new \auditoriaprecioarticulo($b);
            }
        }

        return $balist;
    }
    

    public function save()
    {
        if ($this->test()) {

            $sql = "INSERT INTO " . $this->table_name . " (precioanterior,precio,costoanterior,costo,preciocomboanterior,preciocombo,ivaanterior,iva,idarticulo,ultmod,useredit,nombreempleado)
               VALUES (" . $this->var2str($this->precioanterior)
               . "," . $this->var2str($this->precio)
               . "," . $this->var2str($this->costoanterior)
                . "," . $this->var2str($this->costo)
                . "," . $this->var2str($this->preciocomboanterior)
                . "," . $this->var2str($this->preciocombo)
                . "," . $this->var2str($this->ivaanterior)
                . "," . $this->var2str($this->iva)
                . "," . $this->var2str($this->idarticulo)
                . "," . $this->var2str($this->ultmod)
                . "," . $this->var2str($this->useredit)
                . "," . $this->var2str($this->nombreempleado) . ");";

            if ($this->db->exec($sql)) {
                $this->idauditoriaprecioarticulo = $this->db->lastval();
                return TRUE;
            }
        }

        return FALSE;
    }

}
