<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importanción de objeto de conexión.
require_once ("./ConexionDAO.php");
// Clase de consulta de departamento, municipios y localidades.
class DeptoMpioLocalDAO{
	// Funcion de consulta de departamentos.
	function ConsultarDepto(){
	 	// Creación e instanciación de la conexión.
		$bdConexion = new ConexionDAO;
		$connect=$bdConexion->conect_bd();
			if(!$connect){
			die("Error al conectarse con la Base de Datos!");
			exit();
			}
		// Sentencia SQL de consulta.
		$sql = ("	SELECT gid, nombre 
					FROM departamento 
					ORDER BY nombre ASC;");
	 	// Ejecución de la consulta.	 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;	 
	}
	// Funcion de consulta de Municipio.
	function ConsultarMpio($depto){
	 	// Creación e instanciación de la conexión.	 
		$bdConexion = new ConexionDAO;
		$connect=$bdConexion->conect_bd();
			if(!$connect){
			die("Error al conectarse con la Base de Datos!");
			exit();
			}
		// Sentencia SQL de consulta.
		$sql = ("SELECT m.gid, m.nombre
				  FROM departamento d, municipio m
				  WHERE d.codigod=m.codigod 
				  AND d.gid='$depto' 
				  ORDER BY m.nombre ASC");
 		// Ejecución de la consulta.	 	 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;	 
	}
	// Funcion de consulta de Localidades dado el nombre.	
	function ConsultarLocalidad($localidad){
	 	// Creación e instanciación de la conexión.	 
		$bdConexion = new ConexionDAO;
		$connect=$bdConexion->conect_bd();
			if(!$connect){
			die("Error al conectarse con la Base de Datos!");
			exit();
			}
		// Sentencia SQL de consulta.
		$sql = ("SELECT gid, nombre
				  FROM localidad
				  WHERE nombre like '%$localidad%'
				  ORDER BY nombre ASC");
 		// Ejecución de la consulta.		 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;		
	}
	// Funcion para obtener el nom
	function ObtenerMpio($Depto,$Mpio){
	 	// Creación e instanciación de la conexión.	 
		$bdConexion = new ConexionDAO;
		$connect=$bdConexion->conect_bd();
			if(!$connect){
			die("Error al conectarse con la Base de Datos!");
			exit();
			}
		// Sentencia SQL de consulta.
		$sql = ("SELECT m.nombre
					FROM municipio m, departamento d
					WHERE d.nombre='$Depto'
					AND m.nombre='$Mpio'
					AND m.codigod=d.codigod");
 		// Ejecución de la consulta.		 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;	
	}
}
?>