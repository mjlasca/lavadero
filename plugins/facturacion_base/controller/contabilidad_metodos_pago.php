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

use FacturaScripts\model\metodo_pago;

require_once 'plugins/facturacion_base/extras/fbase_controller.php';

class contabilidad_metodos_pago extends fbase_controller
{

    public $button_plazos;
    public $cuentas_banco;
    public $metodo_pago;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Métodos de Pago', 'contabilidad');
    }

    protected function private_core()
    {
        parent::private_core();

        $this->metodo_pago = new metodo_pago();

        if(isset($_POST['disabled'])){
            $this->editar_metodo_pago(0);
        }else if (isset($_POST['id']) || isset($_POST['nombre'])) {
            $this->editar_metodo_pago();
        }

    }

    private function editar_metodo_pago($codestado = 1)
    {
        /// crear/modificar forma de pago
        $metodopago = new metodo_pago();
        if($codestado == 0)
            $metodopago->id = $_POST['disabled'];
        $metodopago->codestado = $codestado;
       if(isset($_POST['id'])){
            $metodopago->id = $_POST['id'];
            $metodopago->nombre = $_POST['nombre'];
            $metodopago->descripcion = $_POST['descripcion'];
            $metodopago->user_mod = $this->user->nick;
            $metodopago->ultmod = date("Y-m-d h:i:s");
       }else if(isset($_POST['nombre'])){
            $metodopago->nombre = $_POST['nombre'];
            $metodopago->descripcion = $_POST['descripcion'];
            $metodopago->user_mod = $this->user->nick;
            $metodopago->ultmod = date("Y-m-d h:i:s");
       }

       if($metodopago->save())
            $this->new_message('Se ha guardado el método de pago correctamente');
       else
            $this->new_error_msg('No se ha podido guardar el método de pago');
    }

    private function eliminar_metodo_pago()
    {
        $fp0 = $this->metodo_pago->get($_GET['delete']);
        if ($fp0) {
            if (!$this->user->admin) {
                $this->new_error_msg('Sólo un administrador puede eliminar formas de pago.');
            } else if ($fp0->delete()) {
                $this->new_message('Forma de pago ' . $fp0->codpago . ' eliminada correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar la forma de pago ' . $fp0->codpago . '.');
            }
        } else {
            $this->new_error_msg('Forma de pago no encontrada.');
        }
    }

}
