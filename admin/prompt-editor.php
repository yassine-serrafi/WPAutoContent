<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_prompt = get_option('apiapu_prompt', APIAPU_Utils::get_default_prompt());
?>

<div class="wrap apiapu-wrap">
    <h1 class="apiapu-title">
        <span class="dashicons dashicons-editor-code"></span>
        <?php _e('Éditeur de Prompt IA', 'auto-post-ia-pro-ultimate'); ?>
    </h1>
    
    <div class="apiapu-prompt-editor">
    
        <!-- PRO Banner -->
        <div class="notice notice-info" style="border-left-color: #5219bc; padding: 15px; background: #fff; margin-bottom: 20px;">
             <p style="margin: 0;"><strong>💡 ASTUCE PRO :</strong> Les utilisateurs PRO bénéficient de prompts optimisés quotidiennement et d'un assistant de création de prompt avancé. <a href="https://www.wpautocontent.xyz/" target="_blank" style="color: #5219bc; font-weight: bold;">En savoir plus &rarr;</a></p>
        </div>
        
        <div class="apiapu-card">
            <h2><?php _e('Prompt de génération d\'articles', 'auto-post-ia-pro-ultimate'); ?></h2>
            
            <div class="apiapu-prompt-info">
                <div class="apiapu-info-box">
                    <h3><span class="dashicons dashicons-info"></span> <?php _e('Variables disponibles', 'auto-post-ia-pro-ultimate'); ?></h3>
                    <ul>
                        <li><code>{{KEYWORD}}</code> - <?php _e('Le mot-clé sélectionné pour l\'article', 'auto-post-ia-pro-ultimate'); ?></li>
                    </ul>
                </div>
                
                <div class="apiapu-info-box">
                    <h3><span class="dashicons dashicons-warning"></span> <?php _e('Important', 'auto-post-ia-pro-ultimate'); ?></h3>
                    <p><?php _e('Le prompt doit demander une réponse en JSON avec les champs suivants :', 'auto-post-ia-pro-ultimate'); ?></p>
                    <ul>
                        <li><code>title</code> - <?php _e('Titre de l\'article', 'auto-post-ia-pro-ultimate'); ?></li>
                        <li><code>content</code> - <?php _e('Contenu HTML', 'auto-post-ia-pro-ultimate'); ?></li>
                        <li><code>meta_title</code> - <?php _e('Meta title SEO', 'auto-post-ia-pro-ultimate'); ?></li>
                        <li><code>meta_description</code> - <?php _e('Meta description SEO', 'auto-post-ia-pro-ultimate'); ?></li>
                        <li><code>slug</code> - <?php _e('URL slug', 'auto-post-ia-pro-ultimate'); ?></li>
                        <li><code>excerpt</code> - <?php _e('Extrait de l\'article', 'auto-post-ia-pro-ultimate'); ?></li>
                    </ul>
                </div>
            </div>
            
            <form id="apiapu-prompt-form" class="apiapu-form">
                <div class="apiapu-form-row">
                    <label for="apiapu-prompt-textarea"><?php _e('Votre prompt personnalisé', 'auto-post-ia-pro-ultimate'); ?></label>
                    <textarea id="apiapu-prompt-textarea" name="prompt" class="apiapu-textarea apiapu-textarea-lg" rows="25"><?php echo esc_textarea($current_prompt); ?></textarea>
                </div>
                
                <div class="apiapu-prompt-stats">
                    <span id="apiapu-prompt-chars"><?php echo strlen($current_prompt); ?></span> <?php _e('caractères', 'auto-post-ia-pro-ultimate'); ?>
                    |
                    <span id="apiapu-prompt-words"><?php echo str_word_count($current_prompt); ?></span> <?php _e('mots', 'auto-post-ia-pro-ultimate'); ?>
                </div>
                
                <div class="apiapu-form-actions">
                    <button type="submit" class="apiapu-btn apiapu-btn-primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Sauvegarder le prompt', 'auto-post-ia-pro-ultimate'); ?>
                    </button>
                    
                    <button type="button" id="apiapu-reset-prompt-btn" class="apiapu-btn apiapu-btn-secondary">
                        <span class="dashicons dashicons-undo"></span>
                        <?php _e('Réinitialiser par défaut', 'auto-post-ia-pro-ultimate'); ?>
                    </button>
                    
                    <span id="apiapu-prompt-save-result" class="apiapu-save-result"></span>
                </div>
            </form>
        </div>
        
        <!-- Prévisualisation -->
        <div class="apiapu-card">
            <h2><?php _e('Aperçu avec mot-clé test', 'auto-post-ia-pro-ultimate'); ?></h2>
            
            <div class="apiapu-form-row apiapu-form-row-inline">
                <div class="apiapu-form-col apiapu-form-col-grow">
                    <input type="text" id="apiapu-test-keyword" class="apiapu-input" placeholder="<?php _e('Entrez un mot-clé de test...', 'auto-post-ia-pro-ultimate'); ?>" value="marketing digital" />
                </div>
                <div class="apiapu-form-col">
                    <button type="button" id="apiapu-preview-prompt-btn" class="apiapu-btn apiapu-btn-secondary">
                        <span class="dashicons dashicons-visibility"></span>
                        <?php _e('Prévisualiser', 'auto-post-ia-pro-ultimate'); ?>
                    </button>
                </div>
            </div>
            
            <div id="apiapu-prompt-preview" class="apiapu-preview-box" style="display: none;">
                <pre id="apiapu-prompt-preview-content"></pre>
            </div>
        </div>
        
    </div>
</div>
