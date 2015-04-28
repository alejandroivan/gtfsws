<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

class Login extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
    }
    
    function index_post()
    {
        if ( ! $this->Usuarios->loggedIn() ) // Si no se ha iniciado una sesión...
        {
            if ( ! $this->Usuarios->login() )
                show_error(
                    'El correo electrónico y/o contraseña son incorrectos o el usuario no se encuentra habilitado.',
                    HTTP_FORBIDDEN,
                    'Acceso denegado'
                );
            
            else
            {
                $message = array(
                    'status'        => 'ok',
                    'message'       => 'Login successful.',
                    'email'         => $this->Usuarios->get_email(),
                    'is_admin'      => $this->Usuarios->isAdmin()
                );
                
                $this->response( $message );
            }
        }
        else
        {
            header( 'HTTP/1.0 409 Conflict' );
            exit;
        }
        
    }
}