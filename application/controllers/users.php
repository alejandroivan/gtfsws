<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

class Users extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        
        // Setear consultas máximas por hora por clave de API
        $this->methods['user_get']['limit']     = 500;
        $this->methods['user_post']['limit']    = 100;
        $this->methods['user_delete']['limit']  = 50;
        
        $this->load->helper(array( 'url', 'string' ));
        
        /*
         * Este módulo solo es accesible por administradores
         */
        if ( ! $this->Usuarios->isAdmin() )
            show_error( 'Solo administradores pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
    }






    /*
     * users/
     */
    
    function index_get() // Mostrar lista completa de usuarios del sistema
    {
        $users = $this->Basedatos->users( $this->Usuarios );
        $this->response( $users );
    }
    
    function index_post() // Crear nuevo usuario en el sistema
    {
        $data = array(
            'email'         => $this->post( 'email' ),
            'raw_password'  => $this->post( 'password' ),
            'name'          => $this->post( 'name' ),
            'is_admin'      => $this->post( 'is_admin' ),
            'enabled'       => $this->post( 'enabled' )
        );
        
        $response = array();
        $added = $this->Basedatos->users_new( $this->Usuarios, $data );
        
        if ( $added )
        {
            $response['success']    = TRUE;
            $response['message']    = 'User created successfully.';
            $response['user_email'] = prepare_string( $data['email'] );
        }
        else
        {
            $response['success']    = FALSE;
            $response['message']    = 'Couldn\'t create user.';
            if ( $data['email'] && valid_email( $data['email'] ) ) $response['user_email'] = prepare_string( $data['email'] );
        }
        
        $this->response( $response );
    }
    
    function index_delete() // Eliminar todos los usuarios del sistema (excepto el propio usuario)
    {
        $response   = array();
        $deleted    = FALSE;
        $confirm    = $this->input->get( 'confirm' );
        
        if ( ! $confirm OR $confirm != '1' )
        {
            $response['success']    = FALSE;
            $response['message']    = 'This operation must be confirmed passing "confirm=1" as a GET argument.';
        }
        else
        {
            $deleted        = $this->Basedatos->users_delete( $this->Usuarios );
            
            if ( $deleted )
            {
                $response['success']    = TRUE;
                $response['message']    = 'All users (different than you) have been deleted.';
            }
            else
            {
                $response['success']    = FALSE;
                $response['message']    = 'Couldn\'t delete all users.';
            }
        }
        
        $this->response( $response );
    }
    
    function index_put() // No realiza ninguna acción
    {
        header('HTTP/1.0 ' . HTTP_METHOD_NOT_ALLOWED . ' Method not allowed' );
        echo HTTP_METHOD_NOT_ALLOWED . ' Method not allowed';
        die;
    }
    
    /*
     * FIN: users/
     */
    
    
    
    
    
    
    
    
    
    
    /*
     * users/email/<email_usuario>
     */
    function email_get( $email ) // Obtiene información del usuario especificado
    {
        $user = $this->Basedatos->users_email( $this->Usuarios, $email );
        
        $this->response( $user );
    }
    
    function email_post( $email ) // No realiza ninguna acción
    {
        header('HTTP/1.0 ' . HTTP_METHOD_NOT_ALLOWED . ' Method not allowed' );
        echo HTTP_METHOD_NOT_ALLOWED . ' Method not allowed';
        die;
    }
    
    function email_delete( $email ) // Elimina a un usuario particular
    {
        $response   = array();
        $deleted    = $this->Basedatos->users_email_delete( $this->Usuarios, $email );
        
        if ( $deleted )
        {
            $response['success']    = TRUE;
            $response['message']    = 'The user has been deleted.';
        }
        else
        {
            $response['success']    = FALSE;
            $response['message']    = 'Couldn\'t delete user.';
        }
        
        $this->response( $response );
    }
    
    function email_put( $email ) // Actualiza la información de un usuario particular
    {
        $data = array( // Dejar cualquier campo en blanco para conservar el valor actual
            'raw_password'  => $this->put( 'password' ),
            'name'          => $this->put( 'name' ),
            'is_admin'      => $this->put( 'is_admin' ),
            'enabled'       => $this->put( 'enabled' )
        );
        
        // Limpiar valores no entregados por PUT del array
        foreach( $data AS $key => $value )
            if (
                $data[$key] === NULL
                OR
                strlen( trim( $data[$key] ) ) == 0
            )
                unset( $data[$key] );
            
        $response   = array();
        $updated    = $this->Basedatos->users_email_update( $this->Usuarios, $email, $data );
        
        if ( $updated )
        {
            $response['success']    = TRUE;
            $response['message']    = 'User updated successfully.';
            $response['user_email'] = prepare_email( $email );
        }
        else
        {
            $response['success']    = FALSE;
            $response['message']    = 'Couldn\'t update user.';
            $response['user_email'] = prepare_email( $email );
        }
        
        $this->response( $response );
    }
    /*
     * FIN: users/email/<email_usuario>
     */
}