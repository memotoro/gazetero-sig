<link rel="stylesheet" href="../../templates/default.css" type="text/css" />
<form id="formName" name="formName" method="post" action="./ProcesarNombre.php" target="navegacion2"> 
	<table width="200px" border="0" align="center">
    <tr>
		<td width="180" align="center">Nombre de la Localidad</td>
	</tr>
	<tr>
		<td width="185"><input type="text" name="NomLocal" id="NomLocal" class="text"/></td>
	</tr>  
	<td colspan="2">
        <div align="center">
          <input type="submit" name="Buscar" id="Buscar" value="Buscar Localidad"/>
        </div>
    </td>	
	</tr>
</form>
<form action="./LimpiarFrames" method="post" name="formLimpiar2" id="formLimpiar2" style="visibility:hidden" target="navegacion2">
</form>
<form action="./LimpiarFrames" method="post" name="formLimpiar3" id="formLimpiar3" style="visibility:hidden" target="navegacion3">
</form>
<script language="JavaScript">  
	document.formLimpiar2.submit();
	document.formLimpiar3.submit();		
</script>