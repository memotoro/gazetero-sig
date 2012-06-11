<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importación de objeto de Utilidades.
require_once("./Utilidades.php");
// Recepción de datos via POST de las latitudes y longitudes ingresadas por el usuario.
$latMaxGrado=$_POST["latMaxGrado"];    
$latMaxMin=$_POST["latMaxMin"];
$latMaxSeg=$_POST["latMaxSeg"];
if($latMaxGrado<0){
	$latMax=$latMaxGrado-($latMaxMin/60)-($latMaxSeg/60);
}else{
	$latMax=$latMaxGrado+($latMaxMin/60)+($latMaxSeg/60);
}

$latMinGrado=$_POST["latMinGrado"];
$latMinMin=$_POST["latMinMin"];
$latMinSeg=$_POST["latMinSeg"];
if($latMinGrado<0){
	$latMin=$latMinGrado-($latMinMin/60)-($latMinSeg/60);
}else{
	$latMin=$latMinGrado+($latMinMin/60)+($latMinSeg/60);
}	
    
$lonMaxGrado=$_POST["lonMaxGrado"];
$lonMaxMin=$_POST["lonMaxMin"];
$lonMaxSeg=$_POST["lonMaxSeg"];    
$lonMax=$lonMaxGrado-($lonMaxMin/60)-($lonMaxSeg/60);

$lonMinGrado=$_POST["lonMinGrado"];
$lonMinMin=$_POST["lonMinMin"];
$lonMinSeg=$_POST["lonMinSeg"];    
$lonMin=$lonMinGrado-($lonMinMin/60)-($lonMinSeg/60);
// Concatenación con formato de pmapper para el zoom extent.
$coords=$lonMin."+".$latMin."+".$lonMax."+".$latMax;
// Instanciación del objeto.
$oLocalZonaUtil=new Utilidades;
// Obtener localidades dadas las coordenadas de la zona.
$varExecLocalidad=$oLocalZonaUtil->ObtenerLocalidadesZona($lonMin,$latMin,$lonMax,$latMax);
// Verifica si se retornaron localidades de la consulta.
if($varExecLocalidad!=false){
	$filas=pg_numrows($varExecLocalidad); 
	// Verifica si exiisten filas.
	if ($filas==0) { 
?>   
<script language="JavaScript">  
alert("NO EXISTEN LOCALIDADES EN EL MUNICIPIO SELECCIONADO");
</script> 
<?php		
	}else{   
?>
<link rel="stylesheet" href="../../templates/default.css" type="text/css" />
<form id="form" name="form" method="post" action="./DespliegueLocalidad.php" target="navegacion4">
	<table width="190" border="0" align="center">   
    <tr>
		<td width="190">Selecicone la Localidad</td>
	</tr>
	<tr>
		<td width="144">
			<select name="Local" id="Local" class="select" style="width:200px;">
			<?php  
			// Bucle que llena el listado de localidades con sus nombres.
			while($fetchArrayBM = pg_fetch_array($varExecLocalidad)){?>
				<option value="<?php echo $fetchArrayBM['gid'];?>"><?php echo $fetchArrayBM['nombre'];?></option>
			<?php
			}		   
			?>  
			</select>
		</td>
    </tr>
    <tr>
		<td colspan="2">
        <div align="center">
			<input type="submit" name="Visualizar" id="Visualizar" value="Visualizar">
        </div>
		</td>
    </tr>
  </table>  
</form>
<?php		
	}   
}		  
?>
<link rel="stylesheet" href="../templates/default.css" type="text/css" />
<form action="../../map.phtml" method="post" name="formMap" target="dinamico" class="None" id="formMap" style="visibility:hidden">
	<table width="761" border="0" align="center">
    <tr>
		<td width="128"><input type="submit" name="Buscar" id="Buscar" value="Buscar"/> </td>
		<td width="128"><input type="hidden" name="coords" id="coords" value="<?php echo $coords ?>"/></td>
		<td width="217"><input type="hidden" name="gid" id="gid" value="0"/></td>
		<td width="217"><input type="hidden" name="capa" id="capa" value="0"/></td>		
    </tr>
	</table>
</form>
<script language="JavaScript">  
	document.formMap.submit();
</script>