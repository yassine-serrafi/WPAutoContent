<?php
/**
 * Script de Cron Job pour exécution via cPanel/CLI
 * Usage: php /path/to/wp-content/plugins/auto-post-ia-pro-ultimate/cron-job.php
 */

// Définir le charset pour la sortie
header('Content-Type: text/plain; charset=utf-8');

// Augmenter les limites de temps et mémoire pour l'IA
@set_time_limit(0);
@ini_set('memory_limit', '512M');

// Force Debug for CLI to see the real error
@ini_set('display_errors', 1);
@error_reporting(E_ALL);

echo "--------------------------------------------------\n";
echo "Démarrage du cron WPAutoContent\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "--------------------------------------------------\n";

// 1. Chargement de WordPress
// On cherche wp-load.php en remontant les dossiers
$dir = __DIR__;
$wp_load_path = '';
$max_depth = 5; // On remonte max 5 niveaux

for ($i = 0; $i < $max_depth; $i++) {
    if (file_exists($dir . '/wp-load.php')) {
        $wp_load_path = $dir . '/wp-load.php';
        break;
    }
    $dir = dirname($dir);
}

if (empty($wp_load_path)) {
    die("ERREUR CRITIQUE: Impossible de trouver wp-load.php. Assurez-vous que ce script est dans un sous-dossier de WordPress.\n");
}

echo "Chargement de WordPress via: $wp_load_path\n";
require_once($wp_load_path);

// 2. Vérification que le plugin est actif
if (!class_exists('APIAPU_Generator')) {
    die("ERREUR: Le plugin WPAutoContent ne semble pas actif ou chargé. Classe APIAPU_Generator introuvable.\n");
}

// 3. Vérification de la licence (Sécurité)
if (!class_exists('APIAPU_DB_Driver') || !APIAPU_DB_Driver::check_status()) {
    die("ERREUR: La licence du plugin n'est pas valide ou active.\n");
}

echo "WordPress chargé et plugin actif.\n";

// Charger les dépendances d'administration pour la gestion des images et des posts
require_once(ABSPATH . 'wp-admin/includes/media.php');
require_once(ABSPATH . 'wp-admin/includes/file.php');
require_once(ABSPATH . 'wp-admin/includes/image.php');
require_once(ABSPATH . 'wp-admin/includes/post.php'); // Requis pour post_exists()

// 4. Exécution de la génération
echo "Lancement de la génération d'article...\n";

try {
    $generator = new APIAPU_Generator();
    
    // On appelle la génération (sans mot-clé spécifique pour laisser le système choisir le prochain en attente)
    $result = $generator->generate_article();
    
    if ($result['success']) {
        echo "SUCCÈS: Article généré !\n";
        echo "Titre: " . $result['title'] . "\n";
        echo "Post ID: " . $result['post_id'] . "\n";
        echo "URL: " . $result['post_url'] . "\n";
        
        // Log explicite pour le cron
        if (class_exists('APIAPU_Logger')) {
            APIAPU_Logger::log('Cron Externe: Succès ' . $result['post_id'], 'success');
        }
    } else {
        echo "ÉCHEC: " . $result['message'] . "\n";
        
        if (isset($result['keyword'])) {
            echo "Mot-clé tenté: " . $result['keyword'] . "\n";
        }
        
        if (class_exists('APIAPU_Logger')) {
            APIAPU_Logger::log('Cron Externe: Échec - ' . $result['message'], 'error');
        }
    }
    
} catch (Exception $e) {
    echo "EXCEPTION: Une erreur inattendue est survenue: " . $e->getMessage() . "\n";
    if (class_exists('APIAPU_Logger')) {
        APIAPU_Logger::log('Cron Externe: Exception - ' . $e->getMessage(), 'error');
    }
}

echo "--------------------------------------------------\n";
echo "Fin du script.\n";
