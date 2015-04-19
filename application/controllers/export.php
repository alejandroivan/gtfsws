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

class Export extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        
        // Admitir acceso solo por usuarios registrados
        if ( ! $this->Usuarios->loggedIn() )
            show_error( 'Solo usuarios con una sesión iniciada pueden utilizar este módulo.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        // Cargar helpers de strings
        $this->load->helper( 'string' ); // Extendido por application/helpers/MY_string_helper.php
    }
    
    function index()
    {
        show_error( 'Se debe especificar el repositorio a exportar. Sintaxis: <b>export/repository/&lt;id_repositorio&gt;</b>', HTTP_NOT_FOUND, 'Recurso no especificado' );
    }
    
    function repository( $repository_id = NULL )
    {
            
        // Bloquear si no se ha especificado el repositorio a exportar como ZIP o si el identificador es inválido
        if
        (
            ! $repository_id
            OR
            ! ctype_digit( $repository_id )
            OR
            ! ( intval( $repository_id ) > 0 )
        )
            show_error( 'Se debe especificar el repositorio a exportar. Sintaxis: <b>export/repository/&lt;id_repositorio&gt;</b>', HTTP_NOT_FOUND, 'Recurso no especificado' );
        
        // Verificar que el usuario tiene acceso al sistema
        if ( ! $this->Basedatos->repository_ownership( $this->Usuarios, $repository_id ) )
            show_error( 'El usuario no tiene acceso al repositorio especificado.', HTTP_FORBIDDEN, 'Acceso restringido' );
        
        // Quitar límite de memoria en PHP (por si hay muchos registros)
        ini_set( 'memory_limit', -1 );
        
        // Todo OK, generar un archivo ZIP
        $this->load->model( 'Zip' );
        $zip_contents = $this->Zip->export( $repository_id, $GLOBALS['accepted_tables'] );
        
        if ( ! ( strlen($zip_contents ) > 0 ) ) // Terminar si hubo error al generar el archivo ZIP
            show_error( 'Error al generar el archivo ZIP.', HTTP_INTERNAL_SERVER_ERROR, 'Error interno' );
        
        $this->output
            ->set_content_type( 'application/zip' )
            ->set_header( 'Content-Length: ' . strlen($zip_contents) )
            ->set_header( 'Content-Disposition: attachment; filename="gtfs.zip"' )
            ->set_output( $zip_contents );
    }
}
