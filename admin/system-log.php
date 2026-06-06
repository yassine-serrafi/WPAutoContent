<?php
if (!defined('ABSPATH')) {
    exit;
}

$logs = APIAPU_Logger::get_logs(100);
$stats = APIAPU_Logger::get_stats();
?>

<div class="wrap apiapu-wrap">
    <h1 class="apiapu-title">
        <span class="dashicons dashicons-list-view"></span>
        <?php _e('Logs Système', 'auto-post-ia-pro-ultimate'); ?>
    </h1>
    
    <div class="apiapu-logs-page">
        
        <!-- Statistiques -->
        <div class="apiapu-stats-row apiapu-stats-row-small">
            <div class="apiapu-stat-card apiapu-stat-card-small">
                <span class="apiapu-stat-number"><?php echo $stats['total']; ?></span>
                <span class="apiapu-stat-label"><?php _e('Total', 'auto-post-ia-pro-ultimate'); ?></span>
            </div>
            <div class="apiapu-stat-card apiapu-stat-card-small apiapu-stat-info">
                <span class="apiapu-stat-number"><?php echo $stats['info']; ?></span>
                <span class="apiapu-stat-label"><?php _e('Info', 'auto-post-ia-pro-ultimate'); ?></span>
            </div>
            <div class="apiapu-stat-card apiapu-stat-card-small apiapu-stat-success">
                <span class="apiapu-stat-number"><?php echo $stats['success']; ?></span>
                <span class="apiapu-stat-label"><?php _e('Succès', 'auto-post-ia-pro-ultimate'); ?></span>
            </div>
            <div class="apiapu-stat-card apiapu-stat-card-small apiapu-stat-warning">
                <span class="apiapu-stat-number"><?php echo $stats['warning']; ?></span>
                <span class="apiapu-stat-label"><?php _e('Avertissements', 'auto-post-ia-pro-ultimate'); ?></span>
            </div>
            <div class="apiapu-stat-card apiapu-stat-card-small apiapu-stat-error">
                <span class="apiapu-stat-number"><?php echo $stats['error']; ?></span>
                <span class="apiapu-stat-label"><?php _e('Erreurs', 'auto-post-ia-pro-ultimate'); ?></span>
            </div>
        </div>
        
        <!-- Actions -->
        <div class="apiapu-card">
            <div class="apiapu-logs-actions">
                <button type="button" id="apiapu-refresh-logs-btn" class="apiapu-btn apiapu-btn-secondary">
                    <span class="dashicons dashicons-update"></span>
                    <?php _e('Rafraîchir', 'auto-post-ia-pro-ultimate'); ?>
                </button>
                <button type="button" id="apiapu-export-logs-btn" class="apiapu-btn apiapu-btn-secondary">
                    <span class="dashicons dashicons-download"></span>
                    <?php _e('Exporter en TXT', 'auto-post-ia-pro-ultimate'); ?>
                </button>
                <button type="button" id="apiapu-clear-logs-btn" class="apiapu-btn apiapu-btn-danger">
                    <span class="dashicons dashicons-trash"></span>
                    <?php _e('Vider les logs', 'auto-post-ia-pro-ultimate'); ?>
                </button>
                
                <select id="apiapu-filter-log-type" class="apiapu-select apiapu-select-small">
                    <option value=""><?php _e('Tous les types', 'auto-post-ia-pro-ultimate'); ?></option>
                    <option value="info"><?php _e('Info', 'auto-post-ia-pro-ultimate'); ?></option>
                    <option value="success"><?php _e('Succès', 'auto-post-ia-pro-ultimate'); ?></option>
                    <option value="warning"><?php _e('Avertissements', 'auto-post-ia-pro-ultimate'); ?></option>
                    <option value="error"><?php _e('Erreurs', 'auto-post-ia-pro-ultimate'); ?></option>
                </select>
            </div>
        </div>
        
        <!-- Logs -->
        <div class="apiapu-card">
            <h2><?php _e('Historique des logs', 'auto-post-ia-pro-ultimate'); ?></h2>
            
            <div id="apiapu-logs-container" class="apiapu-logs-container">
                <?php if (!empty($logs)): ?>
                <table class="apiapu-table apiapu-logs-table">
                    <thead>
                        <tr>
                            <th><?php _e('Date/Heure', 'auto-post-ia-pro-ultimate'); ?></th>
                            <th><?php _e('Type', 'auto-post-ia-pro-ultimate'); ?></th>
                            <th><?php _e('Message', 'auto-post-ia-pro-ultimate'); ?></th>
                            <th><?php _e('Mot-clé', 'auto-post-ia-pro-ultimate'); ?></th>
                            <th><?php _e('Post ID', 'auto-post-ia-pro-ultimate'); ?></th>
                            <th><?php _e('Détails', 'auto-post-ia-pro-ultimate'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $log): ?>
                        <tr class="apiapu-log-row apiapu-log-<?php echo esc_attr($log['type']); ?>" data-type="<?php echo esc_attr($log['type']); ?>">
                            <td class="apiapu-log-date"><?php echo wp_date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></td>
                            <td>
                                <span class="apiapu-badge <?php echo APIAPU_Utils::get_status_class($log['type']); ?>">
                                    <?php echo strtoupper($log['type']); ?>
                                </span>
                            </td>
                            <td class="apiapu-log-message"><?php echo esc_html($log['message']); ?></td>
                            <td><?php echo $log['keyword'] ? esc_html($log['keyword']) : '-'; ?></td>
                            <td>
                                <?php if ($log['post_id']): ?>
                                    <a href="<?php echo get_edit_post_link($log['post_id']); ?>" target="_blank">#<?php echo $log['post_id']; ?></a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($log['error_details'])): ?>
                                    <button type="button" class="apiapu-btn apiapu-btn-small apiapu-btn-secondary apiapu-show-details-btn" data-details="<?php echo esc_attr(is_array($log['error_details']) ? wp_json_encode($log['error_details']) : $log['error_details']); ?>">
                                        <span class="dashicons dashicons-visibility"></span>
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="apiapu-empty"><?php _e('Aucun log pour le moment.', 'auto-post-ia-pro-ultimate'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
    </div>
</div>

<!-- Modal pour détails -->
<div id="apiapu-details-modal" class="apiapu-modal" style="display: none;">
    <div class="apiapu-modal-content">
        <div class="apiapu-modal-header">
            <h3><?php _e('Détails de l\'erreur', 'auto-post-ia-pro-ultimate'); ?></h3>
            <button type="button" class="apiapu-modal-close">&times;</button>
        </div>
        <div class="apiapu-modal-body">
            <pre id="apiapu-details-content"></pre>
        </div>
    </div>
</div>
