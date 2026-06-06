<?php
if (!defined('ABSPATH')) {
    exit;
}

class APIAPU_Utils {
    
    public static function get_default_prompt($language = 'fr') {
        if ($language === 'en') {
            return "You are an expert writer and a genuine specialist on the topic: {{KEYWORD}}. You write for HUMANS first, not for search engines. Your goal: a genuinely useful, original and pleasant article that precisely answers the search intent behind \"{{KEYWORD}}\".

QUALITY RULES (Google Helpful Content 2026):

1. INTENT & VALUE:
- Answer DIRECTLY what the reader is looking for, right from the introduction (no generic filler intro).
- Provide concrete value: specific examples, real use cases, actionable tips, useful comparisons.
- Avoid empty generalities and \"padding\" paragraphs.

2. NATURAL KEYWORD PLACEMENT (no stuffing):
- The keyword \"{{KEYWORD}}\" must appear naturally in the H1 title, in the introduction and in one subheading.
- NEVER force the keyword repetitively or artificially. Use synonyms and related terms instead.

3. HUMAN STYLE (anti-AI footprint):
- Write in a natural, direct and varied tone. Mix short and long sentences.
- FORBIDDEN AI-cliché phrases: \"In today's world\", \"In the digital age\", \"It is important to note\", \"In conclusion\", \"dive into\", \"unlock the potential\", \"ever-evolving landscape\", \"nowadays\". Do not overuse \"Moreover / Furthermore / Additionally\".
- Use <strong> sparingly, only for genuinely key points.

4. ACCURACY (CRUCIAL):
- NEVER invent statistics, numbers, dates, prices, proper names, quotes or specific studies. If unsure about a figure, stay general or hedge (\"often\", \"generally\").
- No fake sources. A cautious statement is better than an invented data point.

5. STRUCTURE (VARIABLE, not a fixed template):
- A short, useful introduction, then 4 to 7 logical H2 sections, with H3 where needed.
- Add a FAQ OR a comparison table ONLY if it genuinely adds value to this topic (do not include them systematically).
- Write a complete, in-depth article of AT LEAST 900 words (ideally 1000 to 1500 words). No filler: every sentence must add value.

6. OUTPUT FORMAT (STRICT JSON):
{
    \"title\": \"Clear, natural H1 title that includes the keyword\",
    \"meta_title\": \"SEO title (max 60 chars)\",
    \"meta_description\": \"Useful, honest meta description (max 155 chars)\",
    \"slug\": \"short-clean-url-slug\",
    \"content\": \"Full HTML content using <h2>, <h3>, <p>, <ul>, <li>, <strong>, and <table> only when relevant\",
    \"excerpt\": \"Honest summary of the article (max 200 chars)\",
    \"image_suggestions\": [\"precise visual description (preferably in English)\", \"description 2\", \"description 3\"],
    \"tags\": [\"main keyword\", \"related topic 1\", \"related topic 2\", \"related topic 3\"]
}

IMPORTANT: Return ONLY valid JSON, with no text before or after. The content must be ready to be reviewed and then published by a human.";
        } elseif ($language === 'ar') {
            return "أنت كاتب محترف وخبير حقيقي في الموضوع: {{KEYWORD}}. تكتب للبشر أولاً وليس لمحركات البحث. هدفك: مقال مفيد فعلاً وأصلي وممتع للقراءة، يجيب بدقة على نية البحث وراء \"{{KEYWORD}}\".

قواعد الجودة (Google Helpful Content 2026):

1. النية والقيمة:
- أجب مباشرة عمّا يبحث عنه القارئ منذ المقدمة (بدون مقدمة عامة حشوية).
- قدّم قيمة ملموسة: أمثلة دقيقة، حالات استخدام واقعية، نصائح قابلة للتطبيق، مقارنات مفيدة.
- تجنّب العموميات الفارغة والفقرات الحشوية.

2. وضع الكلمة المفتاحية بشكل طبيعي (بدون حشو):
- يجب أن تظهر الكلمة المفتاحية \"{{KEYWORD}}\" بشكل طبيعي في عنوان H1 وفي المقدمة وفي أحد العناوين الفرعية.
- لا تُكرّر الكلمة المفتاحية بشكل مصطنع أبداً. استخدم المرادفات والكلمات ذات الصلة.

3. أسلوب بشري (ضد بصمة الذكاء الاصطناعي):
- اكتب بنبرة طبيعية ومباشرة ومتنوعة. نوّع بين الجمل القصيرة والطويلة.
- عبارات ممنوعة نمطية للذكاء الاصطناعي: \"في عالم اليوم\"، \"في العصر الرقمي\"، \"من المهم ملاحظة\"، \"في الختام\"، \"دعنا نغوص\"، \"أطلق العنان\". لا تُفرط في استخدام \"علاوة على ذلك / بالإضافة إلى ذلك\".
- استخدم <strong> باعتدال، فقط للنقاط المهمة فعلاً.

4. الدقة (بالغة الأهمية):
- لا تختلق أبداً إحصاءات أو أرقاماً أو تواريخ أو أسعاراً أو أسماء أو اقتباسات أو دراسات محددة. إذا لم تكن متأكداً من رقم، ابقَ عاماً أو استخدم صيغة تحفّظية (\"غالباً\"، \"بشكل عام\").
- لا مصادر زائفة. التصريح الحذر أفضل من بيانات مختلقة.

5. الهيكل (متغيّر، وليس قالباً ثابتاً):
- مقدمة قصيرة ومفيدة، ثم 4 إلى 7 أقسام H2 منطقية، مع H3 عند الحاجة.
- أضف قسم أسئلة شائعة أو جدول مقارنة فقط إذا كان يضيف قيمة حقيقية لهذا الموضوع (لا تضعهما بشكل تلقائي دائماً).
- اكتب مقالاً كاملاً ومتعمقاً لا يقل عن 900 كلمة (يُفضّل من 1000 إلى 1500 كلمة). بدون حشو: كل جملة يجب أن تضيف قيمة.

6. صيغة الإخراج (JSON صارم):
{
    \"title\": \"عنوان H1 واضح وطبيعي يتضمّن الكلمة المفتاحية\",
    \"meta_title\": \"عنوان SEO (حد أقصى 60 حرفًا)\",
    \"meta_description\": \"وصف Meta مفيد وصادق (حد أقصى 155 حرفًا)\",
    \"slug\": \"رابط-قصير-ونظيف\",
    \"content\": \"محتوى HTML كامل باستخدام <h2>, <h3>, <p>, <ul>, <li>, <strong>، و<table> فقط عند الحاجة\",
    \"excerpt\": \"ملخص صادق للمقال (حد أقصى 200 حرف)\",
    \"image_suggestions\": [\"وصف بصري دقيق (بالإنجليزية يُفضّل)\", \"وصف 2\", \"وصف 3\"],
    \"tags\": [\"الكلمة المفتاحية الرئيسية\", \"موضوع ذو صلة 1\", \"موضوع ذو صلة 2\", \"موضوع ذو صلة 3\"]
}

هام: أرجع فقط JSON صالحًا، بدون أي نص قبله أو بعده. يجب أن يكون المحتوى جاهزاً للمراجعة ثم النشر بواسطة إنسان.";
        }

        // Default to French
        return "Tu es un rédacteur web expert et un véritable spécialiste du sujet : {{KEYWORD}}. Tu écris pour des HUMAINS avant tout, pas pour un moteur de recherche. Ton objectif : un article réellement utile, original et agréable à lire, qui répond précisément à l'intention de recherche derrière \"{{KEYWORD}}\".

RÈGLES DE QUALITÉ (Google Helpful Content 2026) :

1. INTENTION & VALEUR :
- Réponds DIRECTEMENT à ce que cherche le lecteur, dès l'introduction (pas d'intro générique de remplissage).
- Apporte une valeur concrète : exemples précis, cas d'usage réels, conseils actionnables, comparaisons utiles.
- Évite les généralités creuses et les paragraphes \"de remplissage\".

2. PLACEMENT NATUREL DU MOT-CLÉ (sans bourrage) :
- Le mot-clé \"{{KEYWORD}}\" doit apparaître naturellement dans le titre H1, dans l'introduction et dans un sous-titre.
- Ne force JAMAIS le mot-clé de façon répétitive ou artificielle. Utilise des synonymes et le champ lexical.

3. STYLE HUMAIN (anti-empreinte IA) :
- Écris dans un ton naturel, direct et varié. Alterne phrases courtes et longues.
- FORMULES INTERDITES (clichés d'IA) : \"Dans le monde d'aujourd'hui\", \"À l'ère du numérique\", \"Il est important de noter\", \"En conclusion\", \"plongeons dans\", \"libérez le potentiel\", \"paysage en constante évolution\", \"de nos jours\". N'abuse pas non plus des \"De plus / Par ailleurs / En outre\".
- Mets en gras (<strong>) avec parcimonie, uniquement les points vraiment clés.

4. EXACTITUDE (CRUCIAL) :
- N'INVENTE JAMAIS de statistiques, chiffres, dates, prix, noms propres, citations ou études précises. Si tu n'es pas certain d'un chiffre, reste général ou nuancé (\"souvent\", \"en général\").
- Pas de fausses sources. Mieux vaut une affirmation prudente qu'une donnée inventée.

5. STRUCTURE (VARIABLE, pas un gabarit figé) :
- Une introduction courte et utile, puis 4 à 7 sections H2 logiques, avec des H3 si nécessaire.
- Ajoute une FAQ OU un tableau comparatif UNIQUEMENT si cela apporte une vraie valeur au sujet (ne les mets pas systématiquement).
- Rédige un article complet et approfondi d'AU MOINS 900 mots (idéalement 1000 à 1500 mots). Pas de remplissage : chaque phrase doit apporter de la valeur.

6. FORMAT DE SORTIE (JSON STRICT) :
{
    \"title\": \"Titre H1 clair et naturel incluant le mot-clé\",
    \"meta_title\": \"Titre SEO (max 60 caractères)\",
    \"meta_description\": \"Meta description utile et honnête (max 155 caractères)\",
    \"slug\": \"url-courte-et-propre\",
    \"content\": \"Contenu HTML complet (avec <h2>, <h3>, <p>, <ul>, <li>, <strong>, et <table> uniquement si pertinent)\",
    \"excerpt\": \"Résumé honnête de l'article (max 200 caractères)\",
    \"image_suggestions\": [\"description visuelle précise (en anglais de préférence)\", \"description 2\", \"description 3\"],
    \"tags\": [\"mot-clé principal\", \"sujet connexe 1\", \"sujet connexe 2\", \"sujet connexe 3\"]
}

IMPORTANT : Retourne UNIQUEMENT le JSON valide, sans aucun texte avant ou après. Le contenu doit être prêt à être relu puis publié par un humain.";
    }
    
    /**
     * Compte les mots d'un contenu HTML, de façon fiable en FR / EN / AR.
     */
    public static function count_words($html) {
        $plain = trim(wp_strip_all_tags((string) $html));

        if ($plain === '') {
            return 0;
        }

        return (int) preg_match_all('/[\p{L}\p{N}]+/u', $plain);
    }

    public static function sanitize_slug($text) {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
    
    public static function truncate_text($text, $length = 155) {
        if (strlen($text) <= $length) {
            return $text;
        }
        
        $text = substr($text, 0, $length);
        $last_space = strrpos($text, ' ');
        
        if ($last_space !== false) {
            $text = substr($text, 0, $last_space);
        }
        
        return $text . '...';
    }
    
    public static function clean_html_for_wp($html) {
        $allowed_tags = array(
            'h1' => array('class' => array(), 'id' => array()),
            'h2' => array('class' => array(), 'id' => array()),
            'h3' => array('class' => array(), 'id' => array()),
            'h4' => array('class' => array(), 'id' => array()),
            'h5' => array('class' => array(), 'id' => array()),
            'h6' => array('class' => array(), 'id' => array()),
            'p' => array('class' => array()),
            'ul' => array('class' => array()),
            'ol' => array('class' => array()),
            'li' => array('class' => array()),
            'strong' => array(),
            'em' => array(),
            'b' => array(),
            'i' => array(),
            'a' => array('href' => array(), 'title' => array(), 'target' => array(), 'rel' => array()),
            'img' => array('src' => array(), 'alt' => array(), 'class' => array(), 'width' => array(), 'height' => array()),
            'blockquote' => array('class' => array()),
            'br' => array(),
            'hr' => array(),
            'table' => array('class' => array()),
            'thead' => array(),
            'tbody' => array(),
            'tr' => array(),
            'th' => array(),
            'td' => array(),
            'span' => array('class' => array()),
            'div' => array('class' => array()),
            'figure' => array('class' => array()),
            'figcaption' => array(),
            'iframe' => array(
                'src' => array(),
                'width' => array(),
                'height' => array(),
                'frameborder' => array(),
                'allow' => array(),
                'allowfullscreen' => array(),
                'style' => array()
            ),
        );
        
        return wp_kses($html, $allowed_tags);
    }
    
    public static function format_file_size($bytes) {
        $units = array('B', 'KB', 'MB', 'GB');
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    public static function get_status_label($status) {
        $labels = array(
            'pending' => __('En attente', 'auto-post-ia-pro-ultimate'),
            'in_progress' => __('En cours', 'auto-post-ia-pro-ultimate'),
            'completed' => __('Terminé', 'auto-post-ia-pro-ultimate'),
            'error' => __('Erreur', 'auto-post-ia-pro-ultimate'),
            'used' => __('Utilisé', 'auto-post-ia-pro-ultimate'),
        );
        
        return isset($labels[$status]) ? $labels[$status] : $status;
    }
    
    public static function get_status_class($status) {
        $classes = array(
            'pending' => 'status-pending',
            'in_progress' => 'status-progress',
            'completed' => 'status-success',
            'error' => 'status-error',
            'used' => 'status-used',
            'info' => 'status-info',
            'warning' => 'status-warning',
            'success' => 'status-success',
        );
        
        return isset($classes[$status]) ? $classes[$status] : 'status-default';
    }
    
    public static function extract_json_from_response($response) {
        $response = trim($response);
        
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $response, $matches)) {
            $response = $matches[1];
        } elseif (preg_match('/```\s*([\s\S]*?)\s*```/', $response, $matches)) {
            $response = $matches[1];
        }
        
        $start = strpos($response, '{');
        $end = strrpos($response, '}');
        
        if ($start !== false && $end !== false) {
            $response = substr($response, $start, $end - $start + 1);
        }
        
        return $response;
    }
    
    public static function validate_article_json($json_string) {
        $data = json_decode($json_string, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return array(
                'valid' => false,
                'error' => 'JSON invalide: ' . json_last_error_msg(),
                'data' => null
            );
        }
        
        $required_fields = array('title', 'content');
        $missing_fields = array();
        
        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $missing_fields[] = $field;
            }
        }
        
        if (!empty($missing_fields)) {
            return array(
                'valid' => false,
                'error' => 'Champs manquants: ' . implode(', ', $missing_fields),
                'data' => null
            );
        }
        
        return array(
            'valid' => true,
            'error' => null,
            'data' => $data
        );
    }
    
