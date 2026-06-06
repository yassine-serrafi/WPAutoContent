<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_Generator {
    
    private $openai;
    private $keywords_manager;
    private $image_fetcher;
    private $video_fetcher;
    private $seo_tools;
    private $settings;
    
    public function __construct() {
        $this->openai = new APIAPU_OpenAI();
        $this->keywords_manager = new APIAPU_Keywords_Manager();
        $this->image_fetcher = new APIAPU_Image_Fetcher();
        $this->video_fetcher = new APIAPU_Video_Fetcher();
        $this->seo_tools = new APIAPU_SEO_Tools();
        $this->settings = get_option('apiapu_settings', array());
    }
    
    public function generate_article($keyword_id = 0) {
        // La génération IA peut être longue (jusqu'à 2 appels API de 120 s :
        // génération + enrichissement automatique). On empêche PHP de couper le
        // script trop tôt sur les hébergements mutualisés (max_execution_time ~30 s).
        if (function_exists('set_time_limit')) {
            @set_time_limit(300);
        }

        update_option('apiapu_generation_status', 'in_progress');

        APIAPU_Logger::log('Début génération article', 'info');
        
        if (!$this->openai->is_configured()) {
            $this->set_error_status();
            return array(
                'success' => false,
                'message' => __('Clé API OpenAI non configurée', 'auto-post-ia-pro-ultimate'),
            );
        }
        
        if ($keyword_id > 0) {
            $keyword_data = $this->keywords_manager->get_keyword($keyword_id);
        } else {
            $keyword_data = $this->keywords_manager->get_next_keyword();
        }
        
        if (!$keyword_data) {
            $this->set_error_status();
            APIAPU_Logger::log('Aucun mot-clé disponible', 'error');
            return array(
                'success' => false,
                'message' => __('Aucun mot-clé disponible pour la génération', 'auto-post-ia-pro-ultimate'),
            );
        }
        
        $keyword = $keyword_data['keyword'];
        $keyword_id = $keyword_data['id'];
        
        $this->keywords_manager->update_status($keyword_id, 'in_progress');
        
        APIAPU_Logger::log('Mot-clé sélectionné: ' . $keyword, 'info', array('keyword' => $keyword));
        
        // Anti-Corn/Adult Filter (Freemium Restriction)
        $corn_keywords = array('porn', 'sex', 'xxx', 'adult', 'nude', 'naked', 'erotic', 'kamasutra', 'escort', 'cam', 'hentai', 'fetish', 'bondage', 'incest', 'milf', 'uncensored', '18+', 'nsfw');
        foreach ($corn_keywords as $bad_word) {
            if (stripos($keyword, $bad_word) !== false) {
                $this->keywords_manager->update_status($keyword_id, 'error');
                update_option('apiapu_generation_status', 'idle');
                APIAPU_Logger::log('Contenu adulte bloqué (Version Gratuite): ' . $keyword, 'warning');
                return array(
                    'success' => false,
                    'message' => __('Le contenu "Adulte" est réservé à la version PRO. Veuillez mettre à jour votre plugin.', 'auto-post-ia-pro-ultimate'),
                    'keyword' => $keyword,
                );
            }
        }
        
        // Anti-duplicate check
        if ($this->is_duplicate($keyword)) {
            $this->keywords_manager->mark_as_used($keyword_id);
            update_option('apiapu_generation_status', 'idle');
            
            APIAPU_Logger::log('Article ignoré : doublon détecté pour ' . $keyword, 'warning');
            
            return array(
                'success' => false,
                'message' => __('Article ignoré : un contenu existe déjà pour ce mot-clé', 'auto-post-ia-pro-ultimate'),
                'keyword' => $keyword,
            );
        }
        
        $ai_result = $this->openai->generate_article($keyword);
        
        if (!$ai_result['success']) {
            $this->keywords_manager->update_status($keyword_id, 'error');
            $this->set_error_status();
            
            return array(
                'success' => false,
                'message' => $ai_result['error'],
                'keyword' => $keyword,
            );
        }
        
        $article_data = $ai_result['data'];

        // Garde-fou qualité : filet de sécurité final contre le contenu trop court
        // (thin content) qui exposerait le site au "scaled content abuse" de Google.
        // La longueur est désormais surtout forcée en amont dans APIAPU_OpenAI.
        $word_count = APIAPU_Utils::count_words($article_data['content']);
        $min_words = 500;

        if ($word_count < $min_words) {
            $this->keywords_manager->update_status($keyword_id, 'error');
            $this->set_error_status();

            APIAPU_Logger::log(
                "Article rejeté : contenu trop court ({$word_count} mots, minimum {$min_words})",
                'warning',
                array('keyword' => $keyword)
            );

            return array(
                'success' => false,
                'message' => sprintf(
                    /* translators: %d = nombre de mots */
                    __('Article rejeté : contenu trop court (%d mots). Relancez la génération pour un article plus complet.', 'auto-post-ia-pro-ultimate'),
                    $word_count
                ),
                'keyword' => $keyword,
            );
        }

        // Détermination du meilleur terme de recherche pour les images
        // Priorité : Suggestions d'images > Titre généré > Tags (Jargon) > Mot-clé
        $image_search_query = $keyword;
        
        if (!empty($article_data['image_suggestions']) && is_array($article_data['image_suggestions']) && !empty($article_data['image_suggestions'][0])) {
            $image_search_query = $article_data['image_suggestions'][0];
        } elseif (!empty($article_data['title'])) {
            $image_search_query = $article_data['title'];
        } elseif (!empty($article_data['tags']) && is_array($article_data['tags'])) {
            $image_search_query = implode(' ', array_slice($article_data['tags'], 0, 2));
        }
        
        APIAPU_Logger::log('Recherche image avec: ' . $image_search_query, 'info');

        $images = $this->image_fetcher->fetch_images($image_search_query);
        
        $post_id = $this->create_post($article_data, $keyword, $images);
        
        if (!$post_id) {
            $this->keywords_manager->update_status($keyword_id, 'error');
            $this->set_error_status();
            
            APIAPU_Logger::log('Erreur création post WordPress', 'error', array('keyword' => $keyword));
            
            return array(
                'success' => false,
                'message' => __('Erreur lors de la création de l\'article WordPress', 'auto-post-ia-pro-ultimate'),
                'keyword' => $keyword,
            );
        }
        
        $this->keywords_manager->mark_as_used($keyword_id);
        
        update_option('apiapu_generation_status', 'completed');
        update_option('apiapu_last_generation', array(
            'timestamp' => time(),
            'post_id' => $post_id,
            'keyword' => $keyword,
        ));
        
        APIAPU_Logger::log('Article créé avec succès - Post ID: ' . $post_id, 'success', array(
            'keyword' => $keyword,
            'post_id' => $post_id,
        ));
        
        APIAPU_Cron_Manager::update_last_run();
        
        return array(
            'success' => true,
            'message' => __('Article généré avec succès!', 'auto-post-ia-pro-ultimate'),
            'post_id' => $post_id,
            'post_url' => get_permalink($post_id),
            'edit_url' => get_edit_post_link($post_id, 'raw'),
            'keyword' => $keyword,
            'title' => $article_data['title'],
        );
    }
    
    private function create_post($article_data, $keyword, $images) {
        $title = isset($article_data['title']) ? sanitize_text_field($article_data['title']) : $keyword;

        // Le prompt impose déjà le mot-clé dans le titre de façon naturelle.
        // On évite tout ajout mécanique (" : Mot-clé") qui crée une empreinte
        // de keyword-stuffing pénalisée par Google. On se contente de tracer le cas.
        if (stripos($title, $keyword) === false) {
            APIAPU_Logger::log('Info SEO : le mot-clé n\'apparaît pas tel quel dans le titre généré', 'info', array('keyword' => $keyword));
        }
        $content = isset($article_data['content']) ? $article_data['content'] : '';
        $excerpt = isset($article_data['excerpt']) ? sanitize_text_field($article_data['excerpt']) : '';
        // Utilise le titre complet pour le slug au lieu du mot-clé uniquement
        $slug = sanitize_title($title);
        
        $content = $this->seo_tools->optimize_headings($content, $title);
        
        // Remove image insertion here, will do it after post creation to allow import
        // if (!empty($images)) {
        //    $content = $this->image_fetcher->insert_images_in_content($content, array_slice($images, 1), $keyword);
        // }
        
        $content = APIAPU_Utils::clean_html_for_wp($content);
        
        $post_status = isset($this->settings['post_status']) ? $this->settings['post_status'] : 'draft';
        $category = isset($this->settings['category']) ? intval($this->settings['category']) : 0;
        
        $post_data = array(
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $excerpt,
            'post_name' => $slug,
            'post_status' => $post_status,
            'post_type' => 'post',
            'post_author' => $this->get_default_author(),
        );
        
        if ($category > 0) {
            $post_data['post_category'] = array($category);
        }
        
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            APIAPU_Logger::log('Erreur wp_insert_post: ' . $post_id->get_error_message(), 'error');
            return false;
        }

        // Add tags
        if (isset($article_data['tags']) && is_array($article_data['tags'])) {
            wp_set_post_tags($post_id, $article_data['tags']);
        }
        
        // Insert images into content now that we have a post ID
        if (!empty($images)) {
            // Use array_slice to skip the first image (featured image)
            $content_with_images = $this->image_fetcher->insert_images_in_content($content, array_slice($images, 1), $keyword, $post_id);
            
            if ($content_with_images !== $content) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $content_with_images
                ));
                $content = $content_with_images; // Update local content var
            }
        }
        
        // Insert YouTube Video
        if ($this->video_fetcher->is_enabled()) {
            // New method: directly get embed HTML using the keyword (no API search needed)
            $video_html = $this->video_fetcher->get_embed_html($keyword);
            
            if (!empty($video_html)) {
                // Insert after the first paragraph (or at the end if not enough paragraphs)
                $paragraphs = explode('</p>', $content);
                if (count($paragraphs) > 1) {
                    // Insert after first paragraph
                    $paragraphs[0] .= '</p>' . $video_html;
                    $content_with_video = implode('</p>', $paragraphs);
                } else {
                    // Append to end
                    $content_with_video = $content . $video_html;
                }
                
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $content_with_video
                ));
            }
        } else {
            APIAPU_Logger::log('Vidéo YouTube ignorée (Module désactivé dans les réglages)', 'info');
        }
        
        if (!empty($images) && isset($images[0])) {
            $this->image_fetcher->set_featured_image($post_id, $images[0]['url'], $keyword);
        }
        
        $seo_data = array(
            'meta_title' => isset($article_data['meta_title']) ? $article_data['meta_title'] : $title,
            'meta_description' => isset($article_data['meta_description']) ? $article_data['meta_description'] : $excerpt,
            'keyword' => $keyword,
        );
        
        $this->seo_tools->apply_seo_to_post($post_id, $seo_data);
        
        update_post_meta($post_id, '_apiapu_generated', 1);
        update_post_meta($post_id, '_apiapu_keyword', $keyword);
        update_post_meta($post_id, '_apiapu_generated_at', current_time('mysql'));

        // Sommaire (optionnel) + maillage interne automatique : on part du contenu
        // final (images/vidéo déjà insérées) et on enregistre une seule fois.
        $final_content = get_post_field('post_content', $post_id);
        $enhanced_content = $final_content;

        if (!empty($this->settings['auto_toc'])) {
            // Sommaire autonome : on garantit nous-mêmes une ancre (id) sur chaque
            // H2, indépendamment de l'option "Optimisation Hn". Puis on construit
            // le sommaire à partir de ces ancres.
            $enhanced_content = $this->ensure_heading_ids($enhanced_content);
            $toc = $this->build_toc($enhanced_content);
            if ($toc !== '') {
                $enhanced_content = $toc . $enhanced_content;
            }
        }

        $enhanced_content = $this->add_internal_links($enhanced_content, $post_id, $keyword);

        if ($enhanced_content !== $final_content) {
            wp_update_post(array(
                'ID' => $post_id,
                'post_content' => $enhanced_content,
            ));
        }

        return $post_id;
    }

    /**
     * Maillage interne : quelques liens contextuels en ligne + un bloc "À lire
     * aussi" vers des articles existants liés (mêmes tags, sinon même catégorie).
     */
    private function add_internal_links($content, $post_id, $keyword) {
        $related = $this->find_related_posts($post_id, 4);

        if (empty($related)) {
            // Aucun article lié (ex. site neuf) : on ne force aucun lien.
            return $content;
        }

        // 1) Liens contextuels en ligne (max 3, uniquement dans le corps de texte).
        $content = $this->inject_inline_links($content, $related, 3);

        // 2) Bloc "À lire aussi" en fin d'article.
        $content .= $this->build_related_block($related);

        return $content;
    }

    /**
     * Trouve des articles publiés liés : d'abord par tags communs, puis par catégorie.
     */
    private function find_related_posts($post_id, $limit = 4) {
        $found = array();

        $tag_ids = wp_get_post_tags($post_id, array('fields' => 'ids'));

        if (!empty($tag_ids)) {
            $by_tags = get_posts(array(
                'post_type'      => 'post',
                'post_status'    => 'publish',
                'posts_per_page' => $limit,
                'post__not_in'   => array($post_id),
                'tag__in'        => $tag_ids,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'no_found_rows'  => true,
            ));
            foreach ($by_tags as $p) {
                $found[$p->ID] = $p;
            }
        }

        if (count($found) < $limit) {
            // Complément par catégorie, MAIS on exclut la catégorie par défaut
            // ("Non classé") : si tous les articles y tombent, on obtiendrait des
            // liens totalement hors-sujet (ex. un article gaming relié à des
            // articles bébé). On préfère aucun lien plutôt qu'un lien non pertinent.
            $default_cat = (int) get_option('default_category');
            $cat_ids = array_values(array_diff(wp_get_post_categories($post_id), array($default_cat)));

            if (!empty($cat_ids)) {
                $by_cat = get_posts(array(
                    'post_type'      => 'post',
                    'post_status'    => 'publish',
                    'posts_per_page' => $limit,
                    'post__not_in'   => array_merge(array($post_id), array_keys($found)),
                    'category__in'   => $cat_ids,
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                    'no_found_rows'  => true,
                ));
                foreach ($by_cat as $p) {
                    $found[$p->ID] = $p;
                    if (count($found) >= $limit) {
                        break;
                    }
                }
            }
        }

        return array_slice($found, 0, $limit, true);
    }

    /**
     * Insère prudemment quelques liens en ligne, sans casser le HTML ni sur-optimiser.
     */
    private function inject_inline_links($content, $related, $max_links = 3) {
        // Prépare les cibles valides (une ancre + une URL par article lié).
        $targets = array();

        foreach ($related as $p) {
            $url = get_permalink($p->ID);

            // Ancre : mot-clé d'origine de l'article lié si dispo, sinon son titre.
            $anchor = get_post_meta($p->ID, '_apiapu_keyword', true);
            if (empty($anchor)) {
                $anchor = get_the_title($p->ID);
            }
            $anchor = trim($anchor);

            // On ignore les ancres trop courtes et les liens déjà présents.
            if (strlen($anchor) < 4 || empty($url) || strpos($content, $url) !== false) {
                continue;
            }

            $targets[] = array('url' => $url, 'anchor' => $anchor);
        }

        if (empty($targets)) {
            return $content;
        }

        $remaining = $max_links;

        // On n'injecte QUE dans le texte des paragraphes <p>...</p> : jamais dans
        // un titre, une liste, un tableau ou un lien existant -> HTML propre.
        $content = preg_replace_callback('/<p\b[^>]*>.*?<\/p>/is', function ($m) use (&$targets, &$remaining) {
            $paragraph = $m[0];

            if ($remaining <= 0) {
                return $paragraph;
            }

            // On saute les paragraphes contenant déjà un lien (anti sur-optimisation).
            if (stripos($paragraph, '<a ') !== false) {
                return $paragraph;
            }

            foreach ($targets as $i => $t) {
                if ($remaining <= 0) {
                    break;
                }
                if ($t === null) {
                    continue;
                }

                // (?![^<]*>) : ne pas matcher à l'intérieur d'une balise.
                $pattern = '/(?<![\w])(' . preg_quote($t['anchor'], '/') . ')(?![^<]*>)/iu';
                $new = preg_replace($pattern, '<a href="' . esc_url($t['url']) . '">$1</a>', $paragraph, 1, $cnt);

                if (!empty($cnt) && $new !== null) {
                    $paragraph = $new;
                    $remaining--;
                    $targets[$i] = null; // une seule occurrence par article lié
                }
            }

            return $paragraph;
        }, $content);

        return $content;
    }

    /**
     * Construit le bloc "À lire aussi".
     */
    private function build_related_block($related) {
        $items = '';

        foreach ($related as $p) {
            $items .= '<li><a href="' . esc_url(get_permalink($p->ID)) . '">'
                    . esc_html(get_the_title($p->ID)) . '</a></li>';
        }

        if ($items === '') {
            return '';
        }

        return "\n<div class=\"apiapu-related-posts\" style=\"margin-top:2em;padding:1em 1.25em;border:1px solid #e2e2e2;border-radius:8px;background:#fafafa;\">"
            . '<h2 style="margin-top:0;">' . esc_html__('À lire aussi', 'auto-post-ia-pro-ultimate') . '</h2>'
            . '<ul>' . $items . '</ul></div>' . "\n";
    }

    /**
     * Garantit que chaque sous-titre H2/H3 possède une ancre (id) unique, afin que
     * le sommaire fonctionne même si l'option "Optimisation Hn" est désactivée.
     */
    private function ensure_heading_ids($content) {
        $used = array();

        return preg_replace_callback('/<h([23])([^>]*)>(.*?)<\/h\1>/is', function ($m) use (&$used) {
            $level = $m[1];
            $attrs = $m[2];
            $text  = $m[3];

            // id déjà présent : on l'enregistre pour éviter les doublons, on ne touche à rien.
            if (preg_match('/\bid=["\']([^"\']+)["\']/i', $attrs, $existing)) {
                $used[] = $existing[1];
                return $m[0];
            }

            $base = sanitize_title(wp_strip_all_tags($text));
            if ($base === '') {
                return $m[0];
            }

            $id = $base;
            $n  = 2;
            while (in_array($id, $used, true)) {
                $id = $base . '-' . $n;
                $n++;
            }
            $used[] = $id;

            return '<h' . $level . $attrs . ' id="' . esc_attr($id) . '">' . $text . '</h' . $level . '>';
        }, $content);
    }

    /**
     * Construit un sommaire cliquable à partir des sous-titres H2 (dont l'ancre est
     * garantie par ensure_heading_ids() lorsque le sommaire est activé).
     */
    private function build_toc($content) {
        if (!preg_match_all('/<h2[^>]*\bid="([^"]+)"[^>]*>(.*?)<\/h2>/is', $content, $matches, PREG_SET_ORDER)) {
            return '';
        }

        if (count($matches) < 3) {
            // Pas de sommaire pour un article trop peu structuré.
            return '';
        }

        $items = '';
        foreach ($matches as $h) {
            $text = trim(wp_strip_all_tags($h[2]));
            if ($text === '') {
                continue;
            }
            $items .= '<li><a href="#' . esc_attr($h[1]) . '">' . esc_html($text) . '</a></li>';
        }

        if ($items === '') {
            return '';
        }

        return "<div class=\"apiapu-toc\" style=\"margin:0 0 1.5em;padding:1em 1.25em;border:1px solid #e2e2e2;border-radius:8px;background:#f7f7f7;\">"
            . '<strong>' . esc_html__('Sommaire', 'auto-post-ia-pro-ultimate') . '</strong>'
            . '<ul>' . $items . '</ul></div>' . "\n";
    }

    private function set_error_status() {
        update_option('apiapu_generation_status', 'error');
    }
    
    public static function get_generation_status() {
        return get_option('apiapu_generation_status', 'idle');
    }
    
    public static function get_last_generation() {
        return get_option('apiapu_last_generation', null);
    }
    
    public static function get_stats() {
        global $wpdb;
        
        $generated_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_apiapu_generated' AND meta_value = '1'"
        );
        
        $today_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_apiapu_generated' 
            AND pm.meta_value = '1'
            AND DATE(p.post_date) = %s",
            current_time('Y-m-d')
        ));
        
        $published_count = $wpdb->get_var(
            "SELECT COUNT(*) FROM {$wpdb->postmeta} pm
            JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE pm.meta_key = '_apiapu_generated' 
            AND pm.meta_value = '1'
            AND p.post_status = 'publish'"
        );
        
        return array(
            'total_generated' => intval($generated_count),
            'today_generated' => intval($today_count),
            'published' => intval($published_count),
            'drafts' => intval($generated_count) - intval($published_count),
        );
    }
    
    public static function get_recent_articles($limit = 5) {
        global $wpdb;
        
        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title, p.post_status, p.post_date,
            (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = p.ID AND meta_key = '_apiapu_keyword' LIMIT 1) as keyword
            FROM {$wpdb->posts} p
            JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE pm.meta_key = '_apiapu_generated' AND pm.meta_value = '1'
            ORDER BY p.post_date DESC
            LIMIT %d",
            $limit
        ), ARRAY_A);
        
        return $posts;
    }
    
    private function is_duplicate($keyword) {
        global $wpdb;
        
        // Check by title
        if (!function_exists('post_exists')) {
            require_once(ABSPATH . 'wp-admin/includes/post.php');
        }
        
        if (post_exists($keyword)) {
            return true;
        }
        
        // Check by meta key _apiapu_keyword
        $existing_by_meta = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_apiapu_keyword' AND meta_value = %s LIMIT 1",
            $keyword
        ));
        
        if ($existing_by_meta) {
            return true;
        }
        
        return false;
    }
    
    private function get_default_author() {
        $user_id = get_current_user_id();
        
        if ($user_id > 0) {
            return $user_id;
        }
        
        // If cron (no user logged in), find first admin
        $admins = get_users(array('role' => 'administrator', 'number' => 1));
        
        if (!empty($admins)) {
            return $admins[0]->ID;
        }
        
        return 1; // Fallback
    }
}
