<?php
  session_start();
  // on stocke les variables de session dans des variables locales
  unset($_SESSION["nom"]);
  unset($_SESSION["motif"]);
  unset($_SESSION["pays"]);
  unset($_SESSION["etat_date_naiss"]);
  unset($_SESSION["date_naiss_debut"]);
  unset($_SESSION["date_naiss_fin"]);
  include("ressources_communes.php");     
// bouton retour
  if(isset($_GET['retour'])){
    $_POST = $_SESSION['save_form'] ;
  }
?>
<!DOCTYPE html>
<html>

<head>
  <?php include("head.php"); ?>
  <script type="text/javascript">
// afficher la div qui contient les champs de saisie de l'intervalle de date de naissance
    function ShowHideDiv() {
      var elem = document.getElementById('choixIntervalle');
      var date_d = document.getElementById('debut');
      var date_f = document.getElementById('fin');
      elem.style.display = "block";
      date_d.required = true;
      date_f.required = true;
    }
// cacher la div qui contient les champs de saisie de l'intervalle de date de naissance
    function HideDiv() {
      var elem = document.getElementById('choixIntervalle');
      var date_d = document.getElementById('debut');
      var date_f = document.getElementById('fin');
      elem.style.display = "none";
      date_d.required = false;
      date_f.required = false;
    }
  </script>
</head>
<body class="sub_page">
  <div class="hero_area">
    <?php include("header.php"); ?>
  </div>
  <?php
    // la connexion à la base de données 
    $bdd=connexion();
    $res=search($bdd);
    recup_donnees_motif_pays($bdd);
  ?>
  <section id="recherche" class="layout_padding">
      <div class="container">
        <div class="heading_container heading_center">
          <h2>
          Search for patients
          </h2>
          <p>
            You can search for patients by name, by admission reason, by country of origin or by date of birth.
          </p>
        </div>
        <div class="row justify-content-md-center">
          <div class='mt-5 mb-5 col-8'>
            <form action="recherche_patient.php#resultats" class="text-vert" method="post">
              <div class="row mb-3">
                  <label for="Nom" >Name</label>
                  <input type="text" class="form-control" name="Nom" placeholder="Exemple: SY" value="<?php if(isset($_POST['btn_recherche'])) { echo $_SESSION["nom"]; } ?>">
              </div>
              <div class="row mb-3">
                <label for="Motifs">Motifs of admission</label>
                <select name="Motifs" class="form-control">
                  <option value="">Indifferent</option>
                  <?php
                    
                    foreach($_SESSION['infos_motifs'] as $m){
                      if($m['Code_m'] == $_SESSION["motif"]){
                        echo '<option value="'.$m['Code_m'].'" selected>'.$m['Libelle_motif'].'</option>';
                      }else{
                        echo '<option value="'.$m['Code_m'].'">'.$m['Libelle_motif'].'</option>';
                      }
                    }
                  ?>
                </select>
              </div>
              <div class="row mb-3">
                <label for="Pays">Country</label>
                <select name="Pays" class="form-control">
                    <option value="">Indifferent</option>
                    <?php
                      //on met en option tous les pays existant dans la BD
                      //on séléctionne l'option mémorisée si une recherche a été précédemment faite
                      foreach($_SESSION['infos_pays'] as $p){
                        if($p['Code_p'] == $_SESSION["pays"]){
                          echo '<option value="'.$p['Code_p'].'" selected>'.$p['Libelle_pays'].'</option>';
                        }else{
                          echo '<option value="'.$p['Code_p'].'">'.$p['Libelle_pays'].'</option>';
                        }
                      }
                    ?>

                </select>
              </div>
        
              <div class="row">
                <p>birth dates</p>
              </div>
              <div class="mb-3 text-noir ">
                <?php
                  if((isset($_POST['btn_recherche'])) && ($_SESSION["etat_date_naiss"] == "1")){
                    echo "<input type='radio' id='opt1' name='Date_naiss' value='0' onclick='HideDiv()' />
                          <label for='Date_naiss'>Indifferent</label>
                          </br>
                          <input type='radio' id='opt2' name='Date_naiss' value='1' onclick='ShowHideDiv()' checked />
                          <label for='Date_naiss'>Choose an interval</label>";
                  }else{
                    echo "<input type='radio' id='opt1' name='Date_naiss' value='0' onclick='HideDiv()' checked />
                          <label for='Date_naiss'>Indifferent</label>
                          </br>
                          <input type='radio' id='opt2' name='Date_naiss' value='1' onclick='ShowHideDiv()' />
                          <label for='Date_naiss'>Choose an interval</label>";
                  }
                ?>
              </div>
              <div id="choixIntervalle" class="mb-3 text-noir" style="display: none">
                <div class="mb-3">
                  <label for='debut'>Start</label>
                  <input type='date' id='debut' name='debut' min='1900-01-01' max='2022-01-01' class='form-control' value="<?php if(isset($_POST['btn_recherche'])){ echo $_SESSION['date_naiss_debut']; } ?>"/>
                </div>
                <div class="mb-3">
                  <label for='fin'>End</label>
                  <input type='date' id='fin' name='fin' min='1900-01-01' max='2022-01-01' class='form-control' value="<?php if(isset($_POST['btn_recherche'])){ echo $_SESSION['date_naiss_fin']; } ?>"/>
                </div>
              </div>
              <div class="row mt-5 justify-content-md-center">
                <input type="submit" class="btn3" id="soumettre" name="btn_recherche" value="Search"  />
              </div>
            </form>
          </div>
        </div>
      </div>
  </section>
  <script> 
    var elem = document.getElementById('choixIntervalle');
    var btn = document.getElementById('opt2');
    var date_d = document.getElementById('debut');
    var date_f = document.getElementById('fin');
    if(btn.checked){
      elem.style.display="block";
      date_d.required = true;
      date_f.required = true;
    }else elem.style.display="none";
  </script>
  <?php
    afficher_resultat_recherche($res);
  ?>
  <?php include("footer.php"); ?>
</body>
</html>