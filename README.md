# block_pin_user

Bloc Moodle qui affiche, sur la page **Participants** d'un cours, la liste des
inscrits actifs avec jusqu'à six badges conditionnels basés sur des champs de
profil personnalisés (ex. allergies, PAI, statut, etc.).

## Installation

1. Copier ce dossier dans `blocks/pin_user` de votre installation Moodle.
2. Aller dans *Administration du site > Notifications* pour terminer
   l'installation.
3. Ajouter le bloc « Repérer un utilisateur » sur la page Participants d'un
   cours (le bloc ne peut être ajouté que dans le contexte d'un cours).

## Configuration

Les réglages se trouvent dans *Administration du site > Blocs > Repérer un
utilisateur*, sur une seule page.

Pour chaque badge, la condition principale (champ, condition, valeur) est
toujours visible. La **seconde condition optionnelle (ET/OU)** est repliée
par défaut derrière un petit lien **« Combiner avec une seconde condition
(ET/OU) »**, juste sous le champ de valeur de comparaison — un clic suffit
pour la déplier. Si un badge a déjà une seconde condition configurée, cette
section s'affiche dépliée d'emblée. Ce repli utilise l'élément HTML natif
`<details>`/`<summary>` : aucun JavaScript n'est nécessaire, et les valeurs
restent soumises avec le formulaire même quand la section est repliée.

Vous pouvez configurer jusqu'à **6 badges** (constante `MAX_BADGES` dans
`classes/badge_config.php`, modifiable par un développeur si besoin). Chaque
badge inutilisé peut simplement rester sur **« Aucun »** — il n'apparaît
alors nulle part, ni dans la page de réglages au moment de l'enregistrement,
ni dans le rendu.

Pour chaque badge, vous choisissez :

- **Nom du badge** (optionnel) : un nom plus explicite que le texte affiché
  sur le badge, utilisé dans les liens d'export et les en-têtes du CSV
  (ex. « Élève à besoins spécifiques » alors que le badge à l'écran affiche
  juste « EBS »). Laissez vide pour réutiliser le texte du badge.
- **Champ de profil** : un menu déroulant listant les champs de profil
  personnalisés qui existent réellement sur le site. La valeur **« Aucun »**
  désactive entièrement le badge — c'est la valeur par défaut, pour qu'un
  badge ne s'affiche jamais sans avoir été explicitement configuré.
- **Condition** : vide / non vide / égal à / contient / ne contient pas.
- **Valeur de comparaison** : utilisée par « égal à », « contient » et « ne
  contient pas ».
- **Condition supplémentaire (optionnelle, repliée par défaut)** : un
  second champ de profil, avec sa propre condition et valeur, combiné au
  premier via **ET** ou **OU**. Laissez ce second champ sur « Aucun »
  (valeur par défaut) pour n'utiliser qu'une seule condition — c'est le
  comportement historique du plugin, garanti inchangé pour tout badge déjà
  configuré.
- **Icône** (optionnelle) : à choisir dans une liste d'émojis courants
  (⚠️ ❤️ ✚ ♿ ⭐ 🚩 ℹ️ ✅ 🔔 🔒). Volontairement de simples caractères Unicode
  plutôt que des icônes Font Awesome ou Moodle (`pix_icon`) : leur rendu ne
  dépend ni du thème, ni de la version de Moodle, contrairement à un nom de
  classe ou d'identifiant d'icône.
- **Texte** : peut rester vide si l'icône suffit à elle seule (un badge
  icône seule reste accessible : un libellé est automatiquement annoncé aux
  lecteurs d'écran).
- **Couleurs** du badge.

> **Compatibilité ascendante** : les badges 1 et 2 utilisent exactement les
> mêmes noms de réglages que dans les versions précédentes
> (`profilefield1`, `text1`, etc.). Si vous mettez à jour depuis une version
> antérieure, votre configuration existante est conservée telle quelle —
> rien à reconfigurer.

### Créer un nouveau champ de profil sans quitter la page

En haut de la page de réglages, un bouton **« Gérer les champs de profil
personnalisés »** ouvre directement la page d'administration Moodle
correspondante (`/user/profile/index.php`) dans un nouvel onglet, avec un
rappel des champs déjà existants. Une fois le champ créé, revenez sur cet
onglet et rafraîchissez la page de réglages : il apparaîtra dans les menus
déroulants.

