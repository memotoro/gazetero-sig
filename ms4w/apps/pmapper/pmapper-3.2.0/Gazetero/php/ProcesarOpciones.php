<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Recepci�n de los valores de la opci�n del menu.
$Opcion=$_POST["Opcion"];
// Retorna como Location las diferentes paginas de consulta en el iframe de navegaci�n establecido en el menu.
if($Opcion=='Departamento-Municipio'){
	header("Location: ./ConsultaDeptos.php");
}else if($Opcion=='Nombre de Localidad'){
	header("Location: ./ConsultaNombre.php");
}else if($Opcion=='Coordenadas Geogr�ficas'){
	header("Location: ./ConsultaZona.php");
}

?>