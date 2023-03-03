<?php

//On inclue les fichiers et classes nécéssaires pour la fonction d'envoi de mail "send_mail()"
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'includes_PHPMailer/PHPMailer.php';
require 'includes_PHPMailer/SMTP.php';
require 'includes_PHPMailer/Exception.php';

//Fonction de connexion à la BD
function connexion(){

  $servername = "localhost"; //Nom du serveur
  $username = "user1"; //Nom d'utilisateur
  $password = "hcetylop"; //Mot de passe
  $database = "hopital_php"; //Nom de la base de données

    try
    {
        //PDO (PHP Data Objects) est une extension qui permet de définir l'interface pour accéder à la base de données avec PHP
        //Le code ci-dessous permet d'établir la connexion à la base de données avec renseignement des identifiants de connexion et noms du serveur et de la BD
        //charset=utf8 permet l'affichage des caractères particuliers (accents notamment)
        $bdd = new PDO("mysql:host=$servername;dbname=$database;charset=utf8", $username, $password);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $bdd->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
    }
    catch(PDOException $e)
    {
      echo "Erreur : ".$e->getMessage()."</br>"; //Renvoie un message d'erreur si échec de connexion, en renseignant quel est le problème rencontré
      die;
    }

    return $bdd;
}

//Fonction qui récupère les informations des motifs et pays (codes + libéllés) depuis la BD
function recup_donnees_motif_pays($bdd){

    //Requête pour obtenir tous les motifs
    //La commande ORDER BY permet de classer les motifs par ordre alphabétique
    $req_motif="SELECT *
                FROM Motifs
                ORDER BY Libelle_motif";

    $result_motif = $bdd->query($req_motif); //On exécute la requête et stocke le résultat dans $result_motif
    $list_motif=$result_motif->fetchAll(PDO::FETCH_ASSOC); //$list_motif est un tableau associatif contenant toutes les lignes du résultat de la requête

    //Requête pour obtenir tous les pays
    $req_pays="SELECT *
               FROM Pays
               ORDER BY Libelle_pays";

    $result_pays= $bdd->query($req_pays);
    $list_pays=$result_pays->fetchAll(PDO::FETCH_ASSOC);

    //On mémorise les tableaux résultats
    $_SESSION['infos_motifs']=$list_motif;
    $_SESSION['infos_pays']=$list_pays;

}

//Fonction qui récupère les informations des natures de documents (codes + libéllés) et extensions depuis la BD
function recup_donnees_filtres($bdd){

    //Requête pour obtenir toutes les natures de documents
    $req_nature="SELECT *
                 FROM Natures
                 ORDER BY Libelle_nature";

    $result_nature= $bdd->query($req_nature);
    $list_nature=$result_nature->fetchAll(PDO::FETCH_ASSOC);

    //Requête pour obtenir toutes les extensions des documents existants
    $req_extension="SELECT DISTINCT Extension
                    FROM Documents
                    ORDER BY Extension";

    $result_extension= $bdd->query($req_extension);
    $list_ext=$result_extension->fetchAll(PDO::FETCH_ASSOC);

    //On mémorise les tableaux résultats
    $_SESSION['infos_natures']=$list_nature;
    $_SESSION['extensions']=$list_ext;

}

//Fonction qui recherche les patients et mémorise les champs du formulaire saisis
function search($bdd){

    if(isset($_POST['btn_recherche'])) { //Si on lance une recherche
        //On mémorise le formulaire soumis ($_POST), pour pouvoir rétablir le contexte de la page de recherche après clique sur le bouton "Retour" de la fiche patient
        $_SESSION["save_form"] = $_POST;

        //On recupère chaque champs du formulaire
        $nom = $_POST['Nom'];
        $nom=str_replace('"',"'",$nom); //Si le nom contient des " on les remplace par des ' pour éviter l'échec des requêtes SQL
        $motif= $_POST['Motifs'];
        $pays= $_POST['Pays'];
        $etat_date_naiss= $_POST['Date_naiss'];
        $date_naiss_debut= $_POST['debut'];
        $date_naiss_fin= $_POST['fin'];

        //On mémorise les critères de recherche saisis
        $_SESSION["nom"] = $nom;
        $_SESSION["motif"]= $motif;
        $_SESSION["pays"]= $pays;
        $_SESSION["etat_date_naiss"]= $etat_date_naiss;
        $_SESSION["date_naiss_debut"]= $date_naiss_debut;
        $_SESSION["date_naiss_fin"]= $date_naiss_fin;

        $nb_critere = 0; //Nb de critères de recherche
        $clauses=[]; //Tableau qui contiendra l'ensemble des clauses du WHERE pour notre requête SQL qui cherchera les patients

        //Si un nom a été saisi alors il devient un critère de recherche
        //-> On insère la condition de recherche dans le tableau "$clauses" et on incrémente le compteur de critères
        //On fait le même traitement pour les autres champs du formulaire
        if(!empty($nom)) { array_push($clauses, 'Nom LIKE "'.$nom.'%"'); $nb_critere++; }
        if(!empty($motif)) { array_push($clauses, 'Code_motif='.$motif); $nb_critere++; }
        if(!empty($pays)) { array_push($clauses, 'Code_pays="'.$pays.'"'); $nb_critere++; }
        if($etat_date_naiss == '1') { array_push($clauses, 'Date_naiss BETWEEN "'.$date_naiss_debut.'" AND "'.$date_naiss_fin.'"'); $nb_critere++; } //1 veut dire qu'on a choisi un interval de date de naisssance

        //Chaîne de charactères qui englobe toutes les conditions du WHERE
        $critere=implode(" AND ", $clauses);

        if($nb_critere > 0){ //Si on a des critères de recherche qui sont appliqués alors on lance la requête avec les conditions de recherche
            $req="SELECT Code_patient, UPPER(Nom) AS name, Prenom
                  FROM Patients
                  WHERE $critere
                  ORDER BY Nom, Prenom";
        }else{ //Si aucun critère de recherche n'est appelé alors on cherche tous les patients
            $req="SELECT Code_patient, UPPER(Nom) AS name, Prenom
                  FROM Patients
                  ORDER BY Nom, Prenom";
        }

        $result=$bdd->query($req);
        $list_patients=$result->fetchAll(PDO::FETCH_ASSOC);

        //On retourne un tableau associatif contenant tous les patients trouvés
        return $list_patients;

    }
}

