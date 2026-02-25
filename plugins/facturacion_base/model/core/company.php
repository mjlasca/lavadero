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
 * Empresa
 *
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class company extends \fs_model
{

    /**
     * Clave primaria. serial
     * @var int
     */
    public $id;
    public $nombre;
    public $cif;
    public $prefijo;
    public $ultmod;
    public $useredit;
    public $codestado;
    public $idmetodopago;

    public function __construct($data = FALSE)
    {
        parent::__construct('company');
        if ($data) {
            $this->id = $data['id'];
            $this->nombre = $data['nombre'];
            $this->cif = $data['cif'];
            $this->prefijo = $data['prefijo'];
            $this->ultmod = $data['ultmod'];
            $this->useredit = $data['useredit'];
            $this->codestado = $data['codestado'];
            $this->idmetodopago = $data['idmetodopago'];
        } else {
            $this->id = NULL;
            $this->nombre = NULL;
            $this->cif = NULL;
            $this->prefijo = NULL;
            $this->ultmod = NULL;
            $this->useredit = NULL;
            $this->codestado = 1;
            $this->idmetodopago = NULL;
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
        return 'index.php?page=fact_companies';
    }

    /**
     * Devuelve la forma de pago 
     * @param int $id
     * @return \FacturaScripts\model\company|boolean
     */
    public function get($id)
    {
        $pago = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE id = " . $this->var2str($id) . ";");
        if ($pago) {
            return new \company($pago[0]);
        }
        return FALSE;
    }

    /**
     * Devuelve la forma de pago 
     * @param int $id
     * @return \FacturaScripts\model\company|boolean
     */
    public function getCifPref()
    {
        $pago = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE cif = " . $this->var2str($this->cif) . " || prefijo = " . $this->var2str($this->prefijo) . ";");
        if ($pago) {
            return new \company($pago[0]);
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
            $sql = "UPDATE " . $this->table_name . " SET nombre = " . $this->var2str($this->nombre) .
                ", cif = " . $this->var2str($this->cif) .
                ", prefijo = " . $this->var2str($this->prefijo) .
                ", ultmod = " . $this->var2str($this->ultmod) .
                ", useredit = " . $this->var2str($this->useredit) .
                ", codestado = " . $this->var2str($this->codestado) .
                ", idmetodopago = " . $this->var2str($this->idmetodopago) .
                "  WHERE id = " . $this->var2str($this->id) . ";";
        } else {
            $sql = "INSERT INTO " . $this->table_name . " (nombre,cif,prefijo,ultmod,useredit,codestado,idmetodopago) VALUES 
                  (" . $this->var2str($this->nombre) .
                "," . $this->var2str($this->cif) .
                "," . $this->var2str($this->prefijo) .
                "," . $this->var2str($this->ultmod) .
                "," . $this->var2str($this->useredit) .
                "," . $this->var2str($this->codestado) .
                "," . $this->var2str($this->idmetodopago) . ");";
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
        $listaformas = $this->cache->get_array('m_company');
        if (empty($listaformas)) {
            /// si no está en caché, buscamos en la base de datos
            $formas = $this->db->select("SELECT * FROM " . $this->table_name . ";");
            if ($formas) {
                foreach ($formas as $f) {
                    $listaformas[] = new \company($f);
                }
            }

            /// guardamos la lista en caché
            $this->cache->set('m_company', $listaformas);
        }

        return $listaformas;
    }


}
