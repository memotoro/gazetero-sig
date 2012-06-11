<?php
/**
* @author Guillermo Antonio Toro Bayona.
* Especialista SIG.	
*/
// Importación de objeto de consulta
require_once ("./DeptoMpioLocalDAO.php");
// Recepción del nombre de la localidad.
$Localidad=$_POST["NomLocal"];
// Instanciación del objeto.
$oLocal= new DeptoMpioLocalDAO;
// Obtener las localidades por nombre de la localidad.
$varExecLocalidad=$oLocal->ConsultarLocalidad($Localidad);
// Si existen localidades asociadas.
if($varExecLocalidad!=false){
?>
<link rel="stylesheet" href="../../templates/default.css" type="text/css" />
<form id="form" name="form" method="post" action="./DespliegueLocalidad.php" target="navegacion4">
	<table width="180" border="0" align="center"> 
    <tr>
		<td width="180">Selecicone la Localidad</td>
	</tr>
	<tr>
		<td width="144">
			<select name="Local" id="Local" class="select" style="width:200px;">
			<?php  
			// Se llena el listado con los nombres de las localidades.
			while($fetchArrayBM = pg_fetch_array($varExecLocalidad)){?>
				<option value="<?php echo $fetchArrayBM['gid'];?>"><?php echo $fetchArrayBM['nombre'];?></option>
			<?php
			}
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