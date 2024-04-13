<?php
	require 'login.php';
class SQL extends Mysqli{

	public $conexion;

	public function __construct()
  	{ 
		$this->conexion = new mysqli(hostname, username, password, database);
		if (mysqli_connect_errno()) 
		{
			printf("Falló la conexión: %s\n", mysqli_connect_error());
			exit();
		}
  	}

  	public function cerrarConexion()
  	{
  		mysql_close($this->conexion);
  	}


  	//FUNCIÓN PARA HACER CONSULTAS A LA BASE DE DATOS, EL PARÁMETRO ES LA CONSULTA SQL
  	public function consulta($cadenaSQL)
  	{
		if (!$resultado = $this->conexion->query($cadenaSQL)) {
		    echo "Hay un error en la consulta <<< ". $cadenaSQL." >>> ";
		    exit;
		}
		return $resultado;
		cerrarConexion();
  	}

  	//FUNCIÓN PARA COMPROBAR QUE UN DATO ESTÉ EN UNA TABLA ESPECÍFICA CUMPLIENDO CIERTAS CONDICIONES
  	public function comprobarDato($campo, $tabla, $condiciones)
  	{
  		$respuesta = "";
		if (!$resultado = $this->conexion->query("SELECT ".$campo." FROM ".$tabla." ".$condiciones)) {
		    echo "Hay un error en la consulta <<< ". $cadenaSQL." >>> ";
		    exit;
		}

		//se asigna los resultados que se obtienen de la consulta SQL
		$filas = $resultado->fetch_assoc();
		//Si hay un resultado entonces se asigna el resultado a la variable respuesta
		$respuesta = $filas[$campo];

		return $respuesta;
		cerrarConexion();
  	}  	

  	//FUNCIÓN PARA generar un select
  	public function devolverSelect($campoEtiqueta, $campoValor, $condiciones)
  	{
  		$respuesta = "";
		if (!$resultado = $this->conexion->query("SELECT ".$campoEtiqueta.", ".$campoValor." ".$condiciones)) {
		    echo "Hay un error en la consulta <<< ". $cadenaSQL." >>> ";
		    exit;
		}
		while($fila = $resultado->fetch_array())
                {
                    $respuesta .=  "<option value='".$fila[$campoValor]."'>".$fila[$campoEtiqueta]."</option>";
                }
		return $respuesta;
		cerrarConexion();
  	}  	




//LA FUNCIÓN DEVUELVE FALSO O VERDADERO SI EL USUARIO ESTÁ CONECTADO O NO
  	//DATO SERÁ EL LOGGIN DEL USUARIO MANDADO CON LA SESSION Y ACTIVO SERÁ
  	//1 - CUANDO ESTÉ CONECTADO
  	//0 - CUANDO SE VAYA A CERRAR SESIÓN
  	public function usuarioActivo($activo)
  	{
  		$respuesta = false;
  		if(!empty($_SESSION['conectado']))
  		{
	  		if (!$resultado = $this->conexion->query("SELECT usuarios_Activo FROM usuarios WHERE usuarios_Loggin = '".$_SESSION['conectado']."'")) {
			    echo "Hay un error en la consulta";
			    exit;
			}
			else
			{
				if($activo == 1)
				{
					//se asigna los resultados que se obtienen de la consulta SQL
					$filas = $resultado->fetch_assoc();
					//Si hay un resultado entonces se asigna el resultado a la variable respuesta
					if($filas["usuarios_Activo"]==1)	
						$respuesta = true;
				}
				if($activo == 0)
					$this->conexion->query("UPDATE  
						usuarios SET usuarios_Activo='".$activo."' WHERE usuarios_Loggin = '".$_SESSION['conectado']."'");

			}
		}

		return $respuesta;
		cerrarConexion();
  	}  	

}


?>