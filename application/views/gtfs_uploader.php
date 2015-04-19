<!DOCTYPE html>
<html>
    <head>
        <?=header( 'Content-type: text/html; charset=UTF-8' ); ?>
        
        <title>GTFS RESTful WebService</title>
        <style type="text/css">
            body
            {
                font-family: Tahoma, Verdana, sans-serif;
                font-size:          10pt;
            }
            
            h1
            {
                font-size: 20pt;
            }
            
            a
            {
                text-decoration:    none;
                color:              #0000aa;
            }
            
            a:hover
            {
                color:              #ff0000;
                text-decoration:    underline;
            }
            
            .nombre_campo
            {
                display:        inline-block;
                width:          150px;
                vertical-align: top;
            }
            
            .seleccion_campo
            {
                display:    inline-block;
                vertical-align: top;
            }
            
            textarea
            {
                width:  200px;
                height: 70px;
            }
            
            .replace_explanation
            {
                display:    none;
                font-size:  7pt;
                color:      #ff0000;
            }
            
            .replace_explanation_show
            {
                display:    block;
            }
            
            .with_border
            {
                max-width:  400px;
                border:     2px solid #008800;
            }
        </style>
        <noscript>
            This module requires Javascript support.
        </noscript>
        <script type="text/javascript" src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                /*
                 * Comprobar campos al hacer submit
                 */
                $( '#gtfsws_file_upload' ).submit(function(e) {
                    var name                = $( '#repository_name' ).val();
                    var description         = $( '#repository_description' ).val();
                    var start_position_lat  = $( '#repository_start_position_lat' ).val();
                    var start_position_lon  = $( '#repository_start_position_lon' ).val();
                    var repository_select   = $( '#repository_select' ).val();
                    var file                = $( '#file' ).val();
                    
                    if (
                        name.length == 0
                        ||
                        start_position_lat.length < 3
                        ||
                        start_position_lon.length < 3
                        ||
                        isNaN(start_position_lat)
                        ||
                        isNaN(start_position_lon)
                        ||
                        repository_select === '--'
                        ||
                        file.length == 0
                    )
                    {
                        e.preventDefault();
                        alert( 'The fields name, start_position_lat, start_position_lon, repository_id and file are mandatory.');
                    }
                });
                
                /*
                 * Actualizar formulario de acuerdo a la selección del repositorio
                 */
                $("#repository_select").change(function() {
                    var $this               = $(this);
                    var selected            = $( 'option:selected', $this );
                    
                    if ( selected.val() !== 'NEW' && selected.val() !== '--' )
                    {
                        var name                = selected.data( 'name' );
                        var description         = selected.data( 'description' );
                        var start_position_lat  = selected.data( 'start-position-lat' );
                        var start_position_lon  = selected.data( 'start-position-lon' );
                        
                        $( '#repository_name' ).val( name );
                        $( '#repository_description').val( description );
                        $( '#repository_start_position_lat').val( start_position_lat );
                        $( '#repository_start_position_lon').val( start_position_lon );
                        
                        $( '.replace_explanation' ).addClass( 'replace_explanation_show' );
                    }
                    
                    else
                    {
                        $( '#repository_name').val( '' );
                        $( '#repository_description').val( '' );
                        $( '#repository_start_position_lat').val( '' );
                        $( '#repository_start_position_lon').val( '' );
                        $( '.replace_explanation:not(.replace_nohide)' ).removeClass( 'replace_explanation_show' );
                    }
                });
            });
        </script>
    </head>
    
    <body>
        <?=form_open_multipart('import/upload', 'id="gtfsws_file_upload"'); ?>
        
        <br />
        <br />
        
        <div class="nombre_campo">Repository:</div>
        
        <div class="seleccion_campo">
        <select name="repository_id" id="repository_select">
            <option value="NEW" selected="selected">New repository</option>
            <option value="--">-----------------------------------</option>
            <?php
                $repos = $this->Usuarios->get_repositories();
                
                foreach ( $repos AS $repo ):
                    echo "<option value=\"{$repo['id']}\" data-name=\"{$repo['name']}\" data-description=\"{$repo['description']}\" data-start-position-lat=\"{$repo['start_position_lat']}\" data-start-position-lon=\"{$repo['start_position_lon']}\">{$repo['name']}</option>";
                endforeach;
            ?>
        </select>
        </div>
        
        <br />
        <br />
        
        <div class="nombre_campo">GTFS ZIP file:</div>
        <div class="seleccion_campo">
            <input type="file" name="gtfs" id="file" />
        </div>
        
        <br />
        <br />
        
        <div class="nombre_campo">Repository name:</div>
        <div class="seleccion_campo">
            <input type="text" name="repository_name" id="repository_name" placeholder="Enter a repository name" />
            <div class="replace_explanation">Editing this will change the name of this repository.</div>
        </div>
        
        <br />
        <br />
        
        <div class="nombre_campo">Repository description:</div>
        <div class="seleccion_campo">
            <textarea name="repository_description" id="repository_description" placeholder="Enter a description for the repository"></textarea>
            <div class="replace_explanation">Editing this will change the description of this repository.</div>
        </div>
        
        <br />
        <br />
        
        <div class="nombre_campo">Start position latitude:</div>
        <div class="seleccion_campo">
            <input type="text" name="repository_start_position_lat" id="repository_start_position_lat" placeholder="Enter a latitude for the start position of the repository (6 integer digits, 7 decimal digits)" />
            <div class="replace_explanation">Editing this will change the latitude for the start position of this repository.</div>
        </div>
        
        <br />
        <br />
        
        <div class="nombre_campo">Start position longitude:</div>
        <div class="seleccion_campo">
            <input type="text" name="repository_start_position_lon" id="repository_start_position_lon" placeholder="Enter a longitude for the start position of the repository (6 integer digits, 7 decimal digits)" />
            <div class="replace_explanation">Editing this will change the longitude for the start position of this repository.</div>
        </div>

        <br />
        <br />
        
        <input type="submit" value="Create/update repository" /> <?=anchor( '', 'Cancel' ); ?>
        
        <?=form_close(); ?>
        
        <br />
        <br />
        <div class="replace_explanation replace_explanation_show replace_nohide with_border">
            Using this module needs to reset all the data of a repository if being updated.
            <br />
            Use the HTTP PUT method in the REST webservice to update the repository identification data only.
        </div>
    </body>
</html>