//Fonction qui affiche les résultats de la recherche patient
function afficher_resultat_recherche($list_patients){
    if(isset($_POST['btn_recherche'])) { //Si une recherche a été effectuée

        echo '<section id="resultats" class="resultats_section layout_padding light-grey2">
                <div class="container">
                    <div class="heading_container heading_center">
                        <h2 >
                            Résultats de la recherche
                        </h2>';

        if (empty($list_patients)){ //Sous-titre affiché si aucun patient n'a été trouvé dans la BD pour ces critères de recherche
            echo "<p class='text-rouge'>
                    Aucun patient ne correspond à votre recherche
                  </p>
                </div>
                <div class='container mt-5 mb-5'>";
        }else{ //Sous-titre affiché si on a trouvé des patients
            echo '<p>
                    Voici les patients trouvés selon vos critères de recherches
                  </p>
                </div>
                <div class="container mt-5 mb-5">';
        }

        foreach($list_patients as $patient){ //On affiche tous les patients trouvés
            //On envoie en méthode GET le code du patient
            //On redirige vers la page fiche_patient.php (? sépare la redirection des variables, & sépare les variables entre elles)
            echo '<div id ="patient'.$patient['Code_patient'].'" class="row justify-content-md-center mb-3">
                    <a class="patient" href="fiche_patient.php?code='.$patient['Code_patient'].'">'.$patient['name'].' '.$patient['Prenom'].'</a>
                  </div>';
        }

        //On affiche 2 boutons : un pour affiner la recherche (il renvoie vers la section du formulaire de recherche) et un pour réaliser une nouvelle recherche
        echo '</div>
                <div class="container mt-5">
                    <div class="row justify-content-md-center">
                        <div class="col col-md-auto">
                            <a href="recherche_patient.php?retour=ok#recherche" class=" btn btn-primary">Affiner la recherche</a>
                        </div>
                        <div class="col col-md-auto">
                            <a href="recherche_patient.php" class=" btn btn-dark">Nouvelle recherche</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        
        ';
    }
}

//Fonction qui cherche et affiche toutes les informations relatives au patient sélectionné à l'issue du résultat d'une recherche
function fiche($code_patient, $bdd){

    //Requête pour obtenir toutes les informations du patient en question
    $req='SELECT Code_patient, Nom, Prenom, Libelle_sexe, DATE_FORMAT(Date_naiss, "%d/%m/%Y") as Date_naissance, Date_naiss as format_initial_date_naiss, Num_secu, Libelle_pays, DATE_FORMAT(Date_entree, "%d/%m/%Y") as Date_d_entree, Date_entree as format_initial_date_entree, Libelle_motif
            FROM Patients pat JOIN Sexe s ON pat.Sexe=s.Code_s JOIN Pays pay ON pat.Code_pays=pay.Code_p JOIN Motifs mot ON pat.Code_motif=mot.Code_m
            WHERE Code_patient='.$code_patient;

    $result = $bdd->query($req);
    $patient=$result->fetch(PDO::FETCH_ASSOC);

    //On mémorise son code, son nom et son prénom
    $_SESSION["code_patient_select"] = $patient['Code_patient'];
    $_SESSION["nom_patient_select"] = $patient['Nom'];
    $_SESSION["prenom_patient_select"] = $patient['Prenom'];

    return $patient;
}

//Fonction qui modifie, met à jour les données du patient séléctionné
function save_update_fiche($bdd){
    if(isset($_POST['btn_save_update'])) { //Si on sauvegarde les modifications
        //On récupère les infos de la fiche patient modifié
        $nom = mb_strtoupper($_POST['Nom_update'], 'UTF-8');
        $nom=str_replace('"',"'",$nom); //Pour éviter l'échec des requêtes on remplace les " par des '
        $nom=str_replace('/','-',$nom); //Pour éviter les problèmes lors du stockage de documents (sinon les chemins seront faussés, car le nom du patient est inclu dans les noms de dossiers et fichiers)
        $nom=str_replace("_","-",$nom); //Pour éviter les problèmes lors du stockage de documents (car on utilise les _ comme séparateur dans les noms de dossiers et fichiers)

        $prenom=mb_strtolower($_POST['Prenom_update'], 'UTF-8');
        $prenom=str_replace('"',"'",$prenom);
        $prenom=str_replace('/','-',$prenom);
        $prenom=str_replace("_","-",$prenom);

        $sexe=$_POST['Sexe_update'];
        $date_naiss= $_POST['Date_naiss_update'];
        $num_secu=$_POST['Num_secu_update'];
        $date_entree= $_POST['Date_entree_update'];

        $motif= $_POST['Motif_update'];
        $pays= $_POST['Pays_update'];

        //Si la date d'entrée saisie est antérieure à la date de naissance on affiche un alerte
        if($date_entree<$date_naiss){
            echo "<script type ='text/JavaScript'>swal('AVERTISSEMENT', 'La date d\u2019entrée est antérieur à la date de naissance, ressaisissez les données', 'warning');</script>";

        }else{
            //Requête pour vérifier qu'on a pas saisie un nom, prénom et date de naissance d'une personne existante dans la BD
            $req_verif='SELECT *
                        FROM Patients
                        WHERE Nom="'.$nom.'" AND Prenom="'.$prenom.'" AND Date_naiss="'.$date_naiss.'"';

            $result_verif=$bdd->query($req_verif);
            $res_verif=$result_verif->fetch(PDO::FETCH_ASSOC);
            
            if ((empty($res_verif)) OR ($res_verif['Code_patient'] == $_SESSION["code_patient_select"])){ //Si le patient qu'on a modifié ne provoque pas de doublon dans la BD
                //Requête pour insérer le nouveau patient
                $req_update_patient='UPDATE Patients
                                     SET Nom="'.$nom.'", Prenom="'.$prenom.'", Sexe="'.$sexe.'", Date_naiss="'.$date_naiss.'", Num_secu="'.$num_secu.'", Code_pays="'.$pays.'", Date_entree="'.$date_entree.'", Code_motif='.$motif.'
                                     WHERE Code_patient='.$_GET['code'];

                $bdd->query($req_update_patient);
                echo '<script type ="text/JavaScript">swal("Les modifications on bien été pris en compte !", "", "success");</script>';
            
            }else{ //Si en modifiant le patient on saisie des infos d'un patient déjà inscrit
                echo '<script type ="text/JavaScript">swal("ATTENTION", "Modifications invalides, ce patient est déjà inscrit", "error");</script>';
            }
        }
    }
}

//Fonction qui supprime tout un répértoire
function delTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));

    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }

    rmdir($dir);
}

//Fonction qui supprime un patient
function delete_patient($bdd){
    if(isset($_POST['btn_delete_patient'])) { //si on clique sur le bouton "Supprimer patient"
        
        //On supprime tous les documents du patient de la BD
        $req_doc='DELETE FROM Documents WHERE Patient='.$_SESSION["code_patient_select"];
        $bdd->query($req_doc);

        //On supprime le patient de la BD
        $req_patient='DELETE FROM Patients WHERE Code_patient='.$_SESSION["code_patient_select"];
        $bdd->query($req_patient);

        $dossier_patient='fichiers_patients/Patient'.$_SESSION["code_patient_select"].'/';
        
        //Si le patient qu'on supprime possède un dossier sur le serveur alors on le supprime aussi
        if (is_dir($dossier_patient)){
            delTree($dossier_patient);
        }

        //On affiche l'alerte
        echo '<script type ="text/JavaScript">swal("Le patient a bien été supprimé !", "", "success");</script>';
    }

}

