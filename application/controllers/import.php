<?php defined('BASEPATH') OR exit('No direct script access allowed');


/*
 * ARCHIVOS IMPLEMENTADOS DE GTFS
 * Especificar las entidades (archivos) que se pueden leer (son aceptados) en este sistema.
 * A medida que se vaya implementando más archivos de repositorio, irlos agregando.
 */
$accepted_tables = array(
    'agency',
    'routes',
    'trips',
    'stop_times',
    'stops',
    'shapes',
    'calendar'
);
/*
 * /ARCHIVOS IMPLEMENTADOS DE GTFS
 */

 
 
class Import extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        
        // Admitir acceso solo de Administradores
        if (
            ! $this->Usuarios->loggedIn()
            OR
            ! $this->Usuarios->isAdmin()
        )
            show_error( 'Solo Administradores pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        // Cargar helpers de strings
        $this->load->helper( 'string' ); // Extendido por application/helpers/MY_string_helper.php
        
        // Quitar límite de memoria a utilizad
        ini_set( 'memory_limit', '-1');
        set_time_limit( 0 ); // Tiempo infinito de ejecución del script (no funciona en todos los servidores, ver php.ini)
    }
    
    
    
    
    
    function index()
    {
        $this->load->helper('form');
        $this->load->view('gtfs_uploader');
    }
    
    
    
    
    
    function upload() // Procesa subida del archivo, descompresión y luego los envía a métodos de guardado
    {
        /*
         * Comprobar que se ha subido el archivo ZIP del GTFS
         */
        if (
            ! isset($_FILES['gtfs'])
            OR
            ! isset($_FILES['gtfs']['name'])
            OR
            empty($_FILES['gtfs']['name'])
        )
            show_error( 'Se espera un repositorio GTFS (archivo .zip) en una variable POST llamada \'gtfs\'.', HTTP_EXPECTATION_FAILED, 'Falta archivo .zip' );
        
        /*
         * Comprobar que se ha especificado el ID del repositorio a reemplazar (número entero).
         * Este ID podrá definirse como "NEW" si se quiere crear un nuevo repositorio.
         * Otros ID no numéricos darán error.
         */
        $repository_id                  = $this->input->post( 'repository_id', TRUE );
        $repository_name                = $this->input->post( 'repository_name', TRUE );
        $repository_description         = $this->input->post( 'repository_description', TRUE );
        $repository_start_position_lat  = $this->input->post( 'repository_start_position_lat', TRUE );
        $repository_start_position_lon  = $this->input->post( 'repository_start_position_lon', TRUE );
        $repository_data                = array();
        
        if (
            ! $repository_id
            OR
            ! $repository_name
            OR
            ! ( strlen($repository_name) > 0 )
            OR
            ! $repository_start_position_lat
            OR
            ! is_numeric($repository_start_position_lat)
            OR
            ! $repository_start_position_lon
            OR
            ! is_numeric($repository_start_position_lon)
        )
        {
            if ( $repository_id != 'NEW' ) // No se crea un nuevo repositorio
                show_error( 'All fields are required to create or update a repository.', HTTP_EXPECTATION_FAILED, 'Missing information' );
        }
        
        // Procesar ID del repositorio
        $repository_data['id']                  = ( $repository_id == 'NEW' ) ? NULL : $repository_id;
        $repository_data['name']                = $repository_name;
        $repository_data['description']         = $repository_description;
        $repository_data['start_position_lat']  = $repository_start_position_lat;
        $repository_data['start_position_lon']  = $repository_start_position_lon;
        
        
        /*
         * Todo OK, procesar la información del archivo .zip del GTFS
         */
        
        $upload_data = array( // Procesar información para la subida del archivo
            'upload_path'   => 'uploads/',
            'allowed_types' => '*',
            'file_name'     => 'gtfs.zip',
            'overwrite'     => TRUE
        );
        $this->load->library( 'upload', $upload_data );
        
        
        /*
         * Medir tiempos de ejecución
         */
        $utime_inicio = microtime( TRUE );
        
        if ( ! $this->upload->do_upload('gtfs') ) // Intentar subir, entrar al "if" si hubo error
            show_error( $this->upload->display_errors(), HTTP_INTERNAL_SERVER_ERROR, 'Error al subir archivo' );

        else // Archivo subido correctamente, procesar información
        {
            $zip_file = zip_open( $upload_data['upload_path'] . $upload_data['file_name'] ); // Abrir ZIP
            
            // Procesar errores de apertura (como archivo no existente u otros)
            if ( ! is_resource($zip_file) )
                show_error( 'No se ha podido abrir el archivo ZIP.', HTTP_INTERNAL_SERVER_ERROR, 'Imposible abrir archivo .zip' );
            else
            {
                $repository_data['gtfs'] = array();
                
                while( $zip_entry = zip_read( $zip_file ) ) // Lee un campo del ZIP
                {
                    if ( ! zip_entry_open( $zip_file, $zip_entry ) )
                        show_error( 'No se ha podido abrir el archivo ZIP.', HTTP_INTERNAL_SERVER_ERROR, 'Imposible abrir archivo .zip' );
                    
                    array_push( $repository_data['gtfs'], array(
                        'file_name'     => zip_entry_name( $zip_entry ),
                        'file_contents' => zip_entry_read( $zip_entry, zip_entry_filesize( $zip_entry ) )
                    ));
                    
                    zip_entry_close( $zip_entry );
                }
            }
            
            zip_close( $zip_file );
            
            if ( ! $this->save_data( $repository_data ) )
                show_error( 'Ha ocurrido un error al leer la información del archivo .zip', HTTP_INTERNAL_SERVER_ERROR, 'Error al importar' );
            
            /*
             * Fin Medir tiempo de ejecución
             */
            $utime_final = microtime( TRUE );
            $utime_delta = $utime_final - $utime_inicio;
            
            die( "<b>Tiempos en segundos</b><br /><br />Tiempo de inicio (s): {$utime_inicio} - Tiempo de fin (s): {$utime_final}<br />Tiempo de ejecución: {$utime_delta} (s)");
            
            show_error( 'Repositorio importado correctamente.', HTTP_OK, 'Operación exitosa' );
        }
    }

    
    
    
    
    function save_data( $repository_data ) // Guarda información en el repositorio, filtrando archivos aceptados
    {
        if (
            ! isset( $repository_data['gtfs'] )
            OR
            count( $repository_data['gtfs'] ) == 0
        )
            return FALSE;
        
        $accepted_tables    = $GLOBALS['accepted_tables']; // Obtener qué archivos deben leerse
        $gtfs_files         = $repository_data['gtfs'];
        
        
        if ( ! $this->Basedatos->repository_exists($repository_data['id']) ) // Repositorio no existe, crearlo
        {
            $repo_final_data = array(
                'id'                    => $repository_data['id'],
                'name'                  => $repository_data['name'],
                'description'           => $repository_data['description'],
                'start_position_lat'    => $repository_data['start_position_lat'],
                'start_position_lon'    => $repository_data['start_position_lon']
            );
            
            
            
            $this->db->trans_begin(); // Iniciar una transacción (permite deshacer en caso de error)
            
                $this->db->insert( 'gtfsws_repositories', $repo_final_data );
                $repository_data['id'] = $this->db->insert_id();
            
            if ( $this->db->trans_status() === FALSE ) // Error al procesar la transacción: Deshacer y mostrar error
            {
                $this->db->trans_rollback();
                show_error( 'Ha ocurrido un error al importar la información del archivo .zip', HTTP_INTERNAL_SERVER_ERROR, 'Error al importar' );
            }
            else // Transacción exitosa, hacer los cambios permanentes
                $this->db->trans_commit();
            
            
            
        }
        else // Repositorio existe, actualizar información
        {
            $this->db->where( 'id', $repository_data['id'] );
            $this->db->update( 'gtfsws_repositories', array(
                'name'                  => $repository_data['name'],
                'description'           => $repository_data['description'],
                'start_position_lat'    => $repository_data['start_position_lat'],
                'start_position_lon'    => $repository_data['start_position_lon']
            ));
        }
        
        
        if ( ! $repository_data['id'] )
            show_error('Database error when creating a new repository.', HTTP_INTERNAL_SERVER_ERROR, 'Database error');
        
        foreach ( $gtfs_files AS $gtfs_file )
        {
            $table_name     = basename( $gtfs_file['file_name'], '.txt' );
            $file_contents  = $gtfs_file['file_contents'];
            
            //$is_accepted_file = array_search( $table_name, $accepted_tables ); // True = archivo se importa; false = se ignora
            $is_accepted_file = in_array( $table_name, $accepted_tables );
            
            if ( $is_accepted_file )
            {
                if ( ! $this->import_table_data( $table_name, $file_contents, $repository_data['id'] ) ) // Importar archivo, false si falló
                    return FALSE; // Devolver false si la importación falló (no se pudo crear repositorio)
            }
        }
        
        return TRUE; // Todo OK
    }
    
    
    
    
    
    private function import_table_data( $table, $contents, $repository_id )
    {
        // Separar archivo en líneas usando un token
        $separador      = "\r\n"; // Se detiene ante cualquier caracter dentro del string separador
        $titulos_line   = strtok( $contents, $separador ); // Lectura de la primera línea hasta el separador son títulos
        $titulos        = explode( ',', $titulos_line );
        
        foreach ( $titulos AS $pos => $val )
        {
            $titulos[$pos] = prepare_string( $val ); // Limpiar strings de títulos
        }
        
        array_unshift( $titulos, 'gtfsws_repository_id' ); // Agregar el 'repository_id' al comienzo
        
        $titulos        = add_special_quotes( $titulos );
        $num_titulos    = count( $titulos );
        $titulos_line   = implode( $titulos, ',' );
        
        // Primero vaciar tabla que se importará (para evitar colisiones de identificadores)
        //$this->db->empty_table( $table );
        $this->db->start_cache();
        $this->db->where( 'gtfsws_repository_id', $repository_id );
        $this->db->delete( $table );
        $this->db->stop_cache();
        $this->db->flush_cache();
        
        // Preparar consulta SQL inicial
        $sql = 'INSERT INTO `' . $table . '`(' . $titulos_line . ') VALUES';
        
        // Leer línea por línea y generar arreglo con información a insertar
        $data = array();
        $line = strtok( $separador ); // Leer la primera línea de información "real"
        
        $i = 1;
        
        while( $line !== FALSE ) // Mientras haya líneas por leer en el archivo...
        {
            $line_array = explode( ',', $line ); // Obtener información separada por las comas
            
            if ( count($line_array) + 1 != $num_titulos )
            {
                // Si no tiene la misma cantidad de valores que los títulos, entonces la línea está malformada
                
                $line = strtok( $separador ); // Primero se lee la línea siguiente para que el bucle continúe...
                continue; // ...y luego se salta esta iteración
            }
            
            // Agregar el número del repositorio... (para que calce con las tablas en la BD)
            array_unshift( $line_array, $repository_id );

            $line_array = add_quotes( prepare_string($line_array) );
            $sql_line   = '(' . implode( $line_array, ',' ) . ')';
            $sql .= $sql_line . ',';
            
            if ( $i % 100 == 0 )
            {
                $i = 0;
                
                // Quitar coma al final del string
                $sql = substr( $sql, 0, strlen($sql) - 1 );
                
                // Insertar con un máximo de 100 registros simultáneos
                $this->db->query( $sql );
                
                // Reiniciar consulta SQL inicial para seguir agregando valores
                $sql = 'INSERT INTO `' . $table . '`(' . $titulos_line . ') VALUES';
            }
            
            $line = strtok( $separador );
            $i++;
        }
        
        // Insertar los últimos registros que queden
        $sql = substr( $sql, 0, strlen($sql) - 1 );
        
        //die($sql);
        return $this->db->query( $sql ); // Siempre TRUE para pruebas
    }
}
