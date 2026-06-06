<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_Video_Fetcher {
    
    private $enabled;
    
    public function __construct() {
        $settings = get_option('apiapu_settings', array());
        $this->enabled = isset($settings['youtube_enabled']) ? (bool) $settings['youtube_enabled'] : false;
    }
    
    public function is_enabled() {
        return $this->enabled;
    }
    
    /**
     * Recherche une vidéo via scraping (sans API) et retourne le code d'intégration
     */
    public function get_embed_html($keyword) {
        // YouTube Video is a PRO feature
        // APIAPU_Logger::log('Vidéo YouTube réservée à la version PRO.', 'info');
        return '';
    }
    
    /**
     * Scrape la page de recherche YouTube pour trouver le premier ID de vidéo
     */
    private function scrape_video_id($keyword) {
        // PRO FEATURE - REMOVED IN FREE VERSION
        return false;
    }
}
