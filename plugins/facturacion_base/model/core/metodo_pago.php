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
namespace FacturaScripts\model;

/**
 * Forma de pago de una factura, albarán, pedido o presupuesto.
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class metodo_pago extends \fs_model
{

    /**
     * Clave primaria. serial
     * @var int
     */
    public $id;
    public $descripcion;
    public $nombre;
    public $ultmod;
    public $user_mod;
    public $codestado;

    public $total;

    public function __construct($data = FALSE)
    {
        parent::__construct('metodospago');
        if ($data) {
            $this->id = $data['id'];
            $this->nombre = $data['nombre'];
            $this->descripcion = $data['descripcion'];
            $this->ultmod = $data['ultmod'];
            $this->codestado = $data['codestado'];
            $this->user_mod = $data['user_mod'];
        } else {
            $this->id = NULL;
            $this->nombre = NULL;
            $this->descripcion = NULL;
            $this->ultmod = NULL;
            $this->user_mod = NULL;
            $this->codestado = 0;
        }
    }

    public function install()
    {
        $this->clean_cache();
        
    }

    /**
     * Devuelve la URL donde ver/modificar los datos
     * @return string
     */
    public function url()
    {
        return 'index.php?page=contabilidad_metodos_pago';
    }

    /**
     * Devuelve la forma de pago 
     * @param int $id
     * @return \FacturaScripts\model\metodo_pago|boolean
     */
    public function get($id)
    {
        $pago = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE id = " . $this->var2str($id) . ";");
        if ($pago) {
            return new \metodo_pago($pago[0]);
        }
        return FALSE;
    }

    /**
     * Devuelve TRUE si la forma de pago existe
     * @return boolean
     */
    public function exists()
    {
        if (is_null($this->id)) {
            return FALSE;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->id) . ";");
    }

    /**
     * Guarda los datos en la base de datos
     * @return boolean
     */
    public function save()
    {
        $this->clean_cache();
        if ($this->exists()) {
            $sql = "UPDATE " . $this->table_name . " SET descripcion = " . $this->var2str($this->descripcion) .
                ", nombre = " . $this->var2str($this->nombre) .
                ", user_mod = " . $this->var2str($this->user_mod) .
                ", ultmod = " . $this->var2str($this->ultmod) .
                ", codestado = " . $this->var2str($this->codestado) .
                "  WHERE id = " . $this->var2str($this->id) . ";";
        } else {
            $sql = "INSERT INTO " . $this->table_name . " (descripcion,nombre,user_mod
            ,ultmod,codestado) VALUES 
                  (" . $this->var2str($this->descripcion) .
                "," . $this->var2str($this->nombre) .
                "," . $this->var2str($this->user_mod) .
                "," . $this->var2str($this->ultmod) .
                "," . $this->var2str($this->codestado) . ");";
        }

        return $this->db->exec($sql);
    }

    /**
     * Elimina la forma de pago
     * @return boolean
     */
    public function delete()
    {
        $this->clean_cache();
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->id) . ";");
    }

    /**
     * Limpia la caché
     */
    private function clean_cache()
    {
        $this->cache->delete('m_metodo_pago_all');
    }

    /**
     * Devuelve un array con todas las formas de pago
     * @return \metodo_pago
     */
    public function all()
    {
        /// Leemos la lista de la caché
        $listaformas = $this->cache->get_array('m_metodo_pago_all');
        if (empty($listaformas)) {
            /// si no está en caché, buscamos en la base de datos
            $formas = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY descripcion ASC;");
            if ($formas) {
                foreach ($formas as $f) {
                    $listaformas[] = new \metodo_pago($f);
                }
            }

            /// guardamos la lista en caché
            $this->cache->set('m_metodo_pago_all', $listaformas);
        }

        return $listaformas;
    }

    /**
     * OBtener los totales por método de pago de las facturas cliente en un arqueo específico
     */
    public function getTotalCash($idarqueo){
        $sql = "SELECT SUM(t1.total) as total,t2.nombre as nombre FROM facturascli t1 INNER JOIN metodospago t2 ON t1.idmetodopago = t2.id WHERE id_arqueo = $idarqueo GROUP BY idmetodopago";
        return $this->db->select($sql);
    }


}
