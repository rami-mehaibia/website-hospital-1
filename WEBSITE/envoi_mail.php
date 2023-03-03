<?php
  session_start();

  //on inclue le fichier contenant toutes les fonctions du site web
  include("ressources_communes.php");
?>

<!DOCTYPE html>
<html>

<head>
  <?php include("head.php"); ?>
</head>


<body class="sub_page">

  <div class="hero_area">
    <?php include("header.php"); ?>
  </div>

  <?php
  // envoyer le mail
    send_mail(); 
  ?>

  <section id="mail" class="layout_padding-top">
      <div class="container">

        <div class="heading_container heading_center">
          <h2>
          Send the document
          </h2>
          <p>
          
          <b class="text-vert"><?php echo basename($_GET['fichier_mail']); ?></b>
          </p>
        </div>

        <div class="row justify-content-md-center">
          <div class='mt-5 mb-3 col-8'>

            <form action="" class="text-vert" method="post">
              <div class="row mb-3">
                <label for="Destinataire" >To</label>
                <input type="text" class="form-control" name="Destinataire" placeholder="Exemple: rayene.rami@gmail.com" required>
              </div>

              <div class="row mb-3">
                <label for="Objet" >Object</label>
                <input type="text" class="form-control" name="Objet" value="Document pour   <?php echo $_GET['nom_patient']; ?>" required>
              </div>

              <div class="row mb-3">
                <label for="Message">Message</label>
                <textarea name="Message" class="form-control" rows="6" required>
Bonjour Madame Monsieur <?php echo $_GET['nom_patient']." ".$_GET['prenom_patient']; ?>

Vous trouverez ci-joint le document "<?php echo $_GET['nom_doc']; ?>"_ <?php echo $_GET['nature_doc']; ?> 
ce document a été transmis par votre médecin traitant. 
Bonne réception.
Cordialement.

                </textarea>
              </div>

              <div class="row mt-5 justify-content-md-center">
                <input type="submit" class="btn3" id="btn_mail" name="btn_mail" value="Send"  />
              </div>

            </form>

          </div>
        </div>

      </div>
  </section>

  <section id="boutons" class="layout_padding-bottom">
    <div class="container text-center">
      <div class="row justify-content-md-center">
        <a class="col-2 btn8" href="<?php echo $_SESSION['page_actuel_doc']; ?>">Return</a>
      </div>
    </div>
  </section>

  <!-- début Footer -->
  <?php include("footer.php"); ?>
  <!-- fin Footer -->

</body>

</html>
