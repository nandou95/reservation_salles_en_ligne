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
      <div class="container text-center py-5" style="max-width: 900px;">
        <h4 class="text-white display-4 mb-4 wow fadeInDown" data-wow-delay="0.1s">À propos de nous</h4>
        <ol class="breadcrumb d-flex justify-content-center mb-0 wow fadeInDown" data-wow-delay="0.3s">
          <li class="breadcrumb-item"><a href="index.html">Home</a></li>
          <li class="breadcrumb-item"><a href="#">Pages</a></li>
          <li class="breadcrumb-item active text-primary">À propos</li>
        </ol>    
      </div>
    </div>
    <!-- Header End -->
  </div>
  <!-- Navbar & Hero End -->


  <!-- Abvout Start -->
  <?php echo view('About_us_part.php');?>
  <!-- About End -->

  <!-- Team Start -->
  <?php echo view('Team_membres.php');?>
  <!-- Team End -->
  <?php echo view('includes_frontend/footer.php');?>
  <?php echo view('includes_frontend/script.php');?>
</body>
</html>