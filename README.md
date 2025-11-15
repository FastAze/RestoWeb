# RestoWeb 🍕

Application web de commande en ligne pour restaurant développée en PHP/MySQL dans le cadre du projet AP.SLAM BTS SIO 2ème année.

## 📋 Documents de conception

### Diagrammes et modèles de données

| Dossier | Description | Lien |
|:--------|:------------|:----:|
| `doc/DCU/` | Diagramme des Cas d'Utilisation | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/DCU) |
| `doc/mcd/` | Modèle Conceptuel de Données | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/mcd) |
| `doc/mld/` | Modèle Logique de Données | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/mld) |
| `doc/mpd/` | Modèle Physique de Données (SQL) | [📂](https://github.com/FastAze/restoweb/tree/lot4/doc/mpd) |

### Valeurs possibles

#### États des commandes

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

#### Types de consommation

| Type | Description | TVA applicable |
|:-----|:------------|:---------------|
| Sur place | Consommation dans le restaurant | 10% |
| À emporter | Commande à emporter | 5.5% |

### Documentation interface

- **Sitemap** : `doc/sitemap/`
- **Maquettes IHM** : `doc/ihm/`
- **Diagrammes d'activité** : `doc/diagrammes_activites_du_processus_de_commande/`

## 📦 Manuel d'installation

### 1. Releases et branche Git

```bash
# Cloner le repository
git clone https://github.com/FastAze/restoweb.git

# Se positionner sur la branche appropriée
cd restoweb
git checkout lot5
```

> **Note** : Utilisez toujours la branche avec le numéro de lot le plus élevé pour obtenir la dernière version stable.

### 2. Installation dans le serveur web

#### Avec XAMPP
```bash
# Windows
move restoweb C:\xampp\htdocs\

# Mac
mv restoweb /Applications/XAMPP/xamppfiles/htdocs/

# Linux
mv restoweb /opt/lampp/htdocs/
```

Accès : `http://localhost/restoweb`

### 3. Installation de la base de données

```bash
# Se connecter à MySQL
mysql -u root -p

# Créer la base de données
CREATE DATABASE restoweb;
USE restoweb;

# Exécuter le script SQL principal (structure + données + triggers)
SOURCE /chemin/vers/restoweb/doc/mpd/restoweb.sql;
```

Le script `restoweb.sql` contient :
- La structure des tables
- Les triggers de calcul automatique
- Les données de test

### 4. Paramétrage de l'application

Modifier le fichier `template/ini.php` avec vos paramètres de connexion :

```php
<?php
function db_connect()
{
    $dsn = 'mysql:host=localhost;dbname=restoweb';
    $user = 'root';
    $password = '';
    
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

**Paramètres à adapter :**
- `dbname` : Nom de la base de données (défaut : `restoweb`)
- `user` : Utilisateur MySQL (défaut : `root`)
- `password` : Mot de passe MySQL
- `host` : Serveur MySQL (défaut : `localhost`)

## 🧪 Manuel du jeu de test

### Comptes utilisateurs de test

| Nom d'utilisateur | Email | Mot de passe |
|:------------------|:------|:-------------|
| `123` | `123@gmail.com` | `123` |
| `aze` | `aze@gmail.com` | `aze` |

> **⚠️ Attention** : Ces comptes sont uniquement destinés aux tests en environnement de développement.

### Données de test existantes

#### Produits (15 pizzas)

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

### Scénarios de test recommandés

#### Test 1 : Parcours complet nouveau client
```
1. Accéder à index.php
2. Créer un compte via inscription.php
3. Ajouter 3 produits au panier
4. Modifier les quantités
5. Choisir "Sur place"
6. Finaliser le paiement
7. Vérifier l'historique dans le profil
```

#### Test 2 : Client existant
```
1. Se connecter avec aze/aze
2. Ajouter des produits
3. Choisir "À emporter"
4. Compléter le paiement
5. Vérifier les notifications
```

#### Test 3 : Calcul TVA
```
Produit : Pizza Margherita (8.50€) x2 = 17€ HT
- Sur place : 17€ × 1.10 = 18.70€ TTC
- À emporter : 17€ × 1.055 = 17.94€ TTC
```

## 📖 Manuel d'utilisation

### Inscription

1. **Accéder au formulaire**
   - Depuis `index.php`, cliquer sur "Inscription"
   - Ou accéder directement à `inscription.php`

2. **Remplir le formulaire**
   - Nom d'utilisateur (unique)
   - Adresse e-mail valide
   - Mot de passe sécurisé

3. **Validation**
   - Cliquer sur "S'inscrire"
   - Une commande vide est automatiquement créée
   - Redirection vers la page d'accueil connecté

### Connexion

1. **Accès**
   - Depuis `index.php`, cliquer sur "Connexion"
   - Ou accéder à `connection.php`

2. **Authentification**
   - Saisir nom d'utilisateur
   - Saisir mot de passe
   - Cliquer sur "Connexion"

3. **Après connexion**
   - Redirection vers `accueilConnecte.php`
   - Session active
   - Commande automatiquement vérifiée/créée

### Commande

#### 1. Parcourir le catalogue

- Tous les produits disponibles sont affichés sur `accueilConnecte.php`
- Chaque produit affiche : image, nom, prix HT

#### 2. Ajouter au panier

1. Cliquer sur un produit pour voir les détails
2. Une fenêtre modale s'ouvre avec :
   - Image en grand format
   - Nom et prix
   - Champ quantité
3. Saisir la quantité désirée (minimum : 1)
4. Cliquer sur "Valider" pour ajouter au panier
5. Ou cliquer sur "Retour" pour annuler

#### 3. Gérer le panier

1. **Accès** : Cliquer sur "Panier" → redirection vers `panier.php`

2. **Modifications disponibles** :
   - Boutons "+" et "-" pour ajuster les quantités
   - Bouton "Supprimer" pour retirer un article
   - Recalcul automatique des prix

3. **Type de consommation** (obligatoire) :
   - 🍽️ Sur place : TVA à 10%
   - 📦 À emporter : TVA à 5.5%

4. **Actions** :
   - "Valider" : passer au paiement
   - "Retour" : continuer les achats

### Paiement

1. **Informations affichées**
   - Numéro de commande
   - Type de consommation
   - Montant total TTC

2. **Formulaire de paiement**
   - **Numéro de carte** : 16 chiffres (format : 1234 5678 9012 3456)
   - **Code CCV** : 3 chiffres
   - **Date d'expiration** : Format MM/AA

3. **Validation**
   - Les champs sont formatés automatiquement
   - Validation avant soumission

4. **Finalisation**
   - Cliquer sur "Valider"
   - Popup de confirmation
   - État de la commande passe à "finalisée"
   - Notification par e-mail

5. **Annulation**
   - Cliquer sur "Annuler" pour revenir à l'accueil
   - Le panier reste intact

> **🔒 Sécurité** : Traitement sécurisé via requêtes PDO préparées

### Fonctionnalités supplémentaires

#### Profil utilisateur
- Accès : Cliquer sur le nom d'utilisateur
- Visualisation de l'historique complet des commandes
- Informations affichées : N°, date, type, prix, statut

#### Notifications
- Accès : Icône 🔔 dans la navigation
- Affichage de l'état des commandes en cours

#### Déconnexion
- Cliquer sur "Déconnexion"
- Destruction de la session
- Redirection vers la page d'accueil publique

## 📁 Structure du projet

```
restoweb/
├── 📄 index.php                    # Page d'accueil publique
├── 📄 inscription.php              # Formulaire d'inscription
├── 📄 connection.php               # Formulaire de connexion
├── 📄 accueilConnecte.php          # Page d'accueil authentifiée
├── 📄 panier.php                   # Gestion du panier
├── 📄 paiement.php                 # Processus de paiement
├── 📄 profile.php                  # Profil utilisateur
├── 🎨 main.css                     # Styles globaux
│
├── 📂 template/                    # Configuration
│   ├── ini.php                     # Connexion base de données
│   ├── path.php                    # Gestion des chemins
│   └── chekEtat.php                # Vérification commandes
│
├── 📂 component/                   # Composants réutilisables
│   ├── componentArticle.php        # Affichage articles
│   └── componentArticleIndex.php   # Articles page accueil
│
├── 📂 doc/                         # Documentation technique
│   ├── DCU/                        # Diagrammes cas d'usage
│   ├── mcd/                        # Modèle Conceptuel
│   ├── mld/                        # Modèle Logique
│   ├── mpd/                        # Modèle Physique (SQL)
│   ├── ihm/                        # Maquettes interface
│   ├── sitemap/                    # Plan du site
│   └── diagrammes_activites_du_processus_de_commande/
│
└── 📂 image/                       # Ressources images
```

## 🔧 Support technique

### Problèmes courants

**Connexion impossible**
- Vérifier les identifiants
- Contrôler la configuration `template/ini.php`
- Vérifier que la base de données est accessible

**Panier vide après connexion**
- Une nouvelle commande (état = 1) est créée automatiquement
- Vérifier l'état de la commande en base

**Calcul incorrect**
- Les calculs utilisent des triggers SQL automatiques
- Vérifier que les triggers sont bien installés

**Pas de notification**
- Fonctionnalité en cours de développement
- L'envoi d'e-mails sera ajouté prochainement

### Débogage

Activer les logs d'erreurs PHP :
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

Activer les logs PDO :
```php
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
```

## 📞 Contact

- **Projet** : RestoWeb - AP.SLAM BTS SIO 2ème année
- **Institut** : LIMAYRAC
- **Responsable** : Christophe PUEL
- **Repository** : [https://github.com/FastAze/restoweb](https://github.com/FastAze/restoweb)