<?php
/*
|--------------------------------------------------------------------------
| Register The Auto Loader Composer
|--------------------------------------------------------------------------
|
| (EN) Class loader using composer for the entire application
| (ES) Cargador de clases mediante composer para toda la aplicacion
|
*/
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

(file_exists(__DIR__ . '/../vendor/autoload.php')) ? require __DIR__ . '../vendor/autoload.php' : die("🐞");

$app = new \RoyRest\Start(__DIR__ . DIRECTORY_SEPARATOR . "Modules" . DIRECTORY_SEPARATOR, []);
