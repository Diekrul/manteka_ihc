<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH.'controllers/Master.php'; 

class Profesores extends MasterManteka {
	
	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -  
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in 
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see http://codeigniter.com/user_guide/general/urls.html
	 */
	
	/**
	* Manda a la vista 'cuerpo_profesores_verProfesor' los datos necesarios para su funcionamiento
	*
	* Primero se comprueba que el usuario tenga la sesión iniciada, en caso que no sea así se le redirecciona al login
	* Siguiente a esto se cargan los datos para las plantillas de la página.
	* Se carga el modelo de profesores, se cargan los datos de la vista con la lista 'rs_profesores' que contiene toda la información de los
	* profesores del sistema. Finalmente se carga la vista con todos los datos.
	*
	*/
	public function verProfesores()
	{
		$datos_plantilla = array();
		//cargo el modelo de profesores
		$this->load->model('Model_profesor');

		$datos_plantilla = array('rs_profesores' => $this->Model_profesor->VerTodosLosProfesores());

		$subMenuLateralAbierto = 'verProfesores'; //Para este ejemplo, los informes no tienen submenu lateral
		$muestraBarraProgreso = FALSE; //Indica si se muestra la barra que dice anterior - siguiente
		$tipos_usuarios_permitidos = array();
		$tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR; $tipos_usuarios_permitidos[1] = TIPO_USR_PROFESOR;

		$this->cargarTodo("Docentes", "cuerpo_profesores_verProfesor", "barra_lateral_profesores", $datos_plantilla, $tipos_usuarios_permitidos, $subMenuLateralAbierto, $muestraBarraProgreso);
	
	}
	/**
	* Método que responde a una solicitud de post para pedir los datos de un profesor
	* Recibe como parámetro el rut del profesor
	*/
	public function postDetallesProfesor() {
		//Se comprueba que quien hace esta petición de ajax esté logueado
		if (!$this->isLogged()) {
			//echo 'No estás logueado!!';
			return;
		}

		$rut = $this->input->post('rut');
		$this->load->model('Model_profesor');
		$resultado = $this->Model_profesor->getDetallesProfesor($rut);
		echo json_encode($resultado);
	}

	/**
	* Se buscan profesores de forma asincrona para mostrarlos en la vista
	*
	**/
	public function postBusquedaProfesores() {
		if (!$this->isLogged()) {
			//echo 'No estás logueado!!';
			return;
		}
		$textoFiltro = $this->input->post('textoFiltroBasico');
		$textoFiltrosAvanzados = $this->input->post('textoFiltrosAvanzados');

		$this->load->model('Model_profesor');
		$resultado = $this->Model_profesor->getProfesoresByFilter($textoFiltro, $textoFiltrosAvanzados);
		
		/* ACÁ SE ALMACENA LA BÚSQUEDA REALIZADA POR EL USUARIO */
		if (count($resultado) > 0) {
			$this->load->model('model_busquedas');
			//Se debe insertar sólo si se encontraron resultados
			$this->model_busquedas->insertarNuevaBusqueda($textoFiltro, 'profesores', $this->session->userdata('rut'));
			$cantidad = count($textoFiltrosAvanzados);
			for ($i = 0; $i < $cantidad; $i++) {
				$this->model_busquedas->insertarNuevaBusqueda($textoFiltrosAvanzados[$i], 'profesores', $this->session->userdata('rut'));
			}
		}
		echo json_encode($resultado);
		
	}
	
