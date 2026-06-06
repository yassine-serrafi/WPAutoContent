<?php
/**
 * Plugin Name: WPAutoContent
 * Plugin URI: https://www.wpautocontent.xyz/
 * Description: Plugin WordPress premium pour la génération automatique d'articles SEO optimisés via OpenAI avec gestion complète des mots-clés, images, cron et logs.
 * Version: 10.0.0
 * Author: WPAutoContent
 * Author URI: https://www.wpautocontent.xyz/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: auto-post-ia-pro-ultimate
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('APIAPU_VERSION', '10.0.0');
define('APIAPU_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('APIAPU_PLUGIN_URL', plugin_dir_url(__FILE__));
define('APIAPU_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Inclusion des classes nécessaires
require_once APIAPU_PLUGIN_DIR . 'includes/utils.php';
require_once APIAPU_PLUGIN_DIR . 'includes/core/class-db-driver.php';
require_once APIAPU_PLUGIN_DIR . 'includes/logger.php';
require_once APIAPU_PLUGIN_DIR . 'includes/openai.php';
require_once APIAPU_PLUGIN_DIR . 'includes/keywords-manager.php';
require_once APIAPU_PLUGIN_DIR . 'includes/image-fetcher.php';
require_once APIAPU_PLUGIN_DIR . 'includes/video-fetcher.php';
require_once APIAPU_PLUGIN_DIR . 'includes/seo-tools.php';
require_once APIAPU_PLUGIN_DIR . 'includes/cron-manager.php';
require_once APIAPU_PLUGIN_DIR . 'includes/class-generator.php';

// Initialisation du plugin
function apiapu_init() {
    // Initialisation du gestionnaire de licence (renommé pour sécurité)
    if (class_exists('APIAPU_DB_Driver')) {
        APIAPU_DB_Driver::init_driver();
    }
    
    // Initialisation SEO Tools pour le frontend
    new APIAPU_SEO_Tools();
}
add_action('plugins_loaded', 'apiapu_init');

// Vérification de la licence pour les fonctionnalités principales
function apiapu_check_license() {
    return true;
}

class AutoPostIAProUltimate {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
// Initialisation du plugin
        APIAPU_Cron_Manager::init();
            
        // Register AJAX actions for generator
        add_action('wp_ajax_apiapu_generate_article', array($this, 'ajax_generate_article'));
        add_action('wp_ajax_apiapu_test_api', array($this, 'ajax_test_api')); 
        
        // Always register settings and log AJAX actions
        add_action('wp_ajax_apiapu_add_keyword', array($this, 'ajax_add_keyword'));
        add_action('wp_ajax_apiapu_delete_keyword', array($this, 'ajax_delete_keyword'));
        add_action('wp_ajax_apiapu_delete_keywords', array($this, 'ajax_delete_keywords_bulk'));
        add_action('wp_ajax_apiapu_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_apiapu_save_prompt', array($this, 'ajax_save_prompt'));
        add_action('wp_ajax_apiapu_reset_prompt', array($this, 'ajax_reset_prompt'));
        add_action('wp_ajax_apiapu_run_cron', array($this, 'ajax_run_cron'));
        add_action('wp_ajax_apiapu_save_cron_settings', array($this, 'ajax_save_cron_settings'));
        add_action('wp_ajax_apiapu_export_logs', array($this, 'ajax_export_logs'));
        add_action('wp_ajax_apiapu_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_apiapu_get_logs', array($this, 'ajax_get_logs'));
        

    }
    
    public function add_admin_menu() {
        add_menu_page(
            __('WPAutoContent', 'auto-post-ia-pro-ultimate'),
            __('WPAutoContent', 'auto-post-ia-pro-ultimate'),
            'manage_options',
            'apiapu-dashboard',
            array($this, 'render_dashboard'),
            'dashicons-edit-page',
            30
        );
        
        add_submenu_page(
            'apiapu-dashboard',
            __('Tableau de bord', 'auto-post-ia-pro-ultimate'),
            __('Tableau de bord', 'auto-post-ia-pro-ultimate'),
            'manage_options',
            'apiapu-dashboard',
            array($this, 'render_dashboard')
        );
        
        add_submenu_page(
            'apiapu-dashboard',
            __('Mots-clés', 'auto-post-ia-pro-ultimate'),
            __('Mots-clés', 'auto-post-ia-pro-ultimate'),
            'manage_options',
            'apiapu-keywords',
            array($this, 'render_keywords')
        );
        
        add_submenu_page(
            'apiapu-dashboard',
            __('Prompt IA', 'auto-post-ia-pro-ultimate'),
            __('Prompt IA', 'auto-post-ia-pro-ultimate'),
            'manage_options',
            'apiapu-prompt',
            array($this, 'render_prompt')
        );
        
        add_submenu_page(
            'apiapu-dashboard',
            __('Paramètres', 'auto-post-ia-pro-ultimate'),
            __('Paramètres', 'auto-post-ia-pro-ultimate'),
            'manage_options',
            'apiapu-settings',
            array($this, 'render_settings')
        );
        
        add_submenu_page(
            'apiapu-dashboard',
            __('Logs Système', 'auto-post-ia-pro-ultimate'),
            __('Logs Système', 'auto-post-ia-pro-ultimate'),
            'manage_options',
            'apiapu-logs',
            array($this, 'render_logs')
        );
    }
    
    public function enqueue_admin_assets($hook) {
        if (strpos($hook, 'apiapu') === false) {
            return;
        }
        
        wp_enqueue_style(
            'apiapu-admin-css',
            APIAPU_PLUGIN_URL . 'admin/admin.css',
            array(),
            APIAPU_VERSION
        );
        
        wp_enqueue_script(
            'apiapu-admin-js',
            APIAPU_PLUGIN_URL . 'admin/admin.js',
            array('jquery'),
            APIAPU_VERSION,
            true
        );
        
        wp_localize_script('apiapu-admin-js', 'apiapu_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('apiapu_nonce'),
            'loader_url' => APIAPU_PLUGIN_URL . 'assets/loader.gif',
            'strings' => array(
                'generating' => __('Génération en cours...', 'auto-post-ia-pro-ultimate'),
                'success' => __('Article généré avec succès!', 'auto-post-ia-pro-ultimate'),
                'error' => __('Erreur lors de la génération', 'auto-post-ia-pro-ultimate'),
                'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer ce mot-clé?', 'auto-post-ia-pro-ultimate'),
                'saved' => __('Paramètres sauvegardés!', 'auto-post-ia-pro-ultimate'),
                'testing' => __('Test de connexion en cours...', 'auto-post-ia-pro-ultimate'),
                'api_ok' => __('Connexion API réussie!', 'auto-post-ia-pro-ultimate'),
                'api_error' => __('Erreur de connexion API', 'auto-post-ia-pro-ultimate'),
            )
        ));
    }
    
    public function render_dashboard() {
        include APIAPU_PLUGIN_DIR . 'admin/dashboard.php';
    }
    
    public function render_keywords() {
        include APIAPU_PLUGIN_DIR . 'admin/keywords-page.php';
    }
    
    public function render_prompt() {
        include APIAPU_PLUGIN_DIR . 'admin/prompt-editor.php';
    }
    
    public function render_settings() {
        include APIAPU_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    public function render_logs() {
        include APIAPU_PLUGIN_DIR . 'admin/system-log.php';
    }
    
    public function ajax_generate_article() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $keyword_id = isset($_POST['keyword_id']) ? intval($_POST['keyword_id']) : 0;
        
        $generator = new APIAPU_Generator();
        $result = $generator->generate_article($keyword_id);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_test_api() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $openai = new APIAPU_OpenAI();
        $result = $openai->test_connection();
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    public function ajax_add_keyword() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $keyword = isset($_POST['keyword']) ? sanitize_text_field($_POST['keyword']) : '';
        
        if (empty($keyword)) {
            wp_send_json_error(array('message' => __('Le mot-clé ne peut pas être vide', 'auto-post-ia-pro-ultimate')));
        }
        
        $manager = new APIAPU_Keywords_Manager();
        $result = $manager->add_keyword($keyword);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Mot-clé ajouté avec succès', 'auto-post-ia-pro-ultimate'), 'keyword' => $result));
        } else {
            wp_send_json_error(array('message' => __('Erreur lors de l\'ajout du mot-clé', 'auto-post-ia-pro-ultimate')));
        }
    }
    
    public function ajax_delete_keyword() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $keyword_id = isset($_POST['keyword_id']) ? intval($_POST['keyword_id']) : 0;
        
        $manager = new APIAPU_Keywords_Manager();
        $result = $manager->delete_keyword($keyword_id);
        
        if ($result) {
            wp_send_json_success(array('message' => __('Mot-clé supprimé avec succès', 'auto-post-ia-pro-ultimate')));
        } else {
            wp_send_json_error(array('message' => __('Erreur lors de la suppression du mot-clé', 'auto-post-ia-pro-ultimate')));
        }
    }

    public function ajax_delete_keywords_bulk() {
        check_ajax_referer('apiapu_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }

        $ids = isset($_POST['keyword_ids']) ? (array) $_POST['keyword_ids'] : array();
        $ids = array_filter(array_map('intval', $ids));

        if (empty($ids)) {
            wp_send_json_error(array('message' => __('Aucun mot-clé sélectionné', 'auto-post-ia-pro-ultimate')));
        }

        $manager = new APIAPU_Keywords_Manager();
        $deleted = $manager->delete_keywords($ids);

        if ($deleted) {
            wp_send_json_success(array(
                'message' => sprintf(
                    /* translators: %d = nombre de mots-clés supprimés */
                    _n('%d mot-clé supprimé', '%d mots-clés supprimés', $deleted, 'auto-post-ia-pro-ultimate'),
                    $deleted
                ),
                'deleted' => $deleted,
            ));
        } else {
            wp_send_json_error(array('message' => __('Erreur lors de la suppression groupée', 'auto-post-ia-pro-ultimate')));
        }
    }

    public function ajax_save_settings() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $settings = array(
            'api_key' => isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '',
            'model' => isset($_POST['model']) ? sanitize_text_field($_POST['model']) : 'gpt-4o',
            'custom_model' => isset($_POST['custom_model']) ? sanitize_text_field($_POST['custom_model']) : '',
            'temperature' => isset($_POST['temperature']) ? floatval($_POST['temperature']) : 0.7,
            'max_tokens' => isset($_POST['max_tokens']) ? intval($_POST['max_tokens']) : 4000,
            'proxy' => isset($_POST['proxy']) ? sanitize_text_field($_POST['proxy']) : '',
            'post_status' => isset($_POST['post_status']) ? sanitize_text_field($_POST['post_status']) : 'draft',
            'category' => isset($_POST['category']) ? intval($_POST['category']) : 0,
            'image_count' => isset($_POST['image_count']) ? intval($_POST['image_count']) : 3,
            'image_source' => isset($_POST['image_source']) ? sanitize_text_field($_POST['image_source']) : 'unsplash',
            'pexels_api_key' => isset($_POST['pexels_api_key']) ? sanitize_text_field($_POST['pexels_api_key']) : '',
            'youtube_enabled' => isset($_POST['youtube_enabled']) ? 1 : 0,
            'seo_plugin' => isset($_POST['seo_plugin']) ? sanitize_text_field($_POST['seo_plugin']) : 'none',
            'auto_hn' => isset($_POST['auto_hn']) ? 1 : 0,
            'schema_org' => isset($_POST['schema_org']) ? 1 : 0,
            'auto_toc' => isset($_POST['auto_toc']) ? 1 : 0,
            'language' => isset($_POST['language']) ? sanitize_text_field($_POST['language']) : 'fr',
            'niche' => isset($_POST['niche']) ? sanitize_text_field($_POST['niche']) : 'general',
            'delete_data_on_uninstall' => isset($_POST['delete_data_on_uninstall']) ? 1 : 0,
        );

        update_option('apiapu_settings', $settings);
        
        APIAPU_Logger::log('Paramètres sauvegardés', 'info');
        
        wp_send_json_success(array('message' => __('Paramètres sauvegardés avec succès', 'auto-post-ia-pro-ultimate')));
    }
    
    public function ajax_save_prompt() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $prompt = isset($_POST['prompt']) ? wp_kses_post($_POST['prompt']) : '';
        
        update_option('apiapu_prompt', $prompt);
        
        APIAPU_Logger::log('Prompt sauvegardé', 'info');
        
        wp_send_json_success(array('message' => __('Prompt sauvegardé avec succès', 'auto-post-ia-pro-ultimate')));
    }
    
    public function ajax_reset_prompt() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $settings = get_option('apiapu_settings', array());
        $language = isset($settings['language']) ? $settings['language'] : 'fr';
        $default_prompt = APIAPU_Utils::get_default_prompt($language);
        update_option('apiapu_prompt', $default_prompt);
        
        APIAPU_Logger::log('Prompt réinitialisé', 'info');
        
        wp_send_json_success(array('message' => __('Prompt réinitialisé avec succès', 'auto-post-ia-pro-ultimate'), 'prompt' => $default_prompt));
    }
    
    public function ajax_run_cron() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        APIAPU_Cron_Manager::run_scheduled_generation();
        
        wp_send_json_success(array('message' => __('Cron exécuté avec succès', 'auto-post-ia-pro-ultimate')));
    }
    
    public function ajax_save_cron_settings() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $frequency = isset($_POST['frequency']) ? sanitize_text_field($_POST['frequency']) : 'hourly';
        $enabled = isset($_POST['enabled']) ? 1 : 0;
        
        update_option('apiapu_cron_frequency', $frequency);
        update_option('apiapu_cron_enabled', $enabled);
        
        APIAPU_Cron_Manager::reschedule($frequency, $enabled);
        
        APIAPU_Logger::log('Paramètres Cron sauvegardés: ' . $frequency . ' (Activé: ' . ($enabled ? 'Oui' : 'Non') . ')', 'info');
        
        wp_send_json_success(array('message' => __('Paramètres Cron sauvegardés', 'auto-post-ia-pro-ultimate')));
    }
    
    public function ajax_export_logs() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $logs = APIAPU_Logger::get_logs();
        $content = APIAPU_Logger::format_logs_for_export($logs);
        
        wp_send_json_success(array('content' => $content, 'filename' => 'apiapu-logs-' . date('Y-m-d-H-i-s') . '.txt'));
    }
    
    public function ajax_clear_logs() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        APIAPU_Logger::clear_logs();
        
        wp_send_json_success(array('message' => __('Logs effacés avec succès', 'auto-post-ia-pro-ultimate')));
    }
    
    public function ajax_get_logs() {
        check_ajax_referer('apiapu_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permission refusée', 'auto-post-ia-pro-ultimate')));
        }
        
        $logs = APIAPU_Logger::get_logs(50);
        
        wp_send_json_success(array('logs' => $logs));
    }
}

