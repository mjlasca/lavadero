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

require_once 'plugins/facturacion_base/extras/fbase_controller.php';
require_once 'extras/xlsxwriter.class.php';

class ventas_factura extends fbase_controller
{

    public $agencia;
    public $agente;
    public $agentes;
    public $almacen;
    public $cliente;
    public $divisa;
    public $ejercicio;
    public $factura;
    public $forma_pago;
    public $mostrar_boton_pagada;
    public $pais;
    public $rectificada;
    public $rectificativa;
    public $serie;
    public $bandera_pagar;
    public $valuesPerson;
    public $methods;
    public $downloadFact;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Factura de cliente', 'ventas', FALSE, FALSE);
    }

    protected function private_core()
    {
        parent::private_core();

        $this->ppage = $this->page->get('ventas_facturas');
        $this->agencia = new agencia_transporte();
        $this->agente = FALSE;
        $this->agentes = array();
        $this->almacen = new almacen();
        $this->cliente = FALSE;
        $this->divisa = new divisa();
        $this->ejercicio = new ejercicio();
        $this->factura = FALSE;
        $this->forma_pago = new forma_pago();
        $this->pais = new pais();
        $this->rectificada = FALSE;
        $this->rectificativa = FALSE;
        $this->serie = new serie();
        $this->bandera_pagar = FALSE;
        $this->valuesPerson = ['aspiradores' => [], 'jefes' => [], 'lavadores' => []];
        $method = new factura_metodo_pago();


        /**
         * Si hay alguna extensión de tipo config y texto no_button_pagada,
         * desactivamos el botón de pagada/sin pagar.
         */
        $this->mostrar_boton_pagada = TRUE;
        foreach ($this->extensions as $ext) {
            if ($ext->type == 'config' && $ext->text == 'no_button_pagada') {
                $this->mostrar_boton_pagada = FALSE;
                break;
            }
        }


        /**
         * ¿Modificamos la factura?
         */
        $factura = new factura_cliente();
        if (isset($_POST['idfactura'])) {
            $this->factura = $factura->get($_POST['idfactura']);
            $this->modificar();
        } else if (isset($_GET['id'])) {
            $this->factura = $factura->get($_GET['id']);
            $this->methods = $method->all($_GET['id']);
            $this->get_values_person($this->factura->idfactura);
            $this->downloadFact = $this->factura->idfactura;
        }

        if ($this->factura) {
            $this->page->title = $this->factura->codigo;

            /// cargamos el agente
            $agente = new agente();
            if (!is_null($this->factura->codagente)) {
                $this->agente = $agente->get($this->factura->codagente);
            }
            $this->agentes = $agente->all();

            /// cargamos el cliente
            $cliente = new cliente();
            $this->cliente = $cliente->get($this->factura->codcliente);

            if (isset($_GET['gen_asiento']) && isset($_GET['petid'])) {
                if ($this->duplicated_petition($_GET['petid'])) {
                    $this->new_error_msg('Petición duplicada. Evita hacer doble clic sobre los botones.');
                } else {
                    $this->generar_asiento($this->factura);
                }
            } else if (isset($_GET['updatedir'])) {
                $this->actualizar_direccion();
            } else if (isset($_REQUEST['pagada'])) {
                $this->pagar(($_REQUEST['pagada'] == 'TRUE'));
                $this->bandera_pagar = $_REQUEST['pagada'];
            } else if (isset($_POST['anular'])) {
                if ($_POST['rectificativa'] == 'TRUE') {
                    $this->generar_rectificativa();
                } else {
                    $this->anular_factura();
                }
            }

            if ($this->factura->idfacturarect) {
                $this->rectificada = $factura->get($this->factura->idfacturarect);
            } else {
                $this->get_factura_rectificativa();
            }

            /// comprobamos la factura
            $this->factura->full_test();
        } else {
            $this->new_error_msg("¡Factura de cliente no encontrada!", 'error', FALSE, FALSE);
        }

        if (isset($_GET['download_file'])) {
            $this->file_fact_xls($_GET['download_file']);
        }
    }

    protected function file_fact_xls($idfactura)
    {
        $factura = new factura_cliente();
        $factura = $factura->get($idfactura);
        $lineas = new linea_factura_cliente();
        $lineas = $lineas->all_from_factura($idfactura);
        $company = new company();
        $company = $company->get($factura->idempresa);
        $this->get_values_person($idfactura);
        $sumLiqu = 0;
        if (isset($this->valuesPerson['aspiradores'][0]['val_liquidacion']))
            $sumLiqu += $this->valuesPerson['aspiradores'][0]['val_liquidacion'];
        if (isset($this->valuesPerson['jefes'][0]['val_liquidacion']))
            $sumLiqu += $this->valuesPerson['jefes'][0]['val_liquidacion'];
        if (isset($this->valuesPerson['lavadores'][0]['val_liquidacion']))
            $sumLiqu += $this->valuesPerson['lavadores'][0]['val_liquidacion'];
        $fechaFormat = new DateTime($factura->fecha);
        $fechaFormatVenc = new DateTime($factura->vencimiento);
        $consecutive = new company_consecutive();
        $consecutive = $consecutive->get_by_idfactura($idfactura);
        if ($consecutive) {
            $consecutive = $consecutive->consecutive;
        } else {
            $consecutive = "";
        }
        $this->template = FALSE;
        header("Content-Disposition: attachment; filename=\"Fatura{$idfactura}-wordlOffice_" . time() . ".xls\"");
        header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
        header('Content-Transfer-Encoding: binary');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');



        $header = array(
            'Encab: Empresa' => 'string',
            'Encab: Tipo Documento' => 'string',
            'Encab: Prefijo' => 'string',
            'Encab: Documento Número' => 'string',
            'Encab: Fecha' => 'string',
            'Encab: Tercero Interno' => 'string',
            'Encab: Tercero Externo' => 'string',
            'Encab: Nota' => 'string',
            'Encab: FormaPago' => 'string',
            'Encab: Fecha Entrega' => 'string',
            'Encab: Prefijo Documento Externo' => 'string',
            'Encab: Número_Documento_Externo' => 'string',
            'Encab: Verificado' => 'string',
            'Encab: Anulado' => 'string',
            'Encab: Personalizado 1' => 'string',
            'Encab: Personalizado 2' => 'string',
            'Encab: Personalizado 3' => 'string',
            'Encab: Personalizado 4' => 'string',
            'Encab: Personalizado 5' => 'string',
            'Encab: Personalizado 6' => 'string',
            'Encab: Personalizado 7' => 'string',
            'Encab: Personalizado 8' => 'string',
            'Encab: Personalizado 9' => 'string',
            'Encab: Personalizado 10' => 'string',
            'Encab: Personalizado 11' => 'string',
            'Encab: Personalizado 12' => 'string',
            'Encab: Personalizado 13' => 'string',
            'Encab: Personalizado 14' => 'string',
            'Encab: Personalizado 15' => 'string',
            'Encab: Sucursal' => 'string',
            'Encab: Clasificación' => 'string',
            'Detalle: Producto' => 'string',
            'Detalle: Bodega' => 'string',
            'Detalle: UnidadDeMedida' => 'string',
            'Detalle: Cantidad' => 'string',
            'Detalle: IVA' => 'string',
            'Detalle: Valor Unitario' => 'string',
            'Detalle: Descuento' => 'string',
            'Detalle: Vencimiento' => 'string',
            'Detalle: Nota' => 'string',
            'Detalle: Centro costos' => 'string',
            'Detalle: Personalizado1' => 'string',
            'Detalle: Personalizado2' => 'string',
            'Detalle: Personalizado3' => 'string',
            'Detalle: Personalizado4' => 'string',
            'Detalle: Personalizado5' => 'string',
            'Detalle: Personalizado6' => 'string',
            'Detalle: Personalizado7' => 'string',
            'Detalle: Personalizado8' => 'string',
            'Detalle: Personalizado9' => 'string',
            'Detalle: Personalizado10' => 'string',
            'Detalle: Personalizado11' => 'string',
            'Detalle: Personalizado12' => 'string',
            'Detalle: Personalizado13' => 'string',
            'Detalle: Personalizado14' => 'string',
            'Detalle: Personalizado15' => 'string',
            'Detalle: Código Centro Costos' => 'string',
            /*
                        'FECHA' => 'string',
                        'MODULO' => 'string',
                        'CODIGO' => 'string',
                        'CLIENTE/PROVEEDOR/LAVADOR' => 'string',
                        'METODO PAGO' => 'string',
                        'CAJERO/USER' => 'string',
                        'ID ARQUEO' => 'string',
                        'PRODUCTO/GASTO' => 'string',
                        'DESCRIPCION' => 'string',
                        'OBSERVACIONES' => 'string',
                        'UND VEND' => '#,##0.00;[RED]-#,##0.00',
                        'TOTAL INGRESO' => '#,##0.00;[RED]-#,##0.00',
                        'IVA INGRESO' => '#,##0.00;[RED]-#,##0.00',
                        'UND COMP' => '#,##0.00;[RED]-#,##0.00',
                        'TOTAL EGRESO' => '#,##0.00;[RED]-#,##0.00',
                        'IVA EGRESO' => '#,##0.00;[RED]-#,##0.00',*/
        );
        $writter = new XLSXWriter();
        $writter->setAuthor('FacturaScripts');
        $writter->writeSheetHeader("Fatura{$idfactura}-wordlOffice", $header);

        foreach ($lineas as $doc) {
            $product = new articulo();
            $product = $product->get($doc->referencia);
            $iva = ($doc->iva / 100);
            $valUnit = ($doc->pvptotal / $doc->cantidad / (1 + $doc->iva / 100));
            if ($product && $product->codfamilia == 1 && $company->noiva) {
                $iva = 0;
            }
            $linea = array(
                'Encab: Empresa' => $company->nombre,
                'Encab: Tipo Documento' => 'FV',
                'Encab: Prefijo' => $company->prefijo,
                'Encab: Documento Número' => $consecutive,
                'Encab: Fecha' => $factura->fecha,
                'Encab: Tercero Interno' => $factura->cifnif,
                'Encab: Tercero Externo' => $factura->cifnif,
                'Encab: Nota' => round($sumLiqu, 2),
                'Encab: FormaPago' => $factura->codpago == 'CONT' ? 'Contado' : 'Crédito',
                'Encab: Fecha Entrega' => $fechaFormat->format('m/d/Y'),
                'Encab: Prefijo Documento Externo' => '',
                'Encab: Número_Documento_Externo' => '',
                'Encab: Verificado' => -1,
                'Encab: Anulado' => 0,
                'Encab: Personalizado 1' => '',
                'Encab: Personalizado 2' => '',
                'Encab: Personalizado 3' => '',
                'Encab: Personalizado 4' => '',
                'Encab: Personalizado 5' => '',
                'Encab: Personalizado 6' => '',
                'Encab: Personalizado 7' => '',
                'Encab: Personalizado 8' => '',
                'Encab: Personalizado 9' => '',
                'Encab: Personalizado 10' => '',
                'Encab: Personalizado 11' => '',
                'Encab: Personalizado 12' => '',
                'Encab: Personalizado 13' => '',
                'Encab: Personalizado 14' => '',
                'Encab: Personalizado 15' => '',
                'Encab: Sucursal' => '',
                'Encab: Clasificación' => '',
                'Detalle: Producto' => $doc->referencia,
                'Detalle: Bodega' => 'Principal',
                'Detalle: UnidadDeMedida' => 'Und.',
                'Detalle: Cantidad' => $doc->cantidad,
                'Detalle: IVA' => $iva,
                'Detalle: Valor Unitario' => $valUnit,
                'Detalle: Descuento' => 0,
                'Detalle: Vencimiento' => $fechaFormatVenc->format('m/d/Y'),
                'Detalle: Nota' => '',
                'Detalle: Centro costos' => '',
                'Detalle: Personalizado1' => '',
                'Detalle: Personalizado2' => '',
                'Detalle: Personalizado3' => '',
                'Detalle: Personalizado4' => '',
                'Detalle: Personalizado5' => '',
                'Detalle: Personalizado6' => '',
                'Detalle: Personalizado7' => '',
                'Detalle: Personalizado8' => '',
                'Detalle: Personalizado9' => '',
                'Detalle: Personalizado10' => '',
                'Detalle: Personalizado11' => '',
                'Detalle: Personalizado12' => '',
                'Detalle: Personalizado13' => '',
                'Detalle: Personalizado14' => '',
                'Detalle: Personalizado15' => '',
                'Detalle: Código Centro Costos' => '',
            );
            $writter->writeSheetRow("Fatura{$idfactura}-wordlOffice", $linea);
        }
        $writter->writeToStdOut();
    }

    public function decimal_number($number)
    {
        if (is_null($number)) {
            return "0.00";
        }

        return round($number, 2);
    }


    public function get_values_person($idfactura)
    {
        $sql = "SELECT SUM(t3.pvptotal) as total,SUM(t3.val_liquidacion) as val_liquidacion"
            . " FROM aspiradores_lav t1 "
            . " INNER JOIN  facturascli t2 "
            . " INNER JOIN lineasfacturascli t3 "
            . " ON  t2.idfactura=t3.idfactura  AND t2.anulada = 0 AND t2.cod_persona=t1.id_persona AND t3.referencia = '6' "
            . " WHERE t3.idfactura = {$idfactura}";


        $aspi = $this->db->select($sql);
        $this->valuesPerson['aspiradores'] = $aspi ?? [];

        $sql = "SELECT SUM(t3.pvptotal) as total, SUM(t3.val_liquidacion_jefes) as val_liquidacion"
            . " FROM  jefes_patio t1 "
            . " INNER JOIN  facturascli t2 "
            . " INNER JOIN lineasfacturascli t3 "
            . " INNER JOIN articulos t4 "
            . " ON  t2.idfactura=t3.idfactura  AND t2.anulada = 0 AND t2.cod_persona=t1.id_persona AND t4.referencia=t3.referencia AND  t3.val_liquidacion_jefes>0  "
            . " WHERE t2.idfactura = {$idfactura}";

        $jefes = $this->db->select($sql);
        $this->valuesPerson['jefes'] = $jefes ?? [];

        $sql = "SELECT SUM(t1.pvptotal) as total, SUM(t1.val_liquidacion) as val_liquidacion "
            . " FROM lineasfacturascli t1 INNER JOIN facturascli t2 "
            . " ON t1.idfactura=t2.idfactura AND t2.anulada = 0 AND "
            . " t1.proveedor_lav != '' "
            . " WHERE t2.idfactura = {$idfactura}";

        $lavadores = $this->db->select($sql);
        $this->valuesPerson['lavadores'] = $lavadores ?? [];
    }

    public function url()
    {
        if (!isset($this->factura)) {
            return parent::url();
        } else if ($this->factura) {
            return $this->factura->url();
        }

        return $this->ppage->url();
    }

    private function modificar()
    {
        $this->factura->observaciones = $_POST['observaciones'];
        $this->factura->nombrecliente = $_POST['nombrecliente'];
        $this->factura->cifnif = $_POST['cifnif'];
        $this->factura->codpais = $_POST['codpais'];
        $this->factura->provincia = $_POST['provincia'];
        $this->factura->ciudad = $_POST['ciudad'];
        $this->factura->codpostal = $_POST['codpostal'];
        $this->factura->direccion = $_POST['direccion'];
        $this->factura->apartado = $_POST['apartado'];

        $this->factura->envio_nombre = $_POST['envio_nombre'];
        $this->factura->envio_apellidos = $_POST['envio_apellidos'];
        $this->factura->envio_codtrans = NULL;
        if ($_POST['envio_codtrans'] != '') {
            $this->factura->envio_codtrans = $_POST['envio_codtrans'];
        }
        $this->factura->envio_codigo = $_POST['envio_codigo'];
        $this->factura->envio_codpais = $_POST['envio_codpais'];
        $this->factura->envio_provincia = $_POST['envio_provincia'];
        $this->factura->envio_ciudad = $_POST['envio_ciudad'];
        $this->factura->envio_codpostal = $_POST['envio_codpostal'];
        $this->factura->envio_direccion = $_POST['envio_direccion'];
        $this->factura->envio_apartado = $_POST['envio_apartado'];

        $this->factura->codagente = NULL;
        $this->factura->porcomision = 0;
        if ($_POST['codagente'] != '') {
            $this->factura->codagente = $_POST['codagente'];
            $this->factura->porcomision = floatval($_POST['porcomision']);
        }

        $this->factura->set_fecha_hora($_POST['fecha'], $_POST['hora']);

        /// ¿cambiamos la forma de pago?
        if ($this->factura->codpago != $_POST['forma_pago']) {
            $this->factura->codpago = $_POST['forma_pago'];
            $this->factura->vencimiento = $this->nuevo_vencimiento($this->factura->fecha, $this->factura->codpago);
        } else {
            $this->factura->vencimiento = $_POST['vencimiento'];
        }

        /// función auxiliar para implementar en los plugins que lo necesiten
        if (!fs_generar_numero2($this->factura)) {
            $this->factura->numero2 = $_POST['numero2'];
        }

        if ($this->factura->save()) {
            $asiento = $this->factura->get_asiento();
            if ($asiento) {
                $asiento->fecha = $this->factura->fecha;
                if (!$asiento->save()) {
                    $this->new_error_msg("Imposible modificar la fecha del asiento.");
                }
            }

            /// Función de ejecución de tareas post guardado correcto del albarán
            fs_documento_post_save($this->factura);

            $this->new_message("Factura modificada correctamente.");
            $this->new_change('Factura Cliente ' . $this->factura->codigo, $this->factura->url());
        } else {
            $this->new_error_msg("¡Imposible modificar la factura!");
        }
    }

    private function actualizar_direccion()
    {
        foreach ($this->cliente->get_direcciones() as $dir) {
            if ($dir->domfacturacion) {
                $this->factura->cifnif = $this->cliente->cifnif;
                $this->factura->nombrecliente = $this->cliente->razonsocial;

                $this->factura->apartado = $dir->apartado;
                $this->factura->ciudad = $dir->ciudad;
                $this->factura->coddir = $dir->id;
                $this->factura->codpais = $dir->codpais;
                $this->factura->codpostal = $dir->codpostal;
                $this->factura->direccion = $dir->direccion;
                $this->factura->provincia = $dir->provincia;

                if ($this->factura->save()) {
                    $this->new_message('Dirección actualizada correctamente.');
                } else {
                    $this->new_error_msg('Imposible actualizar la dirección de la factura.');
                }

                break;
            }
        }
    }

    private function generar_asiento(&$factura)
    {
        if ($factura->get_asiento()) {
            $this->new_error_msg('Ya hay un asiento asociado a esta factura.');
        } else {
            $asiento_factura = new asiento_factura();
            $asiento_factura->soloasiento = TRUE;
            if ($asiento_factura->generar_asiento_venta($factura)) {
                $this->new_message("<a href='" . $asiento_factura->asiento->url() . "'>Asiento</a> generado correctamente.");

                if (!$this->empresa->contintegrada) {
                    $this->new_message("¿Quieres que los asientos se generen automáticamente?"
                        . " Activa la <a href='index.php?page=admin_empresa#facturacion'>Contabilidad integrada</a>.");
                }
            }

            foreach ($asiento_factura->errors as $err) {
                $this->new_error_msg($err);
            }

            foreach ($asiento_factura->messages as $msg) {
                $this->new_message($msg);
            }
        }
    }

    private function pagar($pagada = TRUE)
    {
        /// ¿Hay asiento?
        if (is_null($this->factura->idasiento)) {
            $this->factura->pagada = $pagada;
            $this->factura->save();
        } else if (!$pagada && $this->factura->pagada) {
            /// marcar como impagada
            $this->factura->pagada = FALSE;

            /// ¿Eliminamos el asiento de pago?
            $as1 = new asiento();
            $asiento = $as1->get($this->factura->idasientop);
            if ($asiento) {
                $asiento->delete();
                $this->new_message('Asiento de pago eliminado.');
            }

            $this->factura->idasientop = NULL;
            if ($this->factura->save()) {
                $this->new_message('Factura marcada como impagada.');
            } else {
                $this->new_error_msg('Error al modificar la factura.');
            }
        } else if ($pagada && !$this->factura->pagada) {
            /// marcar como pagada
            $asiento = $this->factura->get_asiento();
            if ($asiento) {
                $fechita = date("Y-m-d");
                /// nos aseguramos que el cliente tenga subcuenta en el ejercicio actual
                $subcli = FALSE;
                $eje = $this->ejercicio->get_by_fecha($fechita);
                if ($eje && $this->cliente) {
                    $subcli = $this->cliente->get_subcuenta($eje->codejercicio);
                }

                $importe = $this->euro_convert($this->factura->totaleuros, $this->factura->coddivisa, $this->factura->tasaconv);

                $asiento_factura = new asiento_factura();
                $this->factura->idasientop = $asiento_factura->generar_asiento_pago($asiento, $this->factura->codpago, $fechita, $subcli, $importe);
                if ($this->factura->idasientop !== NULL) {
                    $this->factura->pagada = TRUE;
                    if ($this->factura->save()) {
                        $this->new_message('<a href="' . $this->factura->asiento_pago_url() . '">Asiento de pago</a> generado.');
                    } else {
                        $this->new_error_msg('Error al marcar la factura como pagada.');
                    }
                }

                foreach ($asiento_factura->errors as $err) {
                    $this->new_error_msg($err);
                }
            } else {
                $this->new_error_msg('No se ha encontrado el asiento de la factura.');
            }
        }
    }

    private function nuevo_vencimiento($fecha, $codpago)
    {
        $vencimiento = $fecha;

        $formap = $this->forma_pago->get($codpago);
        if ($formap) {
            $cli0 = new cliente();
            $cliente = $cli0->get($this->factura->codcliente);
            if ($cliente) {
                $vencimiento = $formap->calcular_vencimiento($fecha, $cliente->diaspago);
            } else {
                $vencimiento = $formap->calcular_vencimiento($fecha);
            }
        }

        return $vencimiento;
    }

    private function anular_factura()
    {
        $this->factura->anulada = TRUE;

        if ($this->factura->observaciones == '') {
            $this->factura->observaciones = "Motivo de la anulación:\n" . $_POST['motivo'];
        }

        if ($this->factura->save()) {
            $articulo = new articulo();

            /// actualizamos el stock
            foreach ($this->factura->get_lineas() as $linea) {
                if ($linea->referencia) {
                    $art = $articulo->get($linea->referencia);
                    if ($art) {
                        $art->sum_stock($this->factura->codalmacen, $linea->cantidad, FALSE, $linea->codcombinacion);
                    }
                }
            }

            $this->new_message('Factura de venta ' . $this->factura->codigo . ' anulada correctamente.', TRUE);

            /// Función de ejecución de tareas post guardado correcto del albarán
            fs_documento_post_save($this->factura);
        }
    }

    private function generar_rectificativa()
    {
        $ejercicio = $this->ejercicio->get_by_fecha($this->today());
        if ($ejercicio) {
            /// generamos una factura rectificativa a partir de la actual
            $factura = clone $this->factura;
            $factura->idfactura = NULL;
            $factura->numero = NULL;
            $factura->numero2 = NULL;
            $factura->codigo = NULL;
            $factura->idasiento = NULL;
            $factura->idasientop = NULL;
            $factura->femail = NULL;
            $factura->numdocs = 0;

            $factura->idfacturarect = $this->factura->idfactura;
            $factura->codigorect = $this->factura->codigo;
            $factura->codejercicio = $ejercicio->codejercicio;
            $factura->codserie = $_POST['codserie'];
            $factura->set_fecha_hora($this->today(), $this->hour());
            $factura->observaciones = $_POST['motivo'];
            $factura->netosindto = 0;
            $factura->neto = 0;
            $factura->totalirpf = 0;
            $factura->totaliva = 0;
            $factura->totalrecargo = 0;
            $factura->total = 0;

            /// función auxiliar para implementar en los plugins que lo necesiten
            fs_generar_numero2($factura);

            if ($factura->save()) {
                $articulo = new articulo();
                $error = FALSE;

                /// copiamos las líneas en negativo
                foreach ($this->factura->get_lineas() as $lin) {
                    $lin->idlinea = NULL;
                    $lin->idalbaran = NULL;
                    $lin->idfactura = $factura->idfactura;
                    $lin->cantidad = 0 - $lin->cantidad;
                    $lin->pvpsindto = $lin->pvpunitario * $lin->cantidad;
                    $lin->pvptotal = $lin->pvpunitario * (100 - $lin->dtopor) / 100 * $lin->cantidad;

                    if ($lin->save()) {
                        if ($lin->referencia) {
                            /// actualizamos el stock
                            $art = $articulo->get($lin->referencia);
                            if ($art) {
                                $art->sum_stock($factura->codalmacen, 0 - $lin->cantidad, FALSE, $lin->codcombinacion);
                            }
                        }
                    } else {
                        $error = TRUE;
                    }
                }

                /// obtenemos los subtotales por impuesto
                $due_totales = $this->fbase_calc_due([$factura->dtopor1, $factura->dtopor2, $factura->dtopor3, $factura->dtopor4, $factura->dtopor5]);
                foreach ($this->fbase_get_subtotales_documento($factura->get_lineas(), $due_totales) as $subt) {
                    $factura->netosindto += $subt['netosindto'];
                    $factura->neto += $subt['neto'];
                    $factura->totaliva += $subt['iva'];
                    $factura->totalirpf += $subt['irpf'];
                    $factura->totalrecargo += $subt['recargo'];
                }

                $factura->total = round($factura->neto + $factura->totaliva - $factura->totalirpf + $factura->totalrecargo, FS_NF0);

                if ($error || !$factura->save()) {
                    $factura->delete();
                    $this->new_error_msg('Se han producido errores al crear la ' . FS_FACTURA_RECTIFICATIVA);
                } else {
                    $this->new_message('<a href="' . $factura->url() . '">' . ucfirst(FS_FACTURA_RECTIFICATIVA) . '</a> creada correctamenmte.');

                    if ($this->empresa->contintegrada) {
                        $this->generar_asiento($factura);
                    } else {
                        /// generamos las líneas de IVA de todas formas
                        $factura->get_lineas_iva();
                    }

                    /// Función de ejecución de tareas post guardado correcto del albarán
                    fs_documento_post_save($factura);

                    /// anulamos la factura actual
                    $this->factura->anulada = TRUE;
                    $this->factura->save();
                }
            } else {
                $this->new_error_msg('Error al generar la ' . FS_FACTURA_RECTIFICATIVA . '.');
            }
        } else {
            $this->new_error_msg('No se encuentra un ejercicio abierto para la fecha ' . $this->today());
        }
    }

    private function get_factura_rectificativa()
    {
        $sql = "SELECT * FROM facturascli WHERE idfacturarect = " . $this->factura->var2str($this->factura->idfactura);

        $data = $this->db->select($sql);
        if ($data) {
            $this->rectificativa = new factura_cliente($data[0]);
        }
    }

    public function get_cuentas_bancarias()
    {
        $cuentas = array();

        $cbc0 = new cuenta_banco_cliente();
        foreach ($cbc0->all_from_cliente($this->factura->codcliente) as $cuenta) {
            $cuentas[] = $cuenta;
        }

        return $cuentas;
    }
}
