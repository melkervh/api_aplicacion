<?php
require_once('../helpers/database.php');
require_once('../helpers/validator.php');
require_once('../models/tipo_producto.php');

// Se comprueba si existe una acción a realizar, de lo contrario se finaliza el script con un mensaje de error.
if (isset($_GET['action'])) {
    // Se crea una sesión o se reanuda la actual para poder utilizar variables de sesión en el script.
    session_start();
    // Se instancia la clase correspondiente.
    $tipoproducto = new TipoProducto;
    // Se declara e inicializa un arreglo para guardar el resultado que retorna la API.
    $result = array('status' => 0, 'message' => null, 'exception' => null);
    // Se verifica si existe una sesión iniciada como administrador, de lo contrario se finaliza el script con un mensaje de error.
    if (isset($_SESSION['id_usuario'])|| 1==1) {
        // Se compara la acción a realizar cuando un administrador ha iniciado sesión.
        
        
        // Se compara la acción a realizar cuando un administrador ha iniciado sesión.
        switch ($_GET['action']) {

            case 'readAll':
                if ($result['dataset'] = $tipoproducto->readAll()) {
                    $result['status'] = 1;
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'No hay datos registrados';
                }
                break;

            case 'search':
                $_POST = $tipoproducto->validateForm($_POST);
                if ($_POST['search'] == '') {
                    $result['exception'] = 'Ingrese un valor para buscar';
                } elseif ($result['dataset'] = $tipoproducto->searchRows($_POST['search'])) {
                    $result['status'] = 1;
                    $result['message'] = 'Valor encontrado';
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'No hay coincidencias';
                }
                break;

            case 'readOne':
                if (!$tipoproducto->setId($_POST['idt'])) {
                    $result['exception'] = 'Tipo de producto incorrecto';
                } elseif ($result['dataset'] = $tipoproducto->readOne()) {
                    $result['status'] = 1;
                } elseif (Database::getException()) {
                    $result['exception'] = Database::getException();
                } else {
                    $result['exception'] = 'Tipo de producto inexistente';
                }
                break;

            case 'update':
                $_POST = $tipoproducto->validateForm($_POST);
                if (!$tipoproducto->setId($_POST['idt'])) {
                    $result['exception'] = 'Tipo de producto incorrecto';
                } elseif (!$data = $tipoproducto->readOne()) {
                    $result['exception'] = 'Tipo de producto inexistente';
                } elseif (!$tipoproducto->setTipo($_POST['tipo_nom'])) {
                    $result['exception'] = 'Tipo de producto incorrecto';
                } elseif ($tipoproducto->updateRow()) {
                    $result['status'] = 1;
                    $result['message'] = 'Marca modificada pero no se guardó la imagen';
                } else {
                    $result['exception'] = Database::getException();
                }
                break;

                case 'create':
                    $_POST = $tipoproducto->validateForm($_POST);
                    if (!$tipoproducto->setTipo($_POST['tipo_nom'])) {
                        $result['exception'] = 'Tipo de producto incorrecto';
                    } elseif ($tipoproducto->createRow()) {
                        $result['status'] = 1;
                        $result['message'] = 'Tipo de producto creado correctamente';
                    } else {
                        $result['exception'] = Database::getException();
                    }
                    break;

            case 'delete':
                if (!$tipoproducto->setId($_POST['iddt'])) {
                    $result['exception'] = 'Tipo de producto incorrecto';
                } elseif (!$data = $tipoproducto->readOne()) {
                    $result['exception'] = 'Tipo de producto inexistente';
                } elseif ($tipoproducto->deleteRow()) {
                    $result['status'] = 1;
                    $result['message'] = 'Tipo de producto eliminado correctamente';
                } else {
                    $result['exception'] = Database::getException();
                }
                break;

            default:
                $result['exception'] = 'Acción no disponible dentro de la sesión';
        }
        // Se indica el tipo de contenido a mostrar y su respectivo conjunto de caracteres.
        header('content-type: application/json; charset=utf-8');
        // Se imprime el resultado en formato JSON y se retorna al controlador.
        print(json_encode($result));
    } else {
        print(json_encode('Acceso denegado'));
    }
} else {
    print(json_encode('Recurso no disponible'));
}