//Fonction qui inscrit un nouveau patient dans la BD, puis ajoute un nouveau motif et pays si ceux choisis n'existent pas dans la BD
function inscription($bdd){
    if(isset($_POST['btn_inscription'])) { //si on soumet l'inscription

        //On recupère chaque champs du formulaire
        $nom = mb_strtoupper($_POST['Nom_inscr'], 'UTF-8');
        $nom=str_replace('"',"'",$nom); //Pour éviter l'échec des requêtes on remplace les " par des '
        $nom=str_replace('/','-',$nom); //Pour éviter les problèmes lors du stockage de documents (sinon les chemins seront faussés, car le nom du patient est inclu dans les noms de dossiers et fichiers)
        $nom=str_replace("_","-",$nom); //Pour éviter les problèmes lors du stockage de documents (car on utilise les _ comme séparateur dans les noms de dossiers et fichiers)

        $prenom = mb_strtolower($_POST['Prenom_inscr'], 'UTF-8');
        $prenom=str_replace('"',"'",$prenom);
        $prenom=str_replace('/','-',$prenom);
        $prenom=str_replace("_","-",$prenom);

        $sexe=$_POST['Sexe_inscr'];
        $date_naiss= $_POST['Date_naiss_inscr'];
        $num_secu=$_POST['Num_secu_input'];
        $date_entree= $_POST['Date_entree_inscr'];

        $motif= $_POST['Motifs_inscr'];
        $new_motif=$_POST['New_motif_input'];
        $new_motif=str_replace('"',"'",$new_motif);

        $pays= $_POST['Pays_inscr'];
        $new_pays=$_POST['New_pays_select'];

        //Si la date d'entrée saisie est antérieure à la date de naissance on affiche un alerte
        if($date_entree<$date_naiss){
            echo "<script type ='text/JavaScript'>swal('AVERTISSEMENT', 'La date d\u2019entrée est antérieur à la date de naissance, ressaisissez les données', 'warning');</script>";

        }else{
            //Requête pour vérifier si le patient qu'on veut inscrire existe déja dans la BD
            $req_verif='SELECT *
                        FROM Patients
                        WHERE Nom="'.$nom.'" AND Prenom="'.$prenom.'" AND Date_naiss="'.$date_naiss.'"';

            $result_verif=$bdd->query($req_verif);
            $res_verif=$result_verif->fetch(PDO::FETCH_ASSOC);
            
            if (empty($res_verif)){ //Si le patient n'est pas dans la BD

                //Tableau qui contient les valeurs à insérer dans la BD pour le nouveau patient 
                $val=[$nom, $prenom, $sexe, $date_naiss, $num_secu, $pays, $date_entree, $motif]; 

                if ($motif == "autre"){ //Si on a saisi un nouveau motif

                    //Requête pour vérifier que le nouveau motif n'existe pas dans la BD
                    $req_verif_motif='SELECT *
                                      FROM Motifs
                                      WHERE Libelle_motif IN ("'.$new_motif.'")';

                    $result_verif_motif=$bdd->query($req_verif_motif);
                    $res_verif_motif=$result_verif_motif->fetch(PDO::FETCH_ASSOC);
                    
                    if (empty($res_verif_motif)){ //Si le nouveau motif saisi n'est pas dans la BD

                        //Requête pour insérer le nouveau motif
                        $req_motif='INSERT INTO Motifs (Libelle_motif)
                                    VALUES ("'.$new_motif.'")';

                        //Requête pour récupérer le code du nouveau motif car on en a besoin pour insérer le nouveau patient
                        $req_code_motif='SELECT Code_m
                                         FROM Motifs
                                         WHERE Libelle_motif="'.$new_motif.'"';

                        $bdd->query($req_motif);

                        $result=$bdd->query($req_code_motif);
                        $res=$result->fetch(PDO::FETCH_ASSOC);
                        $code_motif=$res['Code_m'];

                        if($pays != "autre"){ //Si le pays existe dans la BD mais que le motif est nouveau

                            //Les valeurs à insérer pour le patient contiennent le code du nouveau motif créé
                            $val[7]=$code_motif;

                        }else{ //Si le pays et le motif sont nouveaux

                            //On extrait le code du nouveau pays
                            $info_pays=explode('_',$new_pays);
                            $code_pays=$info_pays[0];
                            $libelle_pays=$info_pays[1];
        
                            //Requête pour insérer le nouveau pays
                            $req_pays='INSERT INTO Pays
                                       VALUES ("'.$code_pays.'", "'.$libelle_pays.'")';
        
                            $bdd->query($req_pays);

                            //Les valeurs à insérer pour le patient contiennent le code du nouveau pays et du nouveau motif créés
                            $val[5]=$code_pays;
                            $val[7]=$code_motif;
                        }

                        //Chaîne de charactères qui contient les valeurs à insérer pour le patient
                        $valeurs=implode('", "',$val);
                    
                        //Requête pour insérer le nouveau patient
                        $req_patient='INSERT INTO Patients (Nom, Prenom, Sexe, Date_naiss, Num_secu, Code_pays, Date_entree, Code_motif)
                                      VALUES ("'.$valeurs.'")';

                        $bdd->query($req_patient);
                        echo '<script type ="text/JavaScript">swal("Le patient à bien été inscrit !", "", "success");</script>';
                    
                    }else echo '<script type ="text/JavaScript"> swal("AVERTISSEMENT", "Ce motif existe déjà ! Merci de bien vouloir le séléctionner depuis la liste déroulante.", "warning"); </script>'; //si le nouveau motif saisi existe déjà dans la BD

                }else if (($pays == "autre") && ($motif != "autre")){ //Si le pays est nouveau mais que le motif existe dans la BD

                    //On extrait le code et libéllé du nouveau pays
                    $info_pays=explode('_',$new_pays);
                    $code_pays=$info_pays[0];
                    $libelle_pays=$info_pays[1];

                    //Requête pour insérer le nouveau pays
                    $req_pays='INSERT INTO Pays
                               VALUES ("'.$code_pays.'", "'.$libelle_pays.'")';

                    $bdd->query($req_pays);

                    //Les valeurs à insérer pour le patient contiennent le code du nouveau pays créé
                    $val[5]=$code_pays;

                    //Chaîne de charactères qui contient les valeurs à insérer pour le patient
                    $valeurs=implode('", "',$val);
                
                    //Requête pour insérer le nouveau patient
                    $req_patient='INSERT INTO Patients (Nom, Prenom, Sexe, Date_naiss, Num_secu, Code_pays, Date_entree, Code_motif)
                                  VALUES ("'.$valeurs.'")';

                    $bdd->query($req_patient);
                    echo '<script type ="text/JavaScript">swal("Le patient à bien été inscrit !", "", "success");</script>';
                
                }else{ //Si le pays et le motif existent dans la BD
                    //Chaîne de charactères qui contient les valeurs à insérer pour le patient
                    $valeurs=implode('", "',$val);
                    
                    //Requête pour insérer le nouveau patient
                    $req_patient='INSERT INTO Patients (Nom, Prenom, Sexe, Date_naiss, Num_secu, Code_pays, Date_entree, Code_motif)
                                VALUES ("'.$valeurs.'")';

                    $bdd->query($req_patient);
                    echo '<script type ="text/JavaScript">swal("Le patient à bien été inscrit !", "", "success");</script>';
                }

            }else{ //Si le patient est déjà inscrit
                echo '<script type ="text/JavaScript">swal("ATTENTION", "Ce patient est déjà inscrit", "error");</script>';
            }
        }
    }
}

