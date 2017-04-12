<?php

/**
 * Plugin Name: JMC Nonces
 * Description: This is a test plugin which implement nonce in object oriented mode
 * Version: 1.0
 * Author: Jean-Marie Clément
 * Author URI: https://jmclement.net
 * License: GPL2
 */


// Create Text Domain For Translations
load_plugin_textdomain('jmc_nonces', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
 
// set default values
function jmc_nonces_activate_set_default_options() {
    // no options atm
    
}
register_activation_hook( __FILE__, 'jmc_nonces_activate_set_default_options');


if (!class_exists('jmcNonces')) {
    
    final class jmcNonces {
        
        private function __construct() {

        }
        
        public function Create( $action = -1 ) {
            
            $user=wp_get_current_user();
            $uid=(int)$user->ID;
            // not logged
            if (!$uid) {
                $uid = apply_filters( 'nonce_user_logged_out', $uid, $action );
            }
            $token = wp_get_session_token();
            $i = wp_nonce_tick();
            
            return substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'jmcNonce' ), -12, 10 );
            
        }
        
        
        public function CreateURL( $actionurl, $action = -1, $name = '_jmcNonce' ) {
            
            $actionurl = str_replace( '&amp;', '&', $actionurl );
            return esc_html( add_query_arg( $name, self::Create( $action ), $actionurl ) );   
                     
        }
        
        
        public function CreateField( $action = -1, $name = "_jmcNonce", $referer = true , $echo = true ) {
            
            $name = esc_attr( $name );
            $nonce_field = '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . self::Create( $action ) . '" />';
            
            if ( $referer )
                $nonce_field .= wp_referer_field( false );
            
            if ( $echo )
                echo $nonce_field;
            
            return $nonce_field;
            
        }        
        
        public function Verify( $nonce, $action ) {
        
        	$nonce = (string) $nonce;
        	$user = wp_get_current_user();
        	$uid = (int) $user->ID;
            
        	if ( ! $uid ) {

        		$uid = apply_filters( 'nonce_user_logged_out', $uid, $action );

        	}
        
        	if ( empty( $nonce ) ) {
        		return false;
        	}
        
        	$token = wp_get_session_token();
        	$i = wp_nonce_tick();
        
        	// Nonce generated 0-12 hours ago
        	$expected = substr( wp_hash( $i . '|' . $action . '|' . $uid . '|' . $token, 'jmcNonce'), -12, 10 );
        	if ( hash_equals( $expected, $nonce ) ) {
        		return 1;
        	}
        
        	// Nonce generated 12-24 hours ago
        	$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'jmcNonce' ), -12, 10 );
        	if ( hash_equals( $expected, $nonce ) ) {
        		return 2;
        	}

        	do_action( 'wp_verify_nonce_failed', $nonce, $action, $user, $token );
        
        	// Invalid nonce
        	return false;
            
        }
        
        private function __destruct() {
            
        }
        
    }
    
}

$jmcNonce = new jmcNonces;


?>