# Roy REST

REST framework dependency

Install:

<code>composer require plenusservices/roy-rest</code>

Use:

```php
/*
|--------------------------------------------------------------------------
| Register The Auto Loader Composer
|--------------------------------------------------------------------------
|
| (EN) Class loader using composer for the entire application
| (ES) Cargador de clases mediante composer para toda la aplicacion
|
*/

(file_exists(__DIR__.'/../vendor/autoload.php')) ? require __DIR__.'../vendor/autoload.php' : die("🐞");

$modulePath = __DIR__.DIRECTORY_SEPARATOR."Modules".DIRECTORY_SEPARATOR;

$app = new \RoyRest\Start($modulePath, $_ENV);
```
