<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        
        // Modelo de Usuarios se carga automáticamente
        // $this->load->model( 'Usuarios' );
    }
    
    function index()
    {
        $this->load->helper( 'url' );
        $this->load->view( 'home/methods' );
    }
}
