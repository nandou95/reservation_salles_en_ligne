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

    <!-- Carousel Start -->
    <div class="header-carousel owl-carousel">
      <div class="header-carousel-item">
        <img src="<?=base_url()?>/uploads/salle/carousel-2.jpg" class="img-fluid w-100" alt="Image">
        <div class="carousel-caption">
          <div class="container">
            <div class="row g-5">
              <div class="col-12 animated fadeInUp">
                <div class="text-center">
                  <h4 class="text-primary text-uppercase fw-bold mb-4">Bienvenu sur PRIX D'AMOUR</h4>
                  <h1 class="display-4 text-uppercase text-white mb-4">Reserver des salles des cérémonies et des conférences</h1>
                  <p class="mb-5 fs-5">Notre entreprise est spécialisée dans la digitalisation du secteur événementiel à travers une plateforme innovante de réservation en ligne de salles. Nous avons pour mission de faciliter l’organisation des événements en offrant un service rapide, fiable et accessible à tous.</p>
                  <div class="d-flex justify-content-center flex-shrink-0 mb-4">
                    <a class="btn btn-light rounded-pill py-3 px-4 px-md-5 me-2" href="#"><i class="fas fa-play-circle me-2"></i> Salle des mariages et Autres événements</a>
                    <a class="btn btn-primary rounded-pill py-3 px-4 px-md-5 ms-2" href="#"> Salle des conférences</a>
                  </div>
                  <div class="d-flex align-items-center justify-content-center">
                    <h2 class="text-white me-2">Nos réseaux sociaux :</h2>
                    <div class="d-flex justify-content-end ms-2">
                      <a class="btn btn-md-square btn-light rounded-circle me-2" href=""><i class="fab fa-facebook-f"></i></a>
                      <a class="btn btn-md-square btn-light rounded-circle mx-2" href=""><i class="fab fa-twitter"></i></a>
                      <a class="btn btn-md-square btn-light rounded-circle mx-2" href=""><i class="fab fa-instagram"></i></a>
                      <a class="btn btn-md-square btn-light rounded-circle ms-2" href=""><i class="fab fa-linkedin-in"></i></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="header-carousel-item">
        <img src="<?=base_url()?>/uploads/salle/carousel-1.jpg" class="img-fluid w-100" alt="Image">
        <div class="carousel-caption">
          <div class="container">
            <div class="row g-5">
              <div class="col-12 animated fadeInUp">
                <div class="text-center">
                  <h4 class="text-primary text-uppercase fw-bold mb-4">Bienvenu sur PRIX D'AMOUR</h4>
                  <h1 class="display-4 text-uppercase text-white mb-4">Aider à la planification des événements</h1>
                  <p class="mb-5 fs-5">Nous vous aidons à planifier vos événements en toute simplicité grâce à une solution complète et intuitive.</p>
                  <div class="d-flex justify-content-center flex-shrink-0 mb-4">
                    <a class="btn btn-light rounded-pill py-3 px-4 px-md-5 me-2" href="#"><i class="fas fa-play-circle me-2"></i> Salle des mariages et Autres événements</a>
                    <a class="btn btn-primary rounded-pill py-3 px-4 px-md-5 ms-2" href="#">Salle des conférences</a>
                  </div>
                  <div class="d-flex align-items-center justify-content-center">
                    <h2 class="text-white me-2">Nos réseaux sociaux :</h2>
                    <div class="d-flex justify-content-end ms-2">
                      <a class="btn btn-md-square btn-light rounded-circle me-2" href=""><i class="fab fa-facebook-f"></i></a>
                      <a class="btn btn-md-square btn-light rounded-circle mx-2" href=""><i class="fab fa-twitter"></i></a>
                      <a class="btn btn-md-square btn-light rounded-circle mx-2" href=""><i class="fab fa-instagram"></i></a>
                      <a class="btn btn-md-square btn-light rounded-circle ms-2" href=""><i class="fab fa-linkedin-in"></i></a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- Carousel End -->
  </div>
  <!-- Navbar & Hero End -->

  <!-- About Start -->
  <?php echo view('About_us_part.php');?>
  <!-- About End -->

  <!-- Services Start -->
  <div class="container-fluid service pb-5">
    <div class="container pb-5">
      <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
        <h4 class="text-primary">Our Services</h4>
        <h1 class="display-5 mb-4">We Services provided best offer</h1>
        <p class="mb-0">Lorem ipsum dolor, sit amet consectetur adipisicing elit. Tenetur adipisci facilis cupiditate recusandae aperiam temporibus corporis itaque quis facere, numquam, ad culpa deserunt sint dolorem autem obcaecati, ipsam mollitia hic.
        </p>
      </div>
      <div class="row g-4">
        <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.2s">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4"> Strategy Consulting</a>
              <p class="mb-4">Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur, sint? Excepturi facilis neque nesciunt similique officiis veritatis,
              </p>
              <a class="btn btn-primary rounded-pill py-2 px-4" href="#">Lire plus</a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.4s">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-2.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">Financial Advisory</a>
              <p class="mb-4">Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur, sint? Excepturi facilis neque nesciunt similique officiis veritatis,
              </p>
              <a class="btn btn-primary rounded-pill py-2 px-4" href="#">Lire plus</a>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay="0.6s">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-3.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4">Managements</a>
              <p class="mb-4">Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur, sint? Excepturi facilis neque nesciunt similique officiis veritatis,
              </p>
              <a class="btn btn-primary rounded-pill py-2 px-4" href="#">Lire plus</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Services End -->

  <!-- Salle Start -->
  <div class="container-fluid blog pb-5">
    <div class="container pb-5">
      <div class="text-center mx-auto pb-5 wow fadeInUp" data-wow-delay="0.2s" style="max-width: 800px;">
        <h4 class="text-primary">Salles</h4>
        <h1 class="display-5 mb-4">Salles des cérémonies et des conférences </h1>
        <p class="mb-0">La réservation en ligne de salles de cérémonies et de conférences permet aux utilisateurs de trouver rapidement des espaces adaptés à leurs événements sans se déplacer. Elle offre un gain de temps considérable grâce à la consultation des disponibilités, des prix et des services associés en temps réel.</p>
      </div>
      <div class="owl-carousel blog-carousel wow fadeInUp" data-wow-delay="0.2s">
        <div class="blog-item p-4">
          <div class="service-item">
            <div class="service-img">
              <img src="<?=base_url()?>/uploads/service/service-1.jpg" class="img-fluid rounded-top w-100" alt="Image">
            </div>
            <div class="rounded-bottom p-4">
              <a href="#" class="h4 d-inline-block mb-4"> Strategy Consulting</a>
              <p class="mb-4">Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur, sint? Excepturi facilis neque nesciunt similique officiis veritatis,
              </p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Admin</h5>
                    <p class="mb-0">October 9, 2025</p>
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
              <a href="#" class="h4 d-inline-block mb-4"> Strategy Consulting</a>
              <p class="mb-4">Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur, sint? Excepturi facilis neque nesciunt similique officiis veritatis,
              </p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Admin</h5>
                    <p class="mb-0">October 9, 2025</p>
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
              <a href="#" class="h4 d-inline-block mb-4"> Strategy Consulting</a>
              <p class="mb-4">Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur, sint? Excepturi facilis neque nesciunt similique officiis veritatis,
              </p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Admin</h5>
                    <p class="mb-0">October 9, 2025</p>
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
              <a href="#" class="h4 d-inline-block mb-4"> Strategy Consulting</a>
              <p class="mb-4">Lorem ipsum dolor sit amet consectetur adipisicing elit. Tenetur, sint? Excepturi facilis neque nesciunt similique officiis veritatis,
              </p>
              <div class="d-flex align-items-center">
                <img src="<?=base_url()?>/uploads/testimonial/testimonial-1.jpg" class="img-fluid rounded-circle" style="width: 60px; height: 60px;" alt="">
                <div class="col-md-6">
                  <div class="ms-3">
                    <h5>Admin</h5>
                    <p class="mb-0">October 9, 2025</p>
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
  <!-- Salle End -->

  <!-- Team Start -->
  <?php echo view('Team_membres.php');?>
  <!-- Team End -->

  <!-- Testimonial Start -->
  <?php echo view('Testimonial_part.php');?>
  <!-- Testimonial End -->
  <?php echo view('includes_frontend/footer.php');?>
  <?php echo view('includes_frontend/script.php');?>
</body>
</html>