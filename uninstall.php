<?php
/**
 * Script de désinstallation pour WPAutoContent.
 *
 * PRINCIPE DE SÉCURITÉ :
 * Par défaut, la suppression du plugin NE SUPPRIME AUCUNE DONNÉE
 * (articles, mots-clés, réglages, statistiques sont tous conservés).
 *
 * La purge complète n'a lieu QUE si l'utilisateur a explicitement coché
 * « Supprimer toutes les données à la désinstallation » dans les réglages.
 *
 * Dans TOUS les cas, les articles publiés (contenu du site) ne sont JAMAIS
 * supprimés — seules les données internes du plugin peuvent l'être.
 */

// Sécurité : ne s'exécute que dans le contexte de désinstallation WordPress.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Nettoyage non destructif TOUJOURS effectué : on retire juste l'éventuel
// événement cron planifié (sinon il resterait orphelin).
wp_clear_scheduled_hook('apiapu_scheduled_generation');

// On ne purge les données QUE si l'utilisateur l'a explicitement demandé.
$apiapu_settings = get_option('apiapu_settings', array());
$apiapu_should_purge = !empty($apiapu_settings['delete_data_on_uninstall']);

if (!$apiapu_should_purge) {
    // Choix par défaut : on conserve tout. Fin du script.
    return;
}

// ---------------------------------------------------------------------------
// PURGE COMPLÈTE (uniquement si l'utilisateur l'a explicitement activée).
// Les ARTICLES ne sont jamais supprimés, même ici.
// ---------------------------------------------------------------------------
global $wpdb;

// 1. Tables internes du plugin.
$apiapu_tables = array(
    $wpdb->prefix . 'apiapu_keywords',
    $wpdb->prefix . 'apiapu_logs',
);

foreach ($apiapu_tables as $apiapu_table) {
    $wpdb->query("DROP TABLE IF EXISTS {$apiapu_table}");
}

// 2. Options du plugin.
$apiapu_options = array(
    'apiapu_settings',
    'apiapu_prompt',
    'apiapu_cron_frequency',
    'apiapu_cron_enabled',
    'apiapu_generation_status',
    'apiapu_last_generation',
    'apiapu_last_cron_run',
);

foreach ($apiapu_options as $apiapu_option) {
    delete_option($apiapu_option);
}

// 3. Post-meta créées par le plugin (les ARTICLES eux-mêmes restent intacts).
$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '\_apiapu\_%'");
