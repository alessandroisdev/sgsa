<?php

namespace App\Core;

use Illuminate\Database\Capsule\Manager as Capsule;
use DI\ContainerBuilder;
use Exception;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Classe principal da aplicação com implementação Singleton.
 * Responsável por carregar diretórios base do sistema.
 */
final class App
{
    /**
     * @var App|null Instância única da classe
     */
    private static ?self $instance = null;

    /**
     * @readonly Caminho raiz do projeto
     */
    private readonly string $rootPath;

    /**
     * Constantes da aplicação
     */
    private const string DIR_PUBLIC = 'public/';
    private const string DIR_APP = 'app/';
    private const string DIR_STORAGE = 'storage/';
    private const string DIR_CACHE = 'storage/_cache/';
    private const string DIR_CONTROLLERS = 'app/Controllers/';
    private const string DIR_CONFIG = 'app/Config/';
    private const string DIR_VIEWS = 'app/Views/';

    /**
     * Construtor privado: inicializa o caminho raiz e carrega as constantes.
     */
    private function __construct()
    {
        $this->rootPath = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR;
        $this->loadConstants();
    }

    /**
     * @throws Exception
     */
    private function init(): void
    {
        $this->setupDatabase();

        $builder = new ContainerBuilder();
        $builder->useAttributes(true);
        $builder->useAutowiring(true);
        $builder->addDefinitions($this->getDefinitions());
        $container = $builder->build();
        $router = new Router($container);
        $router->registerControllers();
        $router->dispatch();
    }

    private function getDefinitions(): array
    {
        $definitionsFile = DIR_CONFIG . 'providers.php';

        if (!is_file($definitionsFile)) {
            file_put_contents($definitionsFile, "<?php\n\nreturn [];");
        }

        return include $definitionsFile;
    }

    public function setupDatabase(): void
    {
        $capsule = new Capsule;

        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => 'localhost',
            'database' => 'nome_do_banco',
            'username' => 'usuario',
            'password' => 'senha',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
        ]);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * @throws Exception
     */
    public static function run(): void
    {
        self::getInstance()->init();
    }

    public static function isProd(): false
    {
        return false;
    }

    public static function handleException(Throwable|Exception $e): void
    {
        if (!App::isProd()) {
            $whoops = new Run;
            $whoops->pushHandler(new PrettyPageHandler);
            $whoops->handleException($e);
            $whoops->register();
        }

        $response = new Response(
            $e->getMessage(),
            ResponseAlias::HTTP_INTERNAL_SERVER_ERROR
        );

        $response->send();
    }

    /**
     * Impede clonagem da instância.
     */
    private function __clone(): void
    {
    }

    /**
     * Impede desserialização da instância.
     */
    private function __wakeup(): void
    {
    }

    /**
     * Retorna a instância única da aplicação.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Define constantes globais da aplicação se ainda não definidas.
     *
     * @return void
     */
    private function loadConstants(): void
    {
        $this->defineIfNotExists('ROOT_DIR', $this->rootPath);
        $this->defineIfNotExists('PUBLIC_DIR', $this->rootPath . self::DIR_PUBLIC);
        $this->defineIfNotExists('APP_DIR', $this->rootPath . self::DIR_APP);
        $this->defineIfNotExists('STORAGE_DIR', $this->rootPath . self::DIR_STORAGE);
        $this->defineIfNotExists('CACHE_DIR', $this->rootPath . self::DIR_CACHE);
        $this->defineIfNotExists('CONTROLLERS_DIR', $this->rootPath . self::DIR_CONTROLLERS);
        $this->defineIfNotExists('DIR_CONFIG', $this->rootPath . self::DIR_CONFIG);
        $this->defineIfNotExists('DIR_VIEWS', $this->rootPath . self::DIR_VIEWS);
    }

    /**
     * Define uma constante somente se ela ainda não estiver definida.
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    private function defineIfNotExists(string $name, string $value): void
    {
        if (!defined($name)) {
            define($name, $value);
        }
    }
}
