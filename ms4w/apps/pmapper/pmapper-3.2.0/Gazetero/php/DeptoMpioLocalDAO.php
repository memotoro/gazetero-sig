<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importanci�n de objeto de conexi�n.
require_once ("./ConexionDAO.php");
// Clase de consulta de departamento, municipios y localidades.
class DeptoMpioLocalDAO{
	// Funcion de consulta de departamentos.
	function ConsultarDepto(){
	 	// Creaci�n e instanciaci�n de la conexi�n.
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
	 	// Ejecuci�n de la consulta.	 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;	 
	}
	// Funcion de consulta de Municipio.
	function ConsultarMpio($depto){
	 	// Creaci�n e instanciaci�n de la conexi�n.	 
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
 		// Ejecuci�n de la consulta.	 	 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;	 
	}
	// Funcion de consulta de Localidades dado el nombre.	
	function ConsultarLocalidad($localidad){
	 	// Creaci�n e instanciaci�n de la conexi�n.	 
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
 		// Ejecuci�n de la consulta.		 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;		
	}
	// Funcion para obtener el nom
	function ObtenerMpio($Depto,$Mpio){
	 	// Creaci�n e instanciaci�n de la conexi�n.	 
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
 		// Ejecuci�n de la consulta.		 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;	
	}
}
?>