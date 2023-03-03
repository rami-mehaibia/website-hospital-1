

PROJET HOPITAL PHP 



 # Informations générales
Ce projet a pour vocation de permettre au personnel de l'hôpital Polytech, de chercher des patients selon des critères précis et de consulter facilement leurs
informations personnelles, grâce à une interface web intuitive et facile d'utilisation !

Le projet est structuré en 4 fichiers principaux :

	-le document "recherche_patient.php" qui correspond à la page web d'accueil. C'est ici que l'utilisateur va se rendre afin d'avoir accès au formulaire
    	 et au résultat de sa recherche,
       
 	-le document "fiche_patient.php" qui est la page web où l'on pourra explorer les informations associées à un patient choisi,
  
 	-le document "ressources_communes.php" qui contient l'ensemble des fonctionnalités nécéssaires au bon fonctionnement du site (tel que la connexion à la base de données,
  
	 la récupération des informations essentielles à l'affichage du formulaire, la réalisation des différentes recherches de données, l'affichage de leurs résultats, etc..), et enfin,
   
 	-le document "base_hopital.sql" qui est un script sql permettant de créer et d'implémenter la base de données de l'hôpital sur la plateforme PhpMyAdmin.


 # Pré-requis
Afin d'être en mesure d'accéder au site Internet, il vous faudra dans un premier temps :

	1. avoir WAMP d'installé sur votre poste.
     
	2. ouvrir PhpMyAdmin (http://127.0.0.1/phpmyadmin/) et se connecter grâce au nom d'utilisateur "root" (aucun mot de passe n'est attendu),
  
	3. créer un utilisateur "user1" sur votre espace PhpMyAdmin (via l'onglet "Comptes utilisateurs") qui détiendra le mot de passe suivant: "hcetylop", 
  
	4. créer une base de données que vous nommerez "hopital_php",
  
	5. exécuter au niveau de l'onglet "SQL" de PhpMyAdmin le script "base_hopital.sql" pour la création et l'implémentation de la base de données (4 tables seront créées :
	  patients, motifs, pays et sexe). 
    
	  Vous détiendrez ainsi la base de données de l'hôpital afin d'utiliser l'interface web confortablement.






 # Fonctionnalités
Une fois que vous êtes sur le site, vous avez la possibilité de rechercher des patients en cliquant sur le bouton "Commencer".

L'enclanchement du bouton vous redirigera vers un formulaire, où vous aurez la possibilité de saisir les caractéristiques des patients que vous recherchez.

Plusieurs filtres sont à votre disposition afin de faciliter votre recherche. Vous pourrez ainsi filtrer les patients en fonction des champs suivants :
 - Nom,
 - Motifs d'admission,
 - sexe
 - Pays, et 
 - Genre du patient.
Vous pourrez affiner votre recherche facilement grâce à la mémorisation de vos critères, qui ont été définis auparavant.

# Help 
 Pour toute questions n'hésitez pas à nous contacter par mail afin que nous puissions répondre à vos interrogations.
(rami.mehaibia@gmail.com)
 
