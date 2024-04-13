<?php
include("SQL.php");

$con = new SQL();
$dato = array();


if($_POST["peticion"]=="pagohora"){
	$hora = $_POST["hora"];
	$idEmpleado = $_POST["idEmpleado"];
	//$hora = $datetime->format('H:i:s');
	$maximo =  $con->comprobarDato("MAX(idasiento)", "co_asientos", "");
	$con->consulta("UPDATE co_asientos SET hora='$hora', idempleado='$idEmpleado'  WHERE idasiento='$maximo' ");

	$dato["pago"] = $maximo;
}

if($_POST["peticion"]=="cierrecaja"){
	$valor = $_POST["valor"];
	$fecha = $_POST["fecha"];
	$empleado = $_POST["idEmpleado"];
	$idTermi = $_POST["idTermi"];
	$hora = $_POST["hora"];

	$datetime = new DateTime(); 
	//$fecha = $datetime->format('Y-m-d H:i:s');
	//$soloFecha = $datetime->format('Y-m-d');
	//$hora = $datetime->format('H:i:s');

	$fechInicio =  $con->comprobarDato("f_inicio", "cajas", " WHERE f_fin IS NULL AND codagente='$empleado' AND fs_id='$idTermi'");
	$horaInicio =  $con->comprobarDato("TIME(f_inicio)", "cajas", " WHERE f_fin IS NULL AND codagente='$empleado'  AND 	fs_id='$idTermi'");

	$sumacredito =  $con->comprobarDato("SUM(total)", "facturascli", " WHERE codagente='$empleado' AND fecha='$fecha' AND (vencimiento<>'$fecha' OR pagada='0' ) AND hora>='$horaInicio'  ");

	if($sumacredito == null)
		$sumacredito = 0;

	$asientos =  $con->comprobarDato("SUM(importe)", "co_asientos", " WHERE fecha='$fecha' AND  hora>'$horaInicio' AND hora<'$hora' AND concepto LIKE '%Cobro%' AND idempleado='$empleado' ");

	if($asientos == null)
		$asientos = 0;

	//$resultado =  "SUM(total)" + "facturascli" + " WHERE fecha='$fecha' AND pagada='0'";

	$con->consulta("UPDATE cajas SET cierremanual='$valor', totalCredito='$sumacredito', totalAsiento='$asientos' WHERE  f_fin IS NULL AND codagente='$empleado' AND fs_id='$idTermi' ");

	$dato["asientos"] = $asientos;
	$dato["sumacredito"] = $sumacredito;
	$dato["consultaasiento"] = " WHERE fecha='$fecha' AND  hora>'$horaInicio' AND hora<'$hora' AND concepto LIKE '%Cobro%' AND idempleado='$empleado' ";
	$dato["consultacredito"] = " WHERE fecha='$fecha' AND (vencimiento<>'$fecha' OR pagada='0' ) AND hora>='$horaInicio'  ";
	
	$dato["cierrecaja"] = true;	

}

echo json_encode($dato);	
?>