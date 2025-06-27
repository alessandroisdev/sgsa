<?php

namespace App\Core;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Router
{
    private array $routes = [];

    public function __construct(
        private readonly ?ContainerInterface $container = null
    ) {
    }

    /**
     * Registra controllers e suas rotas via attributes
     */
    public function registerControllers(): void
    {
        foreach ($this->loadControllers() as $controllerClass) {
            $this->registerControllerRoutes($controllerClass);
        }
    }

    private function loadControllers(): array
    {
        $controllers = [];
        $directory = CONTROLLERS_DIR;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = $this->resolveClassFromFile($file->getPathname());

                if (!$className || !class_exists($className)) {
                    continue;
                }

                try {
                    $rc = new ReflectionClass($className);
                    if (!$rc->isAbstract() && !$rc->isInterface()) {
                        $controllers[] = $className;
                    }
                } catch (ReflectionException) {
                    continue;
                }
            }
        }

        return $controllers;
    }

    private function resolveClassFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        $namespace = null;
        $class = null;

        if (preg_match('/^namespace\s+(.+?);/m', $content, $matches)) {
            $namespace = trim($matches[1]);
        }

        if (preg_match('/^class\s+([a-zA-Z0-9_]+)/m', $content, $matches)) {
            $class = trim($matches[1]);
        }

        return $class ? ($namespace ? "$namespace\\$class" : $class) : null;
    }

    private function registerControllerRoutes(string $controllerClass): void
    {
        $rc = new ReflectionClass($controllerClass);
        foreach ($rc->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Route::class);
            foreach ($attributes as $attr) {
                /** @var Route $route */
                $route = $attr->newInstance();
                $this->routes[] = [
                    'method' => $route->method,
                    'path' => rtrim($route->path, '/') ?: '/',
                    'controller' => $controllerClass,
                    'action' => $method->getName(),
                ];
            }
        }
    }

    public function dispatch(): void
    {
        $request = Request::capture();
        $requestMethod = $request->method();
        $requestUri = rtrim($request->getPathInfo(), '/') ?: '/';

        foreach ($this->routes as $route) {
            if ($route['method'] !== $requestMethod) {
                continue;
            }

            if ($route['path'] === $requestUri) {
                $response = $this->invokeAction($route['controller'], $route['action'], $request);

                if (!$response instanceof Response) {
                    $response = new Response(
                        is_string($response) ? $response : json_encode($response),
                        ResponseAlias::HTTP_OK,
                        ['Content-Type' => 'application/json']
                    );
                }

                $response->send();
                return;
            }
        }

        $response = new Response(
            json_encode(['error' => 'Route not found']),
            ResponseAlias::HTTP_NOT_FOUND,
            ['Content-Type' => 'application/json']
        );
        $response->send();
    }

    /**
     * Resolve o controller e seus argumentos via container
     * @throws ReflectionException
     */
    private function invokeAction(string $controllerClass, string $action, Request $request): mixed
    {
        // Resolve a instância do controller
        $controller = $this->container && $this->container->has($controllerClass)
            ? $this->container->get($controllerClass)
            : new $controllerClass();

        $rc = new ReflectionClass($controller);
        $rm = $rc->getMethod($action);

        $args = [];

        foreach ($rm->getParameters() as $param) {
            $paramType = $param->getType();

            if ($paramType && !$paramType->isBuiltin()) {
                $paramClassName = $paramType->getName();

                if ($paramClassName === Request::class) {
                    $args[] = $request;
                } elseif ($this->container && $this->container->has($paramClassName)) {
                    $args[] = $this->container->get($paramClassName);
                } else {
                    $args[] = new $paramClassName(); // fallback inseguro, ideal evitar
                }
            } elseif ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $args[] = null;
            }
        }

        return $rm->invokeArgs($controller, $args);
    }
}
