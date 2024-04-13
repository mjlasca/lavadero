<?php


include("SQL.php");

$con = new SQL();
$dato = array();

$ipUsuario = $_POST["ipUsuario"];

if($_POST["peticion"]=="peso"){
	$resultado = 0;
	$resultado =  $con->comprobarDato("balanza", "peso_producto", " WHERE nombrepc='$ipUsuario' AND balanza>0");

	
	$con->consulta("UPDATE peso_producto  SET  balanza='0' WHERE nombrepc='$ipUsuario' ");

	$estad =  $con->comprobarDato("estado", "peso_producto", " WHERE nombrepc='$ipUsuario' ");


//	$con->consulta("UPDATE peso_producto  SET estado='0' WHERE nombrepc='$ipUsuario' ");
	//$con = new fs_mysql();

	//$sql = "SELECT * FROM peso_producto";
	//$aux = $this->select($sql);

	/// cargamos las constantes de configuración
	//require_once 'base/fs_mysql.php';
	$dato["estad"] = $estad;

	$dato["exito"] = $resultado;

	
}

//PARA ACTUALIZAR ESTADO DE LA TABLA Y PERMITIR QUE INGRESE EL DATO
if($_POST["peticion"]=="estado"){
	$estado = $_POST["estado"];

	$con->consulta("UPDATE peso_producto  SET estado='$estado' WHERE nombrepc='$ipUsuario' ");

	$dato["estado"] = $estado +" SI" ;
}



if($_POST["peticion"]=="ip"){

	$resultado =  $con->comprobarDato("nombrepc", "peso_producto", " WHERE nombrepc='$ipUsuario' ");

	if($resultado == "" && $ipUsuario != ""){
		$con->consulta("INSERT INTO peso_producto (nombrepc, balanza, estado) VALUES ('$ipUsuario', '0', '1') ");
		$dato["nuevo"] = true;	
	}
	else{
		$dato["nuevo"] = false;	
	}

}


if($_POST["peticion"]=="update"){
	$valor = $_POST["valor"];
	$con->consulta("UPDATE peso_producto  SET  balanza='$valor' WHERE nombrepc='192.168.1.7' ");
	$dato["update"] = $valor;	
}


if($_POST["peticion"]=="arqueo"){
	$idArqueo = $_POST["idArqueo"];

	$fechaArqueo =  $con->comprobarDato("DATE(f_fin)", "cajas", " WHERE id='$idArqueo' ");
	$idEmpleado =  $con->comprobarDato("codagente", "cajas", " WHERE id='$idArqueo' ");

	$cierrecaja =  $con->comprobarDato("valor", " cierrecaja", " WHERE fecha='$fechaArqueo' AND usuario='$idEmpleado' ");

	$factCredito =  $con->comprobarDato("SUM(total)", "facturascli", " WHERE fecha='$fechaArqueo' AND pagada='0' ");

	$asientos =  $con->comprobarDato("SUM(importe)", "co_asientos", " WHERE fecha='$fechaArqueo' ");

	$dato["cierrecaja"] = $cierrecaja;
	$dato["asientos"] = $asientos;
	$dato["factCredito"] = $factCredito;

}

if($_POST["peticion"]=="cierreCaja"){
	$valor = $_POST["valor"];
	$fecha = $_POST["fecha"];
	$empleado = $_POST["idEmpleado"];

	$con->consulta("INSERT INTO  cierrecaja(usuario, fecha, valor)  VALUES('$empleado', '$fecha', '$valor')");
	$dato["cierrecaja"] = true;	
}

echo json_encode($dato);	
?>