    public static function get_available_models() {
        return array(
            'gpt-4o' => 'GPT-4o (Recommandé)',
            'gpt-4o-mini' => 'GPT-4o Mini',
            'gpt-4-turbo' => 'GPT-4 Turbo',
            'gpt-4' => 'GPT-4',
            'gpt-3.5-turbo' => 'GPT-3.5 Turbo',
            'custom' => 'Modèle personnalisé',
        );
    }
    
    public static function get_cron_frequencies() {
        return array(
            'none' => __('Pas de Cron', 'auto-post-ia-pro-ultimate'),
            'five_minutes' => __('Toutes les 5 minutes', 'auto-post-ia-pro-ultimate'),
            'ten_minutes' => __('Toutes les 10 minutes', 'auto-post-ia-pro-ultimate'),
            'thirty_minutes' => __('Toutes les 30 minutes', 'auto-post-ia-pro-ultimate'),
            'hourly' => __('Toutes les heures', 'auto-post-ia-pro-ultimate'),
            'twicedaily' => __('Toutes les 12 heures', 'auto-post-ia-pro-ultimate'),
            'daily' => __('Toutes les 24 heures', 'auto-post-ia-pro-ultimate'),
        );
    }
    
    public static function get_image_sources() {
        return array(
            'unsplash' => 'Unsplash',
            'pexels' => 'Pexels',
            'pixabay' => 'Pixabay',
        );
    }
    
