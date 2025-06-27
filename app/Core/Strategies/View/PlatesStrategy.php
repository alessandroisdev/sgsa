<?php

namespace App\Core\Strategies\View;

use App\Core\Contracts\ViewInterface;
use League\Plates\Engine;

class PlatesStrategy implements ViewInterface
{
    private Engine $engine;

    public function __construct()
    {
        $this->engine = new Engine(DIR_VIEWS);
    }

    /**
     * Renderiza uma view com dados
     * @param string $view nome do arquivo sem extensão
     * @param array $data dados para a view
     * @return string
     */
    public function render(string $view, array $data = []): string
    {
        return $this->engine->render($view, $data);
    }
}