//Fonction qui cherche et affiche tous les documents de l'hôpital (onglet Documents)
function read_all_files($bdd){

    //Requête qui cherche les informations (dont nous aurons besoin pour l'affichage) de tous les documents
    $req="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom as Nom_patient, Prenom as Prenom_patient
          FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n";

    $results=$bdd->query($req);
    $list_doc=$results->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($list_doc)){ //Si on a des documents stockés sur le serveur

        if(isset($_POST['btn_filtre'])){ //Si on a cliqué sur le bouton "Appliquer" des filtres
            //On récupère la valeur de tous les filtres
            $filtre_nature=$_POST['nature_doc'];
            $filtre_nom=$_POST['nom_doc'];
            $filtre_nom=str_replace('"',"'",$filtre_nom); //Pour éviter l'échec des requêtes SQL
            $filtre_type=$_POST['type_doc'];
            $filtre_contenu=$_POST['contenu_doc'];
            $filtre_contenu=str_replace('"',"'",$filtre_contenu);

            $nb_filtre = 0; //Nb de filtres utilisés
            $clauses=[]; //Tableau qui contiendra l'ensemble des clauses du WHERE pour notre requête SQL qui cherchera les documents

            //Si une nature de document à été choisi alors elle devient un critère de recherche
            //-> On insère la condition de recherche dans le tableau "$clauses" et on incrémente le compteur de filtres
            //On fait le même traitement pour les autres filtres
            if(!empty($filtre_nature)) { array_push($clauses, 'Libelle_nature="'.$filtre_nature.'"'); $nb_filtre++; }
            if(!empty($filtre_nom)) { array_push($clauses, 'Nom_doc LIKE "'.$filtre_nom.'%"'); $nb_filtre++; }
            if(!empty($filtre_type)) { array_push($clauses, 'Extension="'.$filtre_type.'"'); $nb_filtre++; }
            if(!empty($filtre_contenu)) { array_push($clauses, 'Contenu LIKE "'.$filtre_contenu.'%"'); $nb_filtre++; }

            //Chaîne de charactères qui englobe toutes les conditions du WHERE
            $critere=implode(" AND ", $clauses);

            if($nb_filtre > 0){ //Si on a des filtres qui sont appliqués on lance la requête avec les conditions de recherche
                //Requête qui compte le nombre de documents correspondant à la recherche
                $req_nb_doc="SELECT COUNT(*) as nb_doc
                             FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n
                             WHERE $critere";
            }else{ //Si aucun filtre n'est appliqué alors on compte tous les documents
                $req_nb_doc="SELECT COUNT(*) as nb_doc
                             FROM Documents";
            }

            $result_nb_doc=$bdd->query($req_nb_doc);
            $res=$result_nb_doc->fetch(PDO::FETCH_ASSOC);
            $nb_doc=$res['nb_doc'];

            //Affichage du nombre de documents trouvés
            echo "<div class='text-bleu'>
                    <p>
                    Documents trouvés : <b>$nb_doc</b>
                    </p>
                </div>
                <table class='table table-bordered'>
                    <thead>
                        <tr>";

            //Affichage des entêtes des colonnes du tableau qui répertorit les documents
            if (empty($filtre_nature)) echo "<th scope='col'>Tous les documents</th>";
            else echo "<th scope='col'>$filtre_nature</th>";

            echo "<th scope='col'>Type</th>
                    <th scope='col'>Contenu</th>
                    <th scope='col'>Actions</th>
                </tr>
            </thead>
            <tbody>";

            //Contenu du tableau si aucun document avec ces critères de recherche n'a été trouvé
            if ($nb_doc == 0){
                echo "<tr>
                        <td class='text-rouge'>Aucun document correspondant à ces critères de recherche</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>";
            }else{ //Si des documents correspondent aux critères de recherche

                if($nb_filtre > 0){ //Si on a des filtres qui sont appliqués on lance la requête avec les conditions de recherche
                    //Requête qui cherche les infos des documents
                    $req_doc="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom AS Nom_patient, Prenom AS Prenom_patient
                              FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n
                              WHERE $critere";
                }else{ //Si aucun filtre n'est appliqué alors on cherche tous les documents
                    $req_doc="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom AS Nom_patient, Prenom AS Prenom_patient
                              FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n";
                }

                $result_doc=$bdd->query($req_doc);
                $list_document=$result_doc->fetchAll(PDO::FETCH_ASSOC);

                //Pour tous les documents trouvés on les affiche dans le tableau sous le nom suivant: "code patient_nom patient_prénom patient_nom du doc"
                foreach($list_document as $doc){
                    //4 boutons: loupe -> pour afficher un doc, "télécharger", "imprimer", "mail" -> pour envoyer le doc par mail, "supprimer"
                    //On envoie en méthode GET des variables: fichier_"action" qui contiennent le chemin du document qui leur est associé
                    //afin que chaque bouton exécute ses actions sur le document qui lui correspond
                    //de plus ces variables serviront de signal pour lancer l'action du bouton
                    echo '<tr id="'.$doc['Chemin'].'">
                            <td>'.$doc['Code_patient'].'_'.$doc['Nom_patient'].'_'.$doc['Prenom_patient'].'_'.$doc['Nom_doc'].'</td>
                            <td>'.$doc['Extension'].'</td>
                            <td>'.$doc['Contenu'].'</td>
                            <td>
                                <a class="mr-2" href="documents.php?fichier_open='.$doc['Chemin'].'"> <img src="images/loupe.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="documents.php?fichier_download='.$doc['Chemin'].'"> <img src="images/download.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="documents.php?fichier_print='.$doc['Chemin'].'"> <img src="images/print.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="envoi_mail.php?fichier_mail='.$doc['Chemin'].'&code_patient='.$doc['Code_patient'].'&nom_patient='.$doc['Nom_patient'].'&prenom_patient='.$doc['Prenom_patient'].'&nom_doc='.$doc['Nom_doc'].'&nature_doc='.$doc['Libelle_nature'].'#mail"> <img src="images/mail.jpg" height ="30" width="30"/></a>
                                <a class="mr-2" href="documents.php?fichier_delete='.$doc['Chemin'].'"> <img src="images/trash.png" height ="30" width="30"/></a>
                            </td>
                        </tr>';
                }

            }

        }else{ //Affichage de tous les documents (ici nb_doc > 0)
            $nb_doc=sizeof($list_doc); //On compte le nombre de documents trouvés par la première requête "$req" exécutée en début de fonction

            //Affichage du nombre de documents trouvés et des en-têtes du tableau
            echo "<div class='text-bleu'>
                    <p>
                    Documents trouvés : <b>$nb_doc</b>
                    </p>
                </div>
                <table class='table table-bordered '>
                    <thead>
                        <tr>
                            <th scope='col'>Tous les documents</th>
                            <th scope='col'>Type</th>
                            <th scope='col'>Contenu</th>
                            <th scope='col'>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";

            //Pour tous les documents trouvés on les affiche dans le tableau sous le nom suivant : "code patient_nom patient_prénom patient_nom du doc"
            foreach($list_doc as $doc){
                echo '<tr id="'.$doc['Chemin'].'">
                        <td>'.$doc['Code_patient'].'_'.$doc['Nom_patient'].'_'.$doc['Prenom_patient'].'_'.$doc['Nom_doc'].'</td>
                        <td>'.$doc['Extension'].'</td>
                        <td>'.$doc['Contenu'].'</td>
                        <td>
                            <a class="mr-2" href="documents.php?fichier_open='.$doc['Chemin'].'"> <img src="images/loupe.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="documents.php?fichier_download='.$doc['Chemin'].'"> <img src="images/download.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="documents.php?fichier_print='.$doc['Chemin'].'"> <img src="images/print.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="envoi_mail.php?fichier_mail='.$doc['Chemin'].'&code_patient='.$doc['Code_patient'].'&nom_patient='.$doc['Nom_patient'].'&prenom_patient='.$doc['Prenom_patient'].'&nom_doc='.$doc['Nom_doc'].'&nature_doc='.$doc['Libelle_nature'].'#mail"> <img src="images/mail.jpg" height ="30" width="30"/></a>
                            <a class="mr-2" href="documents.php?fichier_delete='.$doc['Chemin'].'"> <img src="images/trash.png" height ="30" width="30"/></a>
                        </td>
                    </tr>';
            }

        }

    }else{ //Affichage du tableau si aucun document n'a été trouvé
        echo "<div class='text-bleu'>
                <p>
                Documents trouvés : <b>0</b>
                </p>
            </div>
            <table class='table table-bordered'>
                <thead>
                    <tr>
                    <th scope='col'>Tous les documents</th>
                    <th scope='col'>Type</th>
                    <th scope='col'>Contenu</th>
                    <th scope='col'>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    <td class='text-rouge'>Aucun document</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    </tr>";
    }

}

