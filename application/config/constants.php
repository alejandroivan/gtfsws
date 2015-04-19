<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
define('FILE_READ_MODE', 0644);
define('FILE_WRITE_MODE', 0666);
define('DIR_READ_MODE', 0755);
define('DIR_WRITE_MODE', 0777);

/*
|--------------------------------------------------------------------------
| File Stream Modes
|--------------------------------------------------------------------------
|
| These modes are used when working with fopen()/popen()
|
*/

define('FOPEN_READ',							'rb');
define('FOPEN_READ_WRITE',						'r+b');
define('FOPEN_WRITE_CREATE_DESTRUCTIVE',		'wb'); // truncates existing file data, use with care
define('FOPEN_READ_WRITE_CREATE_DESTRUCTIVE',	'w+b'); // truncates existing file data, use with care
define('FOPEN_WRITE_CREATE',					'ab');
define('FOPEN_READ_WRITE_CREATE',				'a+b');
define('FOPEN_WRITE_CREATE_STRICT',				'xb');
define('FOPEN_READ_WRITE_CREATE_STRICT',		'x+b');


/* End of file constants.php */
/* Location: ./application/config/constants.php */





/*
 * CODIGOS DE ESTADO HTTP
 */
    $http_status_keys = array(
        // 1xx = Informational
        'CONTINUE', 'SWITCHING_PROTOCOLS', 'PROCESSING',
        // 2xx = Succeed
        'OK', 'CREATED', 'ACCEPTED', 'NON_AUTHORITATIVE_INFORMATION', 'NO_CONTENT', 'RESET_CONTENT', 'PARTIAL_CONTENT', 'MULTI_STATUS', 'ALREADY_REPORTED', 'IM_USED',
        // 3xx = Redirection
        'MULTIPLE_CHOICES', 'MOVED_PERMANENTLY', 'FOUND', 'SEE_OTHER', 'NOT_MODIFIED', 'USE_PROXY', 'SWITCH_PROXY', 'TEMPORARY_REDIRECT', 'PERMANENT_REDIRECT',
        // 4xx = Client Error
        'BAD_REQUEST', 'UNAUTHORIZED', 'PAYMENT_REQUIRED', 'FORBIDDEN', 'NOT_FOUND', 'METHOD_NOT_ALLOWED', 'NOT_ACCEPTABLE', 'PROXY_AUTHENTICATION_REQUIRED', 'REQUEST_TIMEOUT', 'CONFLICT',
        'GONE', 'LENGTH_REQUIRED', 'PRECONDITION_FAILED', 'REQUEST_ENTITY_TOO_LARGE', 'REQUEST_URI_TOO_LONG', 'UNSUPPORTED_MEDIA_TYPE', 'REQUESTED_RANGE_NOT_SATISFIABLE', 'EXPECTATION_FAILED', 'IM_A_TEAPOT', 'AUTHENTICATION_TIMEOUT',
        'METHOD_FAILURE', 'ENHANCE_YOUR_CALM', 'UNPROCESSABLE_ENTITY', 'LOCKED', 'FAILED_DEPENDENCY', 'UPGRADE_REQUIRED', 'PRECONDITION_REQUIRED', 'TOO_MANY_REQUESTS', 'REQUEST_HEADER_FIELDS_TOO_LARGE', 'LOGIN_TIMEOUT',
        'NO_RESPONSE', 'RETRY_WITH', 'BLOCKED_BY_WPC', 'UNAVAILABLE_FOR_LEGAL_REASONS', 'REDIRECT',
        'REQUEST_HEADER_TOO_LARGE', 'CERT_ERROR', 'NO_CERT', 'HTTP_TO_HTTPS', 'TOKEN_EXPIRED_INVALID', 'CLIENT_CLOSED_REQUEST', 'TOKEN_REQUIRED',
        //5xx = Server Error
        'INTERNAL_SERVER_ERROR', 'NOT_IMPLEMENTED', 'BAD_GATEWAY', 'SERVICE_UNAVAILABLE', 'GATEWAY_TIMEOUT', 'HTTP_VERSION_NOT_SUPPORTED', 'VARIANT_ALSO_NEGOTIATES', 'INSUFFICIENT_STORAGE', 'LOOP_DETECTED', 'BANDWIDTH_LIMIT_EXCEEDED',
        'NOT_EXTENDED', 'NETWORK_AUTHENTICATION_REQUIRED', 'ORIGIN_ERROR', 'WEB_SERVER_IS_DOWN', 'CONNECTION_TIMED_OUT', 'PROXY_DECLINED_REQUEST', 'A_TIMEOUT_OCCURRED', 'NETWORK_READ_TIMEOUT_ERROR', 'NETWORK_CONNECT_TIMEOUT_ERROR'
    );
    
    $http_status_codes = array(
        // 1xx = Informational
        100,101,102,
        // 2xx = Success
        200,201,202,203,204,205,206,207,208,226,
        // 3xx = Redirection
        300,301,302,303,304,305,306,307,308,
        // 4xx = Client Error
        400,401,402,403,404,405,406,407,408,409,
        410,411,412,413,414,415,416,417,418,419,
        420,420,422,423,424,426,428,429,431,440,
        444,449,450,451,451,
        494,495,496,497,498,499,499,
        // 5xx = Server Error
        500,501,502,503,504,505,506,507,508,509,
        510,511,520,521,522,523,524,598,599
    );
    
    for ($i = 0, $numkeys = count($http_status_keys); $i < $numkeys; $i++)
        define( "HTTP_{$http_status_keys[$i]}" , $http_status_codes[$i] ); // Definir constantes globales. Ej: HTTP_FORBIDDEN
/*
 * HTTP STATUS CODES
 */