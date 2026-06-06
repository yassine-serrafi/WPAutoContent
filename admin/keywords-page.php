<?php
if (!defined('ABSPATH')) {
    exit;
}

$keywords_manager = new APIAPU_Keywords_Manager();
$keywords = $keywords_manager->get_all_keywords();
$stats = $keywords_manager->get_stats();
?>

<div class="wrap apiapu-wrap">
    <h1 class="apiapu-title">
        <span class="dashicons dashicons-tag"></span>
        <?php _e('Gestion des mots-clés', 'auto-post-ia-pro-ultimate'); ?>
    </h1>
    
    <div class="apiapu-keywords-page">
        
        <!-- Statistiques -->
        <div class="apiapu-stats-row apiapu-stats-row-small">
            <div class="apiapu-stat-card apiapu-stat-card-small">
                <span class="apiapu-stat-number"><?php echo $stats['total']; ?></span>
                <span class="apiapu-stat-label"><?php _e('Total', 'auto-post-ia-pro-ultimate'); ?></span>
            </div>
            <div class="apiapu-stat-card apiapu-stat-card-small">
                <span class="apiapu-stat-number"><?php echo $stats['pending']; ?></span>
                <span class="apiapu-stat-label"><?php _e('En attente', 'auto-post-ia-pro-ultimate'); ?></span>
            </div>
            <div class="apiapu-stat-card apiapu-stat-card-small">
                <span class="apiapu-stat-number"><?php echo $stats['used']; ?></span>
                <span class="apiapu-stat-label"><?php _e('Utilisés', 'auto-post-ia-pro-ultimate'); ?></span>
            </div>
        </div>
        
        <!-- Ajouter un mot-clé -->
        <div class="apiapu-card">
            <h2><?php _e('Ajouter des mots-clés', 'auto-post-ia-pro-ultimate'); ?></h2>
            
            <div class="apiapu-form">
                <div class="apiapu-form-row">
                    <label for="apiapu-new-keyword"><?php _e('Nouveau mot-clé', 'auto-post-ia-pro-ultimate'); ?></label>
                    <div class="apiapu-input-group">
                        <input type="text" id="apiapu-new-keyword" class="apiapu-input" placeholder="<?php _e('Ex: marketing digital', 'auto-post-ia-pro-ultimate'); ?>" />
                        <button type="button" id="apiapu-add-keyword-btn" class="apiapu-btn apiapu-btn-primary">
                            <span class="dashicons dashicons-plus"></span>
                            <?php _e('Ajouter', 'auto-post-ia-pro-ultimate'); ?>
                        </button>
                    </div>
                </div>
                
                <div class="apiapu-form-row">
                    <label for="apiapu-bulk-keywords"><?php _e('Ajout en masse (un mot-clé par ligne)', 'auto-post-ia-pro-ultimate'); ?></label>
                    <textarea id="apiapu-bulk-keywords" class="apiapu-textarea" rows="5" placeholder="<?php _e("Cette fonctionnalité est réservée à la version PRO.\n\nPassez à la version PRO pour importer des listes entières de mots-clés.", 'auto-post-ia-pro-ultimate'); ?>" disabled style="cursor:not-allowed; opacity:0.7;"></textarea>
                    <button type="button" id="apiapu-add-bulk-keywords-btn" class="apiapu-btn apiapu-btn-secondary" style="opacity:0.6;">
                        <span class="dashicons dashicons-lock"></span>
                        <?php _e('Importer en masse (PRO)', 'auto-post-ia-pro-ultimate'); ?>
                    </button>
                </div>
            </div>
            
            <div id="apiapu-keyword-message" class="apiapu-message" style="display: none;"></div>
        </div>
        
        <!-- Liste des mots-clés -->
        <div class="apiapu-card">
            <h2><?php _e('Liste des mots-clés', 'auto-post-ia-pro-ultimate'); ?></h2>
            
            <div class="apiapu-table-actions">
                <input type="text" id="apiapu-search-keywords" class="apiapu-input apiapu-input-search" placeholder="<?php _e('Rechercher...', 'auto-post-ia-pro-ultimate'); ?>" />
                <select id="apiapu-filter-status" class="apiapu-select apiapu-select-small">
                    <option value=""><?php _e('Tous les statuts', 'auto-post-ia-pro-ultimate'); ?></option>
                    <option value="pending"><?php _e('En attente', 'auto-post-ia-pro-ultimate'); ?></option>
                    <option value="used"><?php _e('Utilisés', 'auto-post-ia-pro-ultimate'); ?></option>
                    <option value="in_progress"><?php _e('En cours', 'auto-post-ia-pro-ultimate'); ?></option>
                </select>
                <button type="button" id="apiapu-bulk-delete-btn" class="apiapu-btn apiapu-btn-small apiapu-btn-danger" disabled style="opacity:0.5;">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Supprimer la sélection', 'auto-post-ia-pro-ultimate'); ?> (<span id="apiapu-bulk-count">0</span>)
                </button>
            </div>
            
            <?php if (!empty($keywords)): ?>
            <table class="apiapu-table" id="apiapu-keywords-table">
                <thead>
                    <tr>
                        <th class="apiapu-cb-col" style="width:32px;"><input type="checkbox" id="apiapu-select-all-keywords" title="<?php esc_attr_e('Tout sélectionner', 'auto-post-ia-pro-ultimate'); ?>" /></th>
                        <th><?php _e('Mot-clé', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Statut', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Utilisations', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Dernière utilisation', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Créé le', 'auto-post-ia-pro-ultimate'); ?></th>
                        <th><?php _e('Actions', 'auto-post-ia-pro-ultimate'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($keywords as $keyword): ?>
                    <tr data-id="<?php echo esc_attr($keyword['id']); ?>" data-status="<?php echo esc_attr($keyword['status']); ?>">
                        <td class="apiapu-cb-col"><input type="checkbox" class="apiapu-keyword-cb" value="<?php echo esc_attr($keyword['id']); ?>" /></td>
                        <td class="apiapu-keyword-text"><?php echo esc_html($keyword['keyword']); ?></td>
                        <td>
                            <span class="apiapu-badge <?php echo APIAPU_Utils::get_status_class($keyword['status']); ?>">
                                <?php echo APIAPU_Utils::get_status_label($keyword['status']); ?>
                            </span>
                        </td>
                        <td><?php echo intval($keyword['used_count']); ?></td>
                        <td>
                            <?php echo $keyword['last_used'] ? wp_date('d/m/Y H:i', strtotime($keyword['last_used'])) : '-'; ?>
                        </td>
                        <td><?php echo wp_date('d/m/Y H:i', strtotime($keyword['created_at'])); ?></td>
                        <td>
                            <button type="button" class="apiapu-btn apiapu-btn-small apiapu-btn-secondary apiapu-generate-keyword-btn" data-id="<?php echo esc_attr($keyword['id']); ?>" title="<?php _e('Générer avec ce mot-clé', 'auto-post-ia-pro-ultimate'); ?>">
                                <span class="dashicons dashicons-admin-post"></span>
                            </button>
                            <button type="button" class="apiapu-btn apiapu-btn-small apiapu-btn-danger apiapu-delete-keyword-btn" data-id="<?php echo esc_attr($keyword['id']); ?>" title="<?php _e('Supprimer', 'auto-post-ia-pro-ultimate'); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="apiapu-empty"><?php _e('Aucun mot-clé pour le moment. Ajoutez-en pour commencer à générer des articles!', 'auto-post-ia-pro-ultimate'); ?></p>
            <?php endif; ?>
        </div>
        
    </div>
</div>