//Fonction qui cherche et affiche tous les documents d'un patient séléctionné 
function read_files_patient($bdd){

    //Requête qui cherche les infos de tous les documents du patient
    $req="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom as Nom_patient, Prenom as Prenom_patient
          FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n
          WHERE Code_patient=".$_SESSION["code_patient_select"];

    $results=$bdd->query($req); 
    $list_doc=$results->fetchAll(PDO::FETCH_ASSOC);

    //Si le patient concerné a des documents stockés sur le serveur
    if (!empty($list_doc)){
        //Requête qui créer une vue afin de garder en mémoire les documents du patient pour pouvoir effectuer des traitements dessus par la suite 
        $req_view="CREATE VIEW documents_patient_vw
                   AS SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom as Nom_patient, Prenom as Prenom_patient
                   FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n
                   WHERE Code_patient=".$_SESSION["code_patient_select"];

        $bdd->query($req_view);

        if(isset($_POST['btn_filtre'])){  //Si on a cliqué sur le bouton "Appliquer" des filtres
            //On récupère les infos des filtres
            $filtre_nature=$_POST['nature_doc'];
            $filtre_nom=$_POST['nom_doc'];
            $filtre_nom=str_replace('"',"'",$filtre_nom); //Pour éviter l'échec des requêtes SQL
            $filtre_type=$_POST['type_doc'];
            $filtre_contenu=$_POST['contenu_doc'];
            $filtre_contenu=str_replace('"',"'",$filtre_contenu);

            $nb_filtre = 0; //Nb de filtres utilisés
            $clauses=[]; //Tableau qui contiendra l'ensemble des clauses du WHERE pour notre requête SQL qui cherchera les documents du patient

            //Si une nature de document à été choisi alors elle devient un critère de recherche
            //-> On insère la condition de recherche dans le tableau "$clauses" et on incrémente le compteur de filtres
            //On fait le même traitement pour les autres filtres
            if(!empty($filtre_nature)) { array_push($clauses, 'Libelle_nature="'.$filtre_nature.'"'); $nb_filtre++; }
            if(!empty($filtre_nom)) { array_push($clauses, 'Nom_doc LIKE "'.$filtre_nom.'%"'); $nb_filtre++; }
            if(!empty($filtre_type)) { array_push($clauses, 'Extension="'.$filtre_type.'"'); $nb_filtre++; }
            if(!empty($filtre_contenu)) { array_push($clauses, 'Contenu LIKE "'.$filtre_contenu.'%"'); $nb_filtre++; }

            //Chaîne de charactères qui englobe toutes les conditions du WHERE
            $critere=implode(" AND ", $clauses);

            if($nb_filtre > 0){ //Si on a des filtres qui sont appliqués on lance la requête avec les conditions de recherche
                //Requête qui compte le nombre de documents correspondant à la recherche
                $req_nb_doc="SELECT COUNT(*) as nb_doc
                             FROM documents_patient_vw
                             WHERE $critere";
            }else{ //Si aucun filtre n'est appliqué alors on compte tous les documents du patient
                $req_nb_doc="SELECT COUNT(*) as nb_doc
                             FROM documents_patient_vw";
            }

            $result_nb_doc=$bdd->query($req_nb_doc);
            $res=$result_nb_doc->fetch(PDO::FETCH_ASSOC);
            $nb_doc=$res['nb_doc'];

            //Affichage du nombre de documents trouvés
            echo "<div class='text-bleu'>
                    <p>
                    Documents trouvés : <b>$nb_doc</b>
                    </p>
                </div>
                <table class='table table-bordered'>
                    <thead>
                        <tr>";

            //Affichage des entêtes des colonnes du tableau qui répertorit les documents du patient
            if (empty($filtre_nature)) echo "<th scope='col'>Tous les documents</th>";
            else echo "<th scope='col'>$filtre_nature</th>";

            echo "<th scope='col'>Type</th>
                    <th scope='col'>Contenu</th>
                    <th scope='col'>Actions</th>
                </tr>
            </thead>
            <tbody>";

            //Contenu du tableau si aucun document avec ces critères de recherche n'a été trouvé pour le patient
            if ($nb_doc == 0){
                echo "<tr>
                        <td class='text-rouge'>Aucun document correspondant à ces critères de recherche</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>";
            }else{ //Si des documents correspondent aux critères de recherche 

                if($nb_filtre > 0){ //Si on a des filtres qui sont appliqués on lance la requête avec les conditions de recherche
                    //Requête qui cherche les infos des documents du patient
                    $req_doc="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom_patient, Prenom_patient
                              FROM documents_patient_vw
                              WHERE $critere";
                }else{ //Si aucun filtre n'est appliqué alors on cherche tous les documents du patient
                    $req_doc="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom_patient, Prenom_patient
                              FROM documents_patient_vw";
                }

                $result_doc=$bdd->query($req_doc);
                $list_document=$result_doc->fetchAll(PDO::FETCH_ASSOC);

                //Pour tous les documents trouvés on les affiche dans le tableau sous le nom suivant: "nom du doc"
                foreach($list_document as $doc){
                    //4 boutons: loupe -> pour afficher un doc, "télécharger", "imprimer", "mail" -> pour envoyer le doc par mail, "supprimer" 
                    //On envoie en méthode GET des variables: fichier_"action" qui contiennent le chemin du document qui leur est associé
                    //afin que chaque bouton exécute ses actions sur le document qui lui correspond
                    //de plus ces variables serviront de signal pour lancer l'action du bouton
                    echo '<tr id="'.$doc['Chemin'].'">
                            <td>'.$doc['Nom_doc'].'</td>
                            <td>'.$doc['Extension'].'</td>
                            <td>'.$doc['Contenu'].'</td>
                            <td>
                                <a class="mr-2" href="consulter_doc_patient.php?fichier_open='.$doc['Chemin'].'"> <img src="images/loupe.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="consulter_doc_patient.php?fichier_download='.$doc['Chemin'].'"> <img src="images/download.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="consulter_doc_patient.php?fichier_print='.$doc['Chemin'].'"> <img src="images/print.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="envoi_mail.php?fichier_mail='.$doc['Chemin'].'&code_patient='.$doc['Code_patient'].'&nom_patient='.$doc['Nom_patient'].'&prenom_patient='.$doc['Prenom_patient'].'&nom_doc='.$doc['Nom_doc'].'&nature_doc='.$doc['Libelle_nature'].'#mail"> <img src="images/mail.jpg" height ="30" width="30"/></a>
                                <a class="mr-2" href="consulter_doc_patient.php?fichier_delete='.$doc['Chemin'].'"> <img src="images/trash.png" height ="30" width="30"/></a>
                            </td>
                        </tr>';
                }
            }

        }else{ //Affichage de tous les documents du patient (ici nb_doc > 0)
            $nb_doc=sizeof($list_doc); //On compte le nombre de documents trouvés par la première requête "$req" exécutée en début de fonction 

            //Affichage du nombre de documents trouvés et des entêtes du tableau
            echo "<div class='text-bleu'>
                    <p>
                    Documents trouvés : <b>$nb_doc</b>
                    </p>
                </div>
                <table class='table table-bordered '>
                    <thead>
                        <tr>
                            <th scope='col'>Tous les documents</th>
                            <th scope='col'>Type</th>
                            <th scope='col'>Contenu</th>
                            <th scope='col'>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";

            //Pour tous les documents du patient, on les affiche dans le tableau sous le nom suivant: "nom du doc"
            foreach($list_doc as $doc){
                echo '<tr id="'.$doc['Chemin'].'">
                        <td>'.$doc['Nom_doc'].'</td>
                        <td>'.$doc['Extension'].'</td>
                        <td>'.$doc['Contenu'].'</td>
                        <td>
                            <a class="mr-2" href="consulter_doc_patient.php?fichier_open='.$doc['Chemin'].'"> <img src="images/loupe.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="consulter_doc_patient.php?fichier_download='.$doc['Chemin'].'"> <img src="images/download.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="consulter_doc_patient.php?fichier_print='.$doc['Chemin'].'"> <img src="images/print.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="envoi_mail.php?fichier_mail='.$doc['Chemin'].'&code_patient='.$doc['Code_patient'].'&nom_patient='.$doc['Nom_patient'].'&prenom_patient='.$doc['Prenom_patient'].'&nom_doc='.$doc['Nom_doc'].'&nature_doc='.$doc['Libelle_nature'].'#mail"> <img src="images/mail.jpg" height ="30" width="30"/></a>
                            <a class="del-btn mr-2" href="consulter_doc_patient.php?fichier_delete='.$doc['Chemin'].'"> <img src="images/trash.png" height ="30" width="30"/></a>
                        </td>
                    </tr>';

            }

        }

        //On supprime la vue créée car on en a plus besoin maintenant, on a effectué toutes les recherches
        $req_delete_view="DROP VIEW IF EXISTS documents_patient_vw";
        $bdd->query($req_delete_view);

    }else{ //Affichage du tableau si aucuns documents n'ont été trouvés
        echo "<div class='text-bleu'>
                <p>
                Documents trouvés : <b>0</b>
                </p>
            </div>
            <table class='table table-bordered'>
                <thead>
                    <tr>
                    <th scope='col'>Tous les documents</th>
                    <th scope='col'>Type</th>
                    <th scope='col'>Contenu</th>
                    <th scope='col'>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                    <td class='text-rouge'>Aucun document stocké pour ".$_SESSION["nom_patient_select"]." ".$_SESSION["prenom_patient_select"]."</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    </tr>";
    }

}

