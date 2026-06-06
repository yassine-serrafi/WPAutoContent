<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_Logger {
    
    private static $table_name = null;
    
    private static function get_table_name() {
        if (self::$table_name === null) {
            global $wpdb;
            self::$table_name = $wpdb->prefix . 'apiapu_logs';
        }
        return self::$table_name;
    }
    
    public static function log($message, $type = 'info', $extra_data = array()) {
        global $wpdb;
        
        $data = array(
            'message' => $message,
            'type' => $type,
            'created_at' => current_time('mysql'),
        );
        
        if (isset($extra_data['keyword'])) {
            $data['keyword'] = sanitize_text_field($extra_data['keyword']);
        }
        
        if (isset($extra_data['post_id'])) {
            $data['post_id'] = intval($extra_data['post_id']);
        }
        
        if (isset($extra_data['openai_response'])) {
            $data['openai_response'] = wp_json_encode($extra_data['openai_response']);
        }
        
        if (isset($extra_data['image_urls'])) {
            $data['image_urls'] = wp_json_encode($extra_data['image_urls']);
        }
        
        if (isset($extra_data['error_details'])) {
            $data['error_details'] = is_array($extra_data['error_details']) 
                ? wp_json_encode($extra_data['error_details']) 
                : $extra_data['error_details'];
        }
        
        $wpdb->insert(self::get_table_name(), $data);
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('[APIAPU] [' . strtoupper($type) . '] ' . $message);
        }
    }
    
    public static function get_logs($limit = 100, $offset = 0, $type = null) {
        global $wpdb;
        
        $table = self::get_table_name();
        $where = '';
        $params = array();
        
        if ($type !== null) {
            $where = 'WHERE type = %s';
            $params[] = $type;
        }
        
        $params[] = $limit;
        $params[] = $offset;
        
        $sql = "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        
        $logs = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
        
        foreach ($logs as &$log) {
            if (!empty($log['openai_response'])) {
                $log['openai_response'] = json_decode($log['openai_response'], true);
            }
            if (!empty($log['image_urls'])) {
                $log['image_urls'] = json_decode($log['image_urls'], true);
            }
            if (!empty($log['error_details'])) {
                $decoded = json_decode($log['error_details'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $log['error_details'] = $decoded;
                }
            }
        }
        
        return $logs;
    }
    
    public static function get_logs_count($type = null) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        if ($type !== null) {
            return $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE type = %s", $type));
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }
    
    public static function clear_logs($type = null) {
        global $wpdb;
        
        $table = self::get_table_name();
        
        if ($type !== null) {
            $wpdb->delete($table, array('type' => $type));
        } else {
            $wpdb->query("TRUNCATE TABLE {$table}");
        }
    }
    
    public static function delete_old_logs($days = 30) {
        global $wpdb;
        
        $table = self::get_table_name();
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        
        $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE created_at < %s", $date));
    }
    
    public static function format_logs_for_export($logs) {
        $output = "=== WPAutoContent - Export des Logs ===" . PHP_EOL;
        $output .= "Date d'export: " . current_time('Y-m-d H:i:s') . PHP_EOL;
        $output .= "=================================================" . PHP_EOL . PHP_EOL;
        
        foreach ($logs as $log) {
            $output .= "[" . $log['created_at'] . "] ";
            $output .= "[" . strtoupper($log['type']) . "] ";
            $output .= $log['message'] . PHP_EOL;
            
            if (!empty($log['keyword'])) {
                $output .= "  Mot-clé: " . $log['keyword'] . PHP_EOL;
            }
            
            if (!empty($log['post_id'])) {
                $output .= "  Post ID: " . $log['post_id'] . PHP_EOL;
            }
            
            if (!empty($log['error_details'])) {
                $output .= "  Erreur: " . (is_array($log['error_details']) ? print_r($log['error_details'], true) : $log['error_details']) . PHP_EOL;
            }
            
            $output .= PHP_EOL;
        }
        
        return $output;
    }
    
    public static function get_stats() {
        global $wpdb;
        
        $table = self::get_table_name();
        
        $stats = array(
            'total' => $wpdb->get_var("SELECT COUNT(*) FROM {$table}"),
            'info' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE type = %s", 'info')),
            'success' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE type = %s", 'success')),
            'warning' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE type = %s", 'warning')),
            'error' => $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE type = %s", 'error')),
            'today' => $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table} WHERE DATE(created_at) = %s",
                current_time('Y-m-d')
            )),
        );
        
        return $stats;
    }
}
