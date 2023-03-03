<?php
  session_start();

  //on inclue le fichier contenant toutes les fonctions du site web
  include("ressources_communes.php");
?>

<!DOCTYPE html>
<html>

<head>
  <?php include("head.php"); ?>

  <script type="text/javascript">

    function Apparence_div() {
      var value_select=document.getElementById('type_doc_import').value;

       var elem = document.getElementById('type_identité'); 
      var saisie=document.getElementById('type_identité_select'); 
      var elem_2 = document.getElementById('New_nature'); 
      var saisie_2=document.getElementById('New_nature_input'); 

      if(value_select == "Pièces d'identité"){ 
        elem.style.display = "block";
        saisie.required = true;
        elem_2.style.display = "none";
        saisie_2.required = false;
      }else if(value_select == "Autres"){ 
        elem.style.display = "none";
        saisie.required = false;
        elem_2.style.display = "block";
        saisie_2.required = true;
      }else{
        elem.style.display = "none";
        saisie.required = false;
        elem_2.style.display = "none";
        saisie_2.required = false;
      }
    
    }

  </script>
</head>

<body class="sub_page">

  <div class="hero_area">
    <?php include("header.php"); ?>
  </div>

  <?php
    $bdd=connexion();

    recup_donnees_filtres($bdd);
    
   
    upload_file($_SESSION["code_patient_select"], $bdd);

    recup_donnees_filtres($bdd);
  ?>

  <section id="import" class="layout_padding-top">
    <div class="container">
      <div class="heading_container heading_center">
        <h2>
        Import the documents
        </h2>
        <p>
        Add the document for :  <b class='text-vert'> <?php echo $_SESSION["nom_patient_select"].' '.$_SESSION["prenom_patient_select"]; ?> </b>
        </p>
      </div>
      
      <div class='container layout_padding2-top layout_padding2-bottom'>

        <form action="importer.php" method="post" enctype="multipart/form-data">

        <div class="row  mt-5 mb-5">
            <div class="container">
              <div class="row justify-content-md-center">
                <p class="text-vert">type of document</p>
              </div>
              <div class="row justify-content-md-center">
                <select class=" col-3 form-control" name="type_doc_import" id="type_doc_import" onchange="Apparence_div()" required>
                    <?php
                      //on met en option toutes les natures de documents existantes dans la BD
                      foreach($_SESSION['infos_natures'] as $n){
                        echo '<option value="'.$n['Libelle_nature'].'">'.$n['Libelle_nature'].'</option>';
                      }
                    ?>
                    <option value='Autres'>more</option>
                </select>
              </div>
            </div>
          </div>

          <div class="row  mt-5 mb-5" id="type_identité" style="display: none">
            <div class="container">
              <div class="row justify-content-md-center">
                <p class="text-vert">select the type </p>
              </div>
              <div class="row justify-content-md-center">
                <select class=" col-7 form-control" name="type_identité_select" id="type_identité_select">
                    <?php
                      //on définit les différents types de pièces d'identité 
                      $type_identité=["Carte d'identité", "Carte vitale", "Carte mutuelle",  "Attestation des droits à l'assurance maladie", "CMU"];
                      
                      
                      foreach($type_identité as $t){
                          echo '<option value="'.$t.'">'.$t.'</option>';
                      }
                    ?>
                </select>
              </div>
            </div>
          </div>
          
          <div class="row mb-5" id="New_nature" style="display: none">
            <div class="container">
              <div class="row justify-content-md-center">
                <p>Type of document</p>
              </div>
              <div class="row justify-content-md-center">
                <input type="text" class=" col-3 form-control" maxlength="50" name="New_nature_input" id="New_nature_input" placeholder="Ex: Résultats d'examen">
              </div>
            </div>
          </div>
         
          <div class="row mb-5">
            <div class="container">
              <div class="row justify-content-md-center">
                <p class="text-vert">Select the document </p>
              </div>
              <div class="row justify-content-md-center">
                <input type="file" name="file" required/>
              </div>
            </div>
          </div>
          <!-- fin champ de séléction du document à importer -->

          <!-- début champ de saisie du contenu -->
          <div class="row mb-5">
            <div class="container">
              <div class="row justify-content-md-center">
                <p class="text-vert">ADD content </p>
              </div>
              <div class="row justify-content-md-center">
                <input type="text" class="col-3 form-control" maxlength="100" name="commentaire" />
              </div>
            </div>
          </div>
         
          <div class="row justify-content-md-center mb-5">
            <input type="submit" name="btn_import" class="btn3" value="Importer le fichier" />
          </div>
        </form>

      </div>

    </div>
  </section>

  <section id="boutons" class="layout_padding-bottom">
    <div class="container text-center">
      <div class="row justify-content-md-center">
        <a class="col-2 btn8" href="fiche_patient.php?code=<?php echo $_SESSION["code_patient_select"]; ?>">Retour</a>
      </div>
    </div>
  </section>

  <?php include("footer.php"); ?>

</body>

</html>