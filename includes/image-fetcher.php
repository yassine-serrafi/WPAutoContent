<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_Image_Fetcher {
    
    private $source;
    private $count;
    private $pexels_api_key;
    
    public function __construct() {
        $settings = get_option('apiapu_settings', array());
        
        // FREEMIUM RESTRICTION: Always force Pexels and Count 1
        $this->source = 'pexels'; 
        $this->count = 1; 
        
        $this->pexels_api_key = isset($settings['pexels_api_key']) ? $settings['pexels_api_key'] : '';
    }
    
    public function fetch_images($keyword, $count = null) {
        // Restriction enforced in constructor, but safety check:
        // Force count to 1 for Free Version
        $count = 1;
        
        // Pexels is the ONLY allowed source in the Free version
        if ($this->source === 'pexels') {
             return $this->fetch_from_pexels($keyword, $count);
        }

        return array();
    }
    
    private function fetch_from_unsplash($keyword, $count) {
        // PRO FEATURE - REMOVED IN FREE VERSION
        return array();
    }
    
    private function fetch_from_pexels($keyword, $count) {
        if (empty($this->pexels_api_key)) {
            APIAPU_Logger::log('Clé API Pexels manquante', 'error');
            return array();
        }

        $url = 'https://api.pexels.com/v1/search';
        
        // Get locale and convert to Pexels format (e.g. fr_FR -> fr-FR)
        $locale = get_locale();
        $locale = str_replace('_', '-', $locale);
        
        $args = array(
            'headers' => array(
                'Authorization' => $this->pexels_api_key
            ),
            'body' => array(
                'query' => $keyword,
                'per_page' => $count,
                'orientation' => 'landscape',
                'size' => 'large',
                'locale' => $locale
            )
        );

        // Pexels API expects query params in URL for GET requests usually, but let's check wp_remote_get usage
        // wp_remote_get appends body as params? No, usually body is for POST.
        // Let's build the URL properly.
        // Fetch a larger pool to ensure variety (Random but Relevant)
        $fetch_limit = 20; 
        
        $url = add_query_arg(array(
            'query' => $keyword,
            'per_page' => $fetch_limit,
            'orientation' => 'landscape',
            'size' => 'large',
            'locale' => $locale
        ), $url);

        $response = wp_remote_get($url, array(
            'headers' => array(
                'Authorization' => $this->pexels_api_key
            ),
            'timeout' => 15
        ));

        if (is_wp_error($response)) {
            APIAPU_Logger::log('Erreur API Pexels: ' . $response->get_error_message(), 'error');
            return array();
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            APIAPU_Logger::log('Erreur API Pexels (Code ' . $response_code . ')', 'error');
            return array();
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (empty($data) || empty($data['photos'])) {
            APIAPU_Logger::log('Aucune image trouvée sur Pexels pour: ' . $keyword, 'warning');
            return array();
        }

        $photos = $data['photos'];
        
        // Shuffle to get random images from the top relevant ones
        shuffle($photos);
        
        // Slice to get the requested count
        $photos = array_slice($photos, 0, $count);

        $images = array();
        foreach ($photos as $photo) {
            $alt_text = isset($photo['alt']) ? trim($photo['alt']) : '';
            if (empty($alt_text)) {
                $alt_text = $keyword;
            }
            
            $images[] = array(
                'url' => $photo['src']['large2x'] ?? $photo['src']['large'] ?? $photo['src']['original'],
                'alt' => $alt_text,
                'source' => 'pexels',
                'photographer' => $photo['photographer'] ?? '',
                'photographer_url' => $photo['photographer_url'] ?? ''
            );
        }
        
        APIAPU_Logger::log("Images récupérées depuis Pexels: " . count($images), 'info', array(
            'keyword' => $keyword,
            'image_urls' => array_column($images, 'url')
        ));
        
        return $images;
    }
    
    private function fetch_from_pixabay($keyword, $count) {
        // PRO FEATURE - REMOVED IN FREE VERSION
        return array();
    }
    
    public function import_image_to_media_library($image_url, $post_id = 0, $alt_text = '') {
        if (!function_exists('media_sideload_image')) {
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }
        
        // Use wp_remote_get to download content first to handle headers/User-Agent
        $args = array(
            'timeout' => 30,
            'headers' => array(
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            )
        );
        
        // If Pexels, add auth header just in case, though usually CDN url doesn't need it
        if (strpos($image_url, 'pexels.com') !== false && !empty($this->pexels_api_key)) {
            $args['headers']['Authorization'] = $this->pexels_api_key;
        }

        $response = wp_remote_get($image_url, $args);
        
        if (is_wp_error($response)) {
            APIAPU_Logger::log('Erreur téléchargement image (wp_remote_get): ' . $response->get_error_message(), 'error', array('url' => $image_url));
            return false;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code !== 200) {
            APIAPU_Logger::log('Erreur téléchargement image (Code ' . $response_code . ')', 'error', array('url' => $image_url));
            return false;
        }
        
        $image_contents = wp_remote_retrieve_body($response);
        if (empty($image_contents)) {
            APIAPU_Logger::log('Contenu image vide', 'error', array('url' => $image_url));
            return false;
        }
        
        // Determine filename
        $filename = '';
        if (!empty($alt_text)) {
            $sanitized_keyword = sanitize_title($alt_text);
            if (!empty($sanitized_keyword)) {
                $filename = $sanitized_keyword . '.jpg';
            }
        }
        
        if (empty($filename)) {
            $filename = basename(parse_url($image_url, PHP_URL_PATH));
        }
        
        if (empty($filename) || strpos($filename, '.') === false) {
            $filename = 'image-' . time() . '.jpg';
        }
        
        // Upload to WordPress upload directory
        $upload_dir = wp_upload_dir();
        
        // Clean filename and ensure unique
        $filename = sanitize_file_name($filename);
        $filename = wp_unique_filename($upload_dir['path'], $filename);
        
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        
        // Write content to file
        global $wp_filesystem;
        if (empty($wp_filesystem)) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            WP_Filesystem();
        }
        
        if (!$wp_filesystem->put_contents($file, $image_contents)) {
            APIAPU_Logger::log('Erreur écriture fichier image', 'error', array('file' => $file));
            return false;
        }
        
        // Check file type
        $wp_filetype = wp_check_filetype($filename, null);
        
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => !empty($alt_text) ? sanitize_text_field($alt_text) : sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        
        if (is_wp_error($attach_id)) {
            APIAPU_Logger::log('Erreur wp_insert_attachment: ' . $attach_id->get_error_message(), 'error');
            return false;
        }
        
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        if (!empty($alt_text)) {
            update_post_meta($attach_id, '_wp_attachment_image_alt', sanitize_text_field($alt_text));
        }
        
        APIAPU_Logger::log('Image importée avec succès - ID: ' . $attach_id, 'success');
        
        return $attach_id;
    }
    
    public function set_featured_image($post_id, $image_url, $alt_text = '') {
        $attachment_id = $this->import_image_to_media_library($image_url, $post_id, $alt_text);
        
        if ($attachment_id) {
            set_post_thumbnail($post_id, $attachment_id);
            APIAPU_Logger::log("Image mise en avant définie pour post {$post_id}", 'success');
            return $attachment_id;
        }
        
        return false;
    }
    
    public function insert_images_in_content($content, $images, $keyword, $post_id = 0) {
        if (empty($images)) {
            return $content;
        }
        
        $paragraphs = preg_split('/(<\/p>|<\/h[2-6]>)/i', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        
        if (count($paragraphs) < 3) {
            return $content;
        }
        
        $total_paragraphs = count($paragraphs);
        $image_positions = array();
        
        if (count($images) >= 1) {
            $image_positions[] = max(2, intval($total_paragraphs * 0.25));
        }
        if (count($images) >= 2) {
            $image_positions[] = max(4, intval($total_paragraphs * 0.5));
        }
        if (count($images) >= 3) {
            $image_positions[] = max(6, intval($total_paragraphs * 0.75));
        }
        
        $inserted = 0;
        $new_content = '';
        
        foreach ($paragraphs as $index => $paragraph) {
            $new_content .= $paragraph;
            
            if (in_array($index, $image_positions) && isset($images[$inserted])) {
                $img = $images[$inserted];
                $alt = !empty($img['alt']) ? esc_attr($img['alt']) : esc_attr($keyword);
                
                // Import image if post_id is provided
                $img_url = $img['url'];
                if ($post_id > 0) {
                    $attachment_id = $this->import_image_to_media_library($img['url'], $post_id, $alt);
                    if ($attachment_id && !is_wp_error($attachment_id)) {
                        $img_url = wp_get_attachment_url($attachment_id);
                    }
                }
                
                $new_content .= sprintf(
                    '<figure class="wp-block-image aligncenter"><img src="%s" alt="%s" loading="lazy" /></figure>',
                    esc_url($img_url),
                    $alt
                );
                $inserted++;
            }
        }
        
        return $new_content;
    }
}