//Bouton loupe
function open_file(){ 
    if (isset($_GET['fichier_open'])){ //Actionnement du bouton loupe d'un document

        //Ouverture du document dans un nouvel onglet grâce au JS
        echo '<script type="text/javascript">
                fenetre=window.open("'.$_GET['fichier_open'].'");
              </script>';
    }
}

//Bouton téléchargement
function download_file(){
    if (isset($_GET['fichier_download'])){ //Actionnement du bouton téléchargement d'un document
        if(!empty($_GET['fichier_download'])){
            //On récupère les infos envoyés en méthode GET
            $nom_fichier=basename($_GET['fichier_download']); //Nom du fichier
            $chemin_fichier=$_GET['fichier_download']; //Emplacement du fichier

            $info_nom_fichier = explode('.', $nom_fichier); //On scinde le nom du fichier par rapport au séparateur '.' dans le but de récupérer son extension
            $extension = strtolower(end($info_nom_fichier));

            //Téléchargement du fichier
            if(!empty($nom_fichier) && file_exists($chemin_fichier)){
                header("Cache-Control: public");
                header("Content-Description: File Transfer");
                header("Content-Disposition: attachement; filename=$nom_fichier");
                header("Content-Type: application/$extension");
                header("Content-Transfer-Encoding: binary");
                header('Expires: 0');
                header('Pragma: public');
                header('Content-Length: ' . filesize($chemin_fichier));

                readfile($chemin_fichier);
                exit;
            }else echo "Ce fichier n'existe pas";
        }
    }

}

