<?php

namespace App\Core;

use Exception;
use Throwable;
use SimpleXMLElement;

class Roy
{

    public static function moduleLoader(string $path = ''): void
    {
        spl_autoload_register(function ($className) use ($path) {
            try {

                $path_controller =  DIRECTORY_SEPARATOR . str_replace('\\', '/', $className) .  '.php';
                //Instance by namespace
                $by_namespace = function () use ($className, $path) {
                    //Class
                    $class = explode('\\', $className);
                    //Path file
                    $path_file  =   $path . str_replace('\\', '/',  join("\\", array_unique($class))) .  '.php';
                    //Require
                    (file_exists($path_file)) ? ($className != 'int' ? require_once DIRECTORY_SEPARATOR . $path_file : true) : true;
                };

                $by_statement = function () use ($path_controller) {
                    +require_once $path_controller;
                };

                //Instantiated by the new statement or by namespace
                (file_exists($path_controller)) ? $by_statement() : $by_namespace();
            } catch (Throwable $th) {
                throw new Exception($th->getMessage(), 1);
            }
        });
    }


    /**
     * @param array $data (EN) Array values
     * @param int $code (EN) Http Code
     * @param string $format json, xml
     * @param function $callback
     * @param string $rootName (EN) name data object
     * @return void
     */
    public static function Response(array $data, $code = 200, string  $format = 'json', $callback = false, $rootName = "root")
    {
        function fixArrayKey(&$arr)
        {
            $arr = array_combine(
                array_map(
                    function ($str) {
                        return str_replace(" ", "_", $str);
                    },
                    array_keys($arr)
                ),
                array_values($arr)
            );

            foreach ($arr as $key => $val) {
                if (is_array($val)) {
                    fixArrayKey($arr[$key]);
                }
            }
        }
        $data=  fixArrayKey($data);
        (isset($data['code']) && $code == 200) ? http_response_code($data['code']) : http_response_code($code);


        switch ($format) {
            case 'json':
                header('Content-Type: application/json;charset=utf-8');
                echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                break;
            case 'xml':
                header("Content-type: application/xml");
                //function defination to convert array to xml
                //creating object of SimpleXMLElement
                $document = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8" standalone="no" ?><' . $rootName . '></' . $rootName . '>');

                //function call to convert array to xml
                self::array_to_xml($data, $document);

                //saving generated xml file
                echo $document->asXML();


                /*
                array_walk_recursive($data, array($xml, 'addChild'));
                echo $xml->asXML();*/
                break;

            default:
                print_r($data);
                break;
        }

        ($callback) ? $callback() : true;
        die;
    }


    public static function bugLauncher(string $message = 'Error', int $code = 1): void
    {
        throw new Exception($message, $code);
    }

    private static function array_to_xml($array, &$document)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $document->addChild("$key");
                    self::array_to_xml($value, $subnode);
                } else {
                    $subnode = $document->addChild("item$key");
                    self::array_to_xml($value, $subnode);
                }
            } else {
                $document->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}
