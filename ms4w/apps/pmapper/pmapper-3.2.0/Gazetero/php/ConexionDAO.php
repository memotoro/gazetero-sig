<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importaci�n de Archivo de Configuraci�n de Conexi�n.
require_once ("ConfigDAO.php");
// Clase de conexi�n a la base de datos.
class ConexionDAO{
	// Funci�n de conexi�n.
	function conect_bd(){
		// Creaci�n de un Objeto de tipo DAO.
		$obConfig = new ConfigDAO;
		// Creaci�n de la Conexi�n.
		$cadena=$obConfig->getCadenaConexion();
		$conexion=pg_Connect($cadena);
		// Validaci�n de creaci�n correcta de conexi�n.
		if(!$conexion){
			die("ERROR AL CONECTARSE CON LA BASE DE DATOS!");
			exit();
		}
		return $conexion;
	}
}
?>