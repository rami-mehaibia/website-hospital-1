<?php
  session_start();

  //on inclue le fichier contenant toutes les fonctions du site web
  include("ressources_communes.php"); 

  //on se connecte à la BD
  $bdd=connexion();
//recuperation des informations du patient
  $patient=fiche($_SESSION["code_patient_select"], $bdd);

  
  $Date=date('d/m/Y'); 
  $Time=date('h:i a');  

  //On inclue la classe TCPDF
  include('includes_TCPDF/tcpdf.php');

  //On créer une instance de TCPDF
  $pdf = new TCPDF('P','mm','A4');

  //On désactive les header et footer par défaut
  $pdf->setPrintHeader(false);
  $pdf->setPrintFooter(false);

  //On ajoute une page
  $pdf->AddPage();

  //On met un titre
  $pdf->SetFont('Helvetica','B',14);
  $pdf->Cell(190,10,'Fiche du patient '.$_SESSION["code_patient_select"].' : '.$_SESSION["nom_patient_select"].' '.$_SESSION["prenom_patient_select"],0,1,'C');

  //Sauts de lignes
  $pdf->Ln();
  $pdf->Ln();

  //On met la date
  $pdf->SetFont('Helvetica','',10);
  $pdf->Cell(15,5,"Édité le :",0);
  $pdf->Cell(100,5,$Date.' à '.$Time,0);

  //Sauts de lignes
  $pdf->Ln();
  $pdf->Ln();
	
// le conteny de la page 
	$html = '<table class="table">
              <tbody>
                <tr>
                  <th scope="row">Code patient</th>
                  <td>'.$patient['Code_patient'].'</td>
                </tr>
                <tr>
                  <th scope="row">Nom</th>
                  <td>'.$patient['Nom'].'</td>
                </tr>
                <tr>
                  <th scope="row">Prénom</th>
                  <td>'.$patient['Prenom'].'</td>
                </tr>
                <tr>
                  <th scope="row">Genre</th>
                  <td>'.$patient['Libelle_sexe'].'</td>
                </tr>
                <tr>
                  <th scope="row">Date de naissance</th>
                  <td>'.$patient['Date_naissance'].'</td>
                </tr>
                <tr>
                  <th scope="row">Numéro de sécurité sociale</th>
                  <td>'.$patient['Num_secu'].'</td>
                </tr>
                <tr>
                  <th scope="row">Pays</th>
                  <td>'.$patient['Libelle_pays'].'</td>
                </tr>
                <tr>
                  <th scope="row">Date entrée</th>
                  <td>'.$patient['Date_d_entree'].'</td>
                </tr>
                <tr>
                  <th scope="row">Motif</th>
                  <td>'.$patient['Libelle_motif'].'</td>
                </tr>
              </tbody>
            </table>
            <style>
              table {
                border-collapse:collapse;
                padding: 10px;
              }
              th,td {
                border:1px solid #888;
              }
              table tr th {
                font-weight:bold;
              }
            </style>';

  //Écriture de l'HTML
  $pdf->WriteHTMLCell(192,0,9,'',$html,0);	

  $nom_pdf = 'Fiche-patient-'.$_SESSION["code_patient_select"].'.pdf';

  //Création du PDF
  // on force le téléchargement du fichier
  $pdf->Output($nom_pdf,'D');
  

?>