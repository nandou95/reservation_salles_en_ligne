    <!-- Topbar Start -->
    <div class="container-fluid topbar">
        <div class="row py-2 px-lg-5">
            <div class="col-lg-6 text-center text-lg-left mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center text-white">
                    <small><i class="fa fa-phone-alt mr-2"></i>+257 75 2551455</small>
                    <small class="px-3">|</small>
                    <small><i class="fa fa-envelope mr-2"></i>info@besd.bi</small>
                </div>
            </div>
            <div class="col-lg-6 text-center text-lg-right">
                <div class="d-inline-flex align-items-center">
                    <!-- <a class="text-white px-2" href="">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a class="text-white px-2" href="">
                        <i class="fab fa-twitter"></i>
                    </a>
                    <a class="text-white px-2 mr-5" href="">
                        <i class="fa fa-envelope"></i>
                    </a> -->

                    <a href="<?=base_url('Login_Front')?>" class="btn tertiary-btn  py-2 px-4 d-none d-lg-block">Se connecter</a> &nbsp; &nbsp;
                    <a href="<?=base_url('Inscription')?>" class="btn tertiary-btn py-2 px-4 d-none d-lg-block">S’inscrire</a>
                   
                   
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->
      <!-- ======= Header ======= -->
  <header id="header" class="header d-flex align-items-center">
    <div class="container container-xl d-flex align-items-center justify-content-between">

      <a href="<?= base_url('Home'); ?>" class="logo d-flex align-items-center">
      
       <img src="<?= base_url('assets_frontend/img/logo_burundi.png'); ?>" alt=""> 
        <h2>BESD</h2>
      </a>

      <i class="mobile-nav-toggle mobile-nav-show bi bi-list"></i>
      <i class="mobile-nav-toggle mobile-nav-hide d-none bi bi-x"></i>
      <nav id="navbar" class="navbar">
        <ul>
          <li><a href="<?= base_url('Home'); ?>">Accueil</a></li>
          <li><a href="<?= base_url('Home'); ?>#apropos">Apropos</a></li>

          <li class="dropdown"><a href="<?= base_url('Publications'); ?>">Publications</a>
        <!-- <ul>
          <li> <a href="<?= base_url('Publications'); ?>" class="dropdown-item">Coopération internationale  </a></li>
          <li><a href="<?= base_url('Publications'); ?>" class="dropdown-item">Forum national sur le développement 2eme édition </a></li>
          <li></li> <a href="<?= base_url('Publications'); ?>" class="dropdown-item">Forum National sur le Développement du Burundi</a></li>
          <li>  <a href="<?= base_url('Publications'); ?>" class="dropdown-item">L’AGA KHAN Group aux côtés du Président Ndayishimiye dans le développement du Burundi</a></li>

        </ul> -->
      </li>



          <li  class="dropdown"><a href="">Orientations stratégiques
            <i
                class="bi bi-chevron-down dropdown-indicator"></i>
          </a>
          
            <ul>
           
              <li class="dropdown"><a href="#"><span>Stratégies Nationales</span> <i
                    class="bi bi-chevron-down dropdown-indicator"></i></a>
                <ul>
                  <li><a data-bs-toggle="modal" data-bs-target="#modal_pnb" href="#">PND Burundi 2018-2027</a></li>
                  <li><a data-bs-toggle="modal" data-bs-target="#modal_vision" href="#">Vision 2025</a></li>
                 
                </ul>
          </li>
            </ul>
          </li>
          <li  class="dropdown" ><a href="#">Documentation
            <i class="bi bi-chevron-down dropdown-indicator"></i> </a>


            <ul>
              <li><a href="<?= base_url('besd_info'); ?>"> Informations globales sur le BESD </a></li>
              <li><a href="<?= base_url('Home'); ?>#decret">Decret</a></li>
        
            </ul>
          
          
          
          </li>
     
          <li><a href="<?= base_url('Home'); ?>#FAQ">FAQ</a></li>
          <li><a href="<?= base_url('Contacts'); ?>">Contact</a></li>
        </ul>
      </nav><!-- .navbar -->

    </div>
  </header><!-- End Header -->


<div class="modal fade" id="modal_pnb">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 style="text-align: center;color: #1d2653">PND-Burundi-2018-2027</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
        
      </div>
        <div class="modal-body" style="width: 100%">
          <embed style="width:100% ;height:500px" src="<?=base_url('ShowFile/uploads/document_strategie_nationale/PND-Burundi-2018-2027.pdf')?>" type="">
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modal_vision">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 style="text-align: center;color: #1d2653">Vision-Burundi-2025</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
        
      </div>
        <div class="modal-body" style="width: 100%">
          <embed  style="width:100% ;height:500px" src="<?=base_url('ShowFile/uploads/document_strategie_nationale/Vision-Burundi-2025.pdf')?>" type="">
        </div>
    </div>
  </div>
</div>