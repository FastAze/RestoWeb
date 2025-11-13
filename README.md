# RestoWeb 🍕

RestoWeb est une application web de restaurant permettant la commande en ligne de plats. Ce projet est développé en PHP avec une base de données MySQL.

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

# ⚠️ IMPORTANT : Basculer sur la dernière branche de développement (lot5)
git checkout lot5
```

> **💡 Astuce** : Utilisez toujours la branche avec le numéro de lot le plus élevé (ex: `lot3`, `lot4`, etc.) pour obtenir la version la plus récente du projet.

### 2. Configuration de la base de données

#### a) Créer la base de données

1. Créez une base de données MySQL pour le projet
2. Importez le schéma de base de données :
   ```bash
   # Importer le schéma principal
   mysql -u votre_utilisateur -p votre_base_de_donnees < doc/mpd/restoweb.sql
   ```

#### b) Configurer la connexion PDO

**⚠️ IMPORTANT** : Modifiez le fichier [`template/ini.php`](template/ini.php) avec vos paramètres de connexion :

```php
<?php
function db_connect()
{
    // Modifiez ces valeurs selon votre configuration
    $dsn = 'mysql:host=localhost;dbname=restoweb';  // Nom de votre base de données
    $user = 'root';                                  // Votre nom d'utilisateur MySQL
    $password = '';                                   // Votre mot de passe MySQL
    
    try{
        $dbh = new PDO($dsn, $user, $password, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $ex){
        die("Erreur lors de la connexion SQL : " . $ex->getMessage());
    }
    return $dbh;
}
?>
```

**Paramètres à modifier :**
- `dbname` : Le nom de votre base de données (par défaut : `restoweb`)
- `user` : Votre nom d'utilisateur MySQL (par défaut : `root`)
- `password` : Votre mot de passe MySQL (par défaut : vide `''`)
- `host` : L'hôte de votre serveur MySQL (par défaut : `localhost`)

### 3. Configuration du serveur web

#### 🖥️ Avec XAMPP (installation classique)
```bash
# Déplacer le projet dans le dossier htdocs de XAMPP
# Sur Mac
mv restoweb /Applications/XAMPP/xamppfiles/htdocs/

# Sur Windows
move restoweb C:\xampp\htdocs\

# Sur Linux
mv restoweb /opt/lampp/htdocs/
```

Accédez ensuite à : `http://localhost/restoweb`

#### 🐳 Avec XAMPP Docker
```bash
# Déplacer le projet dans le dossier racine défini lors de l'installation Docker
# Exemple si votre volume Docker pointe vers /var/www/html
docker cp restoweb nom_du_conteneur:/var/www/html/

# Ou montez directement le dossier lors du lancement du conteneur
docker run -d \
  -p 80:80 \
  -v /chemin/vers/restoweb:/var/www/html/restoweb \
  --name xampp_container \
  tomsik68/xampp
```

Accédez ensuite à : `http://localhost/restoweb`

#### ⚙️ Avec un serveur local personnalisé
Configurez votre serveur pour pointer vers le dossier du projet.

## 🌿 Gestion des branches

Ce projet utilise plusieurs branches pour organiser le développement :

### Branches disponibles

| Branche | Description | Statut |
|:-------:|:-----------:|:------:|
| `lot2`  | Développement du lot 2 | ✅ Stable |
| `lot3` | Développement du lot 3 | ✅ Complété |
| `lot4` | Branche principale stable | ✅ Complété |
| `lot5` | Développement du lot 5 (🔥 À utiliser) | 🚧 En cours |

> **⚠️ Important** : Pour le développement ou l'utilisation de la dernière version, utilisez toujours la branche avec le numéro de lot le plus élevé.

### Comment changer de branche

```bash
# Voir toutes les branches disponibles
git branch -a

# Changer vers la branche principale
git checkout main

# Changer vers la branche de développement lot3 (recommandé)
git checkout lot3

# Créer une nouvelle branche basée sur une branche existante
git checkout -b nouvelle-branche branche-existante
```

## 📁 Structure du projet

```
restoweb/
├── 📄 index.php                    # Page d'accueil (non connecté)
├── 📄 accueilConnecte.php          # Page d'accueil utilisateur connecté
├── 📄 connection.php               # Page de connexion
├── 📄 inscription.php              # Page d'inscription
├── 📄 panier.php                   # Page du panier
├── 📄 paiement.php                 # Page de paiement
├── 🎨 main.css                     # Fichier CSS principal
├── 📄 README.md                    # Documentation du projet
│
├── 📂 template/                    # Templates et fichiers de configuration
│   ├── ini.php                     # Configuration base de données
│   ├── path.php                    # Gestion des chemins
│   └── chekEtat.php                # Vérification état des commandes
│
├── 📂 component/                   # Composants réutilisables
│   ├── componentArticle.php        # Affichage des articles
│   ├── componentProfile.php        # Composant profil utilisateur
│   └── componentVoirCommandeOverlay.php  # Modal détail commande
│
├── 📂 doc/                         # Documentation technique
│   ├── 📂 mcd/                     # Modèle Conceptuel de Données
│   ├── 📂 mld/                     # Modèle Logique de Données
│   │   └── mld.txt                 # Description MLD
│   ├── 📂 mpd/                     # Modèle Physique de Données
│   │   └── restoweb.sql            # Script SQL de création BDD
│   ├── 📂 dcu/                     # Diagrammes de Cas d'Usage
│   ├── 📂 ihm/                     # Maquettes Interface Homme-Machine
│   ├── 📂 activite/                # Diagrammes d'activité
│   └── 📂 sitemap/                 # Plan du site
│
└── 📂 image/                       # Ressources images
    ├── notif.png                   # Icône notifications
    ├── pizza.jpg                   # Image par défaut
    └── [autres images produits]    # Images des produits
```

### 📝 Description des fichiers principaux

| Fichier | Description | Rôle | Lien |
|:--------|:------------|:-----|:----:|
| `index.php` | Page d'accueil publique | Point d'entrée pour visiteurs non connectés | [📄](https://github.com/FastAze/restoweb/blob/main/index.php) |
| `accueilConnecte.php` | Page d'accueil authentifiée | Interface principale après connexion | [📄](https://github.com/FastAze/restoweb/blob/main/accueilConnecte.php) |
| `connection.php` | Formulaire de connexion | Authentification utilisateur | [📄](https://github.com/FastAze/restoweb/blob/main/connection.php) |
| `inscription.php` | Formulaire d'inscription | Création de compte | [📄](https://github.com/FastAze/restoweb/blob/main/inscription.php) |
| `panier.php` | Gestion du panier | Visualisation et modification du panier | [📄](https://github.com/FastAze/restoweb/blob/main/panier.php) |
| `paiement.php` | Processus de paiement | Finalisation de la commande | [📄](https://github.com/FastAze/restoweb/blob/main/paiement.php) |
| `main.css` | Styles globaux | Design et mise en page | [🎨](https://github.com/FastAze/restoweb/blob/main/main.css) |

### 🔧 Fichiers de configuration

| Fichier | Description | Lien |
|:--------|:------------|:----:|
| `template/ini.php` | Connexion à la base de données MySQL | [📄](https://github.com/FastAze/restoweb/blob/lot4/template/ini.php) |
| `template/path.php` | Gestion des chemins relatifs/absolus | [📄](https://github.com/FastAze/restoweb/blob/lot4/template/path.php) |
| `template/chekEtat.php` | Fonctions de vérification des commandes | [📄](https://github.com/FastAze/restoweb/blob/lot4/template/chekEtat.php) |

### 🧩 Composants réutilisables

| Composant | Utilisation | Lien |
|:----------|:------------|:----:|
| `componentArticle.php` | Affichage de la liste des produits | [📄](https://github.com/FastAze/restoweb/blob/lot4/component/componentArticle.php) |
| `componentProfile.php` | Section profil utilisateur avec historique | [📄](https://github.com/FastAze/restoweb/blob/lot4/component/componentProfile.php) |
| `componentVoirCommandeOverlay.php` | Modal de détail d'une commande | [📄](https://github.com/FastAze/restoweb/blob/lot4/component/componentVoirCommandeOverlay.php) |

### 📚 Documentation technique

| Dossier | Description | Lien |
|:--------|:------------|:----:|
| `doc/mcd/` | Modèle Conceptuel de Données | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/mcd) |
| `doc/mld/` | Modèle Logique de Données | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/mld) |
| `doc/mpd/` | Modèle Physique de Données | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/mpd) |
| `doc/dcu/` | Diagrammes de Cas d'Usage | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/DCU) |
| `doc/ihm/` | Maquettes Interface Homme-Machine | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/ihm) |
| `doc/activite/` | Diagrammes d'activité | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/activite) |
| `doc/sitemap/` | Plan du site | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/sitemap) |

## 📊 Valeurs possibles

### États des commandes

| État | Description | Code |
|:-----|:------------|:----:|
| 🟡 En attente | Commande reçue, en attente de préparation | `0` |
| 🔵 En cours | Commande en cours de préparation | `1` |
| 🟢 Prête | Commande terminée, prête à être récupérée/livrée | `2` |
| ⚪ Livrée | Commande livrée au client | `3` |
| 🔴 Annulée | Commande annulée | `4` |

### Types de consommation

| Type | Description | Code |
|:-----|:------------|:----:|
| 🍽️ Sur place | Consommation dans le restaurant | `0` |
| 📦 À emporter | Commande à emporter | `1` |

## 📖 Manuel d'utilisation

### 📝 Inscription

1. **Accéder à la page d'inscription**
   - Depuis la page d'accueil ([`index.php`](index.php)), cliquez sur le bouton **"Inscription"**
   - Ou accédez directement à [`inscription.php`](inscription.php)

2. **Remplir le formulaire**
   - **Nom d'utilisateur** : Choisissez un nom unique
   - **E-mail** : Entrez une adresse e-mail valide
   - **Mot de passe** : Créez un mot de passe sécurisé

3. **Valider l'inscription**
   - Cliquez sur le bouton **"S'inscrire"**
   - Une commande vide est automatiquement créée pour vous
   - Vous êtes redirigé vers la page d'accueil connecté

> **💡 Note** : Tous les champs sont obligatoires. Le mot de passe est haché pour votre sécurité.

---

### 🔐 Connexion

1. **Accéder à la page de connexion**
   - Depuis la page d'accueil ([`index.php`](index.php)), cliquez sur **"Connexion"**
   - Ou accédez directement à [`connection.php`](connection.php)

2. **Se connecter**
   - Entrez votre **nom d'utilisateur**
   - Entrez votre **mot de passe**
   - Cliquez sur **"Connexion"**

3. **Après connexion**
   - Vous êtes redirigé vers [`accueilConnecte.php`](accueilConnecte.php)
   - Votre session est active
   - Une commande est vérifiée/créée automatiquement

> **⚠️ Attention** : En cas d'erreur, vérifiez vos identifiants ou créez un compte.

---

### 🍕 Commander

#### Parcourir le catalogue

1. **Visualiser les produits**
   - Sur la page d'accueil connecté, tous les produits disponibles sont affichés
   - Chaque produit affiche :
     - Une image
     - Le nom du produit
     - Le prix HT

2. **Voir les détails d'un produit**
   - Cliquez sur n'importe quel produit
   - Une fenêtre modale s'ouvre avec :
     - L'image en grand format
     - Le nom du produit
     - Le prix
     - Un champ pour saisir la quantité

#### Ajouter au panier

1. **Sélectionner la quantité**
   - Dans la fenêtre modale, saisissez la quantité désirée (minimum : 1)
   - Par défaut : 1 unité

2. **Valider l'ajout**
   - Cliquez sur le bouton **"Valider"**
   - Le produit est ajouté à votre panier
   - Vous pouvez continuer vos achats

3. **Annuler**
   - Cliquez sur **"Retour"** pour fermer sans ajouter

#### Gérer le panier

1. **Accéder au panier**
   - Cliquez sur le bouton **"Panier"** dans la navigation
   - Vous êtes redirigé vers [`panier.php`](panier.php)

2. **Modifier les quantités**
   - Utilisez les boutons **"+"** et **"-"** pour ajuster les quantités
   - Les prix sont automatiquement recalculés

3. **Supprimer un article**
   - Cliquez sur le bouton **"Supprimer"** pour retirer un produit du panier

4. **Choisir le type de consommation**
   - **🍽️ Sur place** : TVA à 10%
   - **📦 À emporter** : TVA à 5.5%
   
5. **Valider le panier**
   - Sélectionnez obligatoirement un type de consommation
   - Cliquez sur **"Valider"** pour passer au paiement
   - Ou cliquez sur **"Retour"** pour continuer vos achats

> **💡 Astuce** : Le total TTC est calculé automatiquement selon le type de consommation choisi.

---

### 💳 Paiement

1. **Informations affichées**
   - **N° de commande** : Identifiant unique de votre commande
   - **Type de consommation** : Sur place ou À emporter
   - **Montant total TTC** : Prix final à payer

2. **Remplir les informations de paiement**
   - **Numéro de carte** : 16 chiffres (format : 1234 5678 9012 3456)
   - **Code CCV** : 3 chiffres au dos de la carte
   - **Date d'expiration** : Format MM/AA

3. **Validation du formatage**
   - Le numéro de carte est formaté automatiquement
   - Les champs sont validés avant soumission

4. **Finaliser le paiement**
   - Cliquez sur **"Valider"**
   - Une popup de confirmation s'affiche
   - L'état de votre commande passe à "finalisée"
   - Vous recevrez une notification par e-mail

5. **Annuler le paiement**
   - Cliquez sur **"Annuler"** pour revenir à l'accueil
   - Votre panier reste intact

> **🔒 Sécurité** : Les informations de paiement sont traitées de manière sécurisée via PDO avec des requêtes préparées.

---

### 👤 Profil utilisateur

1. **Accéder au profil**
   - Cliquez sur votre nom d'utilisateur dans la navigation

2. **Consulter l'historique**
   - Visualisez toutes vos commandes passées
     - N° de commande
     - Date et heure
     - Type (sur place/à emporter)
     - Prix (TTC)
     - Statut de la commande

---

### 🔔 Notifications

1. **Accéder aux notifications**
   - Cliquez sur l'icône 🔔 dans la navigation

2. **Types de notifications**
   - État de vos commandes en cours
   - Commandes prêtes
   - Confirmations de paiement

---

### 🚪 Déconnexion

1. **Se déconnecter**
   - Cliquez sur le bouton **"Déconnexion"** dans la navigation
   - Votre session est détruite
   - Vous êtes redirigé vers la page d'accueil publique

---

## ❓ FAQ et résolution de problèmes

### Problèmes courants

**Je ne peux pas me connecter**
- Vérifiez vos identifiants
- Assurez-vous que la base de données est bien configurée
- Vérifiez que le fichier [`template/ini.php`](template/ini.php) est correctement paramétré

**Mon panier est vide après connexion**
- Une nouvelle commande est créée automatiquement à chaque connexion
- Vérifiez que l'état de votre commande est "initialisée" (état 1)

**Le total n'est pas correct**
- Le calcul se fait automatiquement via des triggers SQL
- Le taux de TVA change selon le type de consommation :
  - Sur place : 10%
  - À emporter : 5.5%

**Je ne reçois pas de notification**
- Les notifications sont simulées pour le moment
- La fonctionnalité d'envoi d'e-mails sera ajoutée dans une version future

---

## 🎯 Cas d'usage typique

1. **Première visite**
   ```
   Accueil → Inscription → Catalogue → Sélection produits → Panier → Paiement → Confirmation
   ```

2. **Client régulier**
   ```
   Connexion → Catalogue → Ajout panier → Modification quantités → Type de consommation → Paiement
   ```

3. **Consultation historique**
   ```
   Connexion → Profil → Historique commandes → Détails commande
   ```

---

## 🧪 Données de test

### 👥 Comptes utilisateurs de test

Pour faciliter les tests de l'application, voici les comptes utilisateurs préenregistrés dans la base de données :

| Nom d'utilisateur | Email | Mot de passe | Description |
|:------------------|:------|:-------------|:------------|
| `123` | `123@gmail.com` | `123` | Compte de test simple |
| `aze` | `aze@gmail.com` | `aze` | Compte de test principal |

> **⚠️ Important** : Ces comptes sont uniquement destinés aux tests en environnement de développement. Ne les utilisez jamais en production.

### 🍕 Produits disponibles

L'application contient 15 pizzas en base de données :

| ID | Nom | Prix HT |
|:---|:----|:--------|
| 1 | Pizza Margherita | 8,50€ |
| 2 | Pizza Quattro Stagioni | 12,00€ |
| 3 | Pizza Pepperoni | 10,50€ |
| 4 | Pizza Hawaienne | 11,00€ |
| 5 | Pizza Calzone | 13,50€ |
| 6 | Pizza Végétarienne | 11,50€ |
| 7 | Pizza Quatre Fromages | 12,50€ |
| 8 | Pizza Chorizo | 13,00€ |
| 9 | Pizza Saumon Fumé | 15,00€ |
| 10 | Pizza Bolognaise | 12,00€ |
| 11 | Pizza Thon | 10,00€ |
| 12 | Pizza Chèvre Miel | 13,50€ |
| 13 | Pizza Orientale | 14,00€ |
| 14 | Pizza Paysanne | 12,50€ |
| 15 | Pizza Regina | 11,50€ |

### 📋 États de commandes disponibles

| ID | Libellé | Description |
|:---|:--------|:------------|
| 1 | initialisée | Commande en cours de création |
| 2 | finalisée | Commande validée et payée |
| 3 | calculée | Montants calculés |
| 4 | en attente | Commande en attente de préparation |
| 5 | abandonnée | Commande abandonnée par le client |
| 6 | en préparation | Commande en cours de préparation |
| 7 | prête | Commande prête à être récupérée |
| 8 | servie | Commande livrée/servie |

### 🔄 Scénarios de test recommandés

#### Scénario 1 : Nouvelle inscription et première commande
```
1. Créer un nouveau compte via inscription.php
2. Ajouter 2-3 produits au panier
3. Choisir "Sur place" (TVA 10%)
4. Finaliser le paiement
5. Vérifier l'historique dans le profil
```

#### Scénario 2 : Connexion et commande multiple
```
1. Se connecter avec : aze / aze
2. Ajouter plusieurs produits
3. Modifier les quantités dans le panier
4. Supprimer un article
5. Choisir "À emporter" (TVA 5.5%)
6. Finaliser le paiement
```

#### Scénario 3 : Test des calculs TVA
```
1. Se connecter
2. Ajouter Pizza Margherita (8.50€) x2 = 17€ HT
3. Sur place : 17€ × 1.10 = 18.70€ TTC
4. À emporter : 17€ × 1.055 = 17.94€ TTC
```

### 🗄️ Réinitialisation de la base de données

Pour remettre la base de données à son état initial :

```bash
# Se connecter à MySQL
mysql -u root -p

# Supprimer et recréer la base
DROP DATABASE IF EXISTS restoweb;
CREATE DATABASE restoweb;
USE restoweb;

# Réimporter le schéma
SOURCE /Applications/XAMPP/xamppfiles/htdocs/restoweb/doc/mpd/restoweb.sql;
```

Ou en une seule commande :
```bash
mysql -u root -p < doc/mpd/restoweb.sql
```

### 📝 Notes pour les tests

- **Mots de passe** : Les mots de passe en base sont hashés avec `password_hash()` (bcrypt)
- **Sessions** : Pensez à vider les cookies/sessions entre les tests de différents utilisateurs
- **Images** : Si une image produit n'existe pas, `pizza.jpg` est utilisée par défaut
- **Triggers** : Les calculs de prix sont automatiques via des triggers SQL
- **Commandes** : Une nouvelle commande (état = 1) est créée automatiquement à chaque connexion

### 🐛 Débogage

Pour activer les logs d'erreurs PHP :

```php
// Ajouter en haut de vos fichiers PHP pour déboguer
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Pour vérifier les requêtes SQL :
```php
// Dans template/ini.php, après la connexion PDO
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
```

---

## 📞 Support

Pour toute question ou problème :
- Consultez la [documentation technique](#-documentation-technique)
- Vérifiez les [valeurs possibles](#-valeurs-possibles)
- Consultez le code source sur [GitHub](https://github.com/FastAze/restoweb)