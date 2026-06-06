<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_Keywords_Manager {
    
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'apiapu_keywords';
    }
    
    public function add_keyword($keyword) {
        global $wpdb;
        
        $keyword = sanitize_text_field(trim($keyword));
        
        if (empty($keyword)) {
            return false;
        }
        
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE keyword = %s",
            $keyword
        ));
        
        if ($existing) {
            APIAPU_Logger::log('Mot-clé déjà existant: ' . $keyword, 'warning');
            return false;
        }
        
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'keyword' => $keyword,
                'status' => 'pending',
                'used_count' => 0,
                'created_at' => current_time('mysql'),
            ),
            array('%s', '%s', '%d', '%s')
        );
        
        if ($result) {
            APIAPU_Logger::log('Mot-clé ajouté: ' . $keyword, 'info');
            return array(
                'id' => $wpdb->insert_id,
                'keyword' => $keyword,
                'status' => 'pending',
                'used_count' => 0,
            );
        }
        
        return false;
    }
    
    public function add_keywords_bulk($keywords_string) {
        // PRO FEATURE RESTRICTION
        APIAPU_Logger::log("Tentative d'ajout en masse (PRO) bloquée.", 'warning');
        return array(
            'added' => 0,
            'skipped' => 0,
        );
    }
    
    public function delete_keyword($id) {
        global $wpdb;
        
        $id = intval($id);
        
        $keyword = $wpdb->get_var($wpdb->prepare(
            "SELECT keyword FROM {$this->table_name} WHERE id = %d",
            $id
        ));
        
        $result = $wpdb->delete(
            $this->table_name,
            array('id' => $id),
            array('%d')
        );
        
        if ($result) {
            APIAPU_Logger::log('Mot-clé supprimé: ' . $keyword, 'info');
            return true;
        }
        
        return false;
    }
    
    public function delete_keywords($ids) {
        global $wpdb;

        $ids = array_filter(array_map('intval', (array) $ids), function ($id) {
            return $id > 0;
        });

        if (empty($ids)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '%d'));
        $sql = "DELETE FROM {$this->table_name} WHERE id IN ({$placeholders})";

        $deleted = $wpdb->query($wpdb->prepare($sql, $ids));

        if ($deleted) {
            APIAPU_Logger::log('Suppression groupée de mots-clés : ' . intval($deleted), 'info');
        }

        return intval($deleted);
    }

    public function get_keyword($id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE id = %d",
            intval($id)
        ), ARRAY_A);
    }
    
    public function get_all_keywords($status = null, $limit = 100, $offset = 0) {
        global $wpdb;
        
        $where = '';
        $params = array();
        
        if ($status !== null) {
            $where = 'WHERE status = %s';
            $params[] = $status;
        }
        
        $params[] = $limit;
        $params[] = $offset;
        
        $sql = "SELECT * FROM {$this->table_name} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d";
        
        return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
    }
    
    public function get_next_keyword() {
        global $wpdb;
        
        $keyword = $wpdb->get_row(
            "SELECT * FROM {$this->table_name} WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1",
            ARRAY_A
        );
        
        if (!$keyword) {
            $keyword = $wpdb->get_row(
                "SELECT * FROM {$this->table_name} ORDER BY used_count ASC, last_used ASC LIMIT 1",
                ARRAY_A
            );
        }
        
        return $keyword;
    }
    
    public function mark_as_used($id) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array(
                'status' => 'used',
                'used_count' => $wpdb->get_var($wpdb->prepare(
                    "SELECT used_count FROM {$this->table_name} WHERE id = %d",
                    $id
                )) + 1,
                'last_used' => current_time('mysql'),
            ),
            array('id' => intval($id)),
            array('%s', '%d', '%s'),
            array('%d')
        );
    }
    
    public function update_status($id, $status) {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array('status' => sanitize_text_field($status)),
            array('id' => intval($id)),
            array('%s'),
            array('%d')
        );
    }
    
    public function get_count($status = null) {
        global $wpdb;
        
        if ($status !== null) {
            return $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table_name} WHERE status = %s",
                $status
            ));
        }
        
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name}");
    }
    
    public function get_stats() {
        global $wpdb;
        
        return array(
            'total' => $this->get_count(),
            'pending' => $this->get_count('pending'),
            'used' => $this->get_count('used'),
            'in_progress' => $this->get_count('in_progress'),
        );
    }
    
    public function reset_all_status() {
        global $wpdb;
        
        return $wpdb->update(
            $this->table_name,
            array('status' => 'pending'),
            array('status' => 'used'),
            array('%s'),
            array('%s')
        );
    }
    
    public function search_keywords($search_term) {
        global $wpdb;
        
        $search = '%' . $wpdb->esc_like($search_term) . '%';
        
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$this->table_name} WHERE keyword LIKE %s ORDER BY created_at DESC",
            $search
        ), ARRAY_A);
    }
}
