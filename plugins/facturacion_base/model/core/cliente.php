<?php
/*
 * This file is part of facturacion_base
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
 * El cliente. Puede tener una o varias direcciones y subcuentas asociadas.
 * 
 * @author Carlos García Gómez <neorazorx@gmail.com>
 */
class cliente extends \fs_model
{

    /**
     * Clave primaria. Varchar (6).
     * @var string 
     */
    public $codcliente;

    /**
     * Nombre por el que conocemos al cliente, no necesariamente el oficial.
     * @var string 
     */
    public $nombre;

    /**
     * Se crea el campo nombre 2 para obtener el nombre real del cliente, ya que el nombre se usa para la placa
     * @var string 
     */
    public $nombre2;

    /**
     * Razón social del cliente, es decir, el nombre oficial. El que aparece en las facturas.
     * @var string
     */
    public $razonsocial;

    /**
     * Tipo de identificador fiscal del cliente.
     * Ejemplos: CIF, NIF, CUIT...
     * @var string 
     */
    public $tipoidfiscal;

    /**
     * Identificador fiscal del cliente.
     * @var string 
     */
    public $cifnif;
    public $telefono1;
    public $telefono2;
    public $fax;
    public $email;
    public $web;

    /**
     * Serie predeterminada para este cliente.
     * @var string 
     */
    public $codserie;

    /**
     * Divisa predeterminada para este cliente.
     * @var string 
     */
    public $coddivisa;

    /**
     * Forma de pago predeterminada para este cliente.
     * @var string 
     */
    public $codpago;

    /**
     * Empleado/agente asignado al cliente.
     * @var string 
     */
    public $codagente;

    /**
     * Grupo al que pertenece el cliente.
     * @var string 
     */
    public $codgrupo;

    /**
     * TRUE -> el cliente ya no nos compra o no queremos nada con él.
     * @var boolean 
     */
    public $debaja;

    /**
     * Fecha en la que se dió de baja al cliente.
     * @var string 
     */
    public $fechabaja;

    /**
     * Fecha en la que se dió de alta al cliente.
     * @var string 
     */
    public $fechaalta;
    public $observaciones;

    /**
     * Régimen de fiscalidad del cliente. Por ahora solo están implementados
     * general y exento.
     * @var string 
     */
    public $regimeniva;

    /**
     * TRUE -> al cliente se le aplica recargo de equivalencia.
     * @var boolean 
     */
    public $recargo;

    /**
     * TRUE  -> el cliente es una persona física.
     * FALSE -> el cliente es una persona jurídica (empresa).
     * @var boolean 
     */
    public $personafisica;

    /**
     * Dias de pago preferidos a la hora de calcular el vencimiento de las facturas.
     * Días separados por comas: 1,15,31
     * @var string 
     */
    public $diaspago;

    /**
     * Proveedor asociado equivalente
     * @var string
     */
    public $codproveedor;
    
    /**
     * Código de la tarifa para este cliente
     * @var string
     */
    public $codtarifa;
    
    private static $regimenes_iva;

    public function __construct($data = FALSE)
    {
        parent::__construct('clientes');
        if ($data) {
            $this->codcliente = $data['codcliente'];
            $this->nombre = $data['nombre'];
            $this->nombre2 = $data['nombre2'];
            
            if (is_null($data['razonsocial'])) {
                $this->razonsocial = $data['nombrecomercial'];
            } else {
                $this->razonsocial = $data['razonsocial'];
            }

            $this->tipoidfiscal = $data['tipoidfiscal'];
            $this->cifnif = $data['cifnif'];
            $this->telefono1 = $data['telefono1'];
            $this->telefono2 = $data['telefono2'];
            $this->fax = $data['fax'];
            $this->email = $data['email'];
            $this->web = $data['web'];
            $this->codserie = $data['codserie'];
            $this->coddivisa = $data['coddivisa'];
            $this->codpago = $data['codpago'];
            $this->codagente = $data['codagente'];
            $this->codgrupo = $data['codgrupo'];
            $this->debaja = $this->str2bool($data['debaja']);

            $this->fechabaja = NULL;
            if ($data['fechabaja']) {
                $this->fechabaja = date('d-m-Y', strtotime($data['fechabaja']));
            }

            $this->fechaalta = date('d-m-Y', strtotime($data['fechaalta']));
            $this->observaciones = $this->no_html($data['observaciones']);
            $this->regimeniva = $data['regimeniva'];
            $this->recargo = $this->str2bool($data['recargo']);
            $this->personafisica = $this->str2bool($data['personafisica']);
            $this->diaspago = $data['diaspago'];
            $this->codproveedor = $data['codproveedor'];
            $this->codtarifa = $data['codtarifa'];
        } else {
            $this->codcliente = NULL;
            $this->nombre = '';
            $this->nombre2 = '';
            $this->razonsocial = '';
            $this->tipoidfiscal = FS_CIFNIF;
            $this->cifnif = '';
            $this->telefono1 = '';
            $this->telefono2 = '';
            $this->fax = '';
            $this->email = '';
            $this->web = '';

            /**
             * Ponemos por defecto la serie a NULL para que en las nuevas ventas
             * a este cliente se utilice la serie por defecto de la empresa.
             * NULL => usamos la serie de la empresa.
             */
            $this->codserie = NULL;

            $this->coddivisa = $this->default_items->coddivisa();
            $this->codpago = $this->default_items->codpago();
            $this->codagente = NULL;
            $this->codgrupo = NULL;
            $this->debaja = FALSE;
            $this->fechabaja = NULL;
            $this->fechaalta = date('d-m-Y');
            $this->observaciones = NULL;
            $this->regimeniva = 'General';
            $this->recargo = FALSE;
            $this->personafisica = TRUE;
            $this->diaspago = NULL;
            $this->codproveedor = NULL;
            $this->codtarifa = NULL;
        }
    }

