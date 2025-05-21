<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includes_frontend/header.php');?>
</head>
<body>
  <?php echo view('includes_frontend/menu_frontend_top.php');?>

  <!-- Navbar & Hero Start -->
  <div class="container-fluid position-relative p-0">
    <?php echo view('includes_frontend/menu_frontend.php');?>
    <!-- Header Start -->
    <div class="container-fluid bg-breadcrumb">
      <div class="container text-center py-5" style="height: 50px;">
        <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Salle</h4>
        <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Pages</a></li>
          <li class="breadcrumb-item active text-primary"><?=$evenement?></li>
        </ol>    
      </div>
    </div>
    <!-- Header End -->
  </div>
  <!-- Navbar & Hero End -->
  <div class="row" style="margin-top : 20px">
    <div class="col-xl-2">
    </div>
    <div class="col-xl-8" style="background-color: blue;">
      <form name="myform" id="myform" action="<?=base_url('Home/salle/')?><?=$i?>" method="POST" enctype="multipart/form-data">
        <div class="row g-4">
          <div class="col-lg-12 col-xl-3">
            <label for="name">Province</label>
            <select class="form-control" id="province_id" name="province_id">
              <option value="">--- Séléctionner ---</option>
              <option value="1">Bujumbura Mairie</option>
              <option value="2">Kayanza</option>
              <option value="3">Ngozi</option>
              <option value="3">Gitega</option>
            </select>
          </div>
          <div class="col-lg-12 col-xl-3">
            <label for="name">Commune</label>
            <select class="form-control" id="province_id" name="commune_id">
              <option value="">--- Séléctionner ---</option>
            </select>
          </div>
          <div class="col-lg-12 col-xl-3">
            <label for="name">Date d'événements</label>
            <input type="date" class="form-control border-0" id="date_evenement" name="date_evenement">
          </div>
        </div>
      </form>
    </div>
    <div class="col-xl-2">
    </div>
  </div>

  <!-- Categorie 1 Start -->
  <div class="container-fluid blog pb-5" style="margin-top: 30px;">
    <div class="container pb-5">
      <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
        <h4 class="text-primary">Categorie 1</h4>
        <p class="mb-0">La réservation en ligne de salles de cérémonies et de conférences permet aux utilisateurs de trouver rapidement des espaces adaptés à leurs événements sans se déplacer. Elle offre un gain de temps considérable grâce à la consultation des disponibilités, des prix et des services associés en temps réel.</p>
      </div>
      <div class="owl-carousel blog-carousel wow fadeInUp" data-wow-delay="0.2s">
        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">PRIX D'AMOUR</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Nandou</h5>
                    <p class="mb-0">69 301 985</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">ARIETTE SALLE</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Ariette</h5>
                    <p class="mb-0">71 483 905</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">ERIEL SALLE</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Eriel</h5>
                    <p class="mb-0">69 301 786</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">Nandy Salle</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Nandy</h5>
                    <p class="mb-0">75 435 689</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Categorie 1 End -->

  <!-- Categorie 2 Start -->
  <div class="container-fluid blog pb-5">
    <div class="container pb-5">
      <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
        <h4 class="text-primary">Categorie 2</h4>
        <p class="mb-0">La réservation en ligne de salles de cérémonies et de conférences permet aux utilisateurs de trouver rapidement des espaces adaptés à leurs événements sans se déplacer. Elle offre un gain de temps considérable grâce à la consultation des disponibilités, des prix et des services associés en temps réel.</p>
      </div>
      <div class="owl-carousel blog-carousel wow fadeInUp" data-wow-delay="0.2s">
        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">PRIX D'AMOUR</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Nandou</h5>
                    <p class="mb-0">69 301 985</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">ARIETTE SALLE</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Ariette</h5>
                    <p class="mb-0">71 483 905</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">ERIEL SALLE</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Eriel</h5>
                    <p class="mb-0">69 301 786</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">Nandy Salle</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Nandy</h5>
                    <p class="mb-0">75 435 689</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Categorie 2 End -->

  <!-- Categorie 3 Start -->
  <div class="container-fluid blog pb-5">
    <div class="container pb-5">
      <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
        <h4 class="text-primary">Categorie 3</h4>
        <p class="mb-0">La réservation en ligne de salles de cérémonies et de conférences permet aux utilisateurs de trouver rapidement des espaces adaptés à leurs événements sans se déplacer. Elle offre un gain de temps considérable grâce à la consultation des disponibilités, des prix et des services associés en temps réel.</p>
      </div>
      <div class="owl-carousel blog-carousel wow fadeInUp" data-wow-delay="0.2s">
        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">PRIX D'AMOUR</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Nandou</h5>
                    <p class="mb-0">69 301 985</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">ARIETTE SALLE</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Ariette</h5>
                    <p class="mb-0">71 483 905</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">ERIEL SALLE</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Eriel</h5>
                    <p class="mb-0">69 301 786</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">Nandy Salle</a>
              <p class="mb-4">Mariages et Conferences</p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Nandy</h5>
                    <p class="mb-0">75 435 689</p>
                  </div>
                </div>
                <div class="col-md-6">
                  <a class="btn btn-primary rounded-pill py-2 px-4" href="<?=base_url()?>/reservation/1">Réserver</a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Categorie 3 End -->

  <!-- Testimonial Start -->
  <?php echo view('Testimonial_part.php');?>
  <!-- Testimonial End -->
  
  <?php echo view('includes_frontend/footer.php');?>
  <?php echo view('includes_frontend/script.php');?>
</body>
</html>