<?php
/*
 * This file is part of facturacion_base
 * Copyright (C) 2014-2017    Carlos Garcia Gomez  neorazorx@gmail.com
 * Copyright (C) 2014         GISBEL JOSE          gpg841@gmail.com
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

use FacturaScripts\model\company;

require_once 'plugins/facturacion_base/extras/fbase_controller.php';

class fact_companies extends fbase_controller
{

    public $companies;
    public $metodo_pago;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Empresas facturación', 'ventas');
    }

    protected function private_core()
    {
        parent::private_core();
        $this->companies = new company();
        $this->metodo_pago = new metodo_pago();

        if (isset($_POST['cif'])) {
            $this->save();
        }

        if (isset($_GET['disabled'])) {
            $this->disable($_GET['disabled'], 0);
        }

        if (isset($_GET['enabled'])) {
            $this->disable($_GET['enabled'], 1);
        }
    }

    public function save()
    {
        $company = new company();
        if (isset($_POST['id'])) {
            $company->id = $_POST['id'];
        }
        $company->cif = $_POST['cif'];
        $company->prefijo = $_POST['prefijo'];
        if (empty($company->id) && $company->getCifPref() != FALSE) {
            $this->new_error_msg('El id y/o el prefijo ya existe');
            return FALSE;
        }
        $company->nombre = $_POST['nombre'];
        $company->useredit = $this->user->nick;
        $company->ultmod = date('Y-m-d H:i:s');
        if (isset($_POST['idmetodopago'])) {
            $company->idmetodopago = $_POST['idmetodopago'];
        }
        if (isset($_POST['noiva'])) {
            $company->noiva = 1;
        } else {
            $company->noiva = 0;
        }
        $company->save();
    }

    public function disable($id, $state)
    {
        $company = new company();
        $company = $company->get($id);
        $company->id = $id;
        $company->disabled = $state;
        $company->save();
    }
}
