<?php
if (!defined('ABSPATH')) {
    exit;
}

$settings = get_option('apiapu_settings', array());
$models = APIAPU_Utils::get_available_models();
$image_sources = APIAPU_Utils::get_image_sources();
$seo_plugins = APIAPU_Utils::get_seo_plugins();
$languages = APIAPU_Utils::get_languages();
$niches = APIAPU_Utils::get_niches();
$cron_frequencies = APIAPU_Utils::get_cron_frequencies();
$cron_status = APIAPU_Cron_Manager::get_status();
$categories = get_categories(array('hide_empty' => false));
?>

<div class="wrap apiapu-wrap">
    <h1 class="apiapu-title">
        <span class="dashicons dashicons-admin-settings"></span>
        <?php _e('Paramètres', 'auto-post-ia-pro-ultimate'); ?>
    </h1>
    
    <div class="apiapu-settings">
        
        <form id="apiapu-settings-form" class="apiapu-form">
            
            <div class="apiapu-settings-container">

            <!-- Licence Removed for Freemium -->

            <!-- Paramètres OpenAI -->
            <div class="apiapu-card">
                <h2><?php _e('Paramètres OpenAI', 'auto-post-ia-pro-ultimate'); ?></h2>
                
                <div class="apiapu-form-row">
                    <label for="apiapu-api-key"><?php _e('Clé API OpenAI', 'auto-post-ia-pro-ultimate'); ?> <span class="required">*</span></label>
                    <input type="password" id="apiapu-api-key" name="api_key" value="<?php echo esc_attr($settings['api_key'] ?? ''); ?>" class="apiapu-input apiapu-input-lg" placeholder="sk-..." />
                    <p class="apiapu-help"><?php _e('Votre clé API OpenAI. Obtenez-la sur platform.openai.com', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>
                
                <div class="apiapu-form-row">
                    <label for="apiapu-model"><?php _e('Modèle IA', 'auto-post-ia-pro-ultimate'); ?></label>
                    <select id="apiapu-model" name="model" class="apiapu-select">
                        <?php foreach ($models as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['model'] ?? 'gpt-4o', $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="apiapu-form-row">
                    <label for="apiapu-language"><?php _e('Langue de génération', 'auto-post-ia-pro-ultimate'); ?></label>
                    <select id="apiapu-language" name="language" class="apiapu-select">
                        <?php foreach ($languages as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['language'] ?? 'fr', $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="apiapu-form-row">
                    <label for="apiapu-niche"><?php _e('Niche / Thématique du site', 'auto-post-ia-pro-ultimate'); ?></label>
                    <select id="apiapu-niche" name="niche" class="apiapu-select">
                        <?php foreach ($niches as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['niche'] ?? 'general', $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="apiapu-help"><?php _e('Adapte le ton et l\'angle de rédaction à votre domaine. Les niches sensibles (Santé, Finance, Droit, Immobilier) ajoutent automatiquement un disclaimer et des liens vers des sources officielles reconnues (E-E-A-T).', 'auto-post-ia-pro-ultimate'); ?></p>
                    <p class="apiapu-help" style="color:#b54708;"><span class="dashicons dashicons-info" style="font-size:16px;vertical-align:text-bottom;"></span> <?php _e('Conseil : pour les niches YMYL pures (Santé, Finance), une relecture humaine occasionnelle reste vivement conseillée avant publication — ce sont les sujets les plus surveillés par Google.', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>

                <div class="apiapu-form-row apiapu-custom-model-row" style="<?php echo ($settings['model'] ?? '') === 'custom' ? '' : 'display:none;'; ?>">
                    <label for="apiapu-custom-model"><?php _e('Nom du modèle personnalisé', 'auto-post-ia-pro-ultimate'); ?></label>
                    <input type="text" id="apiapu-custom-model" name="custom_model" value="<?php echo esc_attr($settings['custom_model'] ?? ''); ?>" class="apiapu-input" placeholder="gpt-4-0125-preview" />
                </div>
                
                <div class="apiapu-form-row apiapu-form-row-inline">
                    <div class="apiapu-form-col">
                        <label for="apiapu-temperature"><?php _e('Température', 'auto-post-ia-pro-ultimate'); ?></label>
                        <input type="number" id="apiapu-temperature" name="temperature" value="<?php echo esc_attr($settings['temperature'] ?? 0.7); ?>" class="apiapu-input" min="0" max="2" step="0.1" />
                        <p class="apiapu-help"><?php _e('0 = déterministe, 2 = créatif', 'auto-post-ia-pro-ultimate'); ?></p>
                    </div>
                    <div class="apiapu-form-col">
                        <label for="apiapu-max-tokens"><?php _e('Max Tokens', 'auto-post-ia-pro-ultimate'); ?></label>
                        <input type="number" id="apiapu-max-tokens" name="max_tokens" value="<?php echo esc_attr($settings['max_tokens'] ?? 4000); ?>" class="apiapu-input" min="500" max="16000" step="100" />
                        <p class="apiapu-help"><?php _e('Longueur maximale de la réponse', 'auto-post-ia-pro-ultimate'); ?></p>
                    </div>
                </div>
                
                <div class="apiapu-form-row">
                    <label for="apiapu-proxy"><?php _e('Proxy (optionnel)', 'auto-post-ia-pro-ultimate'); ?></label>
                    <input type="text" id="apiapu-proxy" name="proxy" value="<?php echo esc_attr($settings['proxy'] ?? ''); ?>" class="apiapu-input" placeholder="http://proxy:port" />
                </div>
                
                <div class="apiapu-form-row">
                    <button type="button" id="apiapu-test-api-btn" class="apiapu-btn apiapu-btn-secondary">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Tester la connexion API', 'auto-post-ia-pro-ultimate'); ?>
                    </button>
                    <span id="apiapu-api-test-result" class="apiapu-test-result"></span>
                </div>
            </div>
            
            <!-- Paramètres de publication -->
            <div class="apiapu-card">
                <h2><?php _e('Paramètres de publication', 'auto-post-ia-pro-ultimate'); ?></h2>
                
                <div class="apiapu-form-row">
                    <label for="apiapu-post-status"><?php _e('Statut de publication', 'auto-post-ia-pro-ultimate'); ?></label>
                    <select id="apiapu-post-status" name="post_status" class="apiapu-select">
                        <option value="draft" <?php selected($settings['post_status'] ?? 'draft', 'draft'); ?>><?php _e('Brouillon', 'auto-post-ia-pro-ultimate'); ?></option>
                        <option value="publish" <?php selected($settings['post_status'] ?? 'draft', 'publish'); ?>><?php _e('Publié', 'auto-post-ia-pro-ultimate'); ?></option>
                        <option value="pending" <?php selected($settings['post_status'] ?? 'draft', 'pending'); ?>><?php _e('En attente de relecture', 'auto-post-ia-pro-ultimate'); ?></option>
                    </select>
                </div>
                
                <div class="apiapu-form-row">
                    <label for="apiapu-category"><?php _e('Catégorie par défaut', 'auto-post-ia-pro-ultimate'); ?></label>
                    <select id="apiapu-category" name="category" class="apiapu-select">
                        <option value="0"><?php _e('-- Aucune --', 'auto-post-ia-pro-ultimate'); ?></option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>" <?php selected($settings['category'] ?? 0, $cat->term_id); ?>>
                                <?php echo esc_html($cat->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <!-- Paramètres d'images -->
            <div class="apiapu-card">
                <h2><?php _e('Paramètres d\'images', 'auto-post-ia-pro-ultimate'); ?></h2>
                
                <div class="apiapu-form-row apiapu-form-row-inline">
                    <div class="apiapu-form-col">
                        <label for="apiapu-image-count"><?php _e('Nombre d\'images', 'auto-post-ia-pro-ultimate'); ?></label>
                        <input type="number" id="apiapu-image-count" name="image_count" value="1" class="apiapu-input" disabled />
                        <p class="apiapu-help"><?php _e('Limité à 1 image en version gratuite.', 'auto-post-ia-pro-ultimate'); ?></p>
                    </div>
                    <div class="apiapu-form-col">
                        <label for="apiapu-image-source"><?php _e('Source d\'images', 'auto-post-ia-pro-ultimate'); ?></label>
                        <select id="apiapu-image-source" name="image_source" class="apiapu-select">
                           <option value="pexels" selected>Pexels</option>
                           <option value="unsplash" disabled>Unsplash (PRO)</option>
                           <option value="pixabay" disabled>Pixabay (PRO)</option>
                        </select>
                    </div>
                </div>

                <div class="apiapu-form-row apiapu-pexels-key-row">
                    <label for="apiapu-pexels-api-key"><?php _e('Clé API Pexels', 'auto-post-ia-pro-ultimate'); ?></label>
                    <input type="password" id="apiapu-pexels-api-key" name="pexels_api_key" value="<?php echo esc_attr($settings['pexels_api_key'] ?? ''); ?>" class="apiapu-input apiapu-input-lg" placeholder="Votre clé API Pexels" />
                    <p class="apiapu-help"><?php _e('Obtenez votre clé API sur pexels.com/api', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>
            </div>

            <div class="apiapu-card">
                <h2><?php _e('Vidéo YouTube', 'auto-post-ia-pro-ultimate'); ?> <span class="apiapu-pro-badge" style="background:#5219bc;color:white;padding:2px 8px;border-radius:4px;font-size:12px;vertical-align:middle;">PRO</span></h2>
                
                <div class="apiapu-form-row">
                    <label class="apiapu-checkbox-label" style="opacity: 0.6; cursor: not-allowed;">
                        <input type="checkbox" id="apiapu-youtube-enabled" name="youtube_enabled" value="0" disabled />
                        <?php _e('Ajouter une vidéo YouTube pertinente', 'auto-post-ia-pro-ultimate'); ?>
                    </label>
                    <p class="apiapu-help"><?php _e('Disponible uniquement dans la version PRO.', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>
            </div>

            <!-- Paramètres SEO -->
            <div class="apiapu-card">
                <h2><?php _e('Paramètres SEO', 'auto-post-ia-pro-ultimate'); ?></h2>
                
                <div class="apiapu-form-row">
                    <label for="apiapu-seo-plugin"><?php _e('Plugin SEO', 'auto-post-ia-pro-ultimate'); ?></label>
                    <select id="apiapu-seo-plugin" name="seo_plugin" class="apiapu-select">
                        <?php foreach ($seo_plugins as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['seo_plugin'] ?? 'none', $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="apiapu-help"><?php _e('Intégration avec votre plugin SEO existant', 'auto-post-ia-pro-ultimate'); ?></p>
                    <p class="apiapu-help" style="background:#eaf3fb;border-left:3px solid #2271b1;padding:10px 12px;border-radius:4px;">
                        <span class="dashicons dashicons-info" style="font-size:16px;vertical-align:text-bottom;"></span>
                        <strong><?php _e('Important :', 'auto-post-ia-pro-ultimate'); ?></strong>
                        <?php _e('le Meta title et la Meta description ne s\'affichent dans le site que si un plugin SEO (Yoast, Rank Math ou All In One SEO) est actif et sélectionné ici. Sans plugin SEO, le contenu reste optimisé, mais la meta description personnalisée n\'est pas envoyée à Google.', 'auto-post-ia-pro-ultimate'); ?>
                    </p>
                </div>
                
                <div class="apiapu-form-row">
                    <label class="apiapu-checkbox-label">
                        <input type="checkbox" id="apiapu-auto-hn" name="auto_hn" value="1" <?php checked($settings['auto_hn'] ?? 1, 1); ?> />
                        <?php _e('Optimisation automatique des titres (Hn)', 'auto-post-ia-pro-ultimate'); ?>
                    </label>
                    <p class="apiapu-help"><?php _e('Convertit les H1 en H2 et ajoute des IDs aux titres', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>
                
                <div class="apiapu-form-row">
                    <label class="apiapu-checkbox-label">
                        <input type="checkbox" id="apiapu-schema-org" name="schema_org" value="1" <?php checked($settings['schema_org'] ?? 0, 1); ?> />
                        <?php _e('Générer Schema.org Article', 'auto-post-ia-pro-ultimate'); ?>
                    </label>
                    <p class="apiapu-help"><?php _e('Ajoute les données structurées Schema.org pour un meilleur référencement', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>

                <div class="apiapu-form-row">
                    <label class="apiapu-checkbox-label">
                        <input type="checkbox" id="apiapu-auto-toc" name="auto_toc" value="1" <?php checked($settings['auto_toc'] ?? 0, 1); ?> />
                        <?php _e('Ajouter un sommaire (table des matières) en haut de l\'article', 'auto-post-ia-pro-ultimate'); ?>
                    </label>
                    <p class="apiapu-help"><?php _e('Génère automatiquement un sommaire cliquable à partir des sous-titres (H2). Affiché uniquement si l\'article contient au moins 3 sections.', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>

                <div class="apiapu-form-row">
                    <p class="apiapu-help" style="background:#fff8e5;border-left:3px solid #f0b849;padding:10px 12px;border-radius:4px;">
                        <strong><?php _e('Maillage interne automatique :', 'auto-post-ia-pro-ultimate'); ?></strong>
                        <?php _e('chaque article est automatiquement relié aux articles existants du même thème (liens contextuels + bloc « À lire aussi »).', 'auto-post-ia-pro-ultimate'); ?>
                    </p>
                </div>
            </div>

            <!-- Paramètres Cron -->
            <div class="apiapu-card">
                <h2><?php _e('Planification Cron', 'auto-post-ia-pro-ultimate'); ?> <span class="apiapu-pro-badge" style="background:#5219bc;color:white;padding:2px 8px;border-radius:4px;font-size:12px;vertical-align:middle;">PRO</span></h2>
                
                <div class="apiapu-form-row">
                    <label class="apiapu-checkbox-label" style="opacity:0.6; cursor:not-allowed;">
                        <input type="checkbox" id="apiapu-cron-enabled" name="cron_enabled" value="0" disabled />
                        <?php _e('Activer la génération automatique', 'auto-post-ia-pro-ultimate'); ?>
                    </label>
                </div>
                
                <div class="apiapu-form-row">
                    <label for="apiapu-cron-frequency"><?php _e('Fréquence', 'auto-post-ia-pro-ultimate'); ?></label>
                    <select id="apiapu-cron-frequency" name="cron_frequency" class="apiapu-select" disabled style="opacity:0.6; cursor:not-allowed;">
                        <?php foreach ($cron_frequencies as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($cron_status['frequency'], $value); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                     <p class="apiapu-help" style="color:#d63638;"><?php _e('L\'automatisation CRON est réservée à la version PRO.', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>
                
                <div class="apiapu-form-row">
                    <button type="button" id="apiapu-run-cron-btn" class="apiapu-btn apiapu-btn-secondary" disabled style="opacity:0.6; cursor:not-allowed;">
                        <span class="dashicons dashicons-controls-play"></span>
                        <?php _e('Exécuter cron maintenant', 'auto-post-ia-pro-ultimate'); ?>
                    </button>
                    <p class="apiapu-help"><?php _e('Passez à la version PRO pour automatiser la création de contenu.', 'auto-post-ia-pro-ultimate'); ?></p>
                </div>
            </div>

            <!-- Données & Désinstallation -->
            <div class="apiapu-card">
                <h2><?php _e('Données & Désinstallation', 'auto-post-ia-pro-ultimate'); ?></h2>

                <div class="apiapu-form-row">
                    <label class="apiapu-checkbox-label">
                        <input type="checkbox" id="apiapu-delete-data" name="delete_data_on_uninstall" value="1" <?php checked($settings['delete_data_on_uninstall'] ?? 0, 1); ?> />
                        <?php _e('Supprimer toutes les données du plugin à la désinstallation', 'auto-post-ia-pro-ultimate'); ?>
                    </label>
                    <p class="apiapu-help" style="background:#fdecea;border-left:3px solid #d63638;padding:10px 12px;border-radius:4px;">
                        <strong><?php _e('Décochée par défaut (recommandé).', 'auto-post-ia-pro-ultimate'); ?></strong>
                        <?php _e('Si vous cochez cette case, la suppression du plugin effacera définitivement vos mots-clés, réglages, logs et statistiques. Sinon, tout est conservé en cas de réinstallation.', 'auto-post-ia-pro-ultimate'); ?>
                        <br><?php _e('Dans tous les cas, vos articles déjà publiés ne sont JAMAIS supprimés.', 'auto-post-ia-pro-ultimate'); ?>
                    </p>
                </div>
            </div>

            <!-- Note SEO -->
            <div class="apiapu-card" style="border-left:4px solid #46b450;">
                <p class="apiapu-help" style="font-size:14px; margin:0;">
                    <span class="dashicons dashicons-yes-alt" style="color:#46b450;vertical-align:text-bottom;"></span>
                    <strong><?php _e('Conforme aux bonnes pratiques SEO & IA de Google 2026.', 'auto-post-ia-pro-ultimate'); ?></strong><br>
                    <?php _e('Structure propre, contenu structuré (tableaux, FAQ, étapes), E-E-A-T et données structurées sont gérés automatiquement.', 'auto-post-ia-pro-ultimate'); ?><br>
                    <em><?php _e('Le « plus » qui fera la différence dans les classements 2026/2027 : l\'originalité humaine ajoutée à la relecture (un chiffre réel, une expérience vécue, une donnée que vous seul possédez).', 'auto-post-ia-pro-ultimate'); ?></em>
                </p>
            </div>

            <!-- Bouton Sauvegarder -->
            <div class="apiapu-form-actions">
                <button type="submit" class="apiapu-btn apiapu-btn-primary apiapu-btn-lg">
                    <span class="dashicons dashicons-saved"></span>
                    <?php _e('Sauvegarder les paramètres', 'auto-post-ia-pro-ultimate'); ?>
                </button>
                <span id="apiapu-save-result" class="apiapu-save-result"></span>
            </div>
            
        </form>

        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#apiapu-image-source').on('change', function() {
        if ($(this).val() === 'pexels') {
            $('.apiapu-pexels-key-row').show();
        } else {
            $('.apiapu-pexels-key-row').hide();
        }
    });

    // Trigger change on load to set initial state
    // $('#apiapu-image-source').trigger('change'); // Already handled by PHP style attribute but good for safety
});
</script>
