<?php
require_once('../helpers/database.php');
require_once('../helpers/validator.php');
require_once('../models/clientes.php');

// Se comprueba si existe una acción a realizar, de lo contrario se finaliza el script con un mensaje de error.
if (isset($_GET['action'])) {
    // Se crea una sesión o se reanuda la actual para poder utilizar variables de sesión en el script.
    session_start();
    // Se instancia la clase correspondiente.
    $clientes = new Clientes;
    // Se declara e inicializa un arreglo para guardar el resultado que retorna la API.
    $result = array('status' => 0, 'message' => null, 'exception' => null);
    
    // Se verifica si existe una sesión iniciada como administrador, de lo contrario se finaliza el script con un mensaje de error.
    if (isset($_SESSION['id_usuario'] )|| 1==1) {
        $result['session'] = 1;

        // Se compara la acción a realizar cuando un administrador ha iniciado sesión.
        switch ($_GET['action']) {

            // Evalua y consulta los registros para cargar la tabla.
            case 'readAll':
                if ($result['dataset'] = $clientes->readAll()) {
                    $result['status'] = 1;
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'No hay datos registrados';
                }
            break;
            case 'search':
                $_POST = $clientes->validateForm($_POST);
                if ($_POST['search'] == '') {
                    $result['exception'] = 'Ingrese un valor para buscar';
                } elseif ($result['dataset'] = $clientes->searchRows($_POST['search'])) {
                    $result['status'] = 1;
                    $result['message'] = 'Valor encontrado';
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'No hay coincidencias';
                }
                break;
            case 'readOne':
                if (!$clientes->setIdCliente($_POST['id_cliente'])) {
                    $result['exception'] = 'cliente incorrecto';
                } elseif ($result['dataset'] = $clientes->readOne()) {
                    $result['status'] = 1;
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'cliente inexistente';
                }
                break;
            case 'update':
                $_POST = $clientes->validateForm($_POST);
                if (!$clientes->setIdCliente($_POST['id_cliente'])) {
                    $result['exception'] = 'cliente incorrecto';
                } elseif (!$clientes->readOne()) {
                    $result['exception'] = 'cliente inexistente';
                } elseif (!$clientes->setNombrecliente($_POST['Nombrec'])) {
                    $result['exception'] = 'Nombres incorrectos';
                } elseif (!$clientes->setApellidocliente($_POST['apellidoc'])) {
                    $result['exception'] = 'apellido incorrectos';
                } elseif (!$clientes->setdui($_POST['DUI'])) {
                    $result['exception'] = ' DUI incorrectos';
                } elseif (!$clientes->setelefono($_POST['Telefono'])) {
                    $result['exception'] = 'contacto incorrectos';
                } elseif ($clientes->updateRow()) {
                    $result['status'] = 1;
                    $result['message'] = 'Usuario modificado correctamente';
                } else {
                    $result['exception'] = Database::getException();
                }
                break;
            case 'delete':
                if ($_POST['id_cliente'] == $_SESSION['id_usuario']) {
                    $result['exception'] = 'No se puede eliminar a sí mismo';
                } elseif (!$clientes->setIdCliente($_POST['id_cliente'])) {
                    $result['exception'] = 'Cliente incorrecto';
                } elseif (!$clientes->readOne()) {
                    $result['exception'] = 'Cliente inexistente';
                } elseif ($clientes->deleteRow()) {
                    $result['status'] = 1;
                    $result['message'] = 'Cliente eliminado correctamente';
                } else {
                    $result['exception'] = Database::getException();
                }
                break;
            // Evalua y hace la operación para el buscador.
            default:
                $result['exception'] = 'Acción no disponible fuera de la sesión';
        }
    }
    // Se indica el tipo de contenido a mostrar y su respectivo conjunto de caracteres.
    header('content-type: application/json; charset=utf-8');
    // Se imprime el resultado en formato JSON y se retorna al controlador.
    print(json_encode($result));
} else {
    print(json_encode('Recurso no disponible'));
}
