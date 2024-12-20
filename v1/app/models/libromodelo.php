<?php

class LibroModelo
{
    private int $id;
    private string $titulo;
    private string $autor;
    private string $resumen;
    private string $unidades;

    public function __construct(int $id, string $titulo, string $autor, string $resumen, string $unidades)
    {
        $this->id = $id;
        $this->titulo = $titulo;
        $this->autor = $autor;
        $this->resumen = $resumen;
        $this->unidades = $unidades;
    }

}

?>