    public static function get_seo_plugins() {
        return array(
            'none' => __('Aucun', 'auto-post-ia-pro-ultimate'),
            'yoast' => 'Yoast SEO',
            'rankmath' => 'Rank Math',
            'aioseo' => 'All In One SEO',
        );
    }

    public static function get_languages() {
        return array(
            'fr' => __('Français', 'auto-post-ia-pro-ultimate'),
            'en' => __('Anglais', 'auto-post-ia-pro-ultimate'),
            'ar' => __('Arabe', 'auto-post-ia-pro-ultimate'),
        );
    }

    /**
     * Liste des niches proposées dans les réglages (menu déroulant).
     */
    public static function get_niches() {
        return array(
            'general'     => __('Général', 'auto-post-ia-pro-ultimate'),
            'sante'       => __('Santé & Bien-être', 'auto-post-ia-pro-ultimate'),
            'sport'       => __('Sport & Fitness', 'auto-post-ia-pro-ultimate'),
            'finance'     => __('Finance & Argent', 'auto-post-ia-pro-ultimate'),
            'business'    => __('Business & Marketing', 'auto-post-ia-pro-ultimate'),
            'immobilier'  => __('Immobilier', 'auto-post-ia-pro-ultimate'),
            'juridique'   => __('Droit & Juridique', 'auto-post-ia-pro-ultimate'),
            'tech'        => __('Technologie & High-Tech', 'auto-post-ia-pro-ultimate'),
            'gaming'      => __('Gaming & Jeux vidéo', 'auto-post-ia-pro-ultimate'),
            'cuisine'     => __('Cuisine & Recettes', 'auto-post-ia-pro-ultimate'),
            'voyage'      => __('Voyage & Tourisme', 'auto-post-ia-pro-ultimate'),
            'beaute_mode' => __('Beauté & Mode', 'auto-post-ia-pro-ultimate'),
            'maison_deco' => __('Maison & Déco', 'auto-post-ia-pro-ultimate'),
            'auto_moto'   => __('Auto & Moto', 'auto-post-ia-pro-ultimate'),
            'animaux'     => __('Animaux', 'auto-post-ia-pro-ultimate'),
            'parentalite' => __('Parentalité & Famille', 'auto-post-ia-pro-ultimate'),
            'education'   => __('Éducation & Formation', 'auto-post-ia-pro-ultimate'),
            'lifestyle'   => __('Lifestyle & Développement personnel', 'auto-post-ia-pro-ultimate'),
        );
    }

