<?php defined('BASEPATH') OR exit('No direct script access allowed');


/*
 * $output = prepare_string( $input );
 */
if ( !function_exists( 'prepare_string' ))
{
    function prepare_string( $input ) // Limpia el string de inyecciones XSS y quita caracteres invisibles
    {
        // Remover todos los caracteres no visibles
        if ( ! is_array( $input ) )
            $output = addcslashes( preg_replace( '/[^\pL\pN\pP\pS\pZ]/u', '', $input ), '\'' );
        else
        {
            $output = array();
            foreach ( $input AS $idx => $elem )
                $output[$idx] = addcslashes( preg_replace( '/[^\pL\pN\pP\pS\pZ]/u', '', $elem ), '\'' );
        }
        
        // Escapar strings para evitar inyecciones SQL
        $CI =& get_instance(); // Obtener instancia principal del framework para usar métodos de clases
        $output = $CI->db->escape_str( $output );
        
        return $output;
    }
}



/*
 * add_special_quotes( $input )
 */
if ( !function_exists( 'add_special_quotes' ) )
{
    function add_special_quotes( $input ) // Encierra los strings del array en `cromillas especiales`
    {
        if ( !is_array( $input ) )
            $output = '`' . $input . '`';
        else
        {
            $output = array();
            foreach( $input AS $index => $value )
                $output[$index] = '`' . $value . '`';
        }
        
        return $output;
    }
}




/*
 * add_quotes( $input )
 */
if ( !function_exists( 'add_quotes' ) )
{
    function add_quotes( $input ) // Encierra los strings del array en 'cromillas simples'
    {
        if ( !is_array( $input ) )
            $output = '`' . $input . '`';
        else
        {
            $output = array();
            foreach( $input AS $index => $value )
                $output[$index] = '\'' . $value . '\'';
        }
        
        return $output;
    }
}
