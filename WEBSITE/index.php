<!DOCTYPE html>
<html>

<head>
  <?php include("head.php"); ?>
</head>

<body>

  <div class="hero_area">

  <div class="hero_bg_box">
      <img src="./images/photo.png" alt="">
    </div>

    <?php include("header.php"); ?>

    <section class="slider_section ">
      <div id="customCarousel1" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
          <div class="carousel-item active">
            <div class="container ">
              <div class="row">
                <div class="col-md-7">
                  <div class="detail-box">
                    <h1>
                        Welcome to Polytech hospital 
                    </h1>
                    <p>We make your job easier     </p>
                    <div class="btn-box">
                      <a href="recherche_patient.php" class="btn1">
                      Search the patients
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          
        </div>
        
      </div>

    </section>
  </div>

  <?php include("footer.php"); ?>

</body>

</html>
