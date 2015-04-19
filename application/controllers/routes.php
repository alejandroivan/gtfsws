<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

class Routes extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        
        // Setear consultas máximas por hora por clave de API
        $this->methods['user_get']['limit']     = 500;
        $this->methods['user_post']['limit']    = 100;
        $this->methods['user_delete']['limit']  = 50;
        
        $this->load->helper('url');
    }
    
    function index_get() // Llamada sin parámetros
    {
        // Quitar límite de memoria en PHP (por si hay muchos registros)
        ini_set( 'memory_limit', -1 );
        
        $routes = $this->Basedatos->routes();
        
        foreach ( $routes AS $index => $route )
        {
            $route['resource_uri']  = base_url( 'routes/id/' . $route['route_id'] );
            $routes[$index]         = $route;
        }
        
        $this->response( $routes );
    }
    
    function agency_get( $agency_id )
    {
        /*
         * Medir tiempos de ejecución
         */
        $utime_inicio = microtime( TRUE );
        
        $routes = $this->Basedatos->routes( $agency_id );
        
        foreach ( $routes AS $index => $route )
        {
            $route['resource_uri']  = base_url( 'routes/id/' . $route['route_id'] );
            $routes[$index]         = $route;
        }
        
        /*
         * Fin Medir tiempo de ejecución
         */
        $utime_final = microtime( TRUE );
        $utime_delta = $utime_final - $utime_inicio;
        
        die( var_dump($routes) . "<br /><br /><b>Tiempos en segundos</b><br /><br />Tiempo de inicio (s): {$utime_inicio} - Tiempo de fin (s): {$utime_final}<br />Tiempo de ejecucion: {$utime_delta} (s)");
        
        $this->response( $routes );
    }
    
    function id_get( $route_id )
    {
        $route                  = $this->Basedatos->route( $route_id );
        $route[0]->trips_uri    = base_url( 'trips/route/' . $route_id );
        
        $this->response( $route );
    }
}