	/**
	* Manda a la vista 'cuerpo_profesores_agregarProfesor' los datos necesarios para su funcionamiento
	*
	* 
	* Se cargan los datos para las plantillas de la página.
	* Se envía un mensaje de confirmación con valor 2, que indica que se está cargando
	* por primera ves la vista de agregar profesor. Finalmente se carga la vista con todos los datos.
	*
	*/
	public function agregarProfesores()
	{
		$rut = $this->session->userdata('rut'); //Se comprueba si el usuario tiene sesi?n iniciada
		if ($rut == FALSE) {
			redirect('/Login/', ''); //Se redirecciona a login si no tiene sesi?n iniciada
		}
		
		$datos_vista=0;
		$subMenuLateralAbierto = "agregarProfesores"; //Para este ejemplo, los informes no tienen submenu lateral
		$muestraBarraProgreso = FALSE; //Indica si se muestra la barra que dice anterior - siguiente
		$tipos_usuarios_permitidos = array();
		$tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
		$this->cargarTodo("Docentes", 'cuerpo_profesores_agregarProfesor', "barra_lateral_profesores", $datos_vista, $tipos_usuarios_permitidos, $subMenuLateralAbierto, $muestraBarraProgreso);	

		
	}

	/**
	* Inserta un profesor al sistema y luego carga los datos para volver a la vista 'cuerpo_profesores_agregarProfesor'
	*
	* Primero se comprueba que el usuario tenga la sesión iniciada, en caso que no sea así se le redirecciona al login
	* Siguiente a esto se cargan los datos para las plantillas de la página.
	* Se carga el modelo de profesores, se llama a la función InsertarProfesor para ingresar al profesor
	* con los datos que se capturan un paso antes en el controlador desde la vista con el uso del POST.
	* El resultado de ésta se recibe en la variable 'confirmacion'
	* que se le envía a la vista a través de la variable 'mensaje_confirmacion' para que de el feedback al usuario, en la vista, de como resulto la operación.
	* Finalmente se carga la vista nuevamente con todos los datos para permitir el ingreso de otro profesor.
	*
	*/
	 public function insertarProfesor()
	{
		$rut = $this->session->userdata('rut'); //Se comprueba si el usuario tiene sesi?n iniciada
		if ($rut == FALSE) {
			redirect('/Login/', ''); //Se redirecciona a login si no tiene sesi?n iniciada
		}
		
		$this->load->model('Model_profesor');

		$rut_profesor = $this->input->post("rut_profesor");
        $nombre1_profesor = $this->input->post("nombre1_profesor");
        $nombre2_profesor = $this->input->post("nombre2_profesor");;
        $apellido1_profesor = $this->input->post("apellido1_profesor");
        $apellido2_profesor = $this->input->post("apellido2_profesor");
        $correo_profesor = $this->input->post("correo_profesor");
		$correo_profesor1 = $this->input->post("correo_profesor1");
        $telefono_profesor = $this->input->post("telefono_profesor");
        $tipo_profesor = $this->input->post("tipo_profesor");
        $confirmacion = $this->Model_profesor->InsertarProfesor($rut_profesor,$nombre1_profesor,$nombre2_profesor,$apellido1_profesor,$apellido2_profesor,$correo_profesor,$correo_profesor1,$telefono_profesor, $tipo_profesor);
	    
		if($confirmacion != 1){
			$datos_plantilla["titulo_msj"] = "Acción No Realizada";
			$datos_plantilla["cuerpo_msj"] = "Ha ocurrido un error al intentar insertar el profesor";
			$datos_plantilla["tipo_msj"] = "alert-error";
		}
		else{
			$datos_plantilla["titulo_msj"] = "Acción Realizada";
			$datos_plantilla["cuerpo_msj"] = "Se ha ingresado el profesor con éxito";
			$datos_plantilla["tipo_msj"] = "alert-success";
	
		}
		$datos_plantilla["redirectAuto"] = FALSE; //Esto indica si por javascript se va a redireccionar luego de 5 segundos
		$datos_plantilla["redirecTo"] = "Profesores/agregarProfesores"; //Acá se pone el controlador/metodo hacia donde se redireccionará
		$datos_plantilla["nombre_redirecTo"] = "Agregar profesores"; //Acá se pone el nombre del sitio hacia donde se va a redireccionar
		$tipos_usuarios_permitidos = array();
		$tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
		$this->cargarMsjLogueado($datos_plantilla, $tipos_usuarios_permitidos);

	}


	
	/**
	* Manda a la vista 'cuerpo_profesores_borrarProfesor' los datos necesarios para su funcionamiento
	*
	* 
	* Siguiente a esto se cargan los datos para las plantillas de la página.
	* Se carga el modelo de profesores, se cargan los datos de la vista con la lista 'rs_profesores' que contiene toda la información de los
	* profesores del sistema y se envia un 'mensaje_confirmacion' que sirve en la vista para que ésta sepa que se está cargando la página por primera vez.
	* Finalmente se carga la vista con todos los datos.
	*
	*/
	public function borrarProfesores(){
		
		$this->load->model('Model_profesor');

        $datos_vista = array('rs_profesores' => $this->Model_profesor->VerTodosLosProfesores(),'mensaje_confirmacion'=>2);
		
		$subMenuLateralAbierto = "borrarProfesores"; //Para este ejemplo, los informes no tienen submenu lateral
		$muestraBarraProgreso = FALSE; //Indica si se muestra la barra que dice anterior - siguiente
		$tipos_usuarios_permitidos = array();
		$tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
		$this->cargarTodo("Docentes", 'cuerpo_profesores_borrarProfesor', "barra_lateral_profesores", $datos_vista, $tipos_usuarios_permitidos, $subMenuLateralAbierto, $muestraBarraProgreso);	


	}

