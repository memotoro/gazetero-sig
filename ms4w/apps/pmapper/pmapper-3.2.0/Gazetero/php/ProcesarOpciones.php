<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Recepción de los valores de la opción del menu.
$Opcion=$_POST["Opcion"];
// Retorna como Location las diferentes paginas de consulta en el iframe de navegación establecido en el menu.
if($Opcion=='Departamento-Municipio'){
	header("Location: ./ConsultaDeptos.php");
}else if($Opcion=='Nombre de Localidad'){
	header("Location: ./ConsultaNombre.php");
}else if($Opcion=='Coordenadas Geográficas'){
	header("Location: ./ConsultaZona.php");
}

?>