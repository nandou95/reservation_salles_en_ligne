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
      width:          1px;
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
                    <a href="<?php echo base_url('ihm/Categorie_Libelle/liste_view')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> Liste</a>
                  </div>
                  <div class="car-body">
                  <h4 style="margin-left:4%;margin-top:10px"> <?=$titre?></h4>
                  <br>
              <div class=" container " style="width:90%">
                <form enctype='multipart/form-data' name="enj" id="Myform" action="<?=base_url('ihm/Categorie_Libelle/edit_categorie')?>" method="post" >
                  <div class="container">
                    <div class="row">
                      <input type="hidden" id="ID_CATEGORIE_LIBELLE" name="ID_CATEGORIE_LIBELLE" value="<?=$enjeux['ID_CATEGORIE_LIBELLE']?>">
                      <div class="col-md-12">
                        <div class="form-group">
                          <label for=""><?= lang('messages_lang.labelle_libelle') ?><font color="red">*</font></label>
                          <input type="text" class="form-control" name="CATEGORIE_LIBELLE1" value="<?=$enjeux['CATEGORIE_LIBELLE']?>" id="CATEGORIE_LIBELLE1">
                          <font color="red" id="error_CATEGORIE_LIBELLE"></font>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('CATEGORIE_LIBELLE'); ?>
                          <?php endif ?>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12 mt-5 " >
                      <div class="form-group " >
                        <a onclick="save_data()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.modifier_phase_comptable_prise_en_charge_comptable') ?></a>
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
<div class="modal fade" id="enjeux_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
    <div class="modal-body">
       <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.confirmation_modal_phase_comptable_prise_en_charge_comptable')?></h5>
      <div class="table-responsive  mt-3">
        <table class="table m-b-0 m-t-20">
          <tbody>
            <tr>
              <td><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.labelle_libelle')?></strong></td>
              <td id="CATEGORIE_LIBELLE_valide" class="text-dark"></td>
            </tr>    
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.modifier_phase_comptable_prise_en_charge_comptable') ?></button>
    <a onclick="finale_save()" style="float: right;margin: 2px" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.confirmer_phase_comptable_prise_en_charge_comptable') ?></a>
  </div>
</div>
</div>
</div>
<script type="text/javascript">
  function save_data()
  {
    var url;
    var CATEGORIE_LIBELLE = $('#CATEGORIE_LIBELLE1').val();
    $('#error_CATEGORIE_LIBELLE').html('');

    var statut=2;
    if (CATEGORIE_LIBELLE=='')
    {
      $('#error_CATEGORIE_LIBELLE').html('Le champ est obligatoire');
      statut=1;
    }
    
    if(statut == 2)
    {
      $('#CATEGORIE_LIBELLE_valide').html(CATEGORIE_LIBELLE);
      $("#enjeux_modal").modal("show");
    }
  }
</script>

<script type="text/javascript">
  function finale_save()
  {
    document.getElementById("Myform").submit();
  }
</script>