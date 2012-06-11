<link rel="stylesheet" href="../../templates/default.css" type="text/css"/>
<form id="form" name="form" method="post" action="./ProcesarOpciones.php" target="navegacion1">
	<table width="200px" border="0" align="center">
    <tr>
		<td width="200" align="center">Opciones de Consulta</td>
	</tr>  
	<tr>
		<td width="200px">
        <div align="center">		
		  <select name="Opcion" id="Opcion" class="select" style="width:190px;">
		    <option>Departamento-Municipio</option>
		    <option>Nombre de Localidad</option>
		    <option>Coordenadas Geográficas</option>
	      </select>
		 </div>
		</td>
	</tr>
    <tr>
	<td colspan="2">
        <div align="center">
          <input type="submit" name="Consultar" id="Consultar" value="Consultar"/>
        </div>    </td>
	</tr> 
	</table>
	<table width="200px" border="0" align="center">	
	<tr>
	<td>   
     	<iframe width="270px" height="200px" name="navegacion1" style="overflow:hidden;background-color:#FFFFFF;">        </iframe>	</td>
	</tr>
	</table>
	<table width="200px" border="0" align="center">		
	<tr>
	<td width="200px">   
     	<iframe width="270px" height="90px" name="navegacion2" style="overflow:hidden;background-color:#FFFFFF;">        </iframe>	</td>	
	</tr>
	<tr>
	<td width="200px">   
     	<iframe width="270px" height="90px" name="navegacion3" style="overflow:hidden;background-color:#FFFFFF;">        </iframe>	</td>	
	</tr>	
	<tr>
	<td width="200px">   
     	<iframe width="0%" height="0px" name="navegacion4" style="overflow:hidden;">        </iframe>	</td>	
	</tr>		
  </table>
</form>
<form action="./LimpiarFrames" method="post" name="formLimpiar1" id="formLimpiar1" style="visibility:hidden" target="navegacion1">
</form>
<form action="./LimpiarFrames" method="post" name="formLimpiar2" id="formLimpiar2" style="visibility:hidden" target="navegacion2">
</form>
<form action="./LimpiarFrames" method="post" name="formLimpiar3" id="formLimpiar3" style="visibility:hidden" target="navegacion3">
</form>
<script language="JavaScript">  
	document.formLimpiar1.submit();
	document.formLimpiar2.submit();	
	document.formLimpiar3.submit();		
</script>