> Ce plugin ne réimplémente pas la création de champs de profil dans sa
> propre interface : Moodle dispose déjà d'un formulaire complet et
> maintenu pour cela. Dupliquer cette fonctionnalité ajouterait de la
> maintenance et des risques de divergence avec le core à chaque nouvelle
> version de Moodle, pour un bénéfice limité par rapport à un simple lien
> direct.

## Permissions

Deux capacités contrôlent deux choses différentes :

| Capacité | Contrôle | Par défaut |
|---|---|---|
| `block/pin_user:addinstance` / `:myaddinstance` | Qui peut **ajouter** le bloc sur une page de cours / sur Mon Moodle. | Enseignant (édition), Manager |
| `block/pin_user:viewbadges` | Qui peut **voir le contenu** du bloc (la liste + les badges), une fois qu'il est ajouté. | Enseignant (édition), Manager |

Ce sont deux portes indépendantes : avoir le droit d'ajouter le bloc ne donne
pas automatiquement le droit d'en voir le contenu, et inversement.

`viewbadges` est une capacité dédiée, séparée de
`moodle/course:manageactivities` (utilisée par la v1.0), afin de pouvoir
restreindre la visibilité des badges indépendamment des droits de gestion du
cours — utile si les champs de profil affichés sont sensibles.

### Sur une installation neuve

Rien à faire : Moodle lit `db/access.php` à l'installation et attribue
automatiquement les deux capacités aux rôles Enseignant et Manager.

### Sur une mise à jour depuis la v1.0

⚠️ Point important, facilement manqué :

- **`viewbadges` (nouvelle capacité)** → Moodle la crée et l'attribue
  automatiquement aux rôles Enseignant/Manager pendant la mise à jour. Rien à
  faire, sauf si vous voulez aussi l'accorder à un autre rôle.
- **`addinstance` / `myaddinstance` (capacités déjà existantes)** → la v2.0.0
  corrige l'archétype par défaut (`teacher` → `editingteacher`, c'est-à-dire
  le rôle Enseignant standard plutôt que le rôle Enseignant non-éditeur).
  **Moodle ne réapplique pas ce changement automatiquement** sur les
  capacités déjà présentes en base de données — c'est volontaire, pour ne
  pas écraser des permissions que vous auriez personnalisées. Si vous mettez
  à jour depuis la v1.0, vérifiez/accordez manuellement ces deux capacités au
  rôle Enseignant :

  *Administration du site → Utilisateurs → Permissions → Définir les rôles →
  Enseignant → rechercher `block/pin_user:addinstance` et
  `block/pin_user:myaddinstance` → Autoriser.*

Deux rappels sont intégrés au plugin pour ne pas que ça passe inaperçu :
- Un message d'avertissement s'affiche automatiquement juste après la mise à
  jour **si elle est faite via l'interface web** (`db/upgrade.php`). Il ne
  s'affichera pas pour une mise à jour en ligne de commande
  (`admin/cli/upgrade.php`).
- Un rappel permanent figure en haut de la page de réglages du plugin, avec
  un raccourci direct vers *Définir les rôles*, visible quel que soit le mode
  de mise à jour utilisé.

## ⚠️ Note sur les données sensibles

Les champs de profil utilisés par ce bloc peuvent contenir des informations
sensibles (ex. informations de santé). Le bloc ne **stocke** aucune donnée
(voir `classes/privacy/provider.php`), mais il **affiche** ces informations à
toute personne disposant de la capacité `block/pin_user:viewbadges`. Pensez à
vérifier qui dispose de cette capacité sur votre site avant d'utiliser ce
plugin pour des champs sensibles.

## Export CSV

Un lien **« Exporter »** apparaît au-dessus de la liste des participants,
visible par toute personne disposant de `block/pin_user:viewbadges` (le
même droit que pour voir les badges à l'écran) :

- **Tous les participants (CSV)** : nom, e-mail, puis une colonne par badge
  configuré, contenant **la valeur réelle du champ de profil** lorsque le
  badge s'applique (ex. « Arachides, gluten »), « Oui » si le badge
  s'applique mais que le champ est vide (cas d'une condition « doit être
  vide »), ou une cellule vide si le badge ne s'applique pas.
- **Un lien par badge** (libellé = texte du badge) : uniquement les
  participants pour qui ce badge précis s'applique, avec une colonne
  « Valeur » (et une seconde colonne si une condition supplémentaire est
  configurée pour ce badge).

Le fichier est encodé en UTF-8 avec BOM et utilise le point-virgule comme
séparateur (convention Excel en français). L'export réutilise exactement la
même logique de correspondance que l'affichage à l'écran (classe
`badge_matcher`), donc la liste exportée ne peut jamais diverger de ce qui
est affiché dans le bloc.

