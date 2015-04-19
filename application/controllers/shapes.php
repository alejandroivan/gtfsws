<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

class Shapes extends REST_Controller
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
    
    
    // ATENCIÓN: Este método puede consumir mucha RAM, ya que son muchísimos registros.
    // PHP 5.4.4 con 128MB de RAM (más que el valor por defecto) no fue capaz de procesar 72000 resultados.
    // Se recomienda utilizar directamente el shape_id: http://<base_url>/shapes/id/<id>
    
    function index_get() // Llamada sin parámetros (NO RECOMENDABLE)
    {
        // Quitar límite de memoria en PHP (por si hay muchos registros)
        ini_set( 'memory_limit', -1 );
        
        $shapes = $this->Basedatos->shapes();
        
        foreach ( $shapes AS $index => $shape )
        {
            $shape['resource_uri']   = base_url( 'shapes/id/' . $shape['shape_id'] );
            $shapes[$index]          = $shape;
        }
        
        $this->response( $shapes );
    }
    
    
    
    function shape_get( $shape_id )
    {
        $shapes = $this->Basedatos->shapes( $shape_id );
        
        foreach ( $shapes AS $index => $shape )
        {
            $shape['resource_uri']   = base_url( 'shapes/id/' . $shape['shape_id'] );
            $shapes[$index]          = $shape;
        }
        
        $this->response( $shapes );
    }
    
    function id_get( $shape_id )
    {
        $shape = $this->Basedatos->shape( $shape_id );
        $this->response( $shape );
    }
    
    function trip_get( $trip_id )
    {
        /*
         * Medir tiempos de ejecución
         */
        $utime_inicio = microtime( TRUE );
        
        $shapes = $this->Basedatos->trip_shapes( $trip_id );
        
        foreach ( $shapes AS $index => $shape )
        {
            $shape['resource_uri']  = base_url( 'shapes/id/' . $shape['shape_id'] );
            $shapes[$index]         = $shape;
        }
        
        /*
         * Fin Medir tiempo de ejecución
         */
        $utime_final = microtime( TRUE );
        $utime_delta = $utime_final - $utime_inicio;
        
        die( var_dump($shapes) . "<br /><br /><b>Tiempos en segundos</b><br /><br />Tiempo de inicio (s): {$utime_inicio} - Tiempo de fin (s): {$utime_final}<br />Tiempo de ejecucion: {$utime_delta} (s)");
        

        $this->response( $shapes );
    }
}
