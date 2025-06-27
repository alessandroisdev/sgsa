<?php

namespace App\Core\Contracts;

interface ViewInterface
{
    public function render(string $view, array $data = []): mixed;
}