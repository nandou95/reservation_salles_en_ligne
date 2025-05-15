<style type="text/css">
  @media screen and (min-width: 1024px)
  {
    .border-right-card
    {
      border-right: 1px solid lightgrey;
    }

    .card-height-desktop
    {
      height: 200px;
    }
  }
</style>
<nav class="navbar navbar-expand navbar-theme">
  <a class="sidebar-toggle d-flex mr-2">
    <i class="hamburger align-self-center"></i>
  </a>
  <div class="navbar-collapse collapse">
    <ul class="navbar-nav ml-auto mr-2 navbar-btns">
      <li class="nav-item dropdown ml-lg-2 ">
        <button  class="btn btn1" onclick="window.location.href='<?=base_url('Change_Password')?>'">  <i class="fa fa-key" aria-hidden="true"></i> <?=lang('messages_lang.bouton_mot_passe')?></button>
      </li>

      <li class="nav-item dropdown ml-lg-2">
        <button class="btn btn2" onclick="window.location.href='<?=base_url('Login_Ptba/do_logout')?>'"> <span class="fa-bg"><i class="fa fa-sign-out"></i></span> <?=lang('messages_lang.bouton_deconnection')?></button>
      </li>

      <li class="nav-item dropdown ml-lg-2 ">
        <!-- <a href="<?=base_url('lang/french');?>"><img width="12rem" class="btn-img" src="<?=base_url()?>/template/img/france.png"></a> -->
      </li>

      <li class="nav-item dropdown ml-lg-2 ">
        <!-- <a href="<?=base_url('lang/english');?>"><img width="12rem" class="btn-img" src="<?=base_url()?>/template/img/british.jpg"></a> -->
      </li>
    </ul>
  </div>
</nav>