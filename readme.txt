=== WPAutoContent ===
Tags: ai, openai, content generator, seo, automatic posts
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 10.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Génère des articles de blog SEO de qualité avec OpenAI : niches, maillage interne, sommaire, E-E-A-T et logs. Version gratuite.

== Description ==

WPAutoContent transforme vos mots-clés en articles de blog structurés et optimisés pour le référencement, grâce à l'API OpenAI.

Le plugin est pensé pour écrire **pour des humains d'abord** : ton naturel, perspective originale, intention de recherche servie directement, et aucune donnée inventée. Il applique automatiquement les bonnes pratiques SEO (un seul H1, sous-titres ancrés, maillage interne, données structurées) afin de produire un contenu propre, conforme aux recommandations « Helpful Content » de Google.

= Qualité de rédaction =

* Rédaction via OpenAI (GPT-4o, GPT-4, GPT-3.5, ou modèle personnalisé).
* Rédaction multilingue : français, anglais, arabe.
* **Sélecteur de niche** (Santé, Finance, Gaming, Tech, Cuisine, Voyage, Immobilier, Droit, Sport, Business, Maison, Auto, Animaux, Parentalité, Éducation, Lifestyle, Beauté & Mode…) : le ton et l'angle s'adaptent à votre domaine.
* **E-E-A-T automatique sur les niches sensibles** (Santé, Finance, Droit, Immobilier) : ajout d'un disclaimer et de liens vers des sources officielles reconnues.
* **Longueur garantie** : consigne de longueur + seconde passe d'enrichissement automatique si l'article est trop court.
* **Exigence d'excellence** : intention de recherche, exemples concrets, angle clair, FAQ non-redondante, pas de clichés IA, pas de chiffres inventés.

= Optimisation SEO =

* **Un seul H1** (le titre) : les H1 du contenu sont rétrogradés en H2 pour éviter le double H1.
* **Ancres (id)** ajoutées aux sous-titres.
* **Sommaire optionnel** (table des matières cliquable, autonome).
* **Maillage interne automatique** : liens contextuels + bloc « À lire aussi » vers vos articles existants liés.
* **Anti-doublon** : aucun article généré deux fois pour le même mot-clé.
* Intégration des meta SEO avec **Yoast SEO, Rank Math ou All In One SEO**.
* **Schema.org (Article)** avec garde-fou anti-doublon si un plugin SEO est déjà actif.
* Image d'illustration automatique via Pexels (image à la une + balise alt).

= Gestion & confiance =

* Garde-fou qualité : les articles trop courts sont rejetés.
* Publication en **brouillon par défaut**, pour relecture humaine.
* Éditeur de prompt personnalisable avec aperçu.
* Gestion des mots-clés (ajout, suivi, statuts).
* Journal système détaillé (logs, export, filtres).
* **Désinstallation sûre** : par défaut, aucune donnée n'est supprimée (option de purge complète disponible). Vos articles publiés ne sont jamais supprimés.

= Fonctionnalités PRO =

* Génération illimitée et planification automatique (Cron).
* Ajout de mots-clés en masse.
* Sources d'images multiples (Unsplash, Pixabay, Pexels) et plusieurs images par article.
* Intégration automatique de vidéos YouTube pertinentes.
* Support prioritaire.

Plus d'informations : https://www.wpautocontent.xyz/

== Installation ==

1. Téléversez le dossier `wpautocontent` dans `/wp-content/plugins/`.
2. Activez le plugin depuis le menu « Extensions » de WordPress.
3. Ouvrez « WPAutoContent » dans le menu d'administration.
4. Dans « Paramètres » : renseignez votre clé API OpenAI (et votre clé Pexels pour les images), choisissez la langue et la niche de votre site.
5. Pour de meilleurs résultats, sélectionnez le modèle `gpt-4o` et laissez « Max Tokens » à 4000.
6. Ajoutez des mots-clés, puis cliquez sur « Générer maintenant ».

== Frequently Asked Questions ==

= Ai-je besoin d'une clé API OpenAI ? =

Oui. Le plugin utilise votre propre clé API OpenAI pour générer le contenu. Obtenez-la sur platform.openai.com.

= Les articles sont-ils publiés automatiquement ? =

Non. Par défaut, les articles sont créés en brouillon afin que vous puissiez les relire avant publication. Vous pouvez choisir « Publié » dans les réglages.

= Le contenu IA va-t-il pénaliser mon site sur Google ? =

Google ne pénalise pas l'IA en soi, mais le contenu sans valeur produit en masse. Le plugin pousse vers un contenu utile, original et structuré, et conserve la relecture humaine par défaut. Pour les niches sensibles (Santé, Finance), une relecture occasionnelle reste recommandée.

= Mes articles sont-ils trop courts =

Le plugin force une longueur minimale et relance automatiquement une passe d'enrichissement si nécessaire. Si les articles restent courts, vérifiez que le modèle est `gpt-4o` et que « Max Tokens » est au moins à 3000.

= Que se passe-t-il si je désinstalle le plugin ? =

Par défaut, rien n'est supprimé : vos mots-clés, réglages et statistiques sont conservés en cas de réinstallation. Une purge complète n'a lieu que si vous cochez explicitement l'option dédiée. Dans tous les cas, vos articles déjà publiés ne sont jamais supprimés.

== Changelog ==

= 10.0.0 =
* Nouveau : sélecteur de niche (18 thématiques) qui adapte le ton et l'angle de rédaction.
* Nouveau : E-E-A-T automatique sur les niches sensibles (disclaimer + liens vers des sources officielles réelles).
* Nouveau : maillage interne automatique (liens contextuels + bloc « À lire aussi »).
* Nouveau : sommaire (table des matières) optionnel et autonome.
* Nouveau : exigence d'excellence injectée à chaque génération (intention de recherche, spécificité, angle, FAQ non-redondante).
* Nouveau : longueur garantie via une seconde passe d'enrichissement automatique.
* Nouveau : désinstallation sûre — conservation des données par défaut, purge en option.
* Correctif SEO : suppression du double H1 (le titre reste le seul H1) + ancres sur les sous-titres.
* Amélioration : titres plus naturels (suppression de l'ajout mécanique du mot-clé), pas de clichés IA, pas de données inventées.
* Amélioration : Schema.org avec garde-fou anti-doublon, plafond « Max Tokens » sécurisé, limite de temps d'exécution étendue.
* Divers : nettoyage du code, protection anti directory-listing, corrections de fiabilité.

== Upgrade Notice ==

= 10.0.0 =
Après la mise à jour, réinitialisez le prompt (Prompt IA → « Réinitialiser par défaut ») pour bénéficier des derniers prompts optimisés, et choisissez votre niche dans les Paramètres.
