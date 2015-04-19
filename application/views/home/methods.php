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
            
            table.methods td
            {
                vertical-align: top;
                border:         1px solid #a0a0a0;
            }
            
            a
            {
                text-decoration:    none;
                color:              #0000aa;
            }
            
            a:hover
            {
                color:              #ff0000;
            }
            
            table.methods tr.no-border td
            {
                vertical-align: top;
                border:         none;
            }

            table.methods thead
            {
                font-weight:        bold;
                background-color:   #c0f3c0;
            }
            
            .logout_message
            {
                color:          #ff0000;
                font-family:    Tahoma, Verdana, sans-serif;
                font-size:      10pt;
            }
            
            .login_message
            {
                color:          #008800;
                font-family:    Tahoma, Verdana, sans-serif;
                font-size:      10pt;
            }
        </style>
    </head>
    
    <body>
        <h1>GTFS RESTful WebService</h1>
        
        
        
        <?php if ( $this->Usuarios->loggedIn() ): ?>
            Correo electrónico: <?=$this->Usuarios->get_email(); ?> &nbsp; &nbsp; &nbsp; Estado de Administrador: <?=( $this->Usuarios->isAdmin() ? 'Habilitado' : 'No habilitado' ); ?> &nbsp; &nbsp; &nbsp; [<?=anchor( base_url('test.html') , 'Login/Logout' ); ?>]
            <br />
        <?php else: ?>
            <p>
                No se ha iniciado una sesión. [<?=anchor( base_url('test.html') , 'Login/Logout' ); ?>]
            </p>
        <?php endif; ?>
        
        
        
        <?php if ( $msg = $this->session->flashdata( 'login_message' ) ): ?>
            <br />
            <span class="login_message"><?=$msg; ?></span>
        <?php endif; ?>
        
        <?php if ( $msg = $this->session->flashdata( 'logout_message' ) ): ?>
            <span class="logout_message"><?=$msg; ?></span>
        <?php endif; ?>
        
        <br />
        <br />
        
        <table class="methods">
            <thead>
                <tr>
                    <td>
                        M&eacute;todo
                    </td>
                    <td>
                        Descripci&oacute;n
                    </td>
                </tr>
            </thead>
            
            <tbody>
                <tr>
                    <td>
                        <?=anchor( 'repositories', 'repositories' ); ?>
                    </td>
                    <td>
                        Gestiona los repositorios accesibles por el usuario que ha iniciado sesión.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <?=anchor( 'import', 'import' ); ?>
                    </td>
                    <td>
                        Permite importar repositorios GTFS desde su archivo ZIP (solo administradores).
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <?=anchor( 'export', 'export' ); ?>
                    </td>
                    <td>
                        Permite exportar la información de un repositorio particular a un archivo ZIP (solo quienes tienen acceso al repositorio).
                    </td>
                </tr>
                
                <tr class="no-border"><td>&nbsp;</td><td>&nbsp;</td></tr><!-- Separador -->
                
                <tr>
                    <td>
                        <?=anchor( 'agencies', 'agencies' ); ?>
                    </td>
                    <td>
                        Gestiona las agencias accesibles por el usuario que ha iniciado sesión.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <?=anchor( 'routes', 'routes' ); ?>
                    </td>
                    <td>
                        Gestiona los recorridos accesibles por el usuario que ha iniciado sesión.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <?=anchor( 'shapes', 'shapes' ); ?>
                    </td>
                    <td>
                        Gestiona las formas gráficas que representan los viajes en el mapa. Método <b>no recomendable</b> por la cantidad de información.
                    </td>
                </tr>
                
                <tr>
                    <td>
                        <?=anchor( 'trips', 'trips' ); ?>
                    </td>
                    <td>
                        Gestiona los viajes accesibles por el usuario que ha iniciado sesión.
                    </td>
                </tr>
            </tbody>
        </table>
        <p>
            Versión del prototipo: <?=GTFSWS_VERSION; ?>
        </p>
    </body>
</html>