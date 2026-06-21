<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Languages configuration for the block_pin_user plugin.
 *
 * @package   block_pin_user
 * @copyright 2025, Miguël Dhyne <miguel.dhyne@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['badgebg']                    = 'Couleur de fond';
$string['badgebg_desc']               = 'Couleur de fond de ce badge.';
$string['badgecolor']                 = 'Couleur du texte';
$string['badgecolor_desc']            = 'Couleur du texte de ce badge.';
$string['badgename']                  = 'Nom du badge';
$string['badgename_desc']             = 'Un nom plus explicite, utilisé dans les liens d\'export et les en-têtes du CSV, là où la place est moins contrainte que sur le badge lui-même (qui utilise souvent une abréviation courte). Laissez vide pour réutiliser le texte du badge ci-dessous (ou « Badge N » si les deux sont vides).';
$string['badgesettings']              = 'Badge {$a}';
$string['badgesettings_desc']         = 'Configurez quand ce badge s\'affiche et son apparence. Laissez le champ de profil sur « Aucun » pour le désactiver.';
$string['badgesintro']                = 'Badges';
$string['badgesintro_desc']           = 'Configurez jusqu\'à {$a} badges, chacun affiché à côté du nom d\'un participant lorsque sa condition est remplie. Les badges inutilisés peuvent simplement rester sur « Aucun ».';
$string['badgetext']                  = 'Texte';
$string['badgetext_desc']             = 'Libellé affiché dans le badge. Peut rester vide si une icône suffit.';
$string['combinator']                 = 'Combiner avec';
$string['combinator_desc']            = 'Comment la condition principale se combine avec la condition supplémentaire ci-dessous. Sans effet si aucun champ de profil supplémentaire n\'est sélectionné.';
$string['combinatorand']              = 'ET (les deux conditions doivent être vraies)';
$string['combinatoror']               = 'OU (au moins une des deux conditions doit être vraie)';
$string['condition']                  = 'Condition';
$string['condition_desc']             = 'Sélectionnez la condition que le champ de profil choisi doit respecter.';
$string['conditionb']                 = 'Condition supplémentaire';
$string['conditionb_desc']            = 'Sélectionnez la condition que le champ de profil supplémentaire doit respecter.';
$string['conditionvalue']             = 'Valeur de comparaison';
$string['conditionvalue_desc']        = 'Utilisée par les conditions « égal à », « contient » et « ne contient pas ».';
$string['conditionvalueb']            = 'Valeur de comparaison supplémentaire';
$string['conditionvalueb_desc']       = 'Utilisée par la condition supplémentaire lorsqu\'elle est « égal à », « contient » ou « ne contient pas ».';
$string['contains']                   = 'Doit contenir';
$string['equals']                     = 'Doit être égal à';
$string['existingfields']             = 'Champs de profil personnalisés déjà présents sur ce site :';
$string['exportall']                  = 'Tous les participants (CSV)';
$string['exportcolemail']             = 'Adresse e-mail';
$string['exportcolname']              = 'Nom complet';
$string['exportcolvalue']             = 'Valeur';
$string['exportcolvalueb']            = 'Valeur (condition supplémentaire)';
$string['exportlabel']                = 'Exporter :';
$string['exportyes']                  = 'Oui';
$string['icon']                       = 'Icône';
$string['icon_desc']                  = 'Icône optionnelle affichée avant le texte du badge (ou seule si le texte est laissé vide).';
$string['iconaccessibility']          = 'Accessibilité';
$string['iconbell']                   = 'Notification';
$string['iconcheck']                  = 'Validé';
$string['iconflag']                   = 'Signalé';
$string['iconheart']                  = 'Cœur / santé';
$string['iconinfo']                   = 'Information';
$string['iconlock']                   = 'Confidentiel';
$string['iconmedical']                = 'Médical';
$string['iconnone']                   = 'Aucune';
$string['iconstar']                   = 'Important';
$string['iconwarning']                = 'Avertissement';
$string['invalidbadge']               = 'Badge invalide ou non configuré.';
$string['isempty']                    = 'Doit être vide';
$string['isnotempty']                 = 'Ne doit pas être vide';
$string['manageprofilefields']        = 'Actions rapides';
$string['manageprofilefields_button'] = 'Gérer les champs de profil personnalisés (nouvel onglet)';
$string['manageprofilefields_desc']   = 'Un champ de profil doit exister avant de pouvoir être sélectionné ci-dessous. Utilisez le raccourci ci-dessous pour créer ou consulter les champs de profil personnalisés sans quitter cette page.';
$string['nofieldsyet']                = 'Aucun champ de profil personnalisé n\'existe encore sur ce site. Utilisez le bouton ci-dessous pour en créer un.';
$string['none']                       = 'Aucun (badge désactivé)';
$string['notcontains']                = 'Ne doit pas contenir';
$string['permissionsnotice']          = 'Permissions : à vérifier';
$string['permissionsnotice_button']   = 'Gérer les rôles et permissions';
$string['permissionsnotice_desc']     = 'Deux capacités distinctes contrôlent ce bloc : block/pin_user:addinstance (et :myaddinstance) déterminent qui peut ajouter le bloc à une page, tandis que block/pin_user:viewbadges détermine qui peut voir son contenu. Sur une installation neuve, les deux sont configurées automatiquement. Si vous mettez à jour depuis une version antérieure de ce plugin, Moodle accorde automatiquement la nouvelle capacité viewbadges aux rôles Enseignant et Manager, mais ne met PAS à jour automatiquement les permissions addinstance/myaddinstance déjà existantes sur votre site : vérifiez manuellement le rôle Enseignant si besoin.';
$string['pin_user']                   = 'Repérer un utilisateur';
$string['pin_user:addinstance']       = 'Ajouter un nouveau bloc « Repérer un utilisateur »';
$string['pin_user:myaddinstance']     = 'Ajouter un nouveau bloc « Repérer un utilisateur » à la page Mon Moodle';
$string['pin_user:viewbadges']        = 'Voir les badges des participants repérés';
$string['pluginname']                 = 'Repérer un utilisateur';
$string['pluginnamedisplay']          = 'Repérer un utilisateur';
$string['privacy:metadata']           = 'Le bloc Pin User ne stocke aucune donnée personnelle. Il lit et affiche temporairement des données de profil existantes (qui peuvent inclure des champs personnalisés sensibles) aux utilisateurs disposant de la capacité requise.';
$string['profilefield']               = 'Champ de profil';
$string['profilefield_desc']          = 'Champ personnalisé de profil utilisateur à vérifier. Choisissez « Aucun » pour désactiver ce badge.';
$string['profilefieldb']              = 'Champ de profil (condition supplémentaire)';
$string['profilefieldb_desc']         = 'Un second champ de profil personnalisé, optionnel. Laissez sur « Aucun » pour n\'utiliser qu\'une seule condition (comportement par défaut, identique aux versions précédentes).';
$string['secondcondition']            = 'Seconde condition';
$string['secondcondition_desc']       = 'Optionnel. Cliquez pour combiner une seconde condition de champ de profil à celle ci-dessus, via ET ou OU.';
$string['secondcondition_toggle']     = 'Combiner avec une seconde condition (ET/OU)';
$string['upgradenotice_v2']           = 'Le bloc Repérer un utilisateur a été mis à jour vers la v2.0.0. Il utilise désormais une capacité dédiée pour contrôler qui voit les badges, et la capacité d’ajout du bloc est désormais accordée par défaut au rôle Enseignant. Moodle ne met pas à jour automatiquement les permissions déjà existantes avant cette mise à jour : vérifiez block/pin_user:addinstance et block/pin_user:myaddinstance pour le rôle Enseignant dans Administration du site > Utilisateurs > Permissions > Définir les rôles.';
$string['upgradenotice_v3']           = 'Le bloc Repérer un utilisateur a été mis à jour vers la v2.1.0 : vous pouvez désormais configurer jusqu\'à {$a} badges (au lieu de 2), chacun avec une icône optionnelle. Vos badges existants ont été conservés tels quels, rien à reconfigurer. Rendez-vous dans les réglages du plugin pour en ajouter.';
$string['upgradenotice_v4']           = 'Le bloc Repérer un utilisateur a été mis à jour vers la v2.2.0 : chaque badge peut désormais combiner une seconde condition (ET/OU) avec la première. C\'est optionnel : vos badges existants n\'utilisent qu\'une seule condition et continuent de fonctionner exactement comme avant.';
$string['upgradenotice_v5']           = 'Le bloc Repérer un utilisateur a été mis à jour vers la v2.3.0 : un lien « Exporter » apparaît maintenant au-dessus de la liste des participants, permettant aux enseignants de télécharger un CSV de tous les participants (avec une colonne par badge) ou seulement des participants correspondant à un badge précis.';
$string['upgradenotice_v6']           = 'Le bloc Repérer un utilisateur a été mis à jour : les champs « condition supplémentaire » (ET/OU) de chaque badge sont désormais repliés derrière un petit lien « Combiner avec une seconde condition », juste sous le champ de valeur de comparaison de ce badge, plutôt que d\'être toujours affichés. Rien n\'est perdu : votre configuration existante (s\'il y en avait une) se trouve exactement où vous l\'aviez laissée, et cette section s\'affiche dépliée si elle était déjà utilisée.';