> ⚠️ Ce fichier peut contenir des données sensibles (mêmes données que les
> badges affichés). Traitez-le selon les règles de protection des données
> de votre établissement.

## Tests

```
vendor/bin/phpunit --filter block_pin_user
```

`tests/condition_evaluator_test.php` couvre la logique des conditions de
badge de façon isolée. `tests/renderer_test.php` vérifie le rendu HTML,
y compris l'échappement du texte des badges.

## Changelog

### v2.5.1
- Retour sur l'approche « page séparée » de la v2.5.0, jugée peu lisible :
  la seconde condition (ET/OU) de chaque badge est maintenant un petit lien
  repliable (`<details>`/`<summary>`, sans JavaScript) directement sous le
  champ de valeur de comparaison, plutôt que sur une page à part.
- Aucun impact sur la configuration existante.
- Tests ajoutés pour la validation/stockage de ce nouveau composant.

### v2.4.1
- Correctifs suite au passage du Moodle Code Checker :
  - Chaînes de langue remises en ordre alphabétique strict (les paires
    `*_button` / `*_desc` n'étaient pas correctement ordonnées).
  - Suppression de `defined('MOODLE_INTERNAL') || die();` là où il n'est
    plus nécessaire (classes autochargées, `lib.php`, `db/upgrade.php`).
  - Docblocks complétés (description manquante sur plusieurs méthodes).
  - Commentaires en ligne mis en majuscule en début de phrase.

### v2.4.0
- Ajout d'un champ « Nom du badge » optionnel, distinct du texte affiché
  sur le badge, utilisé pour les liens d'export et les en-têtes du CSV.
- L'intitulé de chaque section de réglages affiche désormais ce nom une
  fois configuré, pour se repérer plus facilement sur une longue page.

### v2.3.1
- L'export CSV affiche désormais la valeur réelle du champ de profil
  (ex. « Arachides, gluten ») au lieu d'un simple Oui/Non, avec un repli sur
  « Oui » quand le badge correspond mais que le champ est vide.
- L'export par badge unique gagne une colonne « Valeur » (et une seconde
  colonne si une condition supplémentaire est configurée).

### v2.3.0
- Ajout d'un export CSV : tous les participants (avec une colonne par
  badge) ou seulement ceux correspondant à un badge précis.
- Protégé par la même capacité que l'affichage des badges, plus une
  vérification de sesskey.
- Refactorisation : la logique de correspondance des badges
  (`badge_matcher`) et la construction de la requête SQL
  (`participant_loader`) sont désormais partagées entre l'affichage à
  l'écran et l'export, pour garantir qu'ils ne divergent jamais.

### v2.2.0
- Chaque badge peut désormais combiner une seconde condition (champ +
  condition + valeur) avec la première, via ET/OU.
- Entièrement optionnel : un badge sans second champ configuré (tous les
  badges existants) se comporte exactement comme avant.
- Tests ajoutés pour les combinaisons ET/OU.

### v2.1.0
- Généralisation à un nombre configurable de badges (jusqu'à 6, au lieu de 2
  fixes), via des champs de réglages natifs (pas de JSON, pas de JS).
- Ajout d'une icône optionnelle par badge (liste d'émojis Unicode curatée).
- Les réglages des badges 1 et 2 sont conservés sous leurs noms de clés
  d'origine : aucune reconfiguration nécessaire après mise à jour.
- Ajout de tests pour `badge_config` et mise à jour des tests du renderer.

### v2.0.0
- Correction : les deux badges ne s'affichent plus pour tous les
  participants par défaut sur une installation vierge.
- Nouvelle capacité dédiée `block/pin_user:viewbadges`.
- La requête ne montre plus que les inscriptions actives (s'appuie sur
  `get_enrolled_sql()` au lieu de jointures manuelles).
- Échappement du texte des badges (faille XSS stockée potentielle corrigée).
- Couleurs par défaut des badges revues pour respecter le contraste WCAG AA.
- Suppression de l'endpoint `css.php` au profit d'un court `<style>` inline
  (une requête HTTP de moins par page, plus de maintenance de cache).
- Sélection des champs de profil via menu déroulant (au lieu d'un champ texte
  libre), avec lien direct vers la page d'administration des champs de
  profil Moodle.
- Ajout de tests unitaires PHPUnit.
- Ajout d'un avertissement automatique à la mise à jour (`db/upgrade.php`) et
  d'un rappel permanent dans les réglages au sujet du point « Permissions »
  ci-dessus.
