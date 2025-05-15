<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
</head>
<body>
  <div class="wrapper" >
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="showcase">
        <div class="container-fluid showgrid">
          <div class="showcase-text text-center">
            <P><?=lang('messages_lang.bienvenue_plateforme_ptba_pip')?></P>
          </div>
        </div>
      </main>

      <div class="container showmenu">
        <div class="showgrid showgrid-menu text-center">
          <i class=" fa fa-tasks"></i>
          <h4><?=lang('messages_lang.label_droit_double_com')?></h4>
        </div>

        <div class="showgrid showgrid-menu text-center">
          <i class=" fa fa-list"></i>
          <h4><?=lang('messages_lang.planification_cdmt_cbmt')?></h4>
        </div>

        <div class="showgrid showgrid-menu text-center">
          <i class=" fa fa-bars"></i>
          <h4><?=lang('messages_lang.planification_strategique_sectoriel')?></h4>
        </div>

        <div class="showgrid showgrid-menu text-center ">
          <i class="fa fa-indent"></i>
          <h4><?=lang('messages_lang.titre_demande')?></h4>
        </div>

        <div class="showgrid showgrid-menu text-center ">
          <i class="fa fa-file"></i>
          <h4><?=lang('messages_lang.th_programme')?></h4>
        </div>
      </div>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>