<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Basedatos extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper(array( 'string', 'email' ));
    }
    
    /*
     * REPOSITORIOS
     */
    function repositories( $user )
    {
        /*
         * La consulta SQL debe ser de la forma:
         * 
         * SELECT id, name
         * FROM gtfsws_repositories
         * INNER JOIN gtfsws_repository_users ON gtfsws_repositories.id = gtfsws_repository_users.repository_id
         * WHERE gtfsws_repository_users.user_email = '{$username}'
         * ORDER BY name ASC
         */
        if ( ! $user || ! $user->loggedIn() ) // Se requiere un usuario
            return NULL;
        
        if ( ! $user->isAdmin() ) // Si no es Administrador, filtrar a los repositorios asociados (si existen)
        {
            $username = prepare_email( $user->get_email() );
            $this->db->join(
                'gtfsws_repository_users',
                'gtfsws_repositories.id = gtfsws_repository_users.repository_id',
                'inner'
            );
            $this->db->where( 'gtfsws_repository_users.user_email', $username );
        }
        
        $this->db->select( '*' );
        $this->db->from( 'gtfsws_repositories' );
        $this->db->order_by( 'name', 'asc' );
        
        return $this->db->get()->result_array();
    }
    
    function repository( $user, $repository_id )
    {
        /*
         * La consulta SQL debe ser de la forma:
         * 
         * SELECT id, name, description, start_position_lat, start_position
         * FROM gtfsws_repositories
         * INNER JOIN gtfsws_repository_users ON gtfsws_repositories.id = gtfsws_repository_users.repository_id
         * WHERE gtfsws_repository_users.user_email = '{$username}'
         * AND gtfsws_repository_users.repository_id = '{$repository_id}'
         * ORDER BY name ASC
         */
        
        if ( !$user ) // Se requiere un usuario
            return NULL;
        
        $repository_id      = prepare_string( $repository_id );
        $username           = prepare_email( $user->get_email() );
        $where_conditions   = array( // Condiciones en el where (array para poder agregar más)
            'gtfsws_repositories.id' => $repository_id
        );
        
        if ( !$user->isAdmin() ) // Si no es Admin, restringir a sus repositorios asociados (gtfsws_repository_users)
        {        
            $this->db->join( // Inner join para ver solo matches entre ambas tablas
                'gtfsws_repository_users',
                'gtfsws_repositories.id = gtfsws_repository_users.repository_id',
                'inner'
            );
            
            /*
             * Agregar restricción de repositorios al usuario
             * (No debería ser necesario dada la condición del inner join, pero por si acaso...)
             */
            $where_conditions['gtfsws_repository_users.user_email'] = $username;
        }
        
        $this->db->select( 'id, name, description, start_position_lat, start_position_lon' );
        $this->db->from( 'gtfsws_repositories' );
        $this->db->where($where_conditions);

        return $this->db->get()->result();
    }

    function delete_repository( $user, $repository_id )
    {
        $repository_id  = prepare_string( $repository_id );
        $success        = FALSE;
        
        if (
            $this->Basedatos->repository_ownership( $user, $repository_id )
        )
        {
            $this->db->where( 'id', $repository_id );
            $success = $this->db->delete( 'gtfsws_repositories' );
        }
        
        
        return ( $success && $this->db->affected_rows() == 1 );
    }
    
    function update_repository( $user, $repository_id, $data )
    {
        $repository_id  = prepare_string( $repository_id );
        $success        = FALSE;
        
        if (
            $this->Basedatos->repository_ownership( $user, $repository_id )
        )
        {
            $this->db->where( 'id', $repository_id );
            $success = $this->db->update( 'gtfsws_repositories', $data );
        }
        
        return ( $success && $this->db->affected_rows() == 1 );
    }
    
    function repository_ownership( $user, $repository_id )
    {
        if ( $user->isAdmin() )
            return TRUE;
        
        $repository_id  = prepare_string( $repository_id );
        $username       = prepare_email( $user->get_email() );
        
        $this->db->select( 'user_email, role' );
        $this->db->from( 'gtfsws_repository_users' );
        $this->db->where(array(
            'user_email'    => $username,
            'repository_id' => $repository_id
        ));
        
        $result = $this->db->get()->result();
        
        return ( count($result) == 1 );
    }
    
    public function repository_exists( $repo_id )
    {
        if ( ! $repo_id )
            return FALSE;
        
        $repo_id = prepare_string( $repo_id );
        
        $this->db->select( 'COUNT(id) AS num' );
        $this->db->from( 'gtfsws_repositories' );
        $this->db->where( 'id', $repo_id );
        
        $result = $this->db->get()->result();
        
        return ( $result[0]->num == 1 );
    }
    
    public function repository_associates( $user, $repo_id )
    {
        if (
            ! $user->isAdmin()
            OR
            ! $repo_id
            OR
            ! ( is_numeric( $repo_id ) && intval( $repo_id ) > 0 )
        )
            return FALSE;
        
        $repo_id = prepare_string( $repo_id );
        
        return $this->db->query("
            SELECT
                email,
                name,
                is_admin,
                enabled,
                NULLIF( MIN( COALESCE( role, 0 ) ), 0 ) AS role
            FROM
            (
                (
                    SELECT DISTINCT
                        gtfsws_users.email AS email,
                        gtfsws_users.name AS name,
                        gtfsws_users.is_admin AS is_admin,
                        gtfsws_users.enabled AS enabled,
                        gtfsws_repository_users.role AS role
                    FROM
                        gtfsws_users
                    INNER JOIN
                        gtfsws_repository_users
                    ON
                        gtfsws_users.email = gtfsws_repository_users.user_email
                    WHERE
                        gtfsws_repository_users.repository_id = '{$repo_id}'
                )
                UNION
                (
                    SELECT
                        email,
                        name,
                        is_admin,
                        enabled,
                        null AS role
                    FROM
                        gtfsws_users
                    WHERE
                        is_admin = 1
                )
            ) AS U
            GROUP BY
                U.email
        ")->result();
    }
    
    public function repository_associates_add( $user, $repo_id, $email, $role )
    {
        // Comprobación de datos
        if (
            ! $user->isAdmin()
            OR
            ! $repo_id
            OR
            ! $email
            OR
            ! $role
            OR
            ! ( is_numeric( $repo_id ) && intval( $repo_id ) > 0 )
            OR
            ! valid_email( $email )
            OR
            ! ( is_numeric( $role ) && intval( $role ) > 0 )
        )
            return FALSE;
        
        $email = prepare_email( $email );
        
        $data = array(
            'user_email'    => $email,
            'repository_id' => $repo_id,
            'role'          => $role
        );
        
        $this->db->set( $data );
        $this->db->insert( 'gtfsws_repository_users' );
        
        return ( $this->db->affected_rows() == 1 );
    }
    
    public function repository_associates_update( $user, $repo_id, $email, $role )
    {
        // Comprobación de datos
        if (
            ! $user->isAdmin()
            OR
            ! $repo_id
            OR
            ! $email
            OR
            ! $role
            OR
            ! ( is_numeric( $repo_id ) && intval( $repo_id ) > 0 )
            OR
            ! valid_email( $email )
            OR
            ! ( is_numeric( $role ) && intval( $role ) > 0 )
        )
            return FALSE;
        
        $email = prepare_email( $email );
        
        $where_conditions = array(
            'user_email'    => $email,
            'repository_id' => $repo_id
        );
        
        $this->db->set( 'role', $role );
        $this->db->where( $where_conditions );
        $this->db->update( 'gtfsws_repository_users' );
        
        return ( $this->db->affected_rows() == 1 );
    }
    
    public function repository_associates_delete( $user, $repo_id, $email )
    {
        // Comprobación de datos
        if (
            ! $user->isAdmin()
            OR
            ! $repo_id
            OR
            ! $email
            OR
            ! ( is_numeric( $repo_id ) && intval( $repo_id ) > 0 )
            OR
            ! valid_email( $email )
        )
            return FALSE;
        
        $email = prepare_email( $email );
        
        $where_conditions = array(
            'user_email'    => $email,
            'repository_id' => $repo_id
        );
        
        $this->db->where( $where_conditions );
        $this->db->delete( 'gtfsws_repository_users' );
        
        return ( $this->db->affected_rows() > 0 );
    }
    /*
     * /REPOSITORIOS
     */
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
     * USUARIOS
     */
    
    public function users( $user )
    {
        if ( ! $user->isAdmin() )
            return FALSE;
        
        $result = array();
        
        $this->db->select( 'email, name, is_admin, enabled' );
        $this->db->from( 'gtfsws_users' );
        $this->db->order_by( 'enabled', 'desc' );
        $this->db->order_by( 'is_admin', 'desc' );
        $this->db->order_by( 'email', 'asc' );
        
        return $this->db->get()->result();
    }
    
    public function users_new( $user, $data )
    {
        if (
            ! $user->isAdmin()
            OR
            ! isset( $data['email'] )
            OR
            ! isset( $data['raw_password'] )
            OR
            ! isset( $data['name'] )
            OR
            ! isset( $data['is_admin'] )
            OR
            ! isset( $data['enabled'] )
            OR
            ! ( valid_email( $data['email'] ) && strlen( trim( $data['email'] ) ) > 0 )
            OR
            strlen( trim( $data['raw_password'] ) ) == 0
            OR
            strlen( trim( $data['name'] ) ) == 0
            OR
            ( $data['is_admin'] != 1 && $data['is_admin'] != 0 )
            OR
            ( $data['enabled'] != 1 && $data['enabled'] != 0 )
        )
            return FALSE;
        
        $insert_data = array(
            'email'     => prepare_email( $data['email'] ),
            'password'  => md5( prepare_string( $data['raw_password'] ) ),
            'name'      => prepare_string( $data['name'] ),
            'is_admin'  => prepare_string( $data['is_admin'] ),
            'enabled'   => prepare_string( $data['enabled'] )
        );
        
        $this->db->set( $insert_data );
        $this->db->insert( 'gtfsws_users' );
        
        return ( $this->db->affected_rows() == 1 );
    }
    
    public function users_delete( $user )
    {
        if ( ! $user->isAdmin() )
            return FALSE;
        
        $this->db->where( 'gtfsws_users.email != ', prepare_email( $user->get_email() ) );
        $this->db->delete( 'gtfsws_users' );
        
        return ( $this->db->affected_rows() > 0 );
    }
    
    public function users_email( $user, $email )
    {
        $email  = prepare_email( $email );
        
        if (
            ! $user->isAdmin()
            OR
            ! $email
            OR
            ! valid_email( $email )
        )
            return FALSE;
        
        $email = prepare_email( $email );
        
        $this->db->select( 'email, name, is_admin, enabled' );
        $this->db->from( 'gtfsws_users' );
        $this->db->where( 'email', $email );
        
        return $this->db->get()->result();
    }
    
    public function users_email_delete( $user, $email )
    {
        $email  = prepare_email( $email );
        
        if (
            ! $user->isAdmin()
            OR
            ! $email
            OR
            ! valid_email( $email )
        )
            return FALSE;
        
        if ( prepare_email( $user->get_email() ) == $email ) // No se puede eliminar a sí mismo
            return FALSE;
        
        $this->db->where( 'email', $email );
        $this->db->delete( 'gtfsws_users' );
        
        return ( $this->db->affected_rows() > 0 );
    }
    
    public function users_email_update( $user, $email, $data )
    {
        $email = prepare_email( $email );
        
        if (
            ! $user->isAdmin()
            OR
            ( isset( $data['raw_password'] ) && strlen( trim( $data['raw_password'] ) ) == 0 )
            OR
            ( isset( $data['name'] ) && strlen( trim( $data['name'] ) ) == 0 )
            OR
            ( // Los siguientes valores no son modificables para sí mismo
                prepare_email( $user->get_email() ) == $email
                &&
                ( $data['is_admin'] !== NULL OR $data['enabled'] !== NULL )
            )
        )
            return FALSE;
        
        $this->db->where( 'email', $email );
        $updated = $this->db->update( 'gtfsws_users', $data );
        
        return ( $updated && $this->db->affected_rows() == 1 );
    }
    
    /*
     * /USUARIOS
     */
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
     * AGENCIAS
     */
    function agencies( $user, $repository_id = NULL )
    {
        if ( !$user ) // Se requiere un usuario
            return NULL;
        
        $repository_id      = prepare_string( $repository_id );
        $username           = prepare_email( $user->get_email() );
        $where_conditions   = array( // Condiciones en el where (array para poder agregar más)
            'gtfsws_repository_id' => $repository_id
        );
        
        if ( !$user->isAdmin() ) // Si no es administrador, solo selecciona desde repositorios asociados a él
        {
            $this->db->join(
                'gtfsws_repository_users',
                'agency.gtfsws_repository_id = gtfsws_repository_users.repository_id',
                'inner'
            );
            
            /*
             * Agregar restricción de repositorios al usuario
             * (No debería ser necesario dada la condición del inner join, pero por si acaso...)
             */
            $where_conditions['gtfsws_repository_users.user_email'] = $username;
        }
        
        $this->db->select( 'gtfsws_repository_id AS repository_id, agency_id, agency_name' );
        $this->db->from( 'agency' );
        
        if ( $repository_id )
            $this->db->where( 'gtfsws_repository_id', $repository_id );
        
        $this->db->order_by('
            repository_id   asc,
            agency_name     asc,
            agency_id       asc
        ');
        
        return $this->db->get()->result_array();
    }
    
    function agency( $user, $agency_id )
    {
        if ( !$user ) // Se requiere un usuario
            return NULL;
        
        $agency_id          = prepare_string( $agency_id );
        $where_conditions   = array( // Condiciones en el where (array para poder agregar más)
            'agency_id' => $agency_id
        );
        
        if ( !$user->isAdmin() )
        {
            $username = $user->get_email();
    
            $this->db->join(
                'gtfsws_repository_users',
                'agency.gtfsws_repository_id = gtfsws_repository_users.repository_id',
                'inner'
            );
            
            /*
             * Agregar restricción de repositorios al usuario
             * (No debería ser necesario dada la condición del inner join, pero por si acaso...)
             */
            $where_conditions['gtfsws_repository_users.user_email'] = $username;
        }
        
        $this->db->select( 'gtfsws_repository_id AS repository_id, agency_id, agency_name, agency_url, agency_timezone, agency_lang, agency_phone, agency_fare_url' );
        $this->db->from( 'agency' );
        $this->db->where( $where_conditions );
        $this->db->order_by( 'agency_id', 'asc' );
        
        return $this->db->get()->result();
    }
    /*
     * /AGENCIAS
     */
    
    
    
    /*
     * ROUTES
     */
    function routes( $agency_id = NULL )
    {
        $this->db->select( 'gtfsws_repository_id AS repository_id, route_id, agency_id, route_short_name' );
        $this->db->from( 'routes' );
        
        if ( $agency_id )
            $this->db->where( 'agency_id', $agency_id );
        
        $this->db->order_by('
            repository_id       asc,
            route_short_name    asc,
            route_id            asc
        ');
        
        return $this->db->get()->result_array();
    }
    
    function route( $route_id )
    {
        $this->db->select( 'gtfsws_repository_id AS repository_id, route_id, agency_id, route_short_name, route_long_name, route_desc, route_type, route_url, route_color, route_text_color' );
        $this->db->from( 'routes' );
        $this->db->where( 'route_id', $route_id );
        $this->db->order_by( 'route_id', 'asc' );
        
        return $this->db->get()->result();
    }
    /*
     * /ROUTES
     */
    
    
    
    /*
     * TRIPS
     */
    function trips( $route_id = NULL )
    {
        $this->db->select( 'gtfsws_repository_id AS repository_id, route_id, service_id, trip_id, trip_headsign, trip_short_name' );
        $this->db->from( 'trips' );
        
        if ( $route_id )
            $this->db->where( 'route_id', $route_id );
        
        $this->db->order_by('
            repository_id       asc,
            route_id            asc,
            trip_headsign       asc,
            trip_short_name     asc,
            trip_id             asc
        ');
        
        return $this->db->get()->result_array();
    }
    
    function trip( $trip_id )
    {
        $this->db->select( 'gtfsws_repository_id AS repository_id, route_id, service_id, trip_id, trip_headsign, trip_short_name, direction_id, block_id, shape_id, wheelchair_accessible' );
        $this->db->from( 'trips' );
        $this->db->where( 'trip_id', $trip_id );
        $this->db->order_by( 'trip_id', 'asc' );
        
        return $this->db->get()->result();
    }
    /*
     * /TRIPS
     */
     
    
    /*
     * SHAPES
     */
    
    function shapes()
    {
        $this->db->select( 'gtfsws_repository_id AS repository_id, shape_id, shape_pt_lat, shape_pt_lon, shape_pt_sequence, shape_dist_traveled' );
        $this->db->from( 'shapes' );
        
        $this->db->order_by('
            repository_id       asc,
            shape_id            asc,
            shape_pt_sequence   asc,
            shape_dist_traveled asc
        ');

        return $this->db->get()->result_array();
    }
    
    function trip_shapes( $trip_id )
    {
        $this->db->select( 'shapes.gtfsws_repository_id AS repository_id, shapes.shape_id AS shape_id, shape_pt_lat, shape_pt_lon, shape_pt_sequence, shape_dist_traveled' );
        $this->db->from( 'shapes' );
        
        $this->db->join(
            'trips',
            'shapes.shape_id = trips.shape_id',
            'inner'
        );
        
        $this->db->where( 'trips.trip_id', $trip_id );
        
        $this->db->order_by('
            repository_id       asc,
            shape_id            asc,
            shape_pt_sequence   asc,
            shape_dist_traveled asc
        ');
        
        return $this->db->get()->result_array();
    }
    /*
     * /SHAPES
     */
}