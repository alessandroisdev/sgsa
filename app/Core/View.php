<?php

namespace App\Core;

use App\Core\Contracts\ViewInterface;

class View implements ViewInterface
{
    public function __construct(
        protected ViewInterface $strategy
    )
    {
    }

    public function setStrategy(ViewInterface $strategy): self
    {
        $this->strategy = $strategy;
        return $this;
    }

    public function render(string $view, array $data = []): mixed
    {
        return $this->strategy->render($view, $data);
    }
}