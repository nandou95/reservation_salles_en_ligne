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
                    <div class="col-md-10">
                      <h3 class="header-title text-black"><?= lang('messages_lang.labelle_phas') ?>:<?php echo $etape['DESC_ETAPE_DOUBLE_COMMANDE'] ?></h3>
                    </div>
                    <div style="float: right;">
                        <a href="<?php echo base_url('double_commande_new/Liste_Paiement') ?>" style="float: right; width: 100px; margin-right: 20px;margin-top:25px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?= lang('messages_lang.bouton_liste')?></a>
                    </div>
                    
                  </div>
                </div>
                <div style="margin: 20px;margin-top: 0px"><hr></div>
                
                  <!-- debut -->
                  <div style="width:100%">
                      <div id="accordion">
                        <div class="card-header" id="headingThree" style="padding: 0; display: flex; justify-content: space-between">
                          <h5 class="mb-0">
                            <button style="background:#061e69; color:#fff; font-weight: 500; margin-left: 30px; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?= lang('messages_lang.lab_hist') ?>
                            </button>
                          </h5>
                        </div>
                      </div>
                      <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                      <?php include  'includes/Detail_View.php'; ?> 
                    </div>
                  </div>
                  <!--fin-->
                  
                <div class="car-body">
                  <form id='MyFormData' enctype='multipart/form-data' action="<?=base_url('double_commande_new/Phase_comptable/Td_Signe_Ministre/inserer') ?>" method="POST">
                    <input type="hidden" name="id_raccrochage" id="id_raccrochage" value="<?=$etape['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                    <input type="hidden" name="etape" id="etape" value="<?=$etape['ETAPE_DOUBLE_COMMANDE_ID']?>">
                    <div class="card-body">
                      <div class="row">

                        <div class="col-md-4"><br>
                          <label><?= lang('messages_lang.labelle_date_rech') ?><span style="color: red;">*</span></label>
                          <input type="date" class="form-control" name="DATE_RECEPTION" value="<?=set_value('DATE_RECEPTION')?>" min="<?php echo date('Y-m-d',strtotime($hist['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" id="DATE_RECEPTION" onkeypress="return false" onblur="this.type='date'" onchange="get_date_min_trans()">
                          <span id="error_dat_rec" class="text-danger"></span>
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('DATE_RECEPTION'); ?>
                          <?php endif ?>
                        </div>
                        <div class="col-md-4"><br>
                          <label for=""><?= lang('messages_lang.labelle_date_sign') ?><span style="color: red;">*</span></label>
                          <input type="date" class="form-control" id="DATE_SIGNATURE" name="DATE_SIGNATURE" value="<?=set_value('DATE_SIGNATURE')?>" max="<?=date('Y-m-d')?>">
                          <span class="text-danger" id="error_dat_sign"></span>
                          <?= $validation->getError('DATE_SIGNATURE'); ?>
                        </div>
                        <div class="col-md-4"><br>
                          <label><?= lang('messages_lang.labelle_date_tansmiss') ?><span style="color: red;">*</span></label>
                          <input type="date" class="form-control" name="DATE_TRANSMISSION" max="<?=date('Y-m-d')?>" id="DATE_TRANSMISSION" value="<?=set_value('DATE_TRANSMISSION')?>" onkeypress="return false" onblur="this.type='date'">
                          <?php if (isset($validation)) : ?>
                            <?= $validation->getError('DATE_TRANSMISSION'); ?>
                          <?php endif ?>
                          <span id="error_dat_trans" class="text-danger"></span>
                        </div>
                        
                      </div>
                    </div>
                  </form>
                  <div class="card-footer">
                    <div style="float:right;margin-bottom:5%">
                      <a onclick="save();" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.btn_enr') ?></a>
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
  <!--******* Modal pour confirmer les infos saisies *********-->
  <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.titre_modal') ?></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="table-responsive  mt-3">
            <table class="table m-b-0 m-t-20">
              <tbody>
                <tr>
                  <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_date_rech') ?></strong></td>
                  <td id="date_rec_verifie" class="text-dark"></td>
                </tr>
                <tr>
                  <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_date_sign') ?></strong></td>
                  <td id="date_sign_verifie" class="text-dark"></td>
                </tr>
                <tr>
                  <td><i class="fa fa-calendar"></i> <?= lang('messages_lang.labelle_date_tansmiss') ?></strong></td>
                  <td id="date_trans_verifie" class="text-dark"></td>
                </tr>
                
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
                <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.bouton_modifier')?></button>
                <a id="myElement" onclick="confirm();hideButton()"style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.bouton_confirmer')?></a>
            </div>
        
      </div>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>

<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
  }
</script>
<script>
  //function pour donner la date minimum de transmission
  function get_date_min_trans()
  {
    $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
    $("#DATE_SIGNATURE").prop('min',$("#DATE_RECEPTION").val());
  }
</script>
<script type="text/javascript">
  function save()
  {
    var DATE_RECEPTION  = $('#DATE_RECEPTION').val();
    var DATE_TRANSMISSION  = $('#DATE_TRANSMISSION').val();
    var DATE_SIGNATURE  = $('#DATE_SIGNATURE').val();

    $('#error_dat_rec').html(''); 
    $('#error_dat_trans').html('');
    $('#error_dat_sign').html(''); 

    var statut = 2;
    if(DATE_RECEPTION=='')
    {
      $('#error_dat_rec').html('<?= lang('messages_lang.error_sms') ?>');
      statut = 1;
    }

    if(DATE_TRANSMISSION  == '')
    {
      $('#error_dat_trans').html('<?= lang('messages_lang.error_sms') ?>');
      statut = 1;
    }
    if(DATE_SIGNATURE  == '')
    {
      $('#error_dat_sign').html('<?= lang('messages_lang.error_sms') ?>');
      statut = 1;
    }
    if(statut == 2)
    {
      $('#myModal').modal('show');
      var date_rec = moment(DATE_RECEPTION, "YYYY/mm/DD");
      var result_rec = date_rec.format("DD/mm/YYYY");
      $('#date_rec_verifie').html(result_rec);

      var date_trans = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var result_trans = date_trans.format("DD/mm/YYYY");
      $('#date_trans_verifie').html(result_trans);

      var date_sign = moment(DATE_SIGNATURE, "YYYY/mm/DD");
      var result_sign = date_trans.format("DD/mm/YYYY");
      $('#date_sign_verifie').html(result_sign);
    }
  }
</script>
<script>
  function confirm(){
   $('#MyFormData').submit();
 }
</script>

