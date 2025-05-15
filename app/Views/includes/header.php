<meta http-equiv="Content-Type" value="text/html; charset=UTF-8" />
<title>PTBA</title>
<meta content="width=device-width, initial-scale=1.0" name="viewport">
<meta content="Plateforme digitalisée de suivi-évaluation des projets programmes et financés par les partenaires au développement" name="keywords">
<meta content="PTBA" name="description">

<!-- Favicon -->
<link href="<?=base_url()?>/assets_frontend/img/favicon.png" rel="icon">

<!-- Google Web Fonts -->
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">

<!-- Font Awesome -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">

<!-- Libraries Stylesheet -->
<link href="<?=base_url()?>/assets_frontend/lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
<link href="https://unpkg.com/slim-select@latest/dist/slimselect.css" rel="stylesheet"></link>




<!-- Vendor CSS Files -->
<!-- <link href="<?=base_url()?>/assets_frontend/css/bootstrap.min.css" rel="stylesheet"> -->

<link href="<?=base_url()?>/assets_frontend/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="<?=base_url()?>/assets_frontend/assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
<link href="<?=base_url()?>/assets_frontend/assets/vendor/aos/aos.css" rel="stylesheet">
<link href="<?=base_url()?>/assets_frontend/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
<link href="<?=base_url()?>/assets_frontend/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">


<!-- Customized Bootstrap Stylesheet -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">


<link href="<?=base_url()?>/assets_frontend/css/style.css" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="<?=base_url()?>/assets_frontend/dataTables/css/jquery.dataTables.min.css">


<style type="text/css">
  .stepper-wrapper {
    margin-top: auto;
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
  }
  .stepper-item {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex: 1;

    @media (max-width: 768px) {
      font-size: 12px;
    }
  }

  .stepper-item::before {
    position: absolute;
    content: "";
    border-bottom: 2px solid #ccc;
    width: 100%;
    top: 20px;
    left: -50%;
    z-index: 2;
  }

  .stepper-item::after {
    position: absolute;
    content: "";
    border-bottom: 2px solid #ccc;
    width: 100%;
    top: 20px;
    left: 50%;
    z-index: 2;
  }

  .stepper-item .step-counter {
    position: relative;
    z-index: 5;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #ccc;
    margin-bottom: 6px;
  }

  .stepper-item.active1 {
    font-weight: bold;
    color: #1d2653;
  }

  .stepper-item.completed .step-counter {
    background-color: #fd7e14;
  }

  .stepper-item.completed::after {
    position: absolute;
    content: "";
    border-bottom: 2px solid #fd7e14;
    width: 100%;
    top: 20px;
    left: 50%;
    z-index: 3;
  }

  .stepper-item:first-child::before {
    content: none;
  }
  .stepper-item:last-child::after {
    content: none;
  }
</style>