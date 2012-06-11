<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importación del objeto de conexión.
require_once("ConexionDAO.php");
// Clase de utilidades.
class Utilidades{
	// Función que adecua las coordenadas para el formato de pmapper del zoom extent
	function AdecuarCoordenadas($coord){	

		$buscar1 = "BOX(";
		$reemplazar1 = "";
		$buscar2 = " ";
		$reemplazar2 = "+";
		$buscar3 = ",";
		$reemplazar3 = "+";
		$buscar4 = ")";
		$reemplazar4 = "";
		// Reemplazo de valores por los indicados.
		$newcoord1 = str_replace($buscar1, $reemplazar1, $coord);
		$newcoord2 = str_replace($buscar2, $reemplazar2, $newcoord1);
		$newcoord3 = str_replace($buscar3, $reemplazar3, $newcoord2);
		$newcoord4 = str_replace($buscar4, $reemplazar4, $newcoord3);
		return $newcoord4;

	}
	// Función que retorna las coordenadas de una capa y un objeto indicado.
	function BuscarCoords($Capa,$Nombre){	
		// Creación d e la conexión de la base de datos.
		$bdConexion = new ConexionDAO;
		$connect=$bdConexion->conect_bd();
			if(!$connect){
			die("Error al conectarse con la Base de Datos!");
			exit();
			}
		// Sentencia SQL a ejectuar para obtener coordenadas. Utiliza funciones espaciales de PostGIS.
		$sql = ("select box2d(the_geom) as coordenadas, gid as gid FROM $Capa where nombre='$Nombre';");	 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;	 
	}	
	// Función que retorna las coordenadas de una capa y un objeto indicado.
	function BuscarCoordsGid($Capa,$Gid){	
		// Creación d e la conexión de la base de datos.
		$bdConexion = new ConexionDAO;
		$connect=$bdConexion->conect_bd();
			if(!$connect){
			die("Error al conectarse con la Base de Datos!");
			exit();
			}
		// Sentencia SQL a ejectuar para obtener coordenadas. Utiliza funciones espaciales de PostGIS.
		$sql = ("select box2d(the_geom) as coordenadas, gid as gid FROM $Capa where gid='$Gid';");	 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;	 
	}
	// Función que retorna las localidades dado un municipio. La consulta es espacial con relación de contenencia.
	function ObtenerCruceLocalidades($Mpio){
		// Creación d e la conexión de la base de datos.
		$bdConexion = new ConexionDAO;
		$connect=$bdConexion->conect_bd();
			if(!$connect){
			die("Error al conectarse con la Base de Datos!");
			exit();
			}
		// Sentencia SQL a ejectuar para obtener las localidades del municipio por contenencia. Utiliza funciones espaciales de PostGIS.
		$sql = ("select l.gid,l.nombre as nombre FROM localidad l,municipio m where st_within(l.the_geom,m.the_geom) and m.gid='$Mpio';");	  		
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;		
	}
	// Función que obtiene las localidades dada una zona geográfica.
	function ObtenerLocalidadesZona($lonMin,$latMin,$lonMax,$latMax){
		// Creación d e la conexión de la base de datos.
		$bdConexion = new ConexionDAO;
		$connect=$bdConexion->conect_bd();
			if(!$connect){
			die("Error al conectarse con la Base de Datos!");
			exit();
			}
		// Sentencia SQL que permite consultar las localidades dentro de una zona espacial dada por los datos del usuario. Se construye un poligono en tiempo de ejecución de la consulta para poder contener punto.
		$sql = ("select l.gid,l.nombre from localidad l where st_within(l.the_geom,GeometryFromText('POLYGON(($lonMin $latMin,$lonMax $latMin,$lonMax $latMax,$lonMin $latMax,$lonMin $latMin))',4326));");	 		 
		$exec = pg_Exec( $connect, $sql );
		pg_close($connect);
		return $exec;		
	}
}
?>