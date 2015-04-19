<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

class Repositories extends REST_Controller
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
    
    function index_get() // Obtener todos los repositorios disponibles
    {
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $repositories = $this->Basedatos->repositories( $this->Usuarios );
        
        if ( $repositories )
            foreach ( $repositories AS $position => $repository )
            {
                $repository['resource_uri'] = base_url( 'repositories/id/' . $repository['id'] );
                $repositories[$position]    = $repository;
            }
        
        $this->response( $repositories );
    }
    
    
    
    /*
     * Llamadas a: repositories/id/<id_repositorio>
     */
    
    function id_get1( $repository_id ) // GET
    {
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $repository = $this->Basedatos->repository( $this->Usuarios, $repository_id );
        
        if ( $repository )
            $repository[0]->agencies_uri = base_url( 'agencies/repository/' . $repository_id );
        
        $this->response( $repository );
    }
    
    function id_delete( $repository_id ) // DELETE
    {
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $was_deleted    = $this->Basedatos->delete_repository( $repository_id );
        $response       = array();
        
        if ( $was_deleted )
        {
            $response['success']        = TRUE;
            $response['message']        = 'Repository deleted.';
            $response['repository_id']  = $repository_id;
        }
        else
        {
            $response['success']        = FALSE;
            $response['message']        = 'Couldn\'t delete repository.';
            $response['repository_id']  = $repository_id;
        }
        
        $this->response( $response );
    }
    
    function id_get( $repository_id ) // PUT
    {
        die('Put llamado con ID: ' . $repository_id . PHP_EOL);
    }
    
    /*
     * Fin llamadas a: repositories/id/<id_repositorio>
     */
}