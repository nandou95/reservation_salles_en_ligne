<!DOCTYPE html>
  <html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
    <?php $validation = \Config\Services::validation(); ?>
  </head>
  <style>
    hr.vertical {
      border:         none;
      border-left:    1px solid hsla(200, 2%, 12%,100);
      height:         55vh;
      width:          1px; 
      color: #ddd
    }
  </style>
  <body>
    <div class="wrapper">
      <?php echo view('includesbackend/navybar_menu.php'); ?>
      <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
      <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
      <script src="/DataTables/datatables.js"></script>
      <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
      <div class="main">
        <?php echo view('includesbackend/navybar_topbar.php'); ?>
        <main class="content">
          <div class="container-fluid">
            <div class="header">
              <h1 class="header-title text-white"></h1>
            </div>
            <div class="row">
              <div class="col-12">
                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                  <div style="float: right;">
                    <a href="<?php echo base_url('ihm/Secteur_Intervention/list_view')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.link_list')?></a>
                  </div>
                  <div class="car-body">

                    <h4 style="margin-left:4%;margin-top:10px"> <?=$titre?></h4>
                    <br>
                    
              <div class=" container " style="width:90%">
                <form enctype='multipart/form-data' name="enj" id="enj" action="<?=base_url('ihm/Secteur_Intervention/edit_secteur')?>" method="post" >
                  <div class="container">
                    <div class="row">
                      <input type="hidden" id="ID_SECTEUR_INTERVENTION" name="ID_SECTEUR_INTERVENTION" value="<?=$enjeux['ID_SECTEUR_INTERVENTION']?>">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for=""><?= lang('messages_lang.ajout_secteur_intervention_lib')?><font color="red">*</font></label>
                          <input type="text" class="form-control" name="DESCR_SECTEUR" value="<?=$enjeux['DESCR_SECTEUR']?>" id="DESCR_SECTEUR">
                          <font color="red" id="error_SECTEUR"></font>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('DESCR_SECTEUR'); ?>
                          <?php endif ?>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12 mt-5 " >
                      <div class="form-group " >
                        <a onclick="save_SECTEUR()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_modifier')?></a>
                      </div>
                    </div>
                  </div>
                  </form><br><br>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>
<?php echo view('includesbackend/scripts_js.php'); ?>
</body>
</html>
<script type="text/javascript">
  function save_SECTEUR()
  {
    var DESCR_SECTEUR = $('#DESCR_SECTEUR').val();
    $('#error_DESCR_ENJEUX').html('');
    var statut=2;
    if (DESCR_SECTEUR=='')
    {
      $('#error_DESCR_ENJEUX').html('Le champ est obligatoire');
      statut=1;
    }
    var url;
    if(statut == 2)
    {
      $('#SECTEUR_valide').html(DESCR_SECTEUR);
      $("#enjeux_modal").modal("show");
    }
  }
</script>
<div class="modal fade" id="enjeux_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-body">
       <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.enregistre_action_confirmer')?></h5>
      <div class="table-responsive  mt-3">
        <table class="table m-b-0 m-t-20">
          <tbody>
            <tr>
              <td><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.ajout_secteur_intervention_lib')?></strong></td>
              <td id="SECTEUR_valide" class="text-dark"></td>
            </tr>    
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-footer">
  <button type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i><?= lang('messages_lang.bouton_modifier')?></button>
    <a onclick="finale_save()" style="float: right;margin: 2px" class="btn btn-info"><i class="fa fa-check"></i><?= lang('messages_lang.bouton_confirmer')?></a>
  </div>
</div>
</div>
</div>
<script type="text/javascript">
  function finale_save()
  {
    document.getElementById("enj").submit();
  }
</script>