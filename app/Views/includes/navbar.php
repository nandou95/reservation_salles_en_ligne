<!-- Navbar Start -->
             <nav class="dash_nav navbar navbar-expand bg-white navbar-dark  px-4 py-0">
                  <a href="<?=base_url('infoAgrement')?>" class="logo d-flex align-items-center">
      
                    <img src="<?=base_url()?>/assets_frontend/img/logo_burundi.png" alt=""> 
                     <h2>BESD</h2>
                   </a>

                <ul class="dash_nav_right">
                  
                  <!--   <li class="dropdown"><a href="#" data-bs-toggle="modal"
                    data-bs-target="#modal_demande"><i class="fa fa-envelope"></i> <span>Plaintes </span> <i
                          class="bi bi-chevron-down dropdown-indicator"></i></a>
                      <ul>
                        <li><a href="#">Jules vous a envoyé un message <small> <i> Il y a 1 minutes </i></small></a></li>
                       
                        <li><a href="#">Juvenal vous a envoyé un message <small> <i> Il y a 2 minutes </i></small></a></li>
                       
                        <li><a href="#">Eloge vous a envoyé un message <small> <i> Il y a 12 minutes </i></small></a></li>
                       
                        <li><a href="#">Keza vous a envoyé un message <small> <i> Il y a 15 minutes </i></small></a></li>
                      
                      
                      </ul>
                    </li> -->


                    <!-- <li class="dropdown"><a href="#"><i class="fa fa-bell"></i><span>Mes notifications</span> <i
                        class="bi bi-chevron-down dropdown-indicator"></i></a>
                    <ul>
                      <li><a href="#">Vous avez 2 nouveaux mise à jour</a></li>
                     
                      <li><a href="#">3 Demandes ajoutés</a></li>
                     
                      <li><a href="#">Mot de passe a été changé<small> <i> Il y a 12 minutes </i></small></a></li>
                      <hr class="dropdown-divider">
                      <li class="down-dropdown"><a href="#" >  Voir tout les notifications </a></li>
                    
                    
                    </ul>
                  </li> -->


                  
                  <li class="dropdown"><a href="#">  <img src="<?=base_url()?>/assets_frontend/img/R.jpg" alt="" srcset=""><span><?= session()->get('NOM_PAD')?></span> <i
                    class="bi bi-chevron-down dropdown-indicator"></i></a>
                <ul>
                  <!-- <li><a href="#"><i class="fa fa-user"></i>Mon Profile</a></li>
                 
                  <li><a href="#"><i class="fa fa-cog"></i>Paramettre</a></li> -->
                  <!-- <hr class="dropdown-divider"> -->
                  <li ><a href="<?=base_url('perso/logout')?>" class="down-dropdown " ><i class="fa fa-right-from-bracket"></i>Deconnexion</a></li>
          
               
                
                
                </ul>
              </li>
                 
                  </ul>
          
            </nav>
            <!-- Navbar End -->






<!-- </nav> -->



