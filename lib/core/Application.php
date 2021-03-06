<?php
namespace TestApp\Lib;

use Phlib\Logger;
use Psr\Log\InvalidArgumentException;

class Application
{
    public $config;
    public $logger;
    protected $models = [];
    private $controller;
    private $controllerName;

    public function __construct(array $config)
    {
        $this->config = $config;
        $loggerPool = new Logger\Pool(
            new Logger\Config($this->config['logger']),
            new Logger\Factory()
        );
        $this->logger = $loggerPool->getLogger('default');
        $this->initClasses([
            $this->config['application']['controllers'],
            $this->config['application']['models']
        ]);
    }

    protected function initClasses($dirs)
    {
        if (!is_array($dirs) || !$dirs) {
            throw new InvalidArgumentException('Argument "$dirs" is empty or not is array', [__CLASS__]);
        }
        foreach ($dirs as $dir) {
            $fileSystem = new \FilesystemIterator($dir);
            foreach ($fileSystem as $fileName) {
                $this->connectScript($fileName);
            }
        }
    }

    private function connectScript($path)
    {
        if (file_exists($path)) {
            $this->logger->info('Including ' . $path);
            include_once $path;
        } else {
            $this->logger->critical("File '$path' did not found", [__CLASS__]);
            $this->ApplicationError();
        }
    }

    public function ApplicationError($message = 'Application Error')
    {
        $this->logger->info('Sending code 500 to client');
        http_response_code(500);
        echo $message;
        exit;
    }

    /**
     * Connects the controller and calls the desired method
     *
     * @param $controller controller's name
     * @param $method method's name
     * @param array $args method's arguments
     */
    public function callController($controller, $method, array $args = [__CLASS__])
    {
        $this->controllerName = $controller;

        // instantiate controller
        $className = '\TestApp\Controllers\\' . $this->controllerName;
        if ($this->isValidClass($className)) {
            $this->controller = new $className($this);
        } else {
            $this->ApplicationError();
        }

        // call controller's method and pass arguments
        if (!method_exists($className, $method)) {
            $this->logger->critical("Method '$method' of class '$className' did not found", [__CLASS__]);
            $this->ApplicationError();
        }
        call_user_func_array([$this->controller, $method], $args);
    }

    private function isValidClass($className)
    {
        if (class_exists($className)) {
            return true;
        }
        return false;
    }

    public function isAdmin()
    {
        if (isset($_SESSION['admin'])) {
            return true;
        }
        return false;
    }

    public function setTempData(array $data)
    {
        if (!$data) {
            return;
        }
        foreach ($data as $var => $val) {
            $_SESSION['app_tmp_vars'][$var] = $val;
        }
    }

    public function getTempData()
    {
        if (!isset($_SESSION['app_tmp_vars'])) {
            return;
        }
        if (!is_array($_SESSION['app_tmp_vars'])) {
            $varType = gettype($_SESSION['app_tmp_vars']);
            $this->logger->warning('Session var "app_tmp_vars" is not array, is "' . $varType . '"', [__CLASS__]);
        }

        $data = [];
        foreach ($_SESSION['app_tmp_vars'] as $var => $val) {
            $data[$var] = $val;
        }

        return $data;
    }

    public function clearTempData()
    {
        if (isset($_SESSION['app_tmp_vars'])) {
            unset($_SESSION['app_tmp_vars']);
        }
    }
}
