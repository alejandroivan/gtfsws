<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . '/libraries/REST_Controller.php';

class Logout extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
    }
    
    function index_get() // Cerrar sesión inmediatamente si existe y redirigir al home
    {
        // Limpiar variables de sesión
        $this->Usuarios->logout();
        
        $this->response(array( // Sesión cerrada
            'status'    => 'ok',
            'message'   => 'Session closed.'
        ));
    }
}