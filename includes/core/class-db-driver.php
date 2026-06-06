<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_DB_Driver {
    
    /**
     * Initialisation globale
     */
    public static function init_driver() {
        // No-op
    }

    /**
     * Vérifie le statut - Toujours valide en version Freemium (mais fonctionnalités bridées ailleurs)
     */
    public static function check_status() {
        return true;
    }

    public static function get_config() {
        return array(
            'key' => 'FREEMIUM',
            'status' => 'valid'
        );
    }
}
