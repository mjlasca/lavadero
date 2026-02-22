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
class factura_metodo_pago extends \fs_model
{

    /**
     * Clave primaria. serial
     * @var int
     */
    public $id;
    public $idfactura;
    public $idmetodopago;
    public $total;
    public $descripcion;

    public function __construct($data = FALSE)
    {
        parent::__construct('factura_metodo_pago');
        if ($data) {
            $this->id = $data['id'];
            $this->idfactura = $data['idfactura'];
            $this->idmetodopago = $data['idmetodopago'];
            $this->total = $data['total'];
            $this->descripcion = $data['descripcion'];
        } else {
            $this->id = NULL;
            $this->idfactura = NULL;
            $this->idmetodopago = NULL;
            $this->total = NULL;
            $this->descripcion = NULL;
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
        return 'index.php?page=factura_metodo_pago';
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
            return new \factura_metodo_pago($pago[0]);
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
            $sql = "UPDATE " . $this->table_name . " SET idfactura = " . $this->var2str($this->idfactura) .
                ", idmetodopago = " . $this->var2str($this->idmetodopago) .
                ", total = " . $this->var2str($this->total) .
                "  WHERE id = " . $this->var2str($this->id) . ";";
        } else {
            $sql = "INSERT INTO " . $this->table_name . " (idfactura,idmetodopago,total) VALUES 
                  (" . $this->var2str($this->idfactura) .
                "," . $this->var2str($this->idmetodopago) .
                "," . $this->var2str($this->total) . ");";
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
    public function all(int $idfactura)
    {
        /// Leemos la lista de la caché
        $listaformas = $this->cache->get_array('m_factura_metodo_pago' . $idfactura);
        if (empty($listaformas)) {
            /// si no está en caché, buscamos en la base de datos
            $formas = $this->db->select("SELECT fmp.*, mp.descripcion FROM " . $this->table_name . " fmp LEFT JOIN metodospago mp ON fmp.idmetodopago = mp.id WHERE fmp.idfactura = " . $this->var2str($idfactura) . ";");
            if ($formas) {
                foreach ($formas as $f) {
                    $listaformas[] = new \factura_metodo_pago($f);
                }
            }

            /// guardamos la lista en caché
            $this->cache->set('m_factura_metodo_pago' . $idfactura, $listaformas);
        }

        return $listaformas;
    }


}
