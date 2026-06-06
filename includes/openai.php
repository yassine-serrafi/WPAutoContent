<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_OpenAI {
    
    private $api_key;
    private $model;
    private $temperature;
    private $max_tokens;
    private $proxy;
    private $language;
    private $niche;
    private $api_url = 'https://api.openai.com/v1/chat/completions';
    
    public function __construct() {
        $settings = get_option('apiapu_settings', array());
        
        $this->api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
        $this->model = isset($settings['model']) ? $settings['model'] : 'gpt-4o';
        $this->temperature = isset($settings['temperature']) ? floatval($settings['temperature']) : 0.7;
        $this->max_tokens = isset($settings['max_tokens']) ? intval($settings['max_tokens']) : 4000;
        $this->proxy = isset($settings['proxy']) ? $settings['proxy'] : '';
        $this->language = isset($settings['language']) ? $settings['language'] : 'fr';
        $this->niche = isset($settings['niche']) ? $settings['niche'] : 'general';
        
        if ($this->model === 'custom' && !empty($settings['custom_model'])) {
            $this->model = $settings['custom_model'];
        }
    }
    
    public function test_connection() {
        if (empty($this->api_key)) {
            return array(
                'success' => false,
                'message' => __('Clé API non configurée', 'auto-post-ia-pro-ultimate'),
            );
        }
        
        $response = $this->make_request(array(
            array('role' => 'user', 'content' => 'Dis simplement "OK" pour tester la connexion.')
        ), 10);
        
        if (isset($response['error'])) {
            APIAPU_Logger::log('Test API échoué: ' . $response['error'], 'error');
            return array(
                'success' => false,
                'message' => $response['error'],
            );
        }
        
        APIAPU_Logger::log('Test API réussi', 'success');
        
        return array(
            'success' => true,
            'message' => __('Connexion API réussie!', 'auto-post-ia-pro-ultimate'),
            'model' => $this->model,
        );
    }
    
    public function generate_article($keyword, $prompt_template = null) {
        if (empty($this->api_key)) {
            APIAPU_Logger::log('Génération impossible: Clé API manquante', 'error', array('keyword' => $keyword));
            return array(
                'success' => false,
                'error' => __('Clé API OpenAI non configurée', 'auto-post-ia-pro-ultimate'),
            );
        }
        
        if ($prompt_template === null) {
            $prompt_template = get_option('apiapu_prompt', APIAPU_Utils::get_default_prompt());
        }
        
        $prompt = str_replace('{{KEYWORD}}', $keyword, $prompt_template);
        
        $system_message = "";
        $length_instruction = "";
        if ($this->language === 'en') {
            $language_instruction = " You MUST write the content EXCLUSIVELY in ENGLISH.";
            $length_instruction = " The 'content' field MUST contain a complete, in-depth article of AT LEAST 900 words, with no filler.";
            $system_message = "You are an expert SEO assistant. You always respond in valid JSON.";
        } elseif ($this->language === 'ar') {
            $language_instruction = " You MUST write the content EXCLUSIVELY in ARABIC.";
            $length_instruction = " يجب أن يحتوي حقل 'content' على مقال كامل ومتعمق لا يقل عن 900 كلمة، وبدون حشو.";
            $system_message = "أنت مساعد خبير في تحسين محركات البحث. أنت تجيب دائمًا بـ JSON صالح.";
        } else {
            $language_instruction = " Tu dois rédiger le contenu EXCLUSIVEMENT en FRANÇAIS.";
            $length_instruction = " Le champ 'content' DOIT contenir un article complet et approfondi d'AU MOINS 900 mots, sans remplissage inutile.";
            $system_message = "Tu es un assistant expert en rédaction SEO. Tu réponds toujours en JSON valide.";
        }
        
        // Instruction propre à la niche choisie (angle expert, FAQ non-redondante,
        // perspective originale, et pour les niches sensibles : disclaimer + sources réelles).
        $niche_instruction = APIAPU_Utils::get_niche_instruction($this->niche, $this->language);

        // Exigence d'excellence : intention de recherche, spécificité, angle, valeur.
        $quality_instruction = $this->get_quality_instruction();

        APIAPU_Logger::log('Début génération article (' . $this->language . ' / niche: ' . $this->niche . ')', 'info', array('keyword' => $keyword));

        $messages = array(
            array(
                'role' => 'system',
                'content' => $system_message . $language_instruction
            ),
            array(
                'role' => 'user',
                'content' => $prompt . "\n\nIMPORTANT: " . $language_instruction . $length_instruction . $quality_instruction . $niche_instruction
            )
        );
        
        // On garantit un budget de tokens suffisant pour un article long, même si
        // le réglage "Max Tokens" de l'utilisateur est bas (sinon la réponse est
        // tronquée et l'article reste court).
        $article_max_tokens = max($this->max_tokens, 3000);

        $response = $this->make_request($messages, $article_max_tokens);

        if (isset($response['error'])) {
            APIAPU_Logger::log('Erreur OpenAI: ' . $response['error'], 'error', array(
                'keyword' => $keyword,
                'error_details' => $response
            ));

            return array(
                'success' => false,
                'error' => $response['error'],
            );
        }
        
        $content = $response['choices'][0]['message']['content'] ?? '';
        
        $json_content = APIAPU_Utils::extract_json_from_response($content);
        $validation = APIAPU_Utils::validate_article_json($json_content);
        
        if (!$validation['valid']) {
            APIAPU_Logger::log('JSON invalide reçu: ' . $validation['error'], 'error', array(
                'keyword' => $keyword,
                'openai_response' => $content
            ));
            
            return array(
                'success' => false,
                'error' => $validation['error'],
                'raw_response' => $content,
            );
        }
        
        $data = $validation['data'];

        // FORÇAGE INTELLIGENT DE LA LONGUEUR :
        // si le premier jet est trop court, on demande au modèle d'enrichir
        // l'article en une seconde passe (bien plus fiable qu'une simple consigne).
        $target_words = 900;
        $current_words = APIAPU_Utils::count_words($data['content']);

        if ($current_words < $target_words) {
            APIAPU_Logger::log(
                "Premier jet court ({$current_words} mots) : enrichissement automatique en cours",
                'info',
                array('keyword' => $keyword)
            );

            $expanded = $this->expand_article($data, $keyword, $system_message, $language_instruction, $target_words, $article_max_tokens);

            if ($expanded !== null && APIAPU_Utils::count_words($expanded['content']) > $current_words) {
                $data = $expanded;
            }
        }

        APIAPU_Logger::log('Article généré avec succès (' . APIAPU_Utils::count_words($data['content']) . ' mots)', 'success', array(
            'keyword' => $keyword,
            'openai_response' => array(
                'model' => $response['model'] ?? $this->model,
                'usage' => $response['usage'] ?? null,
            )
        ));

        return array(
            'success' => true,
            'data' => $data,
            'usage' => $response['usage'] ?? null,
        );
    }

    /**
     * Seconde passe : demande au modèle d'enrichir un article jugé trop court.
     * Retourne les nouvelles données JSON validées, ou null en cas d'échec
     * (dans ce cas, l'appelant conserve l'article d'origine).
     */
    private function expand_article($data, $keyword, $system_message, $language_instruction, $target_words, $max_tokens) {
        $existing_json = wp_json_encode($data);

        if ($this->language === 'en') {
            $user_instruction = "The following JSON article about \"{$keyword}\" is too short. Rewrite it so the 'content' field reaches AT LEAST {$target_words} words: add depth, concrete examples, extra H2 sections and a relevant FAQ. Keep the EXACT same JSON keys, the same topic and the same language. Do not remove any existing information. Return ONLY the complete, valid JSON.";
        } elseif ($this->language === 'ar') {
            $user_instruction = "مقال JSON التالي حول \"{$keyword}\" قصير جداً. أعد كتابته بحيث يصل حقل 'content' إلى {$target_words} كلمة على الأقل: أضِف العمق وأمثلة ملموسة وأقساماً إضافية (H2) وأسئلة شائعة عند الحاجة. حافِظ على نفس مفاتيح JSON ونفس الموضوع ونفس اللغة. لا تحذف أي معلومة موجودة. أرجِع فقط JSON كاملاً وصالحاً.";
        } else {
            $user_instruction = "L'article JSON suivant sur \"{$keyword}\" est trop court. Réécris-le pour que le champ 'content' atteigne AU MOINS {$target_words} mots : ajoute de la profondeur, des exemples concrets, des sections H2 supplémentaires et une FAQ pertinente. Conserve EXACTEMENT les mêmes clés JSON, le même sujet et la même langue. Ne supprime aucune information existante. Retourne UNIQUEMENT le JSON complet et valide.";
        }

        // On rappelle les exigences de niche + d'excellence (disclaimer, sources,
        // FAQ non-redondante, spécificité, angle) pour les préserver à l'enrichissement.
        $niche_instruction = APIAPU_Utils::get_niche_instruction($this->niche, $this->language);
        $quality_instruction = $this->get_quality_instruction();

        $messages = array(
            array('role' => 'system', 'content' => $system_message . $language_instruction),
            array('role' => 'user', 'content' => $user_instruction . $quality_instruction . $niche_instruction . "\n\n" . $existing_json),
        );

        $response = $this->make_request($messages, $max_tokens);

        if (isset($response['error'])) {
            APIAPU_Logger::log('Enrichissement échoué: ' . $response['error'], 'warning', array('keyword' => $keyword));
            return null;
        }

        $content = $response['choices'][0]['message']['content'] ?? '';
        $json_content = APIAPU_Utils::extract_json_from_response($content);
        $validation = APIAPU_Utils::validate_article_json($json_content);

        return $validation['valid'] ? $validation['data'] : null;
    }
    
    /**
     * Exigence d'excellence appliquée à chaque génération (indépendante du prompt
     * enregistré) : sert l'intention de recherche, pousse la spécificité, l'angle
     * et les éléments à forte valeur. Sans inventer de données.
     */
    private function get_quality_instruction() {
        if ($this->language === 'en') {
            return " EXCELLENCE REQUIREMENT: First identify the real search intent behind the keyword and answer it directly from the start. Be CONCRETE and SPECIFIC: precise examples, actionable steps, named methods/tools, real-life scenarios — ban vague generalities and filler. Take a clear, useful stance (prioritize, recommend, decide) instead of a neutral overview. Use descriptive, specific subheadings (avoid generic 'Introduction'/'Conclusion'). Include at least one high-value, scannable element when relevant (a step-by-step list, a checklist, a comparison table, or a 'key takeaways' box). Also cover practical tips, common mistakes to avoid, and useful nuances. Never invent figures, dates or studies: if you give an order of magnitude, stay cautious. Never explicitly mention 'search intent' or the keyword as a meta-topic in the text — answer it naturally. If you present figures in a table (prices, specs), label them clearly as indicative/approximate, never as exact guaranteed values.";
        }

        if ($this->language === 'ar') {
            return " متطلب التميّز: حدّد أولاً نية البحث الحقيقية وراء الكلمة المفتاحية وأجب عنها مباشرة منذ البداية. كن ملموساً ومحدّداً: أمثلة دقيقة، خطوات قابلة للتطبيق، طرق/أدوات مذكورة بالاسم، سيناريوهات واقعية — وتجنّب العموميات الغامضة والحشو. اتخذ زاوية واضحة ومفيدة (رتّب الأولويات، اقترح، احسم) بدل العرض المحايد. استخدم عناوين فرعية وصفية ومحددة (تجنّب 'مقدمة'/'خاتمة' العامة). أدرج عنصراً واحداً على الأقل عالي القيمة وسهل المسح عند الاقتضاء (قائمة خطوات، قائمة تحقّق، جدول مقارنة، أو صندوق 'أهم النقاط'). غطِّ أيضاً النصائح العملية والأخطاء الشائعة والفروق المفيدة. لا تختلق أرقاماً أو تواريخ أو دراسات: إن أعطيت ترتيب حجم فابقَ حذراً. لا تذكر صراحةً «نية البحث» أو الكلمة المفتاحية كموضوع وصفي داخل النص — أجب عنها بشكل طبيعي. وإذا قدّمت أرقاماً في جدول (أسعار، مواصفات) فبيّنها بوضوح على سبيل الاسترشاد لا كقيَم دقيقة مضمونة.";
        }

        return " EXIGENCE D'EXCELLENCE : Identifie d'abord l'intention de recherche réelle derrière le mot-clé et réponds-y directement dès le début. Sois CONCRET et SPÉCIFIQUE : exemples précis, étapes actionnables, méthodes/outils nommés, scénarios réels — bannis les généralités vagues et le remplissage. Adopte un angle clair et utile (priorise, recommande, tranche) plutôt qu'un survol neutre. Donne des sous-titres descriptifs et spécifiques (évite les 'Introduction'/'Conclusion' génériques). Intègre au moins un élément à forte valeur et facile à parcourir quand c'est pertinent (liste d'étapes, checklist, tableau comparatif, ou encadré 'À retenir'). Couvre aussi les conseils pratiques, les erreurs fréquentes à éviter et les nuances utiles. N'invente jamais de chiffres, dates ou études : si tu donnes un ordre de grandeur, reste prudent. N'évoque jamais explicitement l'« intention de recherche » ni le mot-clé comme méta-sujet dans le texte — réponds-y naturellement. Si tu présentes des chiffres dans un tableau (prix, spécifications), indique-les clairement à titre indicatif/approximatif, jamais comme des valeurs exactes garanties.";
    }

    private function make_request($messages, $max_tokens = null) {
        if ($max_tokens === null) {
            $max_tokens = $this->max_tokens;
        }
        
        $body = array(
            'model' => $this->model,
            'messages' => $messages,
            'temperature' => $this->temperature,
            'max_tokens' => $max_tokens,
        );
        
        $args = array(
            'body' => wp_json_encode($body),
            'headers' => array(
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key,
            ),
            'timeout' => 120,
            'sslverify' => true,
        );
        
        if (!empty($this->proxy)) {
            $args['proxy'] = $this->proxy;
        }
        
        $response = wp_remote_post($this->api_url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'error' => $response->get_error_message(),
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
        
        if ($response_code !== 200) {
            $error_message = isset($data['error']['message']) 
                ? $data['error']['message'] 
                : __('Erreur API OpenAI (Code: ', 'auto-post-ia-pro-ultimate') . $response_code . ')';
            
            return array(
                'error' => $error_message,
                'code' => $response_code,
                'response' => $data,
            );
        }
        
        return $data;
    }
    
    public function get_model() {
        return $this->model;
    }
    
    public function is_configured() {
        return !empty($this->api_key);
    }
}
