<?php
  session_start();

  //on inclue le fichier contenant toutes les fonctions du site web
  include("ressources_communes.php");
   $_SESSION['page_actuel_doc']="documents.php";
  download_file(); 
?>
<!DOCTYPE html>
<html>
<head>
  <?php 
    include("head.php"); 
  // ouverture du fichier
    open_file(); 
    // impression du fichier
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
    //recupération des données des filtres
    recup_donnees_filtres($bdd);
    //suppression du document
    delete_file($bdd);
  ?>
  <section id="documents" class="layout_padding">
    <div class="container">
      <div class="heading_container heading_center">
        <h2>
        Documents
        </h2>
        <p>
        All the documents available
        </p>
      </div>
      <div class='container mt-5 mb-5'>
        <div class="row mb-5">
          
          <form class="col" action="documents.php" method="POST">
            <div class="row mb-3">
              <p><b>Search Documents  </b></sp>
            </div>
            <div class="row mb-3">
              <label for="nature_doc"class="mr-3" >Types :  </label>
              <select class="col-2 form-control" name="nature_doc"> 
                <option value="">All</option>
                <?php
                  //on met en option toutes les natures de documents existantes dans la BD
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
              <label for="type_doc"class="mr-3" >File types :  </label>
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
              <input type="text" class="col-2 form-control" name="contenu_doc" placeholder="Exemple: carte vital">
            </div>
            <div class="row">
              <input type="submit" name="btn_filtre" class="btn-filtre" value="Appliquer" />
            </div>
          </form>  
        </div>
        <div class="row">
            <?php
              // on afficher les documents dans un tableau
              read_all_files($bdd);
            ?>
            </tbody>
          </table>
        </div>
    </div>
  </section>
  <?php include("footer.php"); ?>
</body>
</html>



