<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_Cron_Manager {
    
    const CRON_HOOK = 'apiapu_scheduled_generation';
    
    public static function init() {
        add_action(self::CRON_HOOK, array(__CLASS__, 'run_scheduled_generation'));
        add_filter('cron_schedules', array(__CLASS__, 'add_custom_schedules'));
        
        // FREEMIUM: Force clear schedule if exists
        // This ensures backend is clean as requested
        if (wp_next_scheduled(self::CRON_HOOK)) {
            self::clear_scheduled();
        }
    }
    
    public static function add_custom_schedules($schedules) {
        $schedules['five_minutes'] = array(
            'interval' => 300,
            'display' => __('Toutes les 5 minutes', 'auto-post-ia-pro-ultimate'),
        );
        
        $schedules['ten_minutes'] = array(
            'interval' => 600,
            'display' => __('Toutes les 10 minutes', 'auto-post-ia-pro-ultimate'),
        );
        
        $schedules['thirty_minutes'] = array(
            'interval' => 1800,
            'display' => __('Toutes les 30 minutes', 'auto-post-ia-pro-ultimate'),
        );
        
        return $schedules;
    }
    
    public static function run_scheduled_generation() {
        // PRO FEATURE RESTRICTION
        // In Freemium version, this should NEVER run.
        APIAPU_Logger::log('Tentative d\'exécution Cron bloquée (Version Gratuite). fonctionnalité PRO uniquement.', 'warning');
        return;
    }
    
    public static function reschedule($frequency, $enabled = true) {
        // Disabled in Free Version
        self::clear_scheduled();
    }
    
    public static function clear_scheduled() {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
        
        wp_clear_scheduled_hook(self::CRON_HOOK);
    }
    
    public static function get_next_run() {
        return null; // Always null in Free Version
    }
    
    public static function is_scheduled() {
        return false; // Always false in Free Version
    }
    
    public static function get_status() {
        // Force disabled status for UI
        return array(
            'enabled' => false,
            'frequency' => 'none',
            'frequency_label' => __('Désactivé (PRO)', 'auto-post-ia-pro-ultimate'),
            'is_scheduled' => false,
            'next_run' => null,
        );
    }
    
    public static function get_frequency_label($frequency) {
        $frequencies = APIAPU_Utils::get_cron_frequencies();
        return isset($frequencies[$frequency]) ? $frequencies[$frequency] : $frequency;
    }
    
    public static function get_last_run() {
        return get_option('apiapu_last_cron_run', null);
    }
    
    public static function update_last_run() {
        update_option('apiapu_last_cron_run', array(
            'timestamp' => time(),
            'formatted' => current_time('d/m/Y H:i:s'),
        ));
    }
}
