<?php

require '../core/Router.php';
require '../app/controllers/Libro.php';

$url = str_replace('/v1/public', '', $_SERVER['QUERY_STRING']);
//echo 'URL = ' .$url. '<br>';

//RUTAS

$router = new Router();

$router->add('/', array(
        'controller' => 'Home',
        'action' => 'index'
    ));

//GET ALL
$router->add('/libro/get', array(
    'controller' => 'Libro',
    'action' => 'getLibros'
));

//GET BY ID
$router->add('/libro/get/{id}', array(
    'controller' => 'Libro',
    'action' => 'getLibro'
));

//CREATE
$router->add('/libro/create', array(
    'controller' => 'Libro',
    'action' => 'createLibro'
));

//UPDATE
$router->add('/libro/update/{id}', array(
    'controller' => 'Libro',
    'action' => 'updateLibro'
));

//DELETE
$router->add('/libro/delete/{id}', array(
    'controller' => 'Libro',
    'action' => 'borrarLibro'
));



/*echo '<pre>';
print_r($router->getRoutes()) .'<br>';
echo '</pre>';*/


$urlParams = explode('/', $url);

$urlArray = array(
    'HTTP'=>$_SERVER['REQUEST_METHOD'],
    'path' =>$url,
    'controller'=>'',
    'action'=>'',
    'params'=>''
);

if(!empty($urlParams[1])){
    $urlArray['controller'] = ucwords($urlParams[1]);
    if(!empty($urlParams[2])){
        $urlArray['action'] = $urlParams[2];
        if(!empty($urlParams[3])){
            $urlArray['params'] = $urlParams[3];
        }
    }else{
        $urlArray['action']='index';
    }
}else{
    $urlArray['controller'] = 'Home';
    $urlArray['action'] = 'index';
}

/*echo '<pre>';
print_r($urlArray) .'<br>';
echo '</pre>';
echo '<pre>';
print_r($urlParams) .'<br>';
echo '</pre>';*/

//Método utilizado
$method = $_SERVER['REQUEST_METHOD'];

$params = [];

if ($method === 'GET') {
    $params[]=intval($urlArray['params']) ??null;
   // echo var_dump($params);

}   elseif ($method === 'POST') {
    $json = file_get_contents('php://input');
    $params = json_decode($json, true);

}   elseif($method === 'PUT') {
    $id = intval($urlArray['params']);
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    $params = [$id, $data];

}   elseif($method === 'DELETE') {
    $params[]= intval($urlArray['params']) ??null;
}


/*
echo '<pre>';
print_r($params);
echo '</pre>';
*/


try {
if ($router->match(['path' => $url])) {
    $routeParams = $router->getParams();

/*echo '<pre>';
print_r($params);
echo '</pre>';*/

    if (!empty($routeParams['controller']) && !empty($routeParams['action'])) {
        $controllerName = $routeParams['controller'];
        $actionName = $routeParams['action'];

/*echo '<pre>';
print_r($params);
echo '</pre>';*/

        // Verificar que la clase del controlador exista
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
/*echo '<pre>';
print_r($controller);
echo '</pre>';*/

            // Verificar que el método del controlador exista
            if (method_exists($controller, $actionName)) {
                if ($method === 'POST') {
                    $controller->$actionName($params);
                } elseif($method === 'PUT') {
                    $controller->$actionName($params[0], $params[1]);
                } else {
                    $controller->$actionName($urlArray['params']); // Llama al método del controlador
                }
/*echo '<pre>';
print_r($controller->$actionName());
echo '</pre>';*/
            } else {
                throw new Exception("Método no encontrado: $actionName", 404);
            }
        } else {
            throw new Exception("Controlador no encontrado: $controllerName", 404);
        }
    } else {
        throw new Exception("Parámetros inválidos para la ruta", 400);
    }
} else {
    throw new Exception("Endpoint no disponible", 404);
}

} catch (Exception $e) {
    header("Content-Type: application/json");
    $code = $e->getCode() ?: 500;

    $description = match ($code) {
        404 => match ($e->getMessage()) {
            "Endpoint no disponible" => "La ruta solicitada no existe en la API.",
            default => "El recurso solicitado no pudo ser encontrado."
        },
        400 => "La solicitud enviada no es válida. Verifique los parámetros.",
        default => "Ocurrió un error interno del servidor. Inténtalo nuevamente más tarde."
    };

    http_response_code($code);
    echo json_encode([
        "error" => $e->getMessage(),
        "description" => $description,
        "code" => $code
    ]);
}

/*echo '<pre>';
print_r($urlArray) .'<br>';
echo '</pre>';*/
?>