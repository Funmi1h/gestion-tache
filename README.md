# Taskflow — Gestion de Tâches en Équipe

### Contexte  
Une application web collaborative conçue pour faciliter l'organisation, le suivi et la répartition des tâches au sein d'équipes de travail restreintes (jusqu'à 6 collaborateurs).
Réalisé dans le cadre d'un projet de développement avec le framework **Laravel**, ce projet met en œuvre les bonnes pratiques d'architecture MVC, une sécurité stricte des rôles (Admin/Membre), un système d'invitations sécurisé par jeton unique et e-mail (Mailpit), un tableau de bord analytique en temps réel, ainsi qu'un espace de travail opérationnel sous forme de tableau **Kanban**.

---

## Fonctionnalités

* **Authentification & Profils :** Inscription, connexion sécurisée et déconnexion avec gestion du nom et prénom.
* **Gestion des Équipes :** Création d'équipes avec attribution automatique du rôle `ADMIN` au créateur et limitation stricte à **6 membres par équipe** (CDC §3.2).
* **Système d'Invitations par E-mail :** Envoi d'invitations par e-mail avec un jeton sécurisé unique (expiration sous 7 jours), gestion des statuts (en attente, acceptée, annulée, expirée) et protection contre l'usurpation d'adresse e-mail.
* **Tableau de Bord & KPIs en Temps Réel :** Indicateurs clés de performance (Total, À faire, En cours, Terminées, En retard), barre d'avancement global de l'équipe, jauge de répartition de la charge par membre et section dédiée « Mes tâches ».
* **Espace Kanban Opérationnel :** Tableau Kanban à 3 colonnes de flux (*À faire*, *En cours*, *Terminées*) avec déplacement dynamique des tâches selon leur statut.
* **Alertes de Retard Visuelles :** Détection automatique des tâches en retard avec badge d'alerte et filtre instantané *« En retard uniquement »*.
* **Permissions & Règles Métier :** Seul le responsable d'une tâche peut modifier son statut en *Terminée* ou *En cours* (CDC §6.2) ; seuls le créateur de la tâche ou l'admin de l'équipe peuvent la modifier ou la supprimer.

---

## Prérequis

- [PHP](https://www.php.net/) (version 8.2 ou supérieure)
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/)
- [Mailpit](https://github.com/axllent/mailpit) (pour tester la réception locale des e-mails d'invitation)

---

## Installation

### 1. Cloner le dépôt
<pre>
git clone https://github.com/votre-utilisateur/gestion-taches.git
cd gestion-taches
</pre>

### 2. Installer les dépendances PHP
<pre>
composer install
</pre>

### 3. Configurer les variables d'environnement
Dupliquez le fichier `.env.example` en `.env` :
<pre>
cp .env.example .env
</pre>

Ouvrez le fichier `.env` et ajustez vos paramètres de base de données et de messagerie :
```dotenv
APP_NAME=Taskflow
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gestion_taches
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe

# Configuration Mailpit pour les e-mails locaux
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_FROM_ADDRESS="noreply@taskflow.com"
MAIL_FROM_NAME="Taskflow"
```

### 4. Générer la clé d'application
<pre>
php artisan key:generate
</pre>

### 5. Effectuer les migrations de la base de données
<pre>
php artisan migrate
</pre>

### 6. Charger les données de démonstration (Seeders)
Pour initialiser l'application avec des équipes, des collaborateurs et des tâches réalistes :
<pre>
php artisan db:seed
</pre>

### 7. Démarrer le serveur de développement
<pre>
php artisan serve
</pre>
L'application est accessible sur : **http://localhost:8000**

Si vous testez les e-mails d'invitation, lancez Mailpit en arrière-plan 
<pre>
mailpit
</pre>
Et ouvrez l'interface web sur **http://localhost:8025**.

---

## Exécution des Tests

Pour vérifier la conformité du code et le bon fonctionnement des règles métier :
<pre>
php artisan test
</pre>

---

## Comptes de Démonstration (Jeu de test)

| Nom & Prénom | Adresse E-mail | Mot de passe | Rôle principal |
| :--- | :--- | :---: | :--- |
| **Héloïse Akohou** | `ak.heloise2007@gmail.com` | `password` | **ADMIN** de l'Équipe Marketing |
| **Endedans Ici** | `ici.endedans@gmail.com` | `password` | **ADMIN** de l'Équipe Refonte Web |
| **Marc Dupont** | `marc.dupont@taskflow.test` | `password` | **Membre** collaborateur |
| **Sarah Martin** | `sarah.martin@taskflow.test` | `password` | **Membre** (avec tâche en retard) |
