<?php
//On inclue les fichiers et classes nécéssaires pour la fonction d'envoi de mail "send_mail()"
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'includes_PHPMailer/PHPMailer.php';
require 'includes_PHPMailer/SMTP.php';
require 'includes_PHPMailer/Exception.php';

//connexion à la Base de données
function connexion(){
  $servername = "localhost"; 
  $username = "user1"; 
  $password = "hcetylop"; 
  $database = "hopital_php"; 
    try
    {
      
        $bdd = new PDO("mysql:host=$servername;dbname=$database;charset=utf8", $username, $password);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $bdd->setAttribute(PDO::MYSQL_ATTR_INIT_COMMAND, 'SET NAMES utf8');
    }
    catch(PDOException $e)
    {
      echo "Erreur : ".$e->getMessage()."</br>"; 
      die;
    }
    return $bdd;
}
function recup_donnees_motif_pays($bdd){
   
    $req_motif="SELECT *
                FROM Motifs
                ORDER BY Libelle_motif";

    $result_motif = $bdd->query($req_motif); 
    $list_motif=$result_motif->fetchAll(PDO::FETCH_ASSOC);

    $req_pays="SELECT *
               FROM Pays
               ORDER BY Libelle_pays";
    $result_pays= $bdd->query($req_pays);
    $list_pays=$result_pays->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['infos_motifs']=$list_motif;
    $_SESSION['infos_pays']=$list_pays;
}
function recup_donnees_filtres($bdd){
    $req_nature="SELECT *
                 FROM Natures
                 ORDER BY Libelle_nature";
    $result_nature= $bdd->query($req_nature);
    $list_nature=$result_nature->fetchAll(PDO::FETCH_ASSOC);
    $req_extension="SELECT DISTINCT Extension
                    FROM Documents
                    ORDER BY Extension";
    $result_extension= $bdd->query($req_extension);
    $list_ext=$result_extension->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['infos_natures']=$list_nature;
    $_SESSION['extensions']=$list_ext;
}
function search($bdd){
    // lance la recherche
    if(isset($_POST['btn_recherche'])) { 
        $_SESSION["save_form"] = $_POST;
        $nom = $_POST['Nom'];
        // on remplace les " par ' pour éviter les erreurs de syntaxe
        $nom=str_replace('"',"'",$nom); 
        $motif= $_POST['Motifs'];
        $pays= $_POST['Pays'];
        $etat_date_naiss= $_POST['Date_naiss'];
        $date_naiss_debut= $_POST['debut'];
        $date_naiss_fin= $_POST['fin'];
        $_SESSION["nom"] = $nom;
        $_SESSION["motif"]= $motif;
        $_SESSION["pays"]= $pays;
        $_SESSION["etat_date_naiss"]= $etat_date_naiss;
        $_SESSION["date_naiss_debut"]= $date_naiss_debut;
        $_SESSION["date_naiss_fin"]= $date_naiss_fin;
        // on initialise la requête de la recherche
        $nb_critere = 0; 
        $clauses=[]; 
        if(!empty($nom)) { array_push($clauses, 'Nom LIKE "'.$nom.'%"'); $nb_critere++; }
        if(!empty($motif)) { array_push($clauses, 'Code_motif='.$motif); $nb_critere++; }
        if(!empty($pays)) { array_push($clauses, 'Code_pays="'.$pays.'"'); $nb_critere++; }
        if($etat_date_naiss == '1') { array_push($clauses, 'Date_naiss BETWEEN "'.$date_naiss_debut.'" AND "'.$date_naiss_fin.'"'); $nb_critere++; } //1 veut dire qu'on a choisi un interval de date de naisssance
        $critere=implode(" AND ", $clauses);
        if($nb_critere > 0){ 
            $req="SELECT Code_patient, UPPER(Nom) AS name, Prenom
                  FROM Patients
                  WHERE $critere
                  ORDER BY Nom, Prenom";
        }else{ 
            $req="SELECT Code_patient, UPPER(Nom) AS name, Prenom
                  FROM Patients
                  ORDER BY Nom, Prenom";
        }
        $result=$bdd->query($req);
        $list_patients=$result->fetchAll(PDO::FETCH_ASSOC);
        return $list_patients;
    }
}
function afficher_resultat_recherche($list_patients){
    if(isset($_POST['btn_recherche'])) { 

        echo '<section id="resultats" class="resultats_section layout_padding light-grey2">
                <div class="container">
                    <div class="heading_container heading_center">
                        <h2 >
                            Résultats de la recherche
                        </h2>';
        if (empty($list_patients)){ 
            echo "<p class='text-rouge'>
                    your search - did not match any patients.
                  </p>
                </div>
                <div class='container mt-5 mb-5'>";
        }else{ 
            echo '<p>
                here are the results of your search
            </p>
                </div>
                <div class="container mt-5 mb-5">';
        }
        foreach($list_patients as $patient){ 
            echo '<div id ="patient'.$patient['Code_patient'].'" class="row justify-content-md-center mb-3">
                    <a class="patient" href="fiche_patient.php?code='.$patient['Code_patient'].'">'.$patient['name'].' '.$patient['Prenom'].'</a>
                  </div>';
        }

        echo '</div>
                <div class="container mt-5">
                    <div class="row justify-content-md-center">
                        
                        <div class="col col-md-auto">
                            <a href="recherche_patient.php" class=" btn btn-dark">New search</a>
                        </div>
                    </div>
                </div>
            </div>
        </section> 
        ';
    }
}
function fiche($code_patient, $bdd){
    $req='SELECT Code_patient, Nom, Prenom, Libelle_sexe, DATE_FORMAT(Date_naiss, "%d/%m/%Y") as Date_naissance, Date_naiss as format_initial_date_naiss, Num_secu, Libelle_pays, DATE_FORMAT(Date_entree, "%d/%m/%Y") as Date_d_entree, Date_entree as format_initial_date_entree, Libelle_motif
            FROM Patients pat JOIN Sexe s ON pat.Sexe=s.Code_s JOIN Pays pay ON pat.Code_pays=pay.Code_p JOIN Motifs mot ON pat.Code_motif=mot.Code_m
            WHERE Code_patient='.$code_patient;
    $result = $bdd->query($req);
    $patient=$result->fetch(PDO::FETCH_ASSOC);
    $_SESSION["code_patient_select"] = $patient['Code_patient'];
    $_SESSION["nom_patient_select"] = $patient['Nom'];
    $_SESSION["prenom_patient_select"] = $patient['Prenom'];
    return $patient;
}
function delTree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    rmdir($dir);
}
function read_all_files($bdd){
    $req="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom as Nom_patient, Prenom as Prenom_patient
          FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n";
    $results=$bdd->query($req);
    $list_doc=$results->fetchAll(PDO::FETCH_ASSOC);
    // on fait preseque la meme chose que pour la recherche mais sur  les documents
    if (!empty($list_doc)){ 
        if(isset($_POST['btn_filtre'])){
            $filtre_nature=$_POST['nature_doc'];
            $filtre_nom=$_POST['nom_doc'];
            $filtre_nom=str_replace('"',"'",$filtre_nom); 
            $filtre_type=$_POST['type_doc'];
            $filtre_contenu=$_POST['contenu_doc'];
            $filtre_contenu=str_replace('"',"'",$filtre_contenu);
            $nb_filtre = 0; 
            $clauses=[]; 
            if(!empty($filtre_nature)) { array_push($clauses, 'Libelle_nature="'.$filtre_nature.'"'); $nb_filtre++; }
            if(!empty($filtre_nom)) { array_push($clauses, 'Nom_doc LIKE "'.$filtre_nom.'%"'); $nb_filtre++; }
            if(!empty($filtre_type)) { array_push($clauses, 'Extension="'.$filtre_type.'"'); $nb_filtre++; }
            if(!empty($filtre_contenu)) { array_push($clauses, 'Contenu LIKE "'.$filtre_contenu.'%"'); $nb_filtre++; }
            $critere=implode(" AND ", $clauses);
            if($nb_filtre > 0){ 
                $req_nb_doc="SELECT COUNT(*) as nb_doc
                             FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n
                             WHERE $critere";
            }else{ 
                $req_nb_doc="SELECT COUNT(*) as nb_doc
                             FROM Documents";
            }
            $result_nb_doc=$bdd->query($req_nb_doc);
            $res=$result_nb_doc->fetch(PDO::FETCH_ASSOC);
            $nb_doc=$res['nb_doc'];
            echo "<div class='text-bleu'>
                    <p>
                    Documents found: <b>$nb_doc</b>
                    </p>
                </div>
                <table class='table table-bordered'>
                    <thead>
                        <tr>";
            if (empty($filtre_nature)) echo "<th scope='col'>All the documents</th>";
            else echo "<th scope='col'>$filtre_nature</th>";

            echo "<th scope='col'>Type</th>
                    <th scope='col'>Contenu</th>
                    <th scope='col'>Actions</th>
                </tr>
            </thead>
            <tbody>";
            if ($nb_doc == 0){
                echo "<tr>
                
                        <td class='text-rouge'>No document found</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>";
            }else{
                
                if($nb_filtre > 0){ 
                    $req_doc="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom AS Nom_patient, Prenom AS Prenom_patient
                              FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n
                              WHERE $critere";
                }else{ 
                    $req_doc="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom AS Nom_patient, Prenom AS Prenom_patient
                              FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n";
                }
                $result_doc=$bdd->query($req_doc);
                $list_document=$result_doc->fetchAll(PDO::FETCH_ASSOC);
                foreach($list_document as $doc){
                    echo '<tr id="'.$doc['Chemin'].'">
                            <td>'.$doc['Code_patient'].'_'.$doc['Nom_patient'].'_'.$doc['Prenom_patient'].'_'.$doc['Nom_doc'].'</td>
                            <td>'.$doc['Extension'].'</td>
                            <td>'.$doc['Contenu'].'</td>
                            <td>
                                <a class="mr-2" href="documents.php?fichier_open='.$doc['Chemin'].'"> <img src="images/vusialiser.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="documents.php?fichier_download='.$doc['Chemin'].'"> <img src="images/telecharger.jpg" height ="30" width="30"/></a>
                                <a class="mr-2" href="documents.php?fichier_print='.$doc['Chemin'].'"> <img src="images/imprimer.jpg" height ="30" width="30"/></a>
                                <a class="mr-2" href="envoi_mail.php?fichier_mail='.$doc['Chemin'].'&code_patient='.$doc['Code_patient'].'&nom_patient='.$doc['Nom_patient'].'&prenom_patient='.$doc['Prenom_patient'].'&nom_doc='.$doc['Nom_doc'].'&nature_doc='.$doc['Libelle_nature'].'#mail"> <img src="images/mail.jpg" height ="30" width="30"/></a>
                                <a class="mr-2" href="documents.php?fichier_delete='.$doc['Chemin'].'"> <img src="images/suprimer.jpg" height ="30" width="30"/></a>
                            </td>
                        </tr>';
                }

            }

        }else{
             
            $nb_doc=sizeof($list_doc); 
            // affichage du nombre de documents
            // affichage du tableau
            echo "<div class='text-bleu'>
                    <p>
                    Documents found: <b>$nb_doc</b>
                    </p>
                </div>
                <table class='table table-bordered '>
                    <thead>
                    
                        <tr>
                            <th scope='col'>All the documents</th>
                            <th scope='col'>Type</th>
                            <th scope='col'>Contenu</th>
                            <th scope='col'>Actions</th>
                        </tr>
                    </thead>
                    <tbody>";

            foreach($list_doc as $doc){
                echo '<tr id="'.$doc['Chemin'].'">
                        <td>'.$doc['Code_patient'].'_'.$doc['Nom_patient'].'_'.$doc['Prenom_patient'].'_'.$doc['Nom_doc'].'</td>
                        <td>'.$doc['Extension'].'</td>
                        <td>'.$doc['Contenu'].'</td>
                        <td>
                            <a class="mr-2" href="documents.php?fichier_open='.$doc['Chemin'].'"> <img src="images/vusialiser.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="documents.php?fichier_download='.$doc['Chemin'].'"> <img src="images/telecharger.jpg" height ="30" width="30"/></a>
                            <a class="mr-2" href="documents.php?fichier_print='.$doc['Chemin'].'"> <img src="images/imprimer.jpg" height ="30" width="30"/></a>
                            <a class="mr-2" href="envoi_mail.php?fichier_mail='.$doc['Chemin'].'&code_patient='.$doc['Code_patient'].'&nom_patient='.$doc['Nom_patient'].'&prenom_patient='.$doc['Prenom_patient'].'&nom_doc='.$doc['Nom_doc'].'&nature_doc='.$doc['Libelle_nature'].'#mail"> <img src="images/envoi.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="documents.php?fichier_delete='.$doc['Chemin'].'"> <img src="images/suprimer.jpg" height ="30" width="30"/></a>
                        </td>
                    </tr>';
            }
        }
    }else{ 
        echo "<div class='text-bleu'>
                <p>
                Documents found: <b>0</b>
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
function read_files_patient($bdd){
    $req="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom as Nom_patient, Prenom as Prenom_patient
          FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n
          WHERE Code_patient=".$_SESSION["code_patient_select"];
    $results=$bdd->query($req); 
    $list_doc=$results->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($list_doc)){
        $req_view="CREATE VIEW documents_patient_vw
                   AS SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom as Nom_patient, Prenom as Prenom_patient
                   FROM Documents d JOIN Patients p ON d.Patient=p.Code_patient JOIN Natures n ON d.Nature=n.Code_n
                   WHERE Code_patient=".$_SESSION["code_patient_select"];
        $bdd->query($req_view);
        if(isset($_POST['btn_filtre'])){  
            $filtre_nature=$_POST['nature_doc'];
            $filtre_nom=$_POST['nom_doc'];
            $filtre_nom=str_replace('"',"'",$filtre_nom);
            $filtre_type=$_POST['type_doc'];
            $filtre_contenu=$_POST['contenu_doc'];
            $filtre_contenu=str_replace('"',"'",$filtre_contenu);
            $nb_filtre = 0; 
            $clauses=[]; 
            if(!empty($filtre_nature)) { array_push($clauses, 'Libelle_nature="'.$filtre_nature.'"'); $nb_filtre++; }
            if(!empty($filtre_nom)) { array_push($clauses, 'Nom_doc LIKE "'.$filtre_nom.'%"'); $nb_filtre++; }
            if(!empty($filtre_type)) { array_push($clauses, 'Extension="'.$filtre_type.'"'); $nb_filtre++; }
            if(!empty($filtre_contenu)) { array_push($clauses, 'Contenu LIKE "'.$filtre_contenu.'%"'); $nb_filtre++; }
            $critere=implode(" AND ", $clauses);
            if($nb_filtre > 0){ 
                $req_nb_doc="SELECT COUNT(*) as nb_doc
                             FROM documents_patient_vw
                             WHERE $critere";
            }else{ 
                $req_nb_doc="SELECT COUNT(*) as nb_doc
                             FROM documents_patient_vw";
            }
            $result_nb_doc=$bdd->query($req_nb_doc);
            $res=$result_nb_doc->fetch(PDO::FETCH_ASSOC);
            $nb_doc=$res['nb_doc'];
            echo "<div class='text-bleu'>
                    <p>
                    Documents found: <b>$nb_doc</b>
                    </p>
                </div>
                <table class='table table-bordered'>
                    <thead>
                        <tr>";
                        
            if (empty($filtre_nature)) echo "<th scope='col'>Tous les documents</th>";
            else echo "<th scope='col'>$filtre_nature</th>";
            echo "<th scope='col'>Type</th>
                    <th scope='col'>Contenu</th>
                    <th scope='col'>Actions</th>
                </tr>
            </thead>
            <tbody>";
            if ($nb_doc == 0){
                echo "<tr>
                        <td class='text-rouge'>Aucun document correspondant à ces critères de recherche</td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>";
            }else{ 
                if($nb_filtre > 0){ 
                    $req_doc="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom_patient, Prenom_patient
                              FROM documents_patient_vw
                              WHERE $critere";
                }else{ 
                    $req_doc="SELECT Chemin, Nom_doc, Nature, Libelle_nature, Contenu, Extension, Taille, Code_patient, Nom_patient, Prenom_patient
                              FROM documents_patient_vw";
                }

                $result_doc=$bdd->query($req_doc);
                $list_document=$result_doc->fetchAll(PDO::FETCH_ASSOC);
                foreach($list_document as $doc){
                   
                    echo '<tr id="'.$doc['Chemin'].'">
                            <td>'.$doc['Nom_doc'].'</td>
                            <td>'.$doc['Extension'].'</td>
                            <td>'.$doc['Contenu'].'</td>
                            <td>
                                <a class="mr-2" href="consulter.php?fichier_open='.$doc['Chemin'].'"> <img src="images/loupe.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="consulter.php?fichier_download='.$doc['Chemin'].'"> <img src="images/download.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="consulter.php?fichier_print='.$doc['Chemin'].'"> <img src="images/print.png" height ="30" width="30"/></a>
                                <a class="mr-2" href="envoi_mail.php?fichier_mail='.$doc['Chemin'].'&code_patient='.$doc['Code_patient'].'&nom_patient='.$doc['Nom_patient'].'&prenom_patient='.$doc['Prenom_patient'].'&nom_doc='.$doc['Nom_doc'].'&nature_doc='.$doc['Libelle_nature'].'#mail"> <img src="images/mail.jpg" height ="30" width="30"/></a>
                                <a class="mr-2" href="consulter.php?fichier_delete='.$doc['Chemin'].'"> <img src="images/trash.png" height ="30" width="30"/></a>
                            </td>
                        </tr>';
                }
            }
        }else{ 
            $nb_doc=sizeof($list_doc); 
            echo "<div class='text-bleu'>
                    <p>
                    Documents found: <b>$nb_doc</b>
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
            foreach($list_doc as $doc){
                echo '<tr id="'.$doc['Chemin'].'">
                        <td>'.$doc['Nom_doc'].'</td>
                        <td>'.$doc['Extension'].'</td>
                        <td>'.$doc['Contenu'].'</td>
                        <td>
                            <a class="mr-2" href="consulter.php?fichier_open='.$doc['Chemin'].'"> <img src="images/vusialiser.png" height ="30" width="30"/></a>
                            <a class="mr-2" href="consulter.php?fichier_download='.$doc['Chemin'].'"> <img src="images/telecharger.jpg" height ="30" width="30"/></a>
                            <a class="mr-2" href="consulter.php?fichier_print='.$doc['Chemin'].'"> <img src="images/imprimer.jpg" height ="30" width="30"/></a>
                            <a class="mr-2" href="envoi_mail.php?fichier_mail='.$doc['Chemin'].'&code_patient='.$doc['Code_patient'].'&nom_patient='.$doc['Nom_patient'].'&prenom_patient='.$doc['Prenom_patient'].'&nom_doc='.$doc['Nom_doc'].'&nature_doc='.$doc['Libelle_nature'].'#mail"> <img src="images/envoi.png" height ="30" width="30"/></a>
                            <a class="del-btn mr-2" href="consulter.php?fichier_delete='.$doc['Chemin'].'"> <img src="images/suprimer.jpg" height ="30" width="30"/></a>
                        </td>
                    </tr>';
            }
        }
        $req_delete_view="DROP VIEW IF EXISTS documents_patient_vw";
        $bdd->query($req_delete_view);
    }else{ 
        echo "<div class='text-bleu'>
                <p>
                Documents found: <b>0</b>
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
function open_file(){ 
    if (isset($_GET['fichier_open'])){ 
        echo '<script type="text/javascript">
                fenetre=window.open("'.$_GET['fichier_open'].'");
              </script>';
    }
}
function download_file(){
    if (isset($_GET['fichier_download'])){ 
        if(!empty($_GET['fichier_download'])){
            $nom_fichier=basename($_GET['fichier_download']); 
            $chemin_fichier=$_GET['fichier_download'];
            $info_nom_fichier = explode('.', $nom_fichier); 
            $extension = strtolower(end($info_nom_fichier));
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
    if (isset($_GET['fichier_print'])){    
        echo '<script type="text/javascript">
                fenetre=window.open("'.$_GET['fichier_print'].'");
                fenetre.print();
              </script>';
    }
}
// hospital.polytech@gmail.com
//RayeneRami
//Bouton mail

function send_mail(){
    if (isset($_POST['btn_mail'])){ 
        $chemin_doc=$_GET['fichier_mail']; 
        $destinataire=$_POST['Destinataire']; 
        $destinataire=utf8_decode($destinataire); 
        $objet=$_POST['Objet'];
        $objet=utf8_decode($objet); 
        $message=$_POST['Message']; //notre message
        $message=nl2br($message); 
        $message=utf8_decode($message);
// phpmailer        
        $mail = new PHPMailer(true);
        try{
            $mail->isSMTP();
            
            $mail->Host = "smtp.gmail.com"; 
            $mail->SMTPAuth = true; 
            $mail->Username = utf8_decode("hospital.polytech@gmail.com"); 
            $mail->Password = "abtkkuigetbarger"; 
            $mail->SMTPSecure = "ssl"; 
            $mail->Port = "465"; 

           
            
            $mail->setFrom('hospital.polytech@gmail.com', utf8_decode('Polytech Hôpital')); 
            $mail->addAddress($destinataire); 
            $mail->isHTML(true);
            $mail->addAttachment($chemin_doc); 
            $mail->Subject = $objet; 
            $mail->Body = $message; 
            $mail->send(); 
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
        // on definit le chemin du fichier à supprimer
        $chemin_fichier=$_GET['fichier_delete']; 
        echo '<script type ="text/JavaScript">swal("Le document a bien été supprimé !", "", "success");</script>';
        $req='DELETE FROM Documents WHERE Chemin="'.$chemin_fichier.'"';
        $bdd->query($req);
//on supprime le fichier du serveur
        unlink($chemin_fichier);
        echo '<script type="text/javascript">
                var doc = document.getElementById("'.$chemin_fichier.'");
                doc.remove();
              </script>';
    }
}
// importe un fichier
function upload_file($code_patient, $bdd){
    if(isset($_POST['btn_import'])){ 
        $patient="Patient".$code_patient; 
        $dossier = "fichiers_patients/".$patient."/"; 
        $sous_dossier=$dossier."Documents"; 
        if (!is_dir($dossier) && !file_exists($sous_dossier)){
            mkdir($dossier, 0744);
            mkdir($sous_dossier, 0744);
        }
        $nature=$_POST["type_doc_import"]; 
        $new_nature=$_POST['New_nature_input']; 
        $new_nature=str_replace('"',"'",$new_nature); 
        $new_nature=str_replace("/","-",$new_nature); 
        $new_nature=str_replace("_","-",$new_nature); 
        $type_identité=$_POST["type_identité_select"]; 
        $file_name = $_FILES['file']['name']; 
        $fileTmpName = $_FILES['file']['tmp_name']; 
        $fileSize = $_FILES['file']['size']; 
        $fileError = $_FILES['file']['error']; 
        $fileType = $_FILES['file']['type']; 
        $comment=$_POST["commentaire"]; 
        $comment=str_replace("_","-",$comment);
        $comment=str_replace("/","-",$comment);
        $comment=str_replace(".",",",$comment);
        $comment=str_replace('"',"'",$comment);
        $info_file1 = explode('.', $file_name); 
        $fileExtension = strtolower(end($info_file1)); 
        $info_file2=explode('.', $file_name, -1);  
        $fileName=implode("-",$info_file2);
        $fileName=str_replace("_","-",$fileName);
        $allowed = array('jpg', 'jpeg','png','pdf'); 
        if (in_array($fileExtension, $allowed)) {  
            if($fileError === 0){ 
                if ($fileSize < 1000000) { 
                    if ($nature == "Autres"){ 
                        $req_verif='SELECT *
                                    FROM Natures
                                    WHERE Libelle_nature IN ("'.$new_nature.'")';
                        $result_verif=$bdd->query($req_verif); 
                        $res_verif=$result_verif->fetch(PDO::FETCH_ASSOC);
                        if (empty($res_verif)){ 
                            $req_nature='INSERT INTO Natures (Libelle_nature)
                                         VALUES ("'.$new_nature.'")';
                            $req_code_nature='SELECT Code_n
                                              FROM Natures
                                              WHERE Libelle_nature="'.$new_nature.'"';
                            $bdd->query($req_nature);
                            $result=$bdd->query($req_code_nature); 
                            $res=$result->fetch(PDO::FETCH_ASSOC);
                            $code_nature=$res['Code_n'];
                            if(!empty($comment)){
                                $fileNameNew = $patient."_".$new_nature."_".$fileName."_".$comment.".".$fileExtension;
                            }else {
                                
                                $fileNameNew = $patient."_".$new_nature."_".$fileName.".".$fileExtension; 
                            }
                            $fileDestination = $sous_dossier."/".$fileNameNew; 
                           
                            if(!file_exists($fileDestination) ){ 
                                move_uploaded_file($fileTmpName, $fileDestination); 
                                $req='INSERT INTO Documents
                                      VALUES ("'.$fileDestination.'", "'.$fileName.'", '.$code_nature.', "'.$comment.'", '.$code_patient.', "'.$fileExtension.'", '.$fileSize.')';
                                $bdd->query($req);
                                //alerte
                                echo '<script type ="text/JavaScript"> swal("Le document a bien été ajouté !", "", "success"); </script>';
                            }else echo '<script type ="text/JavaScript"> swal("ATTENTION", "Un document similaire a déjà été stocké pour le patient, importez un autre document.", "error"); </script>';
                        }else echo '<script type ="text/JavaScript"> swal("AVERTISSEMENT", "Cette nature de document existe déjà ! Merci de bien vouloir la séléctionner depuis la liste déroulante.", "warning"); </script>';
                    }else{                        
                        foreach($_SESSION['infos_natures'] as $n){ 
                            if ($nature == $n['Libelle_nature']){ 
                                $code_nature=$n['Code_n']; 
                                if($nature == "Pièces d'identité") $fileName=$type_identité; 
                                if(!empty($comment)){ 
                                    $fileNameNew = $patient."_".$nature."_".$fileName."_".$comment.".".$fileExtension; 
                                }else $fileNameNew = $patient."_".$nature."_".$fileName.".".$fileExtension;
                                $fileDestination = $sous_dossier."/".$fileNameNew;
                                if(!file_exists($fileDestination) ){
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