//Bouton imprimer
function print_file(){
    if (isset($_GET['fichier_print'])){ //Actionnement du bouton imprimer d'un document
        
        //Ouverture du document dans un nouvel onglet + impression de celui-ci
        echo '<script type="text/javascript">
                fenetre=window.open("'.$_GET['fichier_print'].'");
                fenetre.print();
              </script>';
    }

}

//Bouton mail
function send_mail(){
    if (isset($_POST['btn_mail'])){ //Actionnement du bouton mail d'un document

        //On récupère les infos envoyés en méthode GET
        $chemin_doc=$_GET['fichier_mail']; //Emplacement du fichier à joindre au mail
        $destinataire=$_POST['Destinataire']; //Adresse du destinataire
        $destinataire=utf8_decode($destinataire); 
        $objet=$_POST['Objet']; //Objet du mail
        $objet=utf8_decode($objet); //On convertit la chaîne UTF-8 en ISO-8859-1 (afin que les charactères spéciaux soient correctement affichés dans notre mail)
        $message=$_POST['Message']; //Corps du mail
        $message=nl2br($message); //On garde les retours à la ligne fait dans le textarea pour saisir le message à envoyer
        $message=utf8_decode($message);

        //Création d'une instance de PHPMailer
        $mail = new PHPMailer(true);

        try{
            $mail->isSMTP(); //Configuration du mailer pour utiliser smtp
            $mail->Host = "smtp.gmail.com"; //Définition du serveur
            $mail->SMTPAuth = true; //Permettre l'authentification
            $mail->Username = utf8_decode("polytechhopital@gmail.com"); //Notre adresse Gmail qui enverra les mails
            $mail->Password = "MDP_PROJET"; //Mot de passe de notre adresse expéditeur
            $mail->SMTPSecure = "ssl"; //Définition du type de cryptage SMTP
            $mail->Port = "465"; //Connexion au port
            $mail->setFrom('ne-pas-repondre@polytech-hopital.fr', utf8_decode('Polytech Hôpital')); //Définition de l'e-mail expéditeur
            $mail->addAddress($destinataire); //Ajout des adresses destinataires (ici on en a une)
            $mail->isHTML(true);
            $mail->addAttachment($chemin_doc); //Pièce jointe du mail (ici on en a une)
            $mail->Subject = $objet; //Objet du mail
            $mail->Body = $message; //Corps du mail
            $mail->send(); //On envoie le mail

            echo '<script type ="text/JavaScript">swal("Le mail a bien été envoyé !", "", "success");</script>';

        }catch(Exception $e){
            $err=$mail->ErrorInfo;
            echo '<script type ="text/JavaScript">swal("ERREUR", "Le mail n\'a pas pu être envoyé : '.$err.'", "error");</script>';
        }
    }
}

//Bouton supprimer
function delete_file($bdd){
    if (isset($_GET['fichier_delete'])){
        //On récupère les infos envoyés en méthode GET
        $chemin_fichier=$_GET['fichier_delete']; //Emplacement du fichier

        //On affiche l'alerte
        echo '<script type ="text/JavaScript">swal("Le document a bien été supprimé !", "", "success");</script>';

        //On supprime le document de la BD
        $req='DELETE FROM Documents WHERE Chemin="'.$chemin_fichier.'"';
        $bdd->query($req);

        //On supprime le document sur le serveur
        unlink($chemin_fichier);

        //On supprime la ligne du tableau
        echo '<script type="text/javascript">
                var doc = document.getElementById("'.$chemin_fichier.'");
                doc.remove();
              </script>';
    }

}

