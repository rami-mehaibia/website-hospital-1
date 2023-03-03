PROJET PHP LOT 2 - Groupe 2
=====================================


Informations générales
-------------------------------------

Ce projet a pour vocation de faciliter au personnel de l'hôpital Polytech les recherches, saisies, enregistrements et transferts d'informations 
relatifs aux patients, grâce à notre interface web intuitive et facile d'utilisation ! 
Vous trouverez le détail des fonctionnalités réalisées dans la rubrique dédiée à celles-ci.


Organisation du répertoire projet
-------------------------------------

Le projet est structuré en 2 répertoires centraux :
=> 'Fichiers_du_site' qui renferme l'ensemble des fichiers permettant la construction et le fonctionnement de l'interface web.
	Pour plus d'informations, voici sa composition :
	- 9 dossiers avec différents contenus :
		* 'css', 'fonts', 'images' et 'js' -> éléments liées au design du site (styles, polices, images etc...)
		* 'includes_PHPExcel' -> fichiers nécéssaires au fonctionnement de la librairie PHPExcel qui sert à manipuler les fichiers excel en PHP
		* 'includes_PHPMailer' -> fichiers nécéssaires au fonctionnement de la librairie PHPMailer qui sert à envoyer des mails avec PHP
		* 'includes_TCPDF' -> fichiers nécéssaires au fonctionnement de la librairie TCPDF qui sert à créer des fichiers PDF
		* 'fichiers_patients' -> dossiers des patients contenant leurs documents
		* 'exemples de documents à soumettre' -> variété de documents (pièces d'identité, ordonnances, radios, IRM etc...) pouvant être utilisé 
		   pour tester les fonctionnalités de gestion de documents

	- 15 fichiers à usages divers :
		* Page d'accueil de notre interface -> 'index.php'
		* 3 onglets principaux -> 'recherche_patient.php', 'inscription_patient.php', 'documents.php'
		* Pages web secondaires appelées après différents évènements -> 'fiche_patient.php', 'modifier_patient.php', 'consulter_doc_patient.php', 
		  'importer_doc_patient.php', 'envoi_mail.php'
		* Délocalisation des informations communes aux pages web -> 'head.php', 'header.php', 'footer.php'
		* Regroupement des fonctions PHP nécéssaires au bon fonctionnement du site -> 'ressources_communes.php', 'telecharger_fiche_patient.php'
		* Liste des pays avec leur code à 2 ou 3 lettres (norme ISO 3166-1) -> 'liste_code_pays.xlsx'

=> 'Script_bd' qui comprend le script SQL 'base_hopital.sql' à exécuter sur la plateforme PhpMyAdmin pour créer la base de données de l'hôpital.


Fonctionnalités
-------------------------------------

Les fonctionnalités principales sont disponibles depuis le haut et pied de page du site, ou bien via le menu dynamique affiché sur la page d'accueil 
de l'interface.

Une fois que vous êtes sur le site, vous avez donc la possibilité de :

1. Rechercher des patients
Plusieurs filtres sont à votre disposition afin de faciliter la recherche : nom, motif d'admission, pays et date de naissance.
Vous avez la possibilité d'affiner votre recherche facilement grâce à la mémorisation des critères que vous aurez définis auparavant.
Suite aux résultats obtenu vous pourrez séléctionner le patient de votre choix et ainsi accéder à la page web permettant de visualiser sa fiche 
d'informations.
Depuis cette page plusieurs actions sont envisageables : télécharger la fiche du patient, modifier ses informations, le supprimer, consulter ses documents ou en importer, retourner vers les résultats de votre 
rechercher ou en effectuer une nouvelle.
Si vous choisissez de consulter ses documents alors vous serez dans la capacité de les chercher, ouvrir, télécharger, imprimer, envoyer ou encore 
supprimer.

2. Inscrire des nouveaux patients
Un formulaire est mis à votre disposition pour saisir toutes les informations concernant le patient à inscrire (nom, prénom, genre, date de 
naissance et d'entrée à l'hôpital, motif d'admission, pays de résidence et numéro de sécurité social).

3. Accéder aux documents de l'hôpital
Accès global à tous les documents de l'hôpital indépendamment des patients. Vous pouvez effecuter des recherches multi-critères (catégorie, type, 
nom et contenu) sur les documents disponibles. De plus il vous sera possible ici aussi de les ouvrir, télécharger, imprimer, envoyer ou supprimer.


Pré-requis
-------------------------------------

Afin d'être en mesure d'accéder à l'interface web, il vous faudra dans un premier temps :
[✔] Avoir WAMP et Google Chrome d'installés sur votre poste (liens pour les télécharger si votre pc n'en est pas équipé :
	Obtenir WAMP -> https://sourceforge.net/projects/wampserver/files/WampServer%203/WampServer%203.0.0/wampserver3.2.6_x64.exe/download,
	Obtenir Chrome -> https://www.google.fr/chrome/?brand=UEAD&ds_kid=43700053082282936&utm_source=bing&utm_medium=cpc&utm_campaign=1011197%20%7C%20Chrome%20Win10%20%7C%20DR%20%7C%20ESS01%20%7C%20EMEA%20%7C%20FR%20%7C%20fr%20%7C%20Desk%20%7C%20SEM%20%7C%20BKWS%20-%20EXA%20%7C%20Txt%20~%20Top%20KWDS&utm_term=t%C3%A9l%C3%A9charger%20google%20chrome&utm_content=Desk%20%7C%20BKWS%20-%20EXA%20%7C%20Txt%20~%20Download%20Chrome%20~%20Top%20KWDS%20-%20NEW&gclid=4a78f330cfc41abbc46b92ec9792af35&gclsrc=3p.ds)
[✔] Ouvrir PhpMyAdmin (http://127.0.0.1/phpmyadmin/) et vous connecter grâce au nom d'utilisateur "root" (aucun mot de passe n'est exigé)
[✔] Créer un utilisateur "user1" (sur votre espace PhpMyAdmin via l'onglet 'Comptes utilisateurs') qui détiendra le mot de passe suivant : "hcetylop"
[✔] Exécuter le script 'base_hopital.sql' au niveau de l'onglet 'SQL' de PhpMyAdmin pour la création et implémentation de la base de données de 
	l'hôpital "hopital_php". Six tables seront créées : motifs, pays, sexe, natures, patients et documents.

Dans un second temps vous devrez positionner le dossier du projet ('Polytech_Hopital') à l'emplacement suivant : 'C:\wamp64\www\' (répertoire 
www de WAMP) au niveau de votre explorateur de fichiers.


Configuration
-------------------------------------

Dans la barre de recherche du navigateur de votre choix, saisissez le chemin d'accès mentionné ci-dessous :
http://127.0.0.1/Polytech_Hopital/Fichiers_du_site/, vous obtiendrez l'interface web conçue par nos soins, bonne navigation !


Versions
-

Auteurs
-------------------------------------

Ce projet a été réalisé par Rami MEHAIBIA,  et Rayene HAKOUME, étudiants en Master 1 MIAGE à Polytech Lyon. 
