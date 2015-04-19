<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

class Trips extends REST_Controller
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
    
    function index_get() // Obtiene todos los viajes
    {
        // Quitar límite de memoria en PHP (por si hay muchos registros)
        ini_set( 'memory_limit', -1 );
        
        $trips = $this->Basedatos->trips();
        
        foreach ( $trips AS $index => $trip )
        {
            $trip['resource_uri']   = base_url( 'trips/id/' . $trip['trip_id'] );
            $trips[$index]          = $trip;
        }
        
        $this->response( $trips );
    }
    
    function route_get( $route_id ) // Obtiene todos los viajes que pertenecen a una ruta
    {
        // Quitar límite de memoria en PHP (por si hay muchos registros)
        ini_set( 'memory_limit', -1 );
        
        /*
         * Medir tiempos de ejecución
         */
        $utime_inicio = microtime( TRUE );
        
        $trips = $this->Basedatos->trips( $route_id );
        
        foreach ( $trips AS $index => $trip )
        {
            $trip['resource_uri']   = base_url( 'trips/id/' . $trip['trip_id'] );
            $trips[$index]          = $trip;
        }
        
        /*
         * Fin Medir tiempo de ejecución
         */
        $utime_final = microtime( TRUE );
        $utime_delta = $utime_final - $utime_inicio;
        
        die( var_dump($trips) . "<br /><br /><b>Tiempos en segundos</b><br /><br />Tiempo de inicio (s): {$utime_inicio} - Tiempo de fin (s): {$utime_final}<br />Tiempo de ejecucion: {$utime_delta} (s)");
        
        $this->response( $trips );
    }
    
    function id_get( $trip_id ) // Obtiene la información de un viaje particular
    {
        $trip = $this->Basedatos->trip( $trip_id );
        
        $trip[0]->shapes_uri        = base_url( 'shapes/trip/' . $trip_id );
        $trip[0]->calendar_uri      = base_url( 'calendar/trip/' . $trip_id );
        $trip[0]->stop_times_uri    = base_url( 'stop_times/trip/' . $trip_id );
        
        $this->response( $trip );
    }
}