//Fonction qui importe des documents pour un patient séléctionné
function upload_file($code_patient, $bdd){
    if(isset($_POST['btn_import'])){ //Si on a actionné le bouton "Importer le fichier"

        $patient="Patient".$code_patient; //Nom du dossier patient
        $dossier = "fichiers_patients/".$patient."/"; //Emplacement du dossier du patient
        $sous_dossier=$dossier."Documents"; //Emplacement du sous dossier "Documents" du patient

        //Si le dossier du patient n'existe pas sur le serveur alors on le crée
        if (!is_dir($dossier) && !file_exists($sous_dossier)){
            //0 pour dire qu'on est en octal , 744 = tous les droits pour le propriétaire, lecture seulement pour les autres
            mkdir($dossier, 0744);
            mkdir($sous_dossier, 0744);
        }

        //On récupère les champs du formulaire soumis
        $nature=$_POST["type_doc_import"]; //Nature de document séléctionné (Libelle_nature)
        $new_nature=$_POST['New_nature_input']; //Nouvelle nature de document saisie
        $new_nature=str_replace('"',"'",$new_nature); //Pour éviter les problèmes au niveau des requêtes SQL 
        $new_nature=str_replace("/","-",$new_nature); //Pour éviter des problèmes lors du déplacement du document sur le serveur (car la nature sera indiqué dans le nom du fichier upload)
        $new_nature=str_replace("_","-",$new_nature); //Pour éviter les problèmes lors de la création du nom du fichier qu'on mettra sur le serveur (on utilise les _ comme séparateur)

        $type_identité=$_POST["type_identité_select"]; //Type de la pièce d'identité

        $file_name = $_FILES['file']['name']; //Nom complet du fichier
        $fileTmpName = $_FILES['file']['tmp_name']; //Nom temporaire du fichier
        $fileSize = $_FILES['file']['size']; //Taille du fichier
        $fileError = $_FILES['file']['error']; //Erreur (s'il y en a une)
        $fileType = $_FILES['file']['type']; //Type du fichier

        $comment=$_POST["commentaire"]; //Contenu saisis
        $comment=str_replace("_","-",$comment);
        $comment=str_replace("/","-",$comment);
        $comment=str_replace(".",",",$comment);
        $comment=str_replace('"',"'",$comment);
        //$comment=trim($comment, "/.");  //supprimer les / et . -> on a préféré les remplacer comme précédemment par soucis de lisibilité

        $info_file1 = explode('.', $file_name); //On scinde le nom du fichier par rapport au séparateur '.' dans le but de récupérer son extension
        $fileExtension = strtolower(end($info_file1)); //On la met en minuscule

        //On scinde le nom du fichier par rapport au séparateur '.' dans le but de récupérer le nom du fichier (extension exclue) en remplaçant les '.' et '_' par des '-' 
        $info_file2=explode('.', $file_name, -1);  //-1 pour exclure le dernier élément (ici l'extension)
        $fileName=implode("-",$info_file2);
        $fileName=str_replace("_","-",$fileName);

        $allowed = array('jpg', 'jpeg','png','pdf'); //Types de fichiers autorisés

        if (in_array($fileExtension, $allowed)) { //Si l'extention du fichier séléctionné est accepté
            
            if($fileError === 0){ //Si aucune erreur n'est survenue


                if ($fileSize < 1000000) { 
                  //Si la taille du fichie est inférieure à 1Mo (1000000 octets) en
                                //  1Mo = 1024Ko = 1024*1024 octets
                                //1000000 octets = 1Mo




                  
                    if ($nature == "Autres"){ //Si on saisis une nouvelle nature de document

                        //On vérifie que la nouvelle nature saisie n'existe pas dans la BD
                        $req_verif='SELECT *
                                    FROM Natures
                                    WHERE Libelle_nature IN ("'.$new_nature.'")';

                        $result_verif=$bdd->query($req_verif); 
                        $res_verif=$result_verif->fetch(PDO::FETCH_ASSOC);

                        if (empty($res_verif)){ //Si la nouvelle nature n'est pas dans la BD

                            //On insère la nouvelle nature de document dans la BD et on récupère son code (car on en a besoin pour insérer dans la BD les infos du document upload)
                            $req_nature='INSERT INTO Natures (Libelle_nature)
                                         VALUES ("'.$new_nature.'")';

                            $req_code_nature='SELECT Code_n
                                              FROM Natures
                                              WHERE Libelle_nature="'.$new_nature.'"';

                            $bdd->query($req_nature);

                            $result=$bdd->query($req_code_nature); 
                            $res=$result->fetch(PDO::FETCH_ASSOC);
                            $code_nature=$res['Code_n'];

                            if(!empty($comment)){ //Si un commentaire sur le contenu du doc a été saisis
                                //On nomme le fichier sur le serveur de la sorte:
                                //code patient_nom patient_prénom patient_nature du doc_nom du doc_contenu du doc.extension
                                $fileNameNew = $patient."_".$new_nature."_".$fileName."_".$comment.".".$fileExtension;
                            }else {
                                //Si aucun commentaire sur le contenu n'a été fait alors on nomme le fichier ainsi: 
                                //code patient_nom patient_prénom patient_nature du doc_nom du doc.extension
                                $fileNameNew = $patient."_".$new_nature."_".$fileName.".".$fileExtension; 
                            }

                            //Emplacement où l'on souhaite stocké le fichier upload
                            $fileDestination = $sous_dossier."/".$fileNameNew; //correspond à: fichiers_patients/code patient_nom patient_prénom patient/Documents/nom formaté du doc.extension

                            //Puisque l'on a nommé les documents sur le serveur avec l'ensemble de leurs caractéristiques (hormis la taille du doc) 
                            //on a simplement a vérifier si un document avec le même chemin existe pour savoir s'il existe déjà un document similaire et donc refuser l'upload du nouveau fichier sur le serveur
                            //-> Pas besoin d'interroger la BD
                            if(!file_exists($fileDestination) ){ //Si on a aucun fichier similaire sur le serveur
                                move_uploaded_file($fileTmpName, $fileDestination); //On déplace le fichier upload sur le serveur, à l'emplacement définit précédemment

                                //On insère dans la BD les infos du document upload
                                $req='INSERT INTO Documents
                                      VALUES ("'.$fileDestination.'", "'.$fileName.'", '.$code_nature.', "'.$comment.'", '.$code_patient.', "'.$fileExtension.'", '.$fileSize.')';

                                $bdd->query($req);

                                //On affiche l'alerte
                                echo '<script type ="text/JavaScript"> swal("Le document a bien été ajouté !", "", "success"); </script>';

                            }else echo '<script type ="text/JavaScript"> swal("ATTENTION", "Un document similaire a déjà été stocké pour le patient, importez un autre document.", "error"); </script>';

                        }else echo '<script type ="text/JavaScript"> swal("AVERTISSEMENT", "Cette nature de document existe déjà ! Merci de bien vouloir la séléctionner depuis la liste déroulante.", "warning"); </script>';

                    }else{ //Si on séléctionne une nature de document existante
                        
                        foreach($_SESSION['infos_natures'] as $n){ //On parcours toutes les natures de document
                            if ($nature == $n['Libelle_nature']){ //On trouve la nature séléctionné
                                $code_nature=$n['Code_n']; //On récupère son code dans la BD

                                if($nature == "Pièces d'identité") $fileName=$type_identité; //Si la nature est "Pièces d'identité" alors on nomme le fichier upload avec le type de pièce d'identité

                                //On formate le nom du fichier à upload
                                if(!empty($comment)){ 
                                    $fileNameNew = $patient."_".$nature."_".$fileName."_".$comment.".".$fileExtension; 
                                }else $fileNameNew = $patient."_".$nature."_".$fileName.".".$fileExtension;

                                $fileDestination = $sous_dossier."/".$fileNameNew;

                                //Si aucun fichiers similaires n'existent
                                if(!file_exists($fileDestination) ){
                                    //On déplace le fichier upload sur le serveur et on ajoute ses infos dans la BD
                                    move_uploaded_file($fileTmpName, $fileDestination);

                                    $req='INSERT INTO Documents
                                          VALUES ("'.$fileDestination.'", "'.$fileName.'", '.$code_nature.', "'.$comment.'", '.$code_patient.', "'.$fileExtension.'", '.$fileSize.')';

                                    $bdd->query($req);

                                    echo '<script type ="text/JavaScript"> swal("Le document a bien été ajouté !", "", "success"); </script>';

                                }else echo '<script type ="text/JavaScript"> swal("ATTENTION", "Un document similaire a déjà été stocké pour le patient, importez un autre document.", "error"); </script>';

                            }
                        }
                    }

                }else echo '<script type ="text/JavaScript"> swal("AVERTISSEMENT", "Le fichier que vous avez séléctionné est trop volumineux !", "warning"); </script>'; //Alerte si le ficheir est trop lourd
            }else echo '<script type ="text/JavaScript"> swal("ERREUR", "Erreur lors du chargement du document", "error"); </script>'; //Alerte en cas d'échec du chargement du document
        }else echo '<script type ="text/JavaScript"> swal("ERREUR", "Vous ne pouvez pas importer de document de ce type", "error"); </script>'; //Alerte dans le cas où l'extention du fichier séléctionné n'est pas accepté

    }
}

?>
