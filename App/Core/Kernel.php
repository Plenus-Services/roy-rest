<?php

namespace App\Core;

/**
 * Core Class
 * (EN) It is in charge of executing the framework's mvc pattern and routing drivers from the application directory.
 * (ES) Se encarga de ejecutar el patron mvc del framework y el enrutamiento de controladores del directorio de aplicación.
 *
 * @author Juan Bautista <soyjuanbautista0@gmail.com>
 */

use Exception;
use Throwable;
use App\Core\Roy;


class Kernel
{
    /**
     *
     * @var string
     * (EN) Current controller for the application during execution.
     * (ES) Define el controlador actual de la aplicación durante la ejecución.
     */
    protected $currentController = 'roy';

    /**
     *
     * @var string
     * (EN) Current method for the application during execution.
     * (ES) Define el método actual de la aplicación durante la ejecución.
     */
    protected $currentMethod = 'index';

    /**
     *
     * @var array
     * (EN) url parameters.
     * (ES) parámetros url.
     */
    protected $parameters        = [];

    /**
     * @var object
     * (EN) Instance Profile
     * (ES) Perfil de Instancia
     */
    protected $profile;

    /**
     * @var object 
     * (EN) Session
     * (ES) Session
     */
    protected $access;

    /**
     * @var int
     */
    protected $httpCode;
    /**
     * @var string
     */
    protected $httpMessage;

    public function __construct(string $modulePath, array $environmentConfig)
    {
        $this->currentController = (isset($environmentConfig['APP_CONTROLLER'])) ? $environmentConfig['APP_CONTROLLER'] : $this->currentController;
        $this->currentMethod = (isset($environmentConfig['APP_INDEX'])) ? $environmentConfig['APP_INDEX'] : $this->currentMethod;
        try {
            //$this->errorTry();

            $url = $this->getUrl();
            unset($_GET['url']);

            if (file_exists(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . ucwords($url[0]) . '.php')) {

                $this->currentController = ucwords($url[0]);
                unset($url[0]);


                if (file_exists(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . ucwords($this->currentController) . '.php')) {
                    require_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'Controllers' . DIRECTORY_SEPARATOR . ucwords($this->currentController) . '.php';
                    $this->currentController = new $this->currentController;
                }

                if (isset($url[1])) {
                    if (method_exists($this->currentController, $url[1])) {

                        $this->currentMethod = $url[1];
                        unset($url[1]);
                    }
                }
                $this->parameters = $url ? array_values($url) : [];

                call_user_func_array([$this->currentController, $this->currentMethod], $this->parameters);
            } else {

                //Module
                $this->module = (isset($url[0]) && !empty($url[0])) ? ucwords($url[0]) : null;
                //Controller
                $this->controller = (isset($url[1]) && !empty($url[1])) ? ucwords($url[1]) : null;
                //Method
                $this->method = (isset($url[2]) && !empty($url[2])) ? $url[2] : 'index';
                //Param
                $this->param = (isset($url[3]) && !empty($url[3])) ? $url[3] : null;

                $this->manager = new ModuleManager($modulePath);

                if (file_exists($this->manager->plugins_path . $this->module . DIRECTORY_SEPARATOR . "Controllers" . DIRECTORY_SEPARATOR . $this->controller . ".php")) {
                    require_once $this->manager->plugins_path . $this->module . DIRECTORY_SEPARATOR . "Controllers" . DIRECTORY_SEPARATOR . $this->controller . ".php";

                    Roy::moduleLoader($this->manager->plugins_path . $this->module . DIRECTORY_SEPARATOR);

                    $this->defaultMethod = ($this->manager->list_plugins[$this->module]['default_method'] != NULL) ?$this->manager->list_plugins[$this->module]['default_method'] : 'index';
                    $this->controller              = str_replace('/', '\\',  "Modules/" . $this->module . DIRECTORY_SEPARATOR . "Controllers" . DIRECTORY_SEPARATOR . $this->controller);
                    $this->controller              = new $this->controller();
                    $this->controller->pathModule = $this->manager->plugins_path . $this->module . DIRECTORY_SEPARATOR;
                    $this->controller->module      = $this->module;

                    // (EN) clean up and url to just use the parameters
                    // (ES) limpiar y url para solo usar los párametros
                    for ($i = 0; $i < 3; $i++) unset($url[$i]);
                    $this->param = $url ? array_values($url) : [];

                    (method_exists($this->controller, $this->method)) ? call_user_func_array(array($this->controller, $this->method), $this->param) : Roy::bugLauncher('Controller Not Found');
                } else {
                    $this->httpCode = 404;
                    $this->httpMessage = 'Not Found';
                    Roy::bugLauncher('HTTP 404 Not Found', 1);
                }
            }
        } catch (Throwable $th) {
            Roy::Response([
                'code'       => $this->httpCode ?? 500,
                'data'          => [
                    'message'    => $this->httpMessage ?? "Server error",
                    'error'      => $th->getMessage(),
                    'line' => $th->getLine(),
                    'file' => explode(".", basename($th->getFile()))[0],
                ]
            ], $this->httpCode);
        }
    }

    /**
     * getUrl
     * (EN) This method is responsible for mapping the url variable and setting its values ​​in an array.
     * (ES) Este método se encarga de mapear la variable url y setear sus valores en un arreglo.
     * @access public
     * @return array
     */
    public function getUrl(): array
    {

        if (isset($_GET['url'])) {
            // (EN) We clean the spaces that are to the right of the url.
            // (ES) Limpiamos los espacios que estan a la derecha de la url.
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        } else {
            $url = urldecode(
                parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)
            );

            $url = rtrim($url, '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            $url = array_values($url);
            unset($url[0]);
            $url = array_values($url);
            if (empty($url)) {

                $url[0] = $_ENV['APP_CONTROLLER'];
            }
        }
        return array_values($url);
    }

    /**
     *hostUri()
     * (EN) This method verifies the environment server.
     * (ES) Este método Verifica el servidor de entorno.
     * @access public 
     *  
     */
    public function hostUri()
    {
        //(EN) Default url of the application.
        $set = explode("://", $_ENV['APP_URL']);
        if ($set[1] != $_SERVER['HTTP_HOST']) {
            throw new Exception("Your variable for the application url (APP_URL) does not match the execution environment.");
        }
    }

    /**
     * errorTry
     * (EN) This method is responsible for capturing all errors, warnings and news generated during execution.
     * (ES) Este método se encarga de capturar todos los errores, advertencias y noticias generadas durante la ejecución.
     * @access public 
     */
    public function errorTry()
    {
        set_error_handler(function ($errno, $errstr, $errfile, $errline, $errcontext) {
            if (0 === error_reporting()) {
                return false;
            }
            throw new Exception($errstr, 0, $errno, $errfile, $errline);
        });
    }
}
