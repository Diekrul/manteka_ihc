<script>
	var tiposFiltro = ["Sesión", "Módulo temático"]; //Debe ser escrito con PHP
	var valorFiltrosJson = ["", ""];
	var prefijo_tipoDato = "sesion_";
	var prefijo_tipoFiltro = "tipo_filtro_";
	var url_post_busquedas = "<?php echo site_url("Sesiones/postBusquedaSesiones") ?>";
	var url_post_historial = "<?php echo site_url("HistorialBusqueda/buscar/secciones") ?>";

	function verDetalle(elemTabla) {

		/* Obtengo el rut del usuario clickeado a partir del id de lo que se clickeó */
		var idElem = elemTabla.id;
		sesion_clickeado = idElem.substring(prefijo_tipoDato.length, idElem.length);
		//var rut_clickeado = elemTabla;


		/* Defino el ajax que hará la petición al servidor */
		$.ajax({
			type: "POST", /* Indico que es una petición POST al servidor */
			url: "<?php echo site_url("Sesiones/postDetallesSesion") ?>", /* Se setea la url del controlador que responderá */
			data: { sesion: sesion_clickeado }, /* Se codifican los datos que se enviarán al servidor usando el formato JSON */
			success: function(respuesta) { /* Esta es la función que se ejecuta cuando el resultado de la respuesta del servidor es satisfactorio */
				/* Obtengo los objetos HTML donde serán escritos los resultados */
				var nombreDetalle = document.getElementById("nombreDetalle");
				var modTemDetalle = document.getElementById("mod_temDetalle");
				var descrDetalle = document.getElementById("descripcionDetalle");
				
				/* Decodifico los datos provenientes del servidor en formato JSON para construir un objeto */
				var datos = jQuery.parseJSON(respuesta);

				if (datos.mod_tem == null) {
					datos.mod_tem = 'No tiene asignado';
				}
				if (datos.descripcion == null) {
					datos.descripcion = '';
				}

				/* Seteo los valores desde el objeto proveniente del servidor en los objetos HTML */
				$(nombreDetalle).html($.trim(datos.nombre));
				$(modTemDetalle).html($.trim(datos.mod_tem));
				$(descrDetalle).html($.trim(datos.descripcion));
				

				/* Quito el div que indica que se está cargando */
				var iconoCargado = document.getElementById("icono_cargando");
				$(icono_cargando).hide();

			}
		});
		
		/* Muestro el div que indica que se está cargando... */
		var iconoCargado = document.getElementById("icono_cargando");
		$(icono_cargando).show();
	}
	
	//Se cargan por ajax
	$(document).ready(function() {
		escribirHeadTable();
		cambioTipoFiltro(undefined);
	});
</script>
<fieldset>
	<legend>Ver Sesión</legend>

	<div class="row-fluid">
		<div class="span6">
			<div class="controls controls-row">
			    <div class="input-append span7">
					<input id="filtroLista" type="text" onkeypress="getDataSource(this)" onChange="cambioTipoFiltro(undefined)" placeholder="Filtro búsqueda">
					<button class="btn" onClick="cambioTipoFiltro(undefined)" title="Iniciar una búsqueda considerando todos los atributos" type="button"><i class="icon-search"></i></button>
				</div>
				<button class="btn" onClick="limpiarFiltros()" title="Limpiar todos los filtros de búsqueda" type="button"><i class="caca-clear-filters"></i></button>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6" >
			1.- Seleccione una sesión para ver sus detalles:
		</div>
		<div class="span6" >
			2.- Detalle sesión:
		</div>
	</div>
	<div class="row-fluid">
		<div class="span6" style="border:#cccccc 1px solid; overflow-y:scroll; height:400px; -webkit-border-radius: 4px;">
			<table id="listadoResultados" class="table table-hover">
				<thead>
					
				</thead>
				<tbody>

				</tbody>
			</table>
		</div>
		<div class="span6">
			
			<pre style="padding: 2%; cursor:default">
Nombre de la sesión: 	    <b id="nombreDetalle"></b>
Nombre del módulo temático: <b id="mod_temDetalle"></b>
Descripción: 		    <b id="descripcionDetalle"></b></pre>
		</div>
	</div>

</fieldset>