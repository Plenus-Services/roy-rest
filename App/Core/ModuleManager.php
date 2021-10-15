<?php

/**
 * (EN) Class in charge of managing the microkernel architecture and the framework's own format.
 * (ES) Clase encargada de gestionar y lar arquitectura microkernel y el formato propio del framework.
 * @package App
 * @subpackage Core
 * @author Juan Bautista <soyjuanbautista0@gmail.com>
 * 
 */

namespace App\Core;


class ModuleManager
{

    const EXTENSION_PLUGIN  = ".php";
    const RUTA_PLUGINS      = "Modules";

    public $_yo;
    public $_string;
    public $_url;
    public $_pluginName;
    public $_icon;
    public $_autor;
    public $_description;
    public $pluginListFolder;

    public $plugins_path;
    public $list_plugins;


    public $instance;

    public $details;


    public function __construct(string $instance = '')
    {
        $this->plugins_path =  $instance;
        $this->list_plugins = $this->GetExistingPlugins();
    }




    private function GetExistingPlugins(): array
    {
        $folder = opendir($this->plugins_path);
        $i      = 0;
        while ($file = readdir($folder)) {
            if ($file != "." && $file != ".." && !is_dir($file) && $file != ".htaccess") {
                $plugs[$i++] = str_replace(self::EXTENSION_PLUGIN, "", $file);
            }
            if ($i == 0) {
                //Aun no hay plugins cargados en el sistema
                $plugs = [];
            }
        }
        return $this->PluginDetails($plugs);
    }

    private function PluginDetails(array $plugins): array
    {
        $this->details = [];
        if (count($plugins) > 0) :

            foreach ($plugins as $plugin) {
                if ($plugin != "Views" && file_exists($this->plugins_path . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . "info.json")) {
                    $i =  json_decode(file_get_contents($this->plugins_path . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . "info.json"), true)['detalles'];
                    if (in_array($i["status"], ["active","enable", "activo", "start"]) ) :
                        $this->details[$i["package"]] =  $i;
                    endif;
                }
            }
            return $this->details;
        endif;
        return $this->details;
    }

    public function PluginDetail(string $plugin): array
    {
        $this->details = array();

        if (file_exists($this->plugins_path . $plugin . DIRECTORY_SEPARATOR . 'info.ini')) {
            $i = parse_ini_file($this->plugins_path . $plugin . DIRECTORY_SEPARATOR . 'info.ini');
            if ($i["status"] != "inactivo" && $i["status"] != "inactive") :
                $info = array(
                    'name'            => $i["name"],
                    'icon'            => $i["icon"],
                    'package'         => $i["package"],
                    'main_controller' => $i["main_controller"],
                    'author'          => $i["author"],
                    'desc'            => $i["desc"],
                );
                $this->details['module'] =  $info;
            endif;
        }

        return $this->details;
    }

    /**
     * @access public 
     * (EN) Says if a specific plugin exists
     * (ES) Dice si un plugin específico existe
     * @param string $plugin
     * (EN) PluginName
     * (ES) Nombre del plugin
     */
    public  function PluginExists(string $plugin = "", array $data = [
        'json' => false,
        'bool' => true
    ])
    {
        //print_debug($data);
        if ($plugin != "") {
            $counter = 0;
            foreach ($this->GetExistingPlugins() as $key => $value) {
                if (strtolower($value['name']) === strtolower($plugin) || strtolower($value['package']) === strtolower($plugin)) {
                    $counter++;
                }
            }
            if ($data['json'] == true) {
                if ($_SERVER['REQUEST_METHOD'] == "POST") {
                    if ($counter > 0) {
                        //answers(200, "true");
                    } else {
                        //answers(400, "false");
                    }
                }
            }
            if ($data['bool'] == true) {
                if ($counter > 0) {
                    return true;
                } else {
                    return false;
                }
            }
        }
    }
}