    protected function install()
    {
        $this->clean_cache();

        /**
         * La tabla tiene varias claves ajenas, por eso debemos forzar la comprobación
         * de estas tablas.
         */
        new \grupo_clientes();

        return '';
    }

    public function observaciones_resume()
    {
        if ($this->observaciones == '') {
            return '-';
        } else if (strlen($this->observaciones) < 60) {
            return $this->observaciones;
        }

        return substr($this->observaciones, 0, 50) . '...';
    }

    public function url()
    {
        if (is_null($this->codcliente)) {
            return "index.php?page=ventas_clientes";
        }

        return "index.php?page=ventas_cliente&cod=" . $this->codcliente;
    }

    /**
     * @deprecated since version 50
     * @return boolean
     */
    public function is_default()
    {
        return FALSE;
    }

    /**
     * Devuelve un array con los regimenes de iva disponibles.
     * @return array
     */
    public function regimenes_iva()
    {
        if (!isset(self::$regimenes_iva)) {
            /// Si hay usa lista personalizada en fs_vars, la usamos
            $fsvar = new \fs_var();
            $data = $fsvar->simple_get('cliente::regimenes_iva');
            if ($data) {
                self::$regimenes_iva = array();
                foreach (explode(',', $data) as $d) {
                    self::$regimenes_iva[] = trim($d);
                }
            } else {
                /// sino usamos estos
                self::$regimenes_iva = array('General', 'Exento');
            }

            /// además de añadir los que haya en la base de datos
            $data = $this->db->select("SELECT DISTINCT regimeniva FROM clientes ORDER BY regimeniva ASC;");
            if ($data) {
                foreach ($data as $d) {
                    if (!in_array($d['regimeniva'], self::$regimenes_iva)) {
                        self::$regimenes_iva[] = $d['regimeniva'];
                    }
                }
            }
        }

        return self::$regimenes_iva;
    }

    /**
     * Devuelve el cliente que tenga ese codcliente.
     * @param string $cod
     * @return \cliente|boolean
     */
    public function get($cod)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codcliente = " . $this->var2str($cod) . ";");
        if ($data) {
            return new \cliente($data[0]);
        }

