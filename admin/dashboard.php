<?php
if (!defined('ABSPATH')) {
    exit;
}

$generator_stats = APIAPU_Generator::get_stats();
$keywords_manager = new APIAPU_Keywords_Manager();
$keywords_stats = $keywords_manager->get_stats();
$cron_status = APIAPU_Cron_Manager::get_status();
$log_stats = APIAPU_Logger::get_stats();
$recent_articles = APIAPU_Generator::get_recent_articles(5);
$generation_status = APIAPU_Generator::get_generation_status();
$last_generation = APIAPU_Generator::get_last_generation();
$openai = new APIAPU_OpenAI();
?>

<div class="wrap apiapu-wrap">
    <h1 class="apiapu-title">
        <span class="dashicons dashicons-edit-page"></span>
        <?php _e('WPAutoContent', 'auto-post-ia-pro-ultimate'); ?>
    </h1>
    
    <div class="apiapu-dashboard">
    
        <!-- PRO Banner -->
        <div class="notice notice-info" style="border-left-color: #5219bc; padding: 20px; background: #fff; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px;">
             <h3 style="margin-top:0; color: #5219bc;">🚀 Passez à WPAutoContent PRO !</h3>
             <p style="font-size: 14px;">Débloquez tout le potentiel de l'IA pour votre site WordPress :</p>
             <ul style="list-style: disc; margin-left: 20px; margin-bottom: 15px;">
                 <li><strong>Génération Illimitée</strong> en un clic</li>
                 <li><strong>Mode Automatique</strong> avec planification Cron</li>
                 <li><strong>Vidéos YouTube</strong> intégrées automatiquement</li>
                 <li><strong>Images Illimitées</strong> (Unsplash, Pixabay, Pexels)</li>
                 <li><strong>Support Prioritaire</strong></li>
             </ul>
             <a href="https://www.wpautocontent.xyz/" target="_blank" class="button button-primary button-hero" style="background-color: #5219bc; border-color: #5219bc;">Obtenir la version PRO maintenant</a>
        </div>
        
        <!-- Statut API -->
        <div class="apiapu-card apiapu-card-status">
            <h2><?php _e('Statut du système', 'auto-post-ia-pro-ultimate'); ?></h2>
            <div class="apiapu-status-grid">
                <div class="apiapu-status-item">
                    <span class="apiapu-status-label"><?php _e('API OpenAI', 'auto-post-ia-pro-ultimate'); ?></span>
                    <?php if ($openai->is_configured()): ?>
                        <span class="apiapu-badge apiapu-badge-success"><?php _e('Configurée', 'auto-post-ia-pro-ultimate'); ?></span>
                    <?php else: ?>
                        <span class="apiapu-badge apiapu-badge-error"><?php _e('Non configurée', 'auto-post-ia-pro-ultimate'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="apiapu-status-item">
                    <span class="apiapu-status-label"><?php _e('Cron', 'auto-post-ia-pro-ultimate'); ?></span>
                    <?php if ($cron_status['enabled']): ?>
                        <span class="apiapu-badge apiapu-badge-success"><?php _e('Actif', 'auto-post-ia-pro-ultimate'); ?></span>
                    <?php else: ?>
                        <span class="apiapu-badge apiapu-badge-warning"><?php _e('Inactif', 'auto-post-ia-pro-ultimate'); ?></span>
                    <?php endif; ?>
                </div>
                <div class="apiapu-status-item">
                    <span class="apiapu-status-label"><?php _e('Génération', 'auto-post-ia-pro-ultimate'); ?></span>
                    <span class="apiapu-badge apiapu-badge-<?php echo $generation_status === 'in_progress' ? 'warning' : ($generation_status === 'error' ? 'error' : 'info'); ?>">
                        <?php echo APIAPU_Utils::get_status_label($generation_status === 'idle' ? 'pending' : $generation_status); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Statistiques -->
        <div class="apiapu-stats-row">
            <div class="apiapu-stat-card">
                <div class="apiapu-stat-icon"><span class="dashicons dashicons-media-text"></span></div>
                <div class="apiapu-stat-content">
                    <span class="apiapu-stat-number"><?php echo $generator_stats['total_generated']; ?></span>
                    <span class="apiapu-stat-label"><?php _e('Articles générés', 'auto-post-ia-pro-ultimate'); ?></span>
                </div>
            </div>
            <div class="apiapu-stat-card">
                <div class="apiapu-stat-icon"><span class="dashicons dashicons-calendar-alt"></span></div>
                <div class="apiapu-stat-content">
                    <span class="apiapu-stat-number"><?php echo $generator_stats['today_generated']; ?></span>
                    <span class="apiapu-stat-label"><?php _e('Aujourd\'hui', 'auto-post-ia-pro-ultimate'); ?></span>
                </div>
            </div>
            <div class="apiapu-stat-card">
                <div class="apiapu-stat-icon"><span class="dashicons dashicons-tag"></span></div>
                <div class="apiapu-stat-content">
                    <span class="apiapu-stat-number"><?php echo $keywords_stats['total']; ?></span>
                    <span class="apiapu-stat-label"><?php _e('Mots-clés', 'auto-post-ia-pro-ultimate'); ?></span>
                </div>
            </div>
            <div class="apiapu-stat-card">
                <div class="apiapu-stat-icon"><span class="dashicons dashicons-clock"></span></div>
                <div class="apiapu-stat-content">
                    <span class="apiapu-stat-number"><?php echo $keywords_stats['pending']; ?></span>
                    <span class="apiapu-stat-label"><?php _e('En attente', 'auto-post-ia-pro-ultimate'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Bouton Générer maintenant -->
        <div class="apiapu-card apiapu-card-generate">
            <h2><?php _e('Générer un article maintenant', 'auto-post-ia-pro-ultimate'); ?></h2>
            
            <div class="apiapu-generate-form">
                <div class="apiapu-form-row">
                    <label for="apiapu-keyword-select"><?php _e('Mot-clé (optionnel)', 'auto-post-ia-pro-ultimate'); ?></label>
                    <select id="apiapu-keyword-select" class="apiapu-select">
                        <option value="0"><?php _e('-- Prochain mot-clé automatique --', 'auto-post-ia-pro-ultimate'); ?></option>
                        <?php 
                        $keywords = $keywords_manager->get_all_keywords('pending', 50);
                        foreach ($keywords as $kw): 
                        ?>
                            <option value="<?php echo esc_attr($kw['id']); ?>"><?php echo esc_html($kw['keyword']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="button" id="apiapu-generate-btn" class="apiapu-btn apiapu-btn-primary apiapu-btn-lg">
                    <span class="dashicons dashicons-admin-post"></span>
                    <?php _e('Générer maintenant', 'auto-post-ia-pro-ultimate'); ?>
                </button>
                
                <div id="apiapu-generate-status" class="apiapu-status-message" style="display: none;">
                    <img src="<?php echo APIAPU_PLUGIN_URL; ?>assets/loader.gif" alt="Loading" class="apiapu-loader" />
                    <span class="apiapu-status-text"><?php _e('Génération en cours...', 'auto-post-ia-pro-ultimate'); ?></span>
                </div>
                
                <div id="apiapu-generate-result" class="apiapu-result-message" style="display: none;"></div>
            </div>
        </div>
        
        <!-- Informations Cron -->
        <div class="apiapu-card">
            <h2><?php _e('Planification automatique', 'auto-post-ia-pro-ultimate'); ?> <span class="apiapu-pro-badge" style="background:#5219bc;color:white;padding:2px 8px;border-radius:4px;font-size:12px;vertical-align:middle;">PRO</span></h2>
            <div class="apiapu-cron-info">
                <p style="text-align: center; color: #666; font-style: italic;">
                    <?php _e('La planification automatique est réservée à la version PRO.', 'auto-post-ia-pro-ultimate'); ?><br>
                    <a href="https://www.wpautocontent.xyz/" target="_blank" style="color: #5219bc; font-weight: bold; text-decoration: none;">Passer à la version PRO &rarr;</a>
                </p>
            </div>
        </div>
        
        <!-- Articles récents -->
        <div class="apiapu-card">
            <h2><?php _e('Articles récemment générés', 'auto-post-ia-pro-ultimate'); ?></h2>
            <?php if (!empty($recent_articles)): ?>
            <table class="apiapu-table">
                <thead>
                    <tr>
                        <th><?php _e('Titre', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Mot-clé', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Statut', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Date', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Actions', 'auto-post-ia-pro-ultimate'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_articles as $article): ?>
                    <tr>
                        <td><?php echo esc_html($article['post_title']); ?></td>
                        <td><?php echo esc_html($article['keyword']); ?></td>
                        <td>
                            <span class="apiapu-badge apiapu-badge-<?php echo $article['post_status'] === 'publish' ? 'success' : 'warning'; ?>">
                                <?php echo $article['post_status'] === 'publish' ? __('Publié', 'auto-post-ia-pro-ultimate') : __('Brouillon', 'auto-post-ia-pro-ultimate'); ?>
                            </span>
                        </td>
                        <td><?php echo wp_date('d/m/Y H:i', strtotime($article['post_date'])); ?></td>
                        <td>
                            <a href="<?php echo get_edit_post_link($article['ID']); ?>" class="apiapu-link"><?php _e('Modifier', 'auto-post-ia-pro-ultimate'); ?></a>
                            <a href="<?php echo get_permalink($article['ID']); ?>" class="apiapu-link" target="_blank"><?php _e('Voir', 'auto-post-ia-pro-ultimate'); ?></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="apiapu-empty"><?php _e('Aucun article généré pour le moment.', 'auto-post-ia-pro-ultimate'); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Logs récents -->
        <div class="apiapu-card">
            <h2>
                <?php _e('Logs récents', 'auto-post-ia-pro-ultimate'); ?>
                <a href="<?php echo admin_url('admin.php?page=apiapu-logs'); ?>" class="apiapu-link-small"><?php _e('Voir tous', 'auto-post-ia-pro-ultimate'); ?></a>
            </h2>
            <div class="apiapu-logs-stats">
                <span class="apiapu-log-stat"><span class="dashicons dashicons-info"></span> Info: <?php echo $log_stats['info']; ?></span>
                <span class="apiapu-log-stat apiapu-log-success"><span class="dashicons dashicons-yes-alt"></span> Succès: <?php echo $log_stats['success']; ?></span>
                <span class="apiapu-log-stat apiapu-log-warning"><span class="dashicons dashicons-warning"></span> Avertissements: <?php echo $log_stats['warning']; ?></span>
                <span class="apiapu-log-stat apiapu-log-error"><span class="dashicons dashicons-dismiss"></span> Erreurs: <?php echo $log_stats['error']; ?></span>
            </div>
        </div>
        
    </div>
</div>
