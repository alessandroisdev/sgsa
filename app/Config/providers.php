<?php

return [
    \App\Core\Contracts\ViewInterface::class => \DI\autowire(\App\Core\Strategies\View\PlatesStrategy::class)
];