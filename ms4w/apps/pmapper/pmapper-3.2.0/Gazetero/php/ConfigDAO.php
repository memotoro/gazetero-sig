<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Clase con los parametros de conexión a la base de datos.
class ConfigDAO{
	// Funcion que retorna la cadena de conexión a la base de datos. En caso de que cambie la base de datos solo se modifica este archivo.
	function getCadenaConexion(){
		return $cadenaConexion = "host=localhost dbname=gazetero user=postgres password=postgres";
	}
}
?>