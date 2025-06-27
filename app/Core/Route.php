<?php

namespace App\Core;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Route
{
    public string $method;
    public string $path;

    public function __construct(string $method, string $path)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
    }
}