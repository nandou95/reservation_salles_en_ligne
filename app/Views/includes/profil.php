<div class="dash_sidebar" id="sidebar"> 
              <div class="sidebar_header d-flex justify-content-center align-items-center">
                <img src="<?=base_url()?>/assets_frontend/img/R.jpg" alt="" srcset="">
                <h6><?= session()->get('NOM_PAD')?></h6>
                <hr>

            </div>
            <div class="sidebar_content">
              
                 <ul class="sidebar_list">
                  <li> <i style="color: #1d2653" class="fa fa-institution"></i> <?= session()->get('NOM_PAD')?> </li>
                  <li> <i style="color: #1d2653" class="fa fa-user"></i> <?= session()->get('NOM').' '.session()->get('PRENOM')?> </li>
                  <li><i style="color: #1d2653" class="fa fa-envelope"></i> <?= session()->get('USERNAME')?> </li>
                  <li><i style="color: #1d2653" class="fa fa-phone"></i> <?= session()->get('TEL')?> </li>
                  <!-- <li> <i class="fa fa-home" ></i>Bujumbura ,Burundi</li> -->
                
                   <li>
                    <a style="color: #1d2653" class=" down-dropdown d-flex align-items-center" href="<?=base_url('DetailsPad')?>"><i style="color: #1d2653" class="fa fa-eye"></i>Plus de détails</a>
                  </li>

                  <li class="down-dropdown d-flex align-items-center btn-deconnection"><a style="color: #1d2653" href="<?=base_url('perso/logout')?>"  ><i style="color: #1d2653" class="fa fa-right-from-bracket"></i>  Deconnexion </a></li>

                 </ul>
            </div>

            </div>


            
  
            