        return FALSE;
    }

    /**
     * Devuelve el cliente que tenga ese codcliente.
     * @param string $cod
     * @return \cliente|boolean
     */
    public function get_placa($placa)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE lower(nombre) = lower(" . $this->var2str($placa) . ");");
        if ($data) {
            return new \cliente($data[0]);
        }

        return FALSE;
    }

    /**
     * Devuelve el primer cliente que tenga $cifnif como cifnif.
     * Si el cifnif está en blanco y se proporciona una razón social,
     * se devuelve el primer cliente que tenga esa razón social.
     * @param string $cifnif
     * @param string $razon
     * @return boolean|\cliente
     */
    public function get_by_cifnif($cifnif, $razon = FALSE)
    {
        if ($cifnif == '' && $razon) {
            $razon = $this->no_html(mb_strtolower($razon, 'UTF8'));
            $sql = "SELECT * FROM " . $this->table_name . " WHERE cifnif = '' AND lower(razonsocial) = " . $this->var2str($razon) . ";";
        } else {
            $cifnif = mb_strtolower($cifnif, 'UTF8');
            $sql = "SELECT * FROM " . $this->table_name . " WHERE lower(cifnif) = " . $this->var2str($cifnif) . ";";
        }

        $data = $this->db->select($sql);
        if ($data) {
            return new \cliente($data[0]);
        }

        return FALSE;
    }

    /**
     * Devuelve el primer cliente que tenga $email como email.
     * @param string $email
     * @return boolean|\cliente
     */
    public function get_by_email($email)
    {
        $email = mb_strtolower($email, 'UTF8');
        $sql = "SELECT * FROM " . $this->table_name . " WHERE lower(email) = " . $this->var2str($email) . ";";

        $data = $this->db->select($sql);
        if ($data) {
            return new \cliente($data[0]);
        }

        return FALSE;
    }

    /**
     * Devuelve un array con las direcciones asociadas al cliente.
     * @return \direccion_cliente
     */
    public function get_direcciones()
    {
        $dir = new \direccion_cliente();
        return $dir->all_from_cliente($this->codcliente);
    }

    /**
     * Devuelve un array con todas las subcuentas asociadas al cliente.
     * Una para cada ejercicio.
     * @return \subcuenta
     */
    public function get_subcuentas()
    {
        $subclist = array();
        $subc = new \subcuenta_cliente();
        foreach ($subc->all_from_cliente($this->codcliente) as $s) {
            $s2 = $s->get_subcuenta();
            if ($s2) {
                $subclist[] = $s2;
            } else
                $s->delete();
        }

        return $subclist;
    }

    /**
     * Devuelve la subcuenta asociada al cliente para el ejercicio $eje.
     * Si no existe intenta crearla. Si falla devuelve FALSE.
     * @param string $codejercicio
     * @return subcuenta
     */
    public function get_subcuenta($codejercicio)
    {
        $subcuenta = FALSE;

        foreach ($this->get_subcuentas() as $s) {
            if ($s->codejercicio == $codejercicio) {
                $subcuenta = $s;
                break;
            }
        }

        if (!$subcuenta) {
            /// intentamos crear la subcuenta y asociarla

            $cuenta = new \cuenta();
            $ccli = $cuenta->get_cuentaesp('CLIENT', $codejercicio);
            if ($ccli) {
                $continuar = FALSE;

                $subc0 = $ccli->new_subcuenta($this->codcliente);
                if ($subc0) {
                    $subc0->descripcion = $this->razonsocial;
                    if ($subc0->save()) {
                        $continuar = TRUE;
                    }
                }

                if ($continuar) {
                    $sccli = new \subcuenta_cliente();
                    $sccli->codcliente = $this->codcliente;
                    $sccli->codejercicio = $codejercicio;
                    $sccli->codsubcuenta = $subc0->codsubcuenta;
                    $sccli->idsubcuenta = $subc0->idsubcuenta;
                    if ($sccli->save()) {
                        $subcuenta = $subc0;
                    } else
                        $this->new_error_msg('Imposible asociar la subcuenta para el cliente ' . $this->codcliente);
                } else {
                    $this->new_error_msg('Imposible crear la subcuenta para el cliente ' . $this->codcliente);
                }
            } else {
                /// obtenemos una url para el mensaje, pero a prueba de errores.
                $eje_url = '';
                $eje0 = new \ejercicio();
                $ejercicio = $eje0->get($codejercicio);
                if ($ejercicio) {
                    $eje_url = $ejercicio->url();
                }

                $this->new_error_msg('No se encuentra ninguna cuenta especial para clientes en el ejercicio '
                    . $codejercicio . ' ¿<a href="' . $eje_url . '">Has importado los datos del ejercicio</a>?');
            }
        }

        return $subcuenta;
    }

    public function exists()
    {
        if (is_null($this->codcliente)) {
            return FALSE;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE codcliente = " . $this->var2str($this->codcliente) . ";");
    }

    /**
     * Devuelve un código que se usará como clave primaria/identificador único para este cliente.
     * @return string
     */
    public function get_new_codigo()
    {
        $data = $this->db->select("SELECT MAX(" . $this->db->sql_to_int('codcliente') . ") as cod FROM " . $this->table_name . ";");
        if ($data) {
            return sprintf('%06s', (1 + intval($data[0]['cod'])));
        }

        return '000001';
    }

    public function test()
    {
        $status = FALSE;

        if (is_null($this->codcliente)) {
            $this->codcliente = $this->get_new_codigo();
        } else {
            $this->codcliente = trim($this->codcliente);
        }

        $this->nombre = $this->no_html($this->nombre);
        $this->razonsocial = $this->no_html($this->razonsocial);
        $this->cifnif = $this->no_html($this->cifnif);
        $this->observaciones = $this->no_html($this->observaciones);

        if ($this->debaja) {
            if (is_null($this->fechabaja)) {
                $this->fechabaja = date('d-m-Y');
            }
        } else {
            $this->fechabaja = NULL;
        }

        /// validamos los dias de pago
        $array_dias = array();
        foreach (str_getcsv($this->diaspago) as $d) {
            if (intval($d) >= 1 && intval($d) <= 31) {
                $array_dias[] = intval($d);
            }
        }
        $this->diaspago = NULL;
        if (!empty($array_dias)) {
            $this->diaspago = join(',', $array_dias);
        }

        if (!preg_match("/^[A-Z0-9]{1,6}$/i", $this->codcliente)) {
            $this->new_error_msg("Código de cliente no válido: " . $this->codcliente);
        } else if (strlen($this->nombre) < 1 || strlen($this->nombre) > 100) {
            $this->new_error_msg("Nombre de cliente no válido: " . $this->nombre);
        } else if (strlen($this->razonsocial) > 1 && strlen($this->razonsocial) > 100) {
            $this->new_error_msg("Razón social del cliente no válida: " . $this->razonsocial);
        } else {
            $status = TRUE;
        }

        return $status;
    }

    public function save()
    {

        
        if ($this->test()) {
            $this->clean_cache();

            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name . " SET nombre = " . $this->var2str($this->nombre)
                    . ", nombre2 = " . $this->var2str($this->nombre2)
                    . ", razonsocial = " . $this->var2str($this->razonsocial)
                    . ", tipoidfiscal = " . $this->var2str($this->tipoidfiscal)
                    . ", cifnif = " . $this->var2str($this->cifnif)
                    . ", telefono1 = " . $this->var2str($this->telefono1)
                    . ", telefono2 = " . $this->var2str($this->telefono2)
                    . ", fax = " . $this->var2str($this->fax)
                    . ", email = " . $this->var2str($this->email)
                    . ", web = " . $this->var2str($this->web)
                    . ", codserie = " . $this->var2str($this->codserie)
                    . ", coddivisa = " . $this->var2str($this->coddivisa)
                    . ", codpago = " . $this->var2str($this->codpago)
                    . ", codagente = " . $this->var2str($this->codagente)
                    . ", codgrupo = " . $this->var2str($this->codgrupo)
                    . ", debaja = " . $this->var2str($this->debaja)
                    . ", fechabaja = " . $this->var2str($this->fechabaja)
                    . ", fechaalta = " . $this->var2str($this->fechaalta)
                    . ", observaciones = " . $this->var2str($this->observaciones)
                    . ", regimeniva = " . $this->var2str($this->regimeniva)
                    . ", recargo = " . $this->var2str($this->recargo)
                    . ", personafisica = " . $this->var2str($this->personafisica)
                    . ", diaspago = " . $this->var2str($this->diaspago)
                    . ", codproveedor = " . $this->var2str($this->codproveedor)
                    . ", codtarifa = " . $this->var2str($this->codtarifa)
                    . "  WHERE codcliente = " . $this->var2str($this->codcliente) . ";";
            } else {
                $sql = "INSERT INTO " . $this->table_name . " (codcliente,nombre,razonsocial,tipoidfiscal,
               cifnif,telefono1,telefono2,fax,email,web,codserie,coddivisa,codpago,codagente,codgrupo,
               debaja,fechabaja,fechaalta,observaciones,regimeniva,recargo,personafisica,diaspago,
               codproveedor,codtarifa,nombre2) VALUES
                      (" . $this->var2str($this->codcliente)
                    . "," . $this->var2str($this->nombre)
                    . "," . $this->var2str($this->razonsocial)
                    . "," . $this->var2str($this->tipoidfiscal)
                    . "," . $this->var2str($this->cifnif)
                    . "," . $this->var2str($this->telefono1)
                    . "," . $this->var2str($this->telefono2)
                    . "," . $this->var2str($this->fax)
                    . "," . $this->var2str($this->email)
                    . "," . $this->var2str($this->web)
                    . "," . $this->var2str($this->codserie)
                    . "," . $this->var2str($this->coddivisa)
                    . "," . $this->var2str($this->codpago)
                    . "," . $this->var2str($this->codagente)
                    . "," . $this->var2str($this->codgrupo)
                    . "," . $this->var2str($this->debaja)
                    . "," . $this->var2str($this->fechabaja)
                    . "," . $this->var2str($this->fechaalta)
                    . "," . $this->var2str($this->observaciones)
                    . "," . $this->var2str($this->regimeniva)
                    . "," . $this->var2str($this->recargo)
                    . "," . $this->var2str($this->personafisica)
                    . "," . $this->var2str($this->diaspago)
                    . "," . $this->var2str($this->codproveedor)
                    . "," . $this->var2str($this->codtarifa)
                    . "," . $this->var2str($this->nombre2) . ");";
            }
            
            return $this->db->exec($sql);
        }

        return FALSE;
    }

    public function delete()
    {
        $this->clean_cache();
        return $this->db->exec("DELETE FROM " . $this->table_name . " WHERE codcliente = " . $this->var2str($this->codcliente) . ";");
    }

    private function clean_cache()
    {
        $this->cache->delete('m_cliente_all');
    }

    public function all($offset = 0)
    {
        $data = $this->db->select_limit("SELECT * FROM " . $this->table_name . " ORDER BY lower(nombre) ASC", FS_ITEM_LIMIT, $offset);
        return $this->all_from_data($data);
    }

    /**
     * Devuelve un array con la lista completa de clientes.
     * @return \cliente
     */
    public function all_full()
    {
        /// leemos la lista de la caché
        $clientlist = $this->cache->get_array('m_cliente_all');
        if (!$clientlist) {
            /// si no la encontramos en la caché, leemos de la base de datos
            $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY lower(nombre) ASC;");
            $clientlist = $this->all_from_data($data);

            /// guardamos la lista en la caché
            $this->cache->set('m_cliente_all', $clientlist);
        }

        return $clientlist;
    }

    public function search($query, $offset = 0)
    {
        $query = mb_strtolower($this->no_html($query), 'UTF8');

        $consulta = "SELECT * FROM " . $this->table_name . " WHERE debaja = FALSE AND ";
        if (is_numeric($query)) {
            $consulta .= "(nombre LIKE '%" . $query . "%' OR nombre2 LIKE '%" . $query . "%' OR razonsocial LIKE '%" . $query . "%'"
                . " OR codcliente LIKE '%" . $query . "%' OR cifnif LIKE '%" . $query . "%'"
                . " OR telefono1 LIKE '" . $query . "%' OR telefono2 LIKE '" . $query . "%'"
                . " OR observaciones LIKE '%" . $query . "%')";
        } else {
            $buscar = str_replace(' ', '%', $query);
            $consulta .= "(lower(nombre) LIKE '%" . $buscar . "%' OR lower(nombre2) LIKE '%" . $buscar . "%' OR lower(razonsocial) LIKE '%" . $buscar . "%'"
                . " OR lower(cifnif) LIKE '%" . $buscar . "%' OR lower(observaciones) LIKE '%" . $buscar . "%'"
                . " OR lower(email) LIKE '%" . $buscar . "%')";
        }
        $consulta .= " ORDER BY lower(nombre) ASC";

        $data = $this->db->select_limit($consulta, FS_ITEM_LIMIT, $offset);
        return $this->all_from_data($data);
    }

    /**
     * Busca por cifnif.
     * @param string $dni
     * @param integer $offset
     * @return \cliente
     */
    public function search_by_dni($dni, $offset = 0)
    {
        $query = mb_strtolower($this->no_html($dni), 'UTF8');
        $consulta = "SELECT * FROM " . $this->table_name . " WHERE debaja = FALSE "
            . "AND lower(cifnif) LIKE '" . $query . "%' ORDER BY lower(nombre) ASC";

        $data = $this->db->select_limit($consulta, FS_ITEM_LIMIT, $offset);
        return $this->all_from_data($data);
    }

    private function all_from_data(&$data)
    {
        $clilist = array();
        if ($data) {
            foreach ($data as $d) {
                $clilist[] = new \cliente($d);
            }
        }

        return $clilist;
    }

    /**
     * Aplicamos algunas correcciones a la tabla.
     */
    public function fix_db()
    {
        /// ponemos debaja a false en los casos que sea null
        $this->db->exec("UPDATE " . $this->table_name . " SET debaja = false WHERE debaja IS NULL;");

        /// desvinculamos de grupos que no existen
        $this->db->exec("UPDATE " . $this->table_name . " SET codgrupo = NULL WHERE codgrupo IS NOT NULL"
            . " AND codgrupo NOT IN (SELECT codgrupo FROM gruposclientes);");

        /// desvinculamos de proveedores que no existan
        $this->db->exec("UPDATE " . $this->table_name . " SET codproveedor = null WHERE codproveedor IS NOT NULL"
            . " AND codproveedor NOT IN (SELECT codproveedor FROM proveedores);");
    }
}