	/**
	* Elimina un profesor del sistema y luego carga los datos para volver a la vista 'cuerpo_profesores_borrarProfesor'
	*
	* Primero se comprueba que el usuario tenga la sesión iniciada, en caso que no sea así se le redirecciona al login
	* Siguiente a esto se cargan los datos para las plantillas de la página.
	* Se carga el modelo de profesores, se llama a la función EliminarProfesor para eliminar el profesor con el rut que se le pasa como parametro
	* y es el que se ha recibido como parametro en esta funcion desde la vista. El resultado de la operación de eliminar desde el modelo se recibe en la variable 'confirmacion'
	* que se le envía a la vista a través de la variable 'mensaje_confirmacion' para que de el feedback al usuario, en la vista, de como resulto la operación.
	* Luego se cargan los datos de la vista con la lista 'rs_profesores' para que se de la opción a escojer un nuevo profesor a eliminar.
	* Finalmente se carga la vista con todos los datos.
	*
	* @param string $rut_profesor
	*/
	public function eliminarProfesores()
	{
		$rut_profesor = $this->input->post("rutEliminar");

		$this->load->model('Model_profesor');
		$confirmacion = $this->Model_profesor->EliminarProfesor($rut_profesor);
		

		if ($confirmacion==1){
			$datos_plantilla["titulo_msj"] = "Acción Realizada";
			$datos_plantilla["cuerpo_msj"] = "Se ha borrado el profesor con éxito";
			$datos_plantilla["tipo_msj"] = "alert-success";
		}
		else{
			$datos_plantilla["titulo_msj"] = "Acción No Realizada";
			$datos_plantilla["cuerpo_msj"] = "Se ha ocurrido un error en la eliminación en base de datos";
			$datos_plantilla["tipo_msj"] = "alert-error";	
		}
		$datos_plantilla["redirectAuto"] = FALSE; //Esto indica si por javascript se va a redireccionar luego de 5 segundos
		$datos_plantilla["redirecTo"] = "Profesores/borrarProfesores"; //Acá se pone el controlador/metodo hacia donde se redireccionará
		//$datos_plantilla["redirecFrom"] = "Login/olvidoPass"; //Acá se pone el controlador/metodo desde donde se llegó acá, no hago esto si no quiero que el usuario vuelva
		$datos_plantilla["nombre_redirecTo"] = "Borrar Profesores"; //Acá se pone el nombre del sitio hacia donde se va a redireccionar
		$tipos_usuarios_permitidos = array();
		$tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
		$this->cargarMsjLogueado($datos_plantilla, $tipos_usuarios_permitidos);
		
	}

