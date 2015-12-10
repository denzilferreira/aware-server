<?php if ( !defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

class MY_Security extends CI_Security
{
    public function csrf_verify( )
    {
        foreach ( config_item('csrf_excludes') as $exclude )
        {
            $uri = load_class('URI', 'core');
            if ( preg_match( $exclude, $uri->uri_string() ) > 0 )
            {
                // still do input filtering to prevent parameter piggybacking in the form
                if (isset($_COOKIE[$this->_csrf_cookie_name]) && preg_match( '#^[0-9a-f]{32}$#iS', $_COOKIE[$this->_csrf_cookie_name] ) == 0)
                {
                    unset( $_COOKIE[$this->_csrf_cookie_name] );
                }
                return;
            }
        }
        parent::csrf_verify( );
    }
}