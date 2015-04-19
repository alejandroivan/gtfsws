<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Zip extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        
        $this->load->model( 'Basedatos' );
        
        // Quitar límite de memoria en PHP (por si hay muchos registros)
        ini_set( 'memory_limit', -1 );
    }
    
    
    function export( $repository_id, $tables )
    {
        // Crear un archivo temporal para escribir el ZIP
        $file = tempnam( 'tmp', 'zip' );
        $zip = new ZipArchive();
        $zip->open( $file, ZipArchive::OVERWRITE );
        
        foreach ( $tables AS $table_name ) // Obtener información de cada tabla especificada
        {
            $table_contents = $this->get_table_contents( $repository_id, $table_name );
            $zip->addFromString( "{$table_name}.txt", $table_contents );
        }
        
        $zip->close();
        
        $zip_contents = file_get_contents( $file );
        @unlink( $file );
        
        return $zip_contents;
    }
    
    private function get_table_contents( $repository_id, $table_name )
    {
        $this->db
            ->select( '*' )
            ->from( $table_name )
            ->where( 'gtfsws_repository_id', $repository_id );
        
        $query_result = $this->db->get()->result_array();
        
        // La primera línea de la tabla es siempre la lista de campos
        $fields = $this->db->list_fields( $table_name );
        unset( $fields[0] );

        $fields_line = '';
        
        foreach ( $fields AS $field )
            $fields_line .= $field . ',';
        $fields_line = substr( $fields_line, 0, strlen($fields_line) - 1 ) . PHP_EOL; // Quitar última coma y agregar salto de línea
        $file_contents = $fields_line;
        
        // Leer registros de la base de datos e ir generando línea por línea la salida
        foreach ( $query_result AS $line_data )
        {
            unset( $line_data['gtfsws_repository_id'] ); // Quitar ID del repositorio de los datos
            
            // Procesar cada campo de la respuesta y agregarlo a una línea
            $line = '';
            
            foreach ( $line_data AS $field )
            {
                $result_field = $field;
                $tiene_comas = strpos( $field, ',' ) !== FALSE;
                $tiene_quotes = strpos( $field, '"' ) !== FALSE;
                
                if ( $tiene_quotes )
                    $result_field = str_replace( '"', '""', $result_field );
                
                if ( $tiene_comas )
                    $result_field = '"' . $result_field . '"';
                
                $line .= $result_field . ',';
            }
            
            $line = substr( $line, 0, strlen($line) - 1 ) . PHP_EOL; // Remover última coma
            $file_contents .= $line;
        }
        
        return $file_contents;
    }
}