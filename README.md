# RestOWeb 🍕

RestOWeb est une application web de restaurant permettant la commande en ligne de plats. Ce projet est développé en PHP avec une base de données MySQL.

## 📋 Prérequis

Avant de commencer, assurez-vous d'avoir installé :
- **PHP** (version 7.4 ou supérieure)
- **MySQL** ou **MariaDB**
- **Serveur web** (Apache/Nginx) ou **XAMPP/WAMP** pour un environnement de développement local
- **Git** pour cloner le repository

## 🚀 Installation et téléchargement

### 1. Cloner le repository

```bash
# Cloner le projet depuis GitHub
git clone https://github.com/FastAze/restoweb.git

# Se déplacer dans le dossier du projet
cd restoweb
```

### 2. Configuration de la base de données

1. Créez une base de données MySQL pour le projet
2. Importez le schéma de base de données :
   ```bash
   # Importer le schéma principal
   mysql -u votre_utilisateur -p votre_base_de_donnees < doc/mpd/mpd.sql
   
   # Importer les données de test (optionnel)
   mysql -u votre_utilisateur -p votre_base_de_donnees < doc/mpd/peuplement.sql
   ```

3. Configurez les paramètres de connexion dans `connection.php`

### 3. Configuration du serveur web

- **Avec XAMPP/WAMP** : Placez le dossier du projet dans `htdocs` ou `www`
- **Avec un serveur local** : Configurez votre serveur pour pointer vers le dossier du projet

## 🌿 Gestion des branches

Ce projet utilise plusieurs branches pour organiser le développement :

### Branches disponibles

| Branche | Description | Statut |
|:-------:|:-----------:|:------:|
| `main`  | Branche principale stable | ✅ Stable |
| `lot°3` | Développement du lot 3 | 🚧 En cours |

### Comment changer de branche

```bash
# Voir toutes les branches disponibles
git branch -a

# Changer vers la branche principale
git checkout main

# Changer vers la branche de développement lot°3
git checkout lot°3

# Créer une nouvelle branche basée sur une branche existante
git checkout -b nouvelle-branche branche-existante
```

## 📁 Structure du projet

```
restoweb/
├── 📄 index.php              # Page d'accueil
├── 📄 accueilConnecte.php    # Accueil utilisateur connecté
├── 📄 connection.php         # Configuration BDD
├── 📄 inscription.php        # Page d'inscription
├── 🎨 main.css              # Styles CSS
├── 📂 component/            # Composants réutilisables
│   ├── componentNav.php     # Navigation
│   ├── componentArticle.php # Affichage articles
│   └── ...
├── 📂 doc/                  # Documentation
│   ├── mcd/                 # Modèle conceptuel
│   ├── mld/                 # Modèle logique
│   ├── mpd/                 # Modèle physique + SQL
│   └── ...
├── 📂 image/               # Images du site
└── 📂 template/            # Templates PHP
```

## 📖 Documentation technique

La documentation technique complète est disponible dans le dossier `doc/` :

- **DCU** : Diagramme de cas d'usage
- **MCD/MLD/MPD** : Modèles de données
- **IHM** : Maquettes interface utilisateur
- **Diagrammes d'activités** : Processus de commande
- **Sitemap** : Structure du site