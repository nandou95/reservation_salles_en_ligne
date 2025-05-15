

<main class="dash_main"> 
  <?php

  //verifier si la PAD à demander l'agrement
  $IS_AGREER = session()->get("IS_AGREER");
  if ($IS_AGREER==1) {
    $agrement_link = 'agrement';// 1 l'agrement ne pas encore faite
  }else if ($IS_AGREER==2) {
    $agrement_link = 'infoAgrement';// 2 l'agrement esr deja fait
  }
  #################################

  //verification du staut de la PAD connecté
  $ID_STATUT_PAD = session()->get("ID_STATUT_PAD");
  if ($ID_STATUT_PAD==1) {
    $hidden = "hidden";
  }else {
    $hidden = '';
  }

  ?>
  <ul class="nav nav-pills">
    <li class="nav-item">
      <!-- <a class="nav-link active" href="#">Active</a> -->
      <a  class="nav-link <?php if($menu == 'infoAgrement' || $menu == 'agrement') echo 'active' ?>" href="<?=base_url(''.$agrement_link.'')?>"><i class="fa fa-edit"></i> Demande d'agrément</a>
    </li>
    <li  class="nav-item"> <!-- <?=$hidden?>  -->
      <a class="nav-link <?php if($menu == 'list_all' ) echo 'active' ?>" href="<?=base_url('list_all')?>"><i class="fa fa-book"></i> Enregistrement programme</a>
    </li>
    <li  class="nav-item"><!-- <?=$hidden?> -->
      <a class="nav-link <?php if($menu == 'projet' || $menu == 'espProjetAdd' || $menu == 'esp/espProjetDetail') echo 'active' ?>" href="<?=base_url('projet')?>"><i class="fa fa-folder-open"></i> Enregistrement projet</a>
    </li>
    <li class="nav-item">
      <a class="nav-link <?php if($menu == 'plainte' ) echo 'active' ?>" href="<?=base_url('plainte')?>"><i class="fa fa-bullhorn"></i>  Plaintes</a>
    </li>
  </ul>

</main>