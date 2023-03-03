<?php
  session_start();
  include("ressources_communes.php");
  $_SESSION['page_actuel_doc']="consulter.php";
  download_file(); 
?>
<!DOCTYPE html>
<html>
<head>
  <?php 
    include("head.php");   
    open_file(); 
    print_file(); 
  ?>
</head>
<body class="sub_page">
  <div class="hero_area">
    <?php include("header.php"); ?>
  </div>
  <?php
    //connexion à la Base de données
    $bdd=connexion();
    recup_donnees_filtres($bdd);
    // on supprime le document
    delete_file($bdd);
  ?>
  <section id="documents" class="layout_padding-top">
    <div class="container">
      <div class="heading_container heading_center">
        <h2>
        Documents
        </h2>
        <!-- on affiche le nom et prénom du patient sélectionné -->
        <p>
        <b class='text-vert'> <?php echo $_SESSION["nom_patient_select"].' '.$_SESSION["prenom_patient_select"]; ?> </b>
        </p>
      </div>

      <div class='container mt-5 mb-5'>
       
        <div class="row mb-5">
          
          <form class="col" action="consulter.php" method="POST">
            <div class="row mb-3">
              <p><b>Search Documents </b></sp>
            </div>

            <div class="row mb-3">
              <label for="nature_doc"class="mr-3" >Types : </label>
              <select class="col-2 form-control" name="nature_doc"> 
                <option value="">All</option>

                <?php
                  foreach($_SESSION['infos_natures'] as $n){
                      echo '<option value="'.$n['Libelle_nature'].'">'.$n['Libelle_nature'].'</option>';
                  }
                ?>
                
              </select>
            </div>

            <div class="row mb-3">
              <label for="nom_doc" class="mr-3">Name of document : </label>
              <input type="text" class="col-2 form-control" name="nom_doc" placeholder="Exemple : Ordonnance">
            </div>
            
            <div class="row mb-3">
              <label for="type_doc"class="mr-3" >File types : </label>
              <select class="col-2 form-control" name="type_doc" >
                <option value="">All</option>

                <?php
                  foreach($_SESSION['extensions'] as $e){
                      echo '<option value="'.$e['Extension'].'">'.$e['Extension'].'</option>';
                  }
                ?>

              </select>
            </div>

            <div class="row mb-3">
              <label for="contenu_doc" class="mr-3">Content:</label>
              <input type="text" class="col-2 form-control" name="contenu_doc" placeholder="Exemple: Resultat">
            </div>

            <div class="row">
              <input type="submit" name="btn" class="btn-filtre" value="Apply" />
            </div>
          </form>  
          
        </div>

        <div class="row">
      
            <?php
              read_files_patient($bdd);
            ?>

          </tbody>

          </table>
        </div>

    </div>
  </section>

  <section id="boutons" class="layout_padding-bottom">
    <div class="container text-center">
      <div class="row justify-content-md-center">
        <a class="col-2 btn8" href="fiche_patient.php?code=<?php echo $_SESSION["code_patient_select"]; ?>">Return</a>
      </div>
    </div>
  </section>

  <?php include("footer.php"); ?>

</body>

</html>