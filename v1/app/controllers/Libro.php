<?php

class Libro
{
    public $jsonFile = __DIR__ . '/../../data/bbdd.json';

    function __construct()
    {
       // echo "Hola constructor Libro<br>";
    }

    // Leer el contenido del archivo JSON
    public function readJson()
    {
        if (!file_exists($this->jsonFile)) {
            return [];
        }
        $data = file_get_contents($this->jsonFile);
        return json_decode($data, true) ?? [];
    }

    // Escribir datos al archivo JSON
    public function writeJson($data)
    {
        file_put_contents($this->jsonFile, json_encode($data, JSON_PRETTY_PRINT));
    }

    // GET - Obtener todos los libros
    public function getLibros()
    {
        $libros = $this->readJson();
        header("Content-Type: application/json");
        echo json_encode($libros);
    }

    // GET - Obtener un libro por ID
    public function getLibro($id)
    {
        $libros = $this->readJson();
        $libro = array_filter($libros, fn($libro) => $libro['id'] == $id);

        header("Content-Type: application/json");
        if (!empty($libro)) {
            echo json_encode(array_values($libro)[0]);
        } else {
            echo json_encode([
                "error" => "Libro no encontrado",
                "description" => "No se encontró un libro con el ID $id",
                "code" => 404
            ]);
        }
    }

    // POST - Crear un nuevo libro
    public function createLibro($data)
    {
        $libros = $this->readJson();

        // Generar un nuevo ID
        $newId = end($libros)['id'] + 1 ?? 1;
        $data['id'] = $newId;

        // Añadir el nuevo libro
        $libros[] = $data;
        $this->writeJson($libros);

        header("Content-Type: application/json");
        echo json_encode(["message" => "Libro creado con éxito", "libro" => $data]);
    }

    // PUT - Actualizar un libro existente
    public function updateLibro($id, $data)
    {
        $libros = $this->readJson();
        $found = false;
    
        foreach ($libros as &$libro) {
            if ($libro['id'] == $id) {
                // Actualiza los datos del libro con los nuevos valores
                $libro = array_merge($libro, $data);
                $found = true;
                break;
            }
        }
    
        if ($found) {
            $this->writeJson($libros);
            header("Content-Type: application/json");
            echo json_encode(["message" => "Libro actualizado con éxito", "libro" => $libro]);
        } else {
            header("Content-Type: application/json");
            echo json_encode([
                "error" => "Libro no encontrado",
                "description" => "No se encontró un libro con el ID $id para actualizar",
                "code" => 404
            ]);
        }
    }

    // DELETE - Borrar un libro por ID
    public function borrarLibro($id)
    {
        $libros = $this->readJson();
        $filteredLibros = array_filter($libros, fn($libro) => $libro['id'] != $id);

        if (count($libros) != count($filteredLibros)) {
            $this->writeJson(array_values($filteredLibros));
            header("Content-Type: application/json");
            echo json_encode(["message" => "Libro eliminado con éxito"]);
        } else {
            header("Content-Type: application/json");
            echo json_encode([
                "error" => "Libro no encontrado",
                "description" => "No se encontró un libro con el ID $id para eliminar",
                "code" => 404
            ]);
        }
    }
}

?>