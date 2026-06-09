# WPAutoContent

> Plugin WordPress de génération d'articles SEO par IA (OpenAI / GPT-4o) — version freemium, **axée qualité et conformité aux bonnes pratiques Google « Helpful Content »**.

WPAutoContent transforme une liste de mots-clés en articles de blog structurés, multilingues et optimisés SEO. Il est pensé pour écrire **pour des humains d'abord** : ton naturel, angle original, intention de recherche servie directement, et aucune donnée inventée.

Le principe directeur : **Google ne pénalise pas l'IA, il pénalise le contenu inutile produit en masse.** Le plugin maximise donc la qualité par article et conserve la relecture humaine comme garde-fou final.

![Aperçu de WPAutoContent](https://www.wpautocontent.xyz/screenshots/C1S.png)

![Aperçu 2 de WPAutoContent](https://www.wpautocontent.xyz/screenshots/C2S.png)

## ✨ Qualité de rédaction

- 🌍 **Multilingue** : français, anglais, arabe
- 🤖 Rédaction via **OpenAI** (GPT-4o, GPT-4, GPT-3.5 ou modèle personnalisé)
- 🎯 **18 niches thématiques** (Santé, Finance, Gaming, Tech, Cuisine, Voyage, Immobilier, Droit, Sport, Business, Maison, Auto, Animaux, Parentalité, Éducation, Lifestyle, Beauté & Mode…) — le ton et l'angle s'adaptent à votre domaine
- 🛡️ **E-E-A-T automatique** sur les niches sensibles (disclaimer + liens vers des sources officielles)
- 📏 **Longueur garantie** : consigne de longueur + seconde passe d'enrichissement automatique
- 🚫 **Exigence d'excellence** : exemples concrets, FAQ non-redondante, pas de clichés IA, pas de chiffres inventés

## 🔍 Optimisation SEO

- **Un seul H1** : les H1 du contenu sont rétrogradés en H2 pour éviter le double H1
- **Ancres (id)** ajoutées aux sous-titres + **sommaire** cliquable optionnel
- **Maillage interne automatique** : liens contextuels + bloc « À lire aussi »
- **Anti-doublon** : aucun article généré deux fois pour le même mot-clé
- Intégration meta SEO avec **Yoast, Rank Math ou All In One SEO**
- **Schema.org (Article)** avec garde-fou anti-doublon
- Image à la une automatique via **Pexels** (+ balise alt)

## 🏗️ Architecture

| Fichier | Rôle |
|---|---|
| `wpautocontent.php` | Cœur du plugin (menus, AJAX, activation) |
| `includes/class-generator.php` | Orchestrateur du pipeline de génération |
| `includes/openai.php` | Communication avec l'API OpenAI |
| `includes/seo-tools.php` | H1, ancres, Schema.org, meta SEO |
| `includes/keywords-manager.php` | Gestion des mots-clés (CRUD) |
| `includes/image-fetcher.php` | Récupération d'images Pexels |
| `includes/logger.php` | Journal système |

## 🆓 Version Démo vs 💎 PRO

Ce dépôt contient la **version démo (freemium)**.

| Gratuit | PRO |
|---|---|
| Génération manuelle | Génération illimitée + automatique (Cron) |
| 1 image (Pexels) | Images multiples (Unsplash, Pixabay) + vidéos YouTube |
| Niches, maillage, sommaire, SEO complet | Import de mots-clés en masse |
| Logs système | Support prioritaire |

## 🔒 Sécurité

Toutes les actions vérifient un **nonce** et les **droits admin**. Requêtes SQL préparées, sorties échappées, protection anti directory-listing. Le plugin ne touche pas à votre infrastructure SEO (canonical, sitemaps, thème) et n'a **aucun impact sur la vitesse** côté visiteur.

## 🚀 Installation

1. Téléversez le dossier `wpautocontent` dans `/wp-content/plugins/`.
2. Activez le plugin depuis le menu « Extensions ».
3. Dans **Paramètres**, renseignez votre clé API OpenAI (et votre clé Pexels pour les images).
4. Choisissez la langue et la niche, sélectionnez le modèle `gpt-4o`.
5. Ajoutez des mots-clés, puis cliquez sur « Générer maintenant ».

> ⚠️ Le plugin nécessite **votre propre clé API OpenAI** (disponible sur platform.openai.com).

## 📄 Licence

Distribué sous licence **GPL-2.0** — cohérent avec l'écosystème WordPress.

---

Développé par **Yassine Serrafi** · [GitHub](https://github.com/yassine-serrafi)
