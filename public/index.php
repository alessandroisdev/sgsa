<?php

require_once __DIR__ . '/../app/Config/bootstrap.php';

use App\Core\App;

try {
    App::run();
} catch (Exception|Throwable $e) {
    App::handleException($e);
}