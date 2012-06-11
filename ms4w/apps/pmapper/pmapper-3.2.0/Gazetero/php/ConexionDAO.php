<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importación de Archivo de Configuración de Conexión.
require_once ("ConfigDAO.php");
// Clase de conexión a la base de datos.
class ConexionDAO{
	// Función de conexión.
	function conect_bd(){
		// Creación de un Objeto de tipo DAO.
		$obConfig = new ConfigDAO;
		// Creación de la Conexión.
		$cadena=$obConfig->getCadenaConexion();
		$conexion=pg_Connect($cadena);
		// Validación de creación correcta de conexión.
		if(!$conexion){
			die("ERROR AL CONECTARSE CON LA BASE DE DATOS!");
			exit();
		}
		return $conexion;
	}
}
?>