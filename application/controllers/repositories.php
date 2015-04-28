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
        
        $this->load->helper(array( 'url', 'string' ));
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
    
    function index_post() // No realiza ninguna acción
    {
        header('HTTP/1.0 ' . HTTP_METHOD_NOT_ALLOWED . ' Method not allowed' );
        echo HTTP_METHOD_NOT_ALLOWED . ' Method not allowed';
        die;
    }
    
    function index_delete() // No realiza ninguna acción
    {
        header('HTTP/1.0 ' . HTTP_METHOD_NOT_ALLOWED . ' Method not allowed' );
        echo HTTP_METHOD_NOT_ALLOWED . ' Method not allowed';
        die;
    }
    
    function index_put() // No realiza ninguna acción
    {
        header('HTTP/1.0 ' . HTTP_METHOD_NOT_ALLOWED . ' Method not allowed' );
        echo HTTP_METHOD_NOT_ALLOWED . ' Method not allowed';
        die;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
     * Llamadas a: repositories/id/<id_repositorio>
     */
    
    function id_get( $repository_id ) // GET
    {
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $repository = $this->Basedatos->repository( $this->Usuarios, $repository_id );
        
        if ( $repository )
            $repository[0]->agencies_uri = base_url( 'agencies/repository/' . $repository_id );
        
        $this->response( $repository );
    }
    
    function id_post( $repository_id ) // No realiza ninguna acción
    {
        header('HTTP/1.0 ' . HTTP_METHOD_NOT_ALLOWED . ' Method not allowed' );
        echo HTTP_METHOD_NOT_ALLOWED . ' Method not allowed';
        die;
    }
    
    function id_delete( $repository_id ) // DELETE
    {
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );

        
        $was_deleted    = $this->Basedatos->delete_repository( $this->Usuarios, $repository_id );
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
    
    function id_put( $repository_id ) // PUT
    {
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios registrados pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );

        // Limpiar ID de repositorio antes de modificar
        $id = prepare_string( $repository_id );
        
        if ( ! $id )
            show_error(
                'The repository with the ID specified doesn\'t exist.',
                HTTP_NOT_FOUND,
                'Repository doesn\'t exist'
            );
        
        // Procesar información a actualizar
        $data = array(
            'name'               => $this->put( 'name' ),
            'description'        => $this->put( 'description' ),
            'start_position_lat' => $this->put( 'start_position_lat' ),
            'start_position_lon' => $this->put( 'start_position_lon' )
        );
        
        if (
            ! $data['name']
            OR
            ! $data['description']
            OR
            ! $data['start_position_lat']
            OR
            ! $data['start_position_lon']
        )
            show_error(
                'Some data is missing in order to perform a repository update.',
                HTTP_BAD_REQUEST,
                'Data not sufficient'
            );
        
        $updated    = $this->Basedatos->update_repository( $this->Usuarios, $id, $data );
        $response   = array();
        
        if ( $updated )
        {
            $response['success']        = TRUE;
            $response['message']        = 'Repository updated.';
            $response['repository_id']  = $id;
        }
        else
        {
            $response['success']        = FALSE;
            $response['message']        = 'Couldn\'t update repository.';
            $response['repository_id']  = $id;
        }
        
        $this->response( $response );
    }
    
    /*
     * Fin llamadas a: repositories/id/<id_repositorio>
     */
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
     
    /*
     * Llamadas a: repositories/associate/<id_repositorio>
     */
    
    function associate_get( $repository_id )
    {
        if ( ! $this->Usuarios->isAdmin() )
            show_error( 'Solo administradores pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $users = $this->Basedatos->repository_associates( $this->Usuarios, $repository_id );
        
        $this->response( $users );
    }
    
    function associate_post( $repo_id )
    {
        if ( ! $this->Usuarios->isAdmin() )
            show_error( 'Solo administradores pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $response   = array();
        $repo_id    = intval( $repo_id );
        $user_email = $this->post( 'email' );
        $user_role  = $this->post( 'role' );
        
        $added      = $this->Basedatos->repository_associates_add( $this->Usuarios, $repo_id, $user_email, $user_role );
        
        if ( $added )
        {
            $response['success']        = TRUE;
            $response['message']        = 'Repository association created successfully.';
            $response['repository_id']  = $repo_id;
        }
        else
        {
            $response['success']        = FALSE;
            $response['message']        = 'Couldn\'t create repository association';
            $response['repository_id']  = $repo_id;
        }
        
        $this->response( $response );
    }
    
    function associate_delete( $repo_id )
    {
        if ( ! $this->Usuarios->isAdmin() )
            show_error( 'Solo administradores pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $response   = array();
        $repo_id    = intval( $repo_id );
        $user_email = $this->input->get( 'email' );
        
        $deleted    = $this->Basedatos->repository_associates_delete( $this->Usuarios, $repo_id, $user_email );
        
        if ( $deleted )
        {
            $response['success']        = TRUE;
            $response['message']        = 'Repository association deleted succesfully.';
            $response['repository_id']  = $repo_id;
        }
        else
        {
            $response['success']        = FALSE;
            $response['message']        = 'Couldn\'t delete repository association.';
            $response['repository_id']  = $repo_id;
        }
        
        $this->response( $response );
    }
    
    function associate_put( $repo_id )
    {
        if ( ! $this->Usuarios->isAdmin() )
            show_error( 'Solo administradores pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        $response   = array();
        $repo_id    = intval( $repo_id );
        $user_email = $this->put( 'email' );
        $user_role  = $this->put( 'role' );
        
        $updated    = $this->Basedatos->repository_associates_update( $this->Usuarios, $repo_id, $user_email, $user_role );
        
        if ( $updated )
        {
            $response['success']        = TRUE;
            $response['message']        = 'Repository association updated succesfully.';
            $response['repository_id']  = $repo_id;
        }
        else
        {
            $response['success']        = FALSE;
            $response['message']        = 'Couldn\'t update repository association.';
            $response['repository_id']  = $repo_id;
        }
        
        $this->response( $response );
    }
    
    /*
     * Fin llamadas a: repositories/associate/<id_repositorio>
     */
    
}