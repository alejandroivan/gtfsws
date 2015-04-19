<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

class Agencies extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        
        // Setear consultas máximas por hora por clave de API
        $this->methods['user_get']['limit']     = 500;
        $this->methods['user_post']['limit']    = 100;
        $this->methods['user_delete']['limit']  = 50;
        
        // Modelo de Usuarios se carga automáticamente
        // $this->load->model( 'Usuarios' );
        
        $this->load->helper('url');
    }
    
    function index_get() // Llamada sin parámetros
    {
        // Quitar límite de memoria en PHP (por si hay muchos registros)
        ini_set( 'memory_limit', -1 );
        
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $agencies = $this->Basedatos->agencies( $this->Usuarios );
        
        if ( $agencies )
            foreach ( $agencies AS $index => $agency )
            {
                $agency['resource_uri'] = base_url( 'agencies/id/' . $agency['agency_id'] );
                $agencies[$index]       = $agency;
            }
        
        $this->response( $agencies );
    }
    
    function repository_get( $repository_id = NULL )
    {
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $agencies = $this->Basedatos->agencies( $this->Usuarios, $repository_id );
        
        if ( $agencies )
            foreach ( $agencies AS $index => $agency )
            {
                $agency['resource_uri'] = base_url( 'agencies/id/' . $agency['agency_id'] );
                $agencies[$index]       = $agency;
            }
        
        $this->response( $agencies );
    }
    
    function id_get( $agency_id )
    {
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        /*
         * Medir tiempos de ejecución
         */
        $utime_inicio = microtime( TRUE );
        
        $agencies = $this->Basedatos->agency( $this->Usuarios, $agency_id );

        foreach ( $agencies AS $pos => $agency )
        {
            $agency->routes_uri = base_url( 'routes/agency/' . $agency_id );
            $agencies[$pos]     = $agency;
        }
        
        /*
         * Fin Medir tiempo de ejecución
         */
        $utime_final = microtime( TRUE );
        $utime_delta = $utime_final - $utime_inicio;
        
        die( "<b>Tiempos en segundos</b><br /><br />Tiempo de inicio (s): {$utime_inicio} - Tiempo de fin (s): {$utime_final}<br />Tiempo de ejecución: {$utime_delta} (s)");
        

        $this->response( $agencies );
    }
}
