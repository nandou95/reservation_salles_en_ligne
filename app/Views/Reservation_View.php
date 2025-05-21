<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includes_frontend/header.php');?>
</head>
<body>
  <!-- Topbar Start -->
  <?php echo view('includes_frontend/menu_frontend_top.php');?>
  <!-- Topbar End -->

  <!-- Navbar & Hero Start -->
  <div class="container-fluid position-relative p-0">
    <?php echo view('includes_frontend/menu_frontend.php');?>

    <!-- Header Start -->
    <div class="container-fluid bg-breadcrumb">
      <div class="container text-center py-5" style="height: 100px;">
        <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">Réservation</h4>
        <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Pages</a></li>
          <li class="breadcrumb-item active text-primary">Réserver</li>
        </ol>    
      </div>
    </div>
    <!-- Header End -->
  </div>
  <!-- Navbar & Hero End -->

  <!-- Contact Start -->
  <div class="container-fluid contact py-5">
    <div class="container py-5">
      <div class="row g-5">
        <div class="col-xl-6">
          <div class="wow fadeInUp" data-wow-delay="0.2s">
            <div class="bg-light rounded p-5 mb-5">
              <h4 class="text-primary mb-4">Contactez-nous</h4>
              <div class="row g-4">
                <div class="col-md-6">
                  <div class="contact-add-item">
                    <div class="contact-icon text-primary mb-4">
                      <i class="fas fa-map-marker-alt fa-2x"></i>
                    </div>
                    <div>
                      <h4>Adresse</h4>
                      <p class="mb-0">123 Avenue Prix d'amour de BUJUMBURA</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="contact-add-item">
                    <div class="contact-icon text-primary mb-4">
                      <i class="fas fa-envelope fa-2x"></i>
                    </div>
                    <div>
                      <h4>Email</h4>
                      <p class="mb-0">nandou95habimana@gmail.com</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="contact-add-item">
                    <div class="contact-icon text-primary mb-4">
                      <i class="fa fa-phone-alt fa-2x"></i>
                    </div>
                    <div>
                      <h4>Téléphone</h4>
                      <p class="mb-0">(+257) 69 301 985</p>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="contact-add-item">
                    <div class="contact-icon text-primary mb-4">
                      <i class="fab fa-firefox-browser fa-2x"></i>
                    </div>
                    <div>
                      <p class="mb-0">tomorrowsjoyprixdamour@gmail.com</p>
                      <p class="mb-0">(+257) 71 483 905</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-light p-5 rounded h-100 wow fadeInUp" data-wow-delay="0.2s">
              <h4 class="text-primary">Envoyez votre message</h4>
              <form name="myform" id="myform" action="<?=base_url('Home/save_contact_us')?>" method="POST" enctype="multipart/form-data">
                <div class="row g-4">
                  <div class="col-lg-12 col-xl-6">
                    <div class="form-floating">
                      <input type="text" class="form-control border-0" id="name" placeholder="Your Name">
                      <label for="name">Votre Nom</label>
                    </div>
                  </div>
                  <div class="col-lg-12 col-xl-6">
                    <div class="form-floating">
                      <input type="email" class="form-control border-0" id="email" placeholder="Your Email">
                      <label for="email">Email</label>
                    </div>
                  </div>
                  <div class="col-lg-12 col-xl-6">
                    <div class="form-floating">
                      <input type="phone" class="form-control border-0" id="phone" placeholder="Phone">
                      <label for="phone">Téléphone</label>
                    </div>
                  </div>
                  <div class="col-lg-12 col-xl-6">
                    <div class="form-floating">
                      <input type="text" class="form-control border-0" id="subject" placeholder="Subject">
                      <label for="subject">Sujet</label>
                    </div>
                  </div>
                  <div class="col-12">
                    <div class="form-floating">
                      <textarea class="form-control border-0" placeholder="Leave a message here" id="message" style="height: 160px"></textarea>
                      <label for="message">Message</label>
                    </div>

                  </div>
                  <div class="col-12">
                    <button class="btn btn-primary w-100 py-3">Envoyer un message</button>
                  </div>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Contact End -->

  <?php echo view('includes_frontend/footer.php');?>
  <?php echo view('includes_frontend/script.php');?>
</body>
</html>