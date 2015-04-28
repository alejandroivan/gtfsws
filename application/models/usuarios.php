<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Usuarios extends CI_Model
{
    function __construct()
    {
        parent::__construct();
    }
    
    
    
    
    
    
    
    
    function login() // Iniciar sesión con email y password obtenidos desde POST
    {
        // El segundo parámetro define si debe usarse el filtro XSS (para evitar inyecciones SQL)
        $username = $this->input->post( 'username', TRUE );
        $password = $this->input->post( 'password', TRUE );
        
        $user = $this->getUserAuthenticated( $username, $password );
        
        if ( ! $user OR $user->enabled == 0 ) // Usuario no existe o no se encuentra habilitado
            return FALSE;
        else // Todo OK, crear sesión y guardar alguna información para mantenerla activa.
        {
            // Guardar información de sesión
            $this->session->set_userdata( 'user', array(
                'email'     => $user->email,
                'name'      => $user->name
            ));
                
            $this->session->set_userdata( 'logged_in', TRUE );
            
            return TRUE;
        }
    }
    
    
    function logout() // Cerrar sesión
    {
        $this->session->unset_userdata( 'user' );
        $this->session->unset_userdata( 'logged_in' );
        
        return TRUE;
    }
    
    
    
    function loggedIn() // Devuelve TRUE si la sesión se encuentra iniciada
    {
        return $this->session->userdata( 'logged_in' );
    }
    
    function get_email() // Devuelve correo electrónico (identificador de usuario) del usuario que ha iniciado sesión
    {
        $user_data = $this->session->userdata('user'); 
        return isset($user_data['email']) ? $user_data['email'] : NULL ;
    }
    
    function getName() // Devuelve nombre del usuario que ha iniciado sesión
    {
        $user_data = $this->session->userdata('user'); 
        return isset($user_data['name']) ? $user_data['name'] : NULL ;
    }
    
    function isAdmin() // Devuelve TRUE si el usuario que ha iniciado sesión es Administrador (comprueba siempre con BD)
    {
        $user = $this->session->userdata( 'user' );
        
        $email = isset( $user['email'] ) ? $user['email'] : NULL;
        if ( !$email ) return FALSE; // Usuario no logueado
        
        $user_data = $this->db->query( 'SELECT is_admin FROM gtfsws_users WHERE email = ? LIMIT 1', $email )->result();
        return count($user_data) == 1 ? $user_data[0]->is_admin : FALSE;
    }
    
    
    
    private function getUserAuthenticated( $email, $raw_password )
    // Devuelve información de usuario mediante mezcla email/password
    // Sirve para comprobar si la password es correcta
    // Devuelve un array asociativo con los datos del usuario si todo está OK
    // Devuelve NULL si usuario o contraseña son incorrectos o si hay inconsistencia de BD (duplicación de usuario)
    {
        $email          = prepare_string( $email );
        $raw_password   = prepare_string( $raw_password );
        
        if ( strlen(prepare_string($email)) == 0 OR strlen(prepare_string($raw_password)) == 0 )
            return NULL;
        
        $password = md5( $raw_password );
        
        $user_data = $this->db->query( 'SELECT email, name, is_admin, enabled FROM gtfsws_users WHERE email = ? AND password = ? LIMIT ?',
            array(
                $email,
                $password,
                1
            )
        )->result();
        
        // Devuelve NULL si el login fue erróneo
        return count($user_data) == 1 ? $user_data[0] : NULL;
    }
    
    
    function get_repositories()
    {
        return $this->Basedatos->repositories( $this );
    }
}
