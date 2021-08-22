<?php

namespace RoyRest;

use App\Core\Kernel;

class Start
{
    /**
     * @access public
     * @param string $modulePath
     * (EN) Module path
     * (ES) Ruta de módulos
     * @param array environmentConfig
     * (EN) Environment configuration
     * (ES) Configuración de entorno
     */
    public function __construct(string $modulePath, array $environmentConfig =[])
    {
        return new Kernel($modulePath, $environmentConfig);
    }
}
