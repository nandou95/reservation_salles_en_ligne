<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
    <?php $validation = \Config\Services::validation(); ?>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
          </div>
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-6">
                      <h1 class="header-title text-black">Transmission au GDC</h1>
                    </div>
                    <div class="col-md-3" style="float: left;">
                      <br>
                      <a href="<?=base_url('double_commande_new/Ordonnancement_Vers_Ced/liste')?>" style="float: right;margin: 4px" class="btn btn-primary"><i class="fa fa-list text-light" aria-hidden="true"></i> <?= lang('messages_lang.link_list') ?> </a>
                    </div>
                  </div>                  
                </div><hr>
                <div id="accordion">
                  <div class="card-header" id="headingThree" style="float: left;">
                    <h5 class="mb-0">
                      <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?=lang('messages_lang.histo_btn')?>
                      </button>
                    </h5>
                  </div>
                </div>
                <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                  <?php include  'includes/Detail_View.php'; ?>
                </div><br><br>
                <div class="car-body">
                  <form id='MyFormData' action="<?=base_url('double_commande_new/Ordonnancement_Vers_Ced/save') ?>" method="POST">
                    <!-- inputs hidden -->
                    <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$info['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                    <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>">
                    <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?=$info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                    <input type="hidden" name="ETAPE_ACTUELLE" value="<?=$info['ETAPE_DOUBLE_COMMANDE_ID']?>">
                    <!-- inputs hidden -->
                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-4">
                          <div class="form-group">
                            <label><?= lang('messages_lang.lab_d_rec') ?> <span style="color: red;">*</span></label>
                            <input type="date" name="DATE_RECEPTION" id="DATE_RECEPTION" min="<?=date('Y-m-d', strtotime($histo['DATE_TRANSMISSION']))?>" value="<?= date('Y-m-d') ?>" class="form-control" max="<?=date('Y-m-d')?>" onchange="get_date_min_trans()">
                            <span id="error_DATE_RECEPTION" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('DATE_RECEPTION'); ?>
                            <?php endif ?>
                          </div>
                        </div>  
                        <div class="col-md-4">
                          <div class="form-group">
                            <label><?= lang('messages_lang.label_etape_corriger') ?> <span style="color: red;">*</span></label>
                            <select type="date" name="ETAPE_CORRIGE" id="ETAPE_CORRIGE" class="form-control">
                              <option value=""><?=lang('messages_lang.label_select')?></option>
                              <?php
                                foreach ($etape as $key) {
                                  echo"<option value=".$key->ETAPE_RETOUR_CORRECTION_ID.">".$key->DESCRIPTION_ETAPE_RETOUR."</option>";
                                }
                              ?>
                            </select>
                            <span id="error_ETAPE_CORRIGE" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('ETAPE_CORRIGE'); ?>
                            <?php endif ?>
                          </div>
                        </div>
                        <div class="col-md-4">
                          <div class="form-group">
                            <label><?= lang('messages_lang.labelle_date_tansmiss') ?> <span style="color: red;">*</span></label>
                            <input type="date" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION" class="form-control" value="<?= date('Y-m-d') ?>" max="<?=date('Y-m-d')?>">
                            <span id="error_DATE_TRANSMISSION" class="text-danger"></span>
                            <?php if (isset($validation)) : ?>
                              <?= $validation->getError('DATE_TRANSMISSION'); ?>
                            <?php endif ?>
                          </div>
                        </div>                         
                      </div>
                    </div>
                    </form>
                    <div class="card-footer">
                      <div style="float:right;margin-bottom:5%">
                        <a onclick="save();" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_enregistrer') ?></a>
                      </div>
                    </div>
                  
                </div>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
//function pour donner la date minimum de transmission
  function get_date_min_trans()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
  }
</script>
<script type="text/javascript">
  function save()
  {
    var DATE_TRANSMISSION  = $('#DATE_TRANSMISSION').val();
    var DATE_RECEPTION  = $('#DATE_RECEPTION').val();
    var ETAPE_CORRIGE  = $('#ETAPE_CORRIGE').val();

    $('#error_DATE_TRANSMISSION').html('');
    $('#error_DATE_RECEPTION').html('');
    $('#error_ETAPE_CORRIGE').html('');

    var statut=2;
    if (DATE_TRANSMISSION=='') {
      $('#error_DATE_TRANSMISSION').html('<?= lang('messages_lang.validation_message')?>');
      statut=1;
    }
    if (DATE_RECEPTION=='') {
      $('#error_DATE_RECEPTION').html('<?= lang('messages_lang.validation_message')?>');
      statut=1;
    }
    if (ETAPE_CORRIGE=='') {
      $('#error_ETAPE_CORRIGE').html('<?= lang('messages_lang.validation_message')?>');
      statut=1;
    }

    if (statut==2) 
    {
      $('#myModal').modal('show');
      $('#DATE_TRANSMISSION_verifie').html(moment(DATE_TRANSMISSION, "YYYY/mm/DD").format("DD/mm/YYYY"));
      $('#DATE_RECEPTION_verifie').html(moment(DATE_RECEPTION, "YYYY/mm/DD").format("DD/mm/YYYY"));
      $('#ETAPE_CORRIGE_verifie').html($('#ETAPE_CORRIGE option:selected').text());
    }
  }
</script>
<script>
  function confirm(){
    $('#MyFormData').submit();
  }
</script>
<!--******* Modal pour confirmer les infos saisies *********-->
  <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_confirmation')?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="table-responsive  mt-3">
            <table class="table m-b-0 m-t-20">
              <tbody>
                <tr>
                  <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.lab_d_rec')?></strong></td>
                  <td id="DATE_RECEPTION_verifie" class="text-dark"></td>
                </tr>
                <tr>
                  <td><i class="fa fa-list"></i> &nbsp;<strong><?= lang('messages_lang.label_etape_corriger')?></strong></td>
                  <td id="ETAPE_CORRIGE_verifie" class="text-dark"></td>
                </tr>
                <tr>
                  <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.labelle_date_tansmiss')?></strong></td>
                  <td id="DATE_TRANSMISSION_verifie" class="text-dark"></td>
                </tr>                  
              </tbody>
            </table>
          </div>
        </div>

        <div class="modal-footer">
            <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"><i class="fa fa-edit" aria-hidden="true"></i>Modifier</button>
            <a id="myElement" onclick="confirm();hideButton()" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i>Confirmer</a>
        </div> 
      </div>
    </div>
  </div>