register_activation_hook(__FILE__, 'apiapu_activate');
function apiapu_activate() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'apiapu_keywords';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        keyword varchar(255) NOT NULL,
        status varchar(20) DEFAULT 'pending',
        used_count int(11) DEFAULT 0,
        last_used datetime DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY keyword (keyword),
        KEY status (status)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    $logs_table = $wpdb->prefix . 'apiapu_logs';
    $sql_logs = "CREATE TABLE IF NOT EXISTS $logs_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        message text NOT NULL,
        type varchar(20) DEFAULT 'info',
        keyword varchar(255) DEFAULT NULL,
        post_id bigint(20) DEFAULT NULL,
        openai_response text DEFAULT NULL,
        image_urls text DEFAULT NULL,
        error_details text DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY type (type),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    dbDelta($sql_logs);
    
    if (!get_option('apiapu_settings')) {
        $default_settings = array(
            'api_key' => '',
            'model' => 'gpt-4o',
            'custom_model' => '',
            'temperature' => 0.7,
            'max_tokens' => 4000,
            'proxy' => '',
            'post_status' => 'draft',
            'category' => 0,
            'image_count' => 3,
            'image_source' => 'unsplash',
            'pexels_api_key' => '',
            'youtube_enabled' => 0,
            'youtube_api_key' => '',
            'seo_plugin' => 'none',
            'auto_hn' => 1,
            'schema_org' => 0,
            'auto_toc' => 0,
            'language' => 'fr',
            'niche' => 'general',
            'delete_data_on_uninstall' => 0,
        );
        update_option('apiapu_settings', $default_settings);
    }
    
    if (!get_option('apiapu_prompt')) {
        update_option('apiapu_prompt', APIAPU_Utils::get_default_prompt());
    }
    
    if (!get_option('apiapu_cron_frequency')) {
        update_option('apiapu_cron_frequency', 'hourly');
    }
    
    if (!get_option('apiapu_cron_enabled')) {
        update_option('apiapu_cron_enabled', 0);
    }
    
    APIAPU_Logger::log('Plugin activé', 'info');
}

register_deactivation_hook(__FILE__, 'apiapu_deactivate');
function apiapu_deactivate() {
    APIAPU_Cron_Manager::clear_scheduled();
    APIAPU_Logger::log('Plugin désactivé', 'info');
}

add_action('plugins_loaded', function() {
    AutoPostIAProUltimate::get_instance();
});