    /**
     * Profil par niche : sensibilité YMYL, angle, et sources autoritatives RÉELLES.
     * ymyl : true (sujet sensible -> disclaimer obligatoire), 'soft' (rappel léger), false.
     * sources : domaines officiels réels à citer/lier (jamais d'URL inventée).
     */
    private static function get_niche_profiles() {
        return array(
            'sante'       => array('ymyl' => true,   'focus' => 'health & wellness',                'sources' => array('ameli.fr', 'has-sante.fr', 'vidal.fr', 'who.int', 'inserm.fr')),
            'sport'       => array('ymyl' => 'soft', 'focus' => 'sports & fitness',                 'sources' => array('ameli.fr', 'who.int')),
            'finance'     => array('ymyl' => true,   'focus' => 'personal finance & money',         'sources' => array('service-public.fr', 'economie.gouv.fr', 'amf-france.org', 'lafinancepourtous.com')),
            'business'    => array('ymyl' => false,  'focus' => 'business & marketing',             'sources' => array('bpifrance.fr', 'service-public.fr')),
            'immobilier'  => array('ymyl' => true,   'focus' => 'real estate',                      'sources' => array('service-public.fr', 'anil.org', 'notaires.fr')),
            'juridique'   => array('ymyl' => true,   'focus' => 'law & legal',                      'sources' => array('service-public.fr', 'legifrance.gouv.fr')),
            'tech'        => array('ymyl' => false,  'focus' => 'technology & high-tech',           'sources' => array()),
            'gaming'      => array('ymyl' => false,  'focus' => 'video games & gaming',             'sources' => array()),
            'cuisine'     => array('ymyl' => false,  'focus' => 'cooking & recipes',                'sources' => array()),
            'voyage'      => array('ymyl' => 'soft', 'focus' => 'travel & tourism',                 'sources' => array('diplomatie.gouv.fr', 'france-visas.gouv.fr')),
            'beaute_mode' => array('ymyl' => false,  'focus' => 'beauty & fashion',                 'sources' => array()),
            'maison_deco' => array('ymyl' => false,  'focus' => 'home & decoration',                'sources' => array()),
            'auto_moto'   => array('ymyl' => 'soft', 'focus' => 'automotive (cars & motorcycles)',  'sources' => array('securite-routiere.gouv.fr', 'service-public.fr')),
            'animaux'     => array('ymyl' => 'soft', 'focus' => 'pets & animals',                   'sources' => array()),
            'parentalite' => array('ymyl' => 'soft', 'focus' => 'parenting & family',               'sources' => array('ameli.fr', 'service-public.fr')),
            'education'   => array('ymyl' => false,  'focus' => 'education & learning',             'sources' => array('education.gouv.fr')),
            'lifestyle'   => array('ymyl' => false,  'focus' => 'lifestyle & personal development', 'sources' => array()),
        );
    }