	/**
	* Modifica los datos de un profesor y luego carga la vista de realización de acuerdo al resultado que le arroje el modelo
	*
	*En primer lugar se reciben los nuevos datos del  profesor que se desea editar mediante el método POST
	*Luego se envian estos datos al modelo para que se realice la actualización en la base de datos. A partir de esto se recibe un -1 en caso de error
	*o un 1 en caso de transacción exitosa, por lo que de acuerdo a esta respuesta se muestra la vista de acción realizada o acción no realizada 
	*
	*
	*/


    public function EditarProfesores() // Modifica profesor
    {

		//SE DEBE COMPROBAR RUT ANTES O HAY UN PROBLEMA DE SEGURIDAD

		if ($_SERVER['REQUEST_METHOD'] == 'POST'){
			$this->load->model('Model_profesor');
			$run_profe = $this->input->post("run_profe");
			$nombre_1 = $this->input->post("nombre_1");
			$nombre_2 = $this->input->post("nombre_2");
			$apellidoPaterno_profe= $this->input->post("apellidoPaterno_profe");
			$apellidoMaterno_profe= $this->input->post("apellidoMaterno_profe");
	        $correo1 = $this->input->post("correo1");
			$correo2 = $this->input->post("correo2");
			$telefono_profe = $this->input->post("telefono_profe");
     
			$resetearPass = $this->input->post('resetContrasegna');
			$tipo_profe = $this->input->post("tipo_profesor");
			

			if($resetearPass){
				$this->load->model('model_coordinadores');
				$this->model_coordinadores->modificarPassword($run_profe, $run_profe);
			}
			$confirmacion = $this->Model_profesor->EditarProfesor($run_profe,$telefono_profe,$tipo_profe, $nombre_1, $nombre_2, $apellidoPaterno_profe,$apellidoMaterno_profe,$correo1,$correo2);
			
			
			if ($confirmacion==1){
			$datos_plantilla["titulo_msj"] = "Acción Realizada";
			$datos_plantilla["cuerpo_msj"] = "El profesor fue editado con éxito.";
			$datos_plantilla["tipo_msj"] = "alert-success";
			}
			else{
				$datos_plantilla["titulo_msj"] = "Acción No Realizada";
				$datos_plantilla["cuerpo_msj"] = "Ha ocurrido un error mientras se actualizaban los datos del profesor";
				$datos_plantilla["tipo_msj"] = "alert-error";	
			}
			$datos_plantilla["redirecTo"] = 'Profesores/editarProfesores';
			$datos_plantilla["nombre_redirecTo"] = "Editar profesores";
			$datos_plantilla["redirectAuto"] = TRUE;
			$tipos_usuarios_permitidos = array(); $tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
			$this->cargarMsjLogueado($datos_plantilla, $tipos_usuarios_permitidos);	
		}
		else {
			$datos_plantilla = array();
			$subMenuLateralAbierto = 'editarProfesores'; //Para este ejemplo, los informes no tienen submenu lateral
			$muestraBarraProgreso = FALSE; //Indica si se muestra la barra que dice anterior - siguiente
			$tipos_usuarios_permitidos = array();
			$tipos_usuarios_permitidos[0] = TIPO_USR_COORDINADOR;
			$this->cargarTodo("Docentes", "cuerpo_profesores_editarProfesor", "barra_lateral_profesores", $datos_plantilla, $tipos_usuarios_permitidos, $subMenuLateralAbierto, $muestraBarraProgreso);

		}
		//
    }


	
	public function index() //Esto hace que el index sea la vista que se desee
	{
		$this->verProfesores();
	}
    
 
    
}

/* End of file Profesores.php */
/* Location: ./application/controllers/Profesores.php */


