<nav class="navbar navbar-expand-lg navbar-light px-4 px-lg-5 py-3 py-lg-0">
  <a href="<?=base_url()?>" class="navbar-brand p-0">
    <h1 class="text-primary"><i class="fas fa-search-dollar me-3"></i>RSE</h1>
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
    <span class="fa fa-bars"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarCollapse">
    <div class="navbar-nav ms-auto py-0">
      <a href="<?=base_url()?>" class="nav-item nav-link active">Accueil</a>
      <a href="<?=base_url()?>/about_us" class="nav-item nav-link">À propos de nous</a>
      <a href="<?=base_url()?>/service" class="nav-item nav-link">Services</a>
      <div class="nav-item dropdown">
        <a href="#" class="nav-link" data-bs-toggle="dropdown">
          <span class="dropdown-toggle">Salle</span>
        </a>
        <div class="dropdown-menu m-0">
          <a href="<?=base_url()?>/salle/1" class="dropdown-item">Mariage</a>
          <a href="<?=base_url()?>/salle/2" class="dropdown-item">Conférence</a>
          <a href="<?=base_url()?>/salle/3" class="dropdown-item">Autres événements</a>
        </div>
      </div>
      <a href="<?=base_url()?>/contact_nous" class="nav-item nav-link">Contactez-nous</a>
    </div>
    <!-- <a href="#" class="btn btn-primary rounded-pill py-2 px-4 my-3 my-lg-0 flex-shrink-0">Get Started</a> -->
  </div>
</nav>