    /**
     * Construit l'instruction de génération propre à la niche choisie (multilingue).
     * Toujours appliquée : perspective originale + FAQ non-redondante.
     * En plus, selon la niche : ton expert, disclaimer YMYL, sources autoritatives.
     */
    public static function get_niche_instruction($niche, $language = 'fr') {
        $profiles = self::get_niche_profiles();
        $profile  = isset($profiles[$niche]) ? $profiles[$niche] : null;

        $focus       = $profile ? $profile['focus'] : '';
        $ymyl        = $profile ? $profile['ymyl'] : false;
        $sources     = $profile ? $profile['sources'] : array();
        $sources_str = !empty($sources) ? implode(', ', $sources) : '';

        if ($language === 'en') {
            $txt  = " QUALITY & ANGLE: Bring a genuine original perspective (a clear point of view, concrete examples, practical specifics) — never a bland, generic summary.";
            $txt .= " The FAQ must answer NEW questions not already covered in the body (no repetition).";
            if ($focus) {
                $txt .= " This article belongs to the '{$focus}' niche: adopt the expert tone, vocabulary and angle of that field.";
            }
            if ($ymyl === true) {
                $txt .= " This is a sensitive (YMYL) topic: stay factual and cautious, never give personalized medical/financial/legal advice, and ADD a clear disclaimer inviting the reader to consult a qualified professional.";
            } elseif ($ymyl === 'soft') {
                $txt .= " Where relevant, remind the reader to consult a qualified professional before important decisions or actions.";
            }
            if ($sources_str) {
                $txt .= " Strengthen E-E-A-T using RECOGNIZED official sources RELEVANT to the COUNTRY the article is about (e.g., for France: {$sources_str}). For another country, cite that country's equivalent official body instead. Link only to an official domain you are certain of; otherwise name the source WITHOUT a link. NEVER invent URLs or studies.";
            }
            return $txt;
        }

        if ($language === 'ar') {
            $txt  = " الجودة والزاوية: قدّم وجهة نظر أصلية حقيقية (رأي واضح، أمثلة ملموسة، تفاصيل عملية) — وليس ملخصاً عاماً باهتاً.";
            $txt .= " يجب أن تجيب الأسئلة الشائعة عن أسئلة جديدة غير مذكورة في النص (بدون تكرار).";
            if ($focus) {
                $txt .= " ينتمي هذا المقال إلى مجال '{$focus}': اعتمد نبرة ومفردات وزاوية خبير في هذا المجال.";
            }
            if ($ymyl === true) {
                $txt .= " هذا موضوع حساس (YMYL): التزم الدقة والحذر، لا تقدّم نصيحة طبية/مالية/قانونية شخصية، وأضِف تنبيهاً واضحاً يدعو القارئ لاستشارة مختص مؤهل.";
            } elseif ($ymyl === 'soft') {
                $txt .= " عند الاقتضاء، ذكّر القارئ باستشارة مختص مؤهل قبل القرارات أو الإجراءات المهمة.";
            }
            if ($sources_str) {
                $txt .= " عزّز E-E-A-T بالاعتماد على مصادر رسمية معروفة وذات صلة بالبلد الذي يتناوله المقال (مثلاً لفرنسا: {$sources_str}). وبالنسبة لبلد آخر، اذكر الجهة الرسمية المكافئة لذلك البلد. اربط فقط بنطاق رسمي أنت متأكد منه؛ وإلا فاذكر الجهة بالاسم دون رابط. لا تختلق روابط أو دراسات أبداً.";
            }
            return $txt;
        }

        // Français (défaut)
        $txt  = " QUALITÉ & ANGLE : Apporte une véritable perspective originale (un point de vue clair, des exemples concrets, des détails pratiques) — jamais un résumé générique et tiède.";
        $txt .= " La FAQ doit répondre à des questions NOUVELLES, non déjà traitées dans le corps de l'article (aucune redite).";
        if ($focus) {
            $txt .= " Cet article relève de la niche « {$focus} » : adopte le ton, le vocabulaire et l'angle d'un expert de ce domaine.";
        }
        if ($ymyl === true) {
            $txt .= " C'est un sujet sensible (YMYL) : reste factuel et prudent, ne donne jamais de conseil médical/financier/juridique personnalisé, et AJOUTE un disclaimer clair invitant le lecteur à consulter un professionnel qualifié.";
        } elseif ($ymyl === 'soft') {
            $txt .= " Quand c'est pertinent, rappelle au lecteur de consulter un professionnel qualifié avant toute décision ou action importante.";
        }
        if ($sources_str) {
            $txt .= " Renforce l'E-E-A-T en t'appuyant sur des sources officielles RECONNUES et PERTINENTES pour le PAYS concerné par l'article (ex. pour la France : {$sources_str}). Pour un autre pays, cite plutôt l'organisme officiel équivalent de CE pays. Lie uniquement vers un domaine officiel dont tu es certain ; sinon, cite l'organisme par son nom SANS lien. N'invente JAMAIS d'URL ni d'étude.";
        }
        return $txt;
    }
}
