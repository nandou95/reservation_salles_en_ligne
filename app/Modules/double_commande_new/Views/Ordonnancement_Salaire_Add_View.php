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
      width: 1px;
      color: #ddd
    }
  </style>
  <body>
    <div class="wrapper">
      <?php echo view('includesbackend/navybar_menu.php'); ?>
      <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
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
                    <a href="<?php echo base_url('double_commande_new/Ordonnancement_Salaire_Liste/index_A_Faire')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">
                    <h4 style="margin-left:4%;margin-top:7px"> <?=$etape?></h4>
                    <br>
                    
                    <div class=" container " style="width:90%">
                      <form enctype='multipart/form-data' name="myForm" id="myForm" action="<?=base_url('double_commande_new/Ordonnancement_Salaire/save')?>" method="post" >
                        <div class="container">
                          <?php
                          if(session()->getFlashKeys('alert'))
                          {
                            ?>
                            <center class="ml-5" style="height=100px;width:90%" >
                              <div class="w-100 bg-danger text-white text-center"  id="message">
                                <?php echo session()->getFlashdata('alert')['message']; ?>
                              </div>
                            </center>
                            <?php
                          }?>
                          <input type="hidden" name="EXECUTION_BUDGETAIRE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>">
                          <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" value="<?=$info['ETAPE_DOUBLE_COMMANDE_ID']?>">
                          <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?=$info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                          <input type="hidden" name="LIQUIDATION" value="<?=$info['LIQUIDATION']?>">
                          <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$info['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-4 mt-3 ml-2"style="margin-bottom:50px" >
                              <div class="col-md-12" >
                                <div class="row">
                                  <!--  input  -->
                                </div><br>       
                              </div>
                              <div class="col-md-12" id="tableau">
                                <table class="table table-responsive">
                                  <thead>
                                    <tr>
                                      <th><?=lang('messages_lang.code_economique')?></th>
                                      <th class="text-lowercase"><?=lang('messages_lang.labelle_montant')?></th>                     
                                    </tr>
                                  </thead>
                                  <tbody>
                                    <?php
                                      function get_precision($value=0)
                                      {
                                        $string = strval($value);
                                        $number=explode('.',$string)[1] ?? '';
                                        $precision='';
                                        for($i=1;$i<=strlen($number);$i++)
                                        {
                                          $precision=$i;
                                        }
                                        if(!empty($precision)) 
                                        {
                                          return $precision;
                                        }
                                        else
                                        {
                                          return 0;
                                        }    
                                      }
                                      $MONTANT_LIQUIDATION=0;
                                      foreach ($get_data as $key)
                                      {
                                        $MONTANT_LIQUIDATION +=floatval($key->MONTANT_LIQUIDATION);
                                        echo '<tr>
                                        <td>'.$key->CODE_SOUS_LITTERA.'</td>
                                        <td>'.number_format($key->MONTANT_LIQUIDATION,get_precision($key->MONTANT_LIQUIDATION),',',' ').'</td>
                                        </tr>';
                                      }
                                      echo '<tr><th class="text-uppercase">'.lang('messages_lang.labelle_total').'</th>
                                        <th>'.number_format($MONTANT_LIQUIDATION,get_precision($MONTANT_LIQUIDATION),',',' ').'</th>
                                        </tr>';
                                    ?>
                                  </tbody>
                                </table>
                              </div>
                            </div><hr class="vertical">
                            <div class="col-md-7 mt-2" style="margin-bottom:50px;margin-left:-40px">
                              <br><br>
                              <div class="col-md-12">
                                <label for=""><?= lang('messages_lang.label_date_rec') ?> <font color="red">*</font></label>
                                <input type="date" value="<?= date('Y-m-d')?>" min="<?//=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?= date('Y-m-d')?>" onchange="changeDate();" class="form-control" onkeypress="return false" name="DATE_RECEPTION" id="DATE_RECEPTION">
                                <font color="red" id="error_DATE_RECEPTION"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('DATE_RECEPTION'); ?>
                                <?php endif ?>
                              </div>
                              <div class="col-md-12">
                                <div class='form-froup'>
                                  <label class="form-label"><?= lang('messages_lang.label_decision') ?> <font color="red">*</font></label>
                                  <select onchange="rejet();" class="form-control" name="ID_OPERATION" id="ID_OPERATION">
                                    <option value=""><?= lang('messages_lang.label_select') ?></option>
                                    <?php  foreach ($get_operation as $keys) { ?>
                                      <?php if($keys->ID_OPERATION==set_value('ID_OPERATION')) { ?>
                                        <option value="<?=$keys->ID_OPERATION ?>" selected>
                                          <?=$keys->DESCRIPTION?></option>
                                        <?php }else{?>
                                         <option value="<?=$keys->ID_OPERATION ?>">
                                          <?=$keys->DESCRIPTION?></option>
                                        <?php } }?>
                                  </select>
                                  <font color="red" id="error_ID_OPERATION"></font>
                                  <?php if (isset($validation)) : ?>
                                    <?= $validation->getError('ID_OPERATION'); ?>
                                  <?php endif ?>
                                </div>
                                <br>
                              </div>
                              <div class="col-md-12">
                                <label for=""><?= lang('messages_lang.label_date_tra') ?><font color="red">*</font></label>
                                <input type="date" value="<?= date('Y-m-d')?>" max="<?= date('Y-m-d')?>"  class="form-control" onkeypress="return false" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION">
                                <font color="red" id="error_DATE_TRANSMISSION"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('DATE_TRANSMISSION'); ?>
                                <?php endif ?>
                                <br>
                              </div>
                              <div style="float: right;" class="col-md-6" >
                                <br>
                                <div class="form-group " >
                                  <a onclick="savesalaire()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><b id="loading_save"></b> <?= lang('messages_lang.label_enre') ?></a>
                                </div>
                              </div>
                            </div>                                                      
                          </div>                                                    
                        </div>                        
                      </form>
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
<script>
  function savesalaire()
  {
    var ID_OPERATION = $('#ID_OPERATION').val();
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();

    $('#error_ID_OPERATION').html('');    
    $('#error_DATE_TRANSMISSION').html('');    
    $('#error_DATE_RECEPTION').html('');

    var statut=2
    if (DATE_RECEPTION=='') 
    {
      $('#error_DATE_RECEPTION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (DATE_TRANSMISSION=='')
    {
      $('#error_DATE_TRANSMISSION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (ID_OPERATION=='')
    {
      $('#error_ID_OPERATION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if(statut==2)
    {
      var DATE_RECEPTION = moment(DATE_RECEPTION, "YYYY/mm/DD");
      var DATE_RECEPTION = DATE_RECEPTION.format("DD/mm/YYYY");

      var DATE_TRANSMISSION = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var DATE_TRANSMISSION = DATE_TRANSMISSION.format("DD/mm/YYYY");
      $('#ID_OPERATION_verifie').html($('#ID_OPERATION option:selected').text());
      $('#DATE_RECEPTION_verifie').html(DATE_RECEPTION);
      $('#DATE_TRANSMISSION_verifie').html(DATE_TRANSMISSION);

      $("#my_modal").modal("show");
    }
  }
</script>
<div class="modal fade" id="my_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.label_titre') ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive  mt-3">
          <table class="table m-b-0 m-t-20">
            <tbody>
              <tr>
                <td><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.label_decision') ?></strong></td>
                <td id="ID_OPERATION_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_rec') ?> </strong></td>
                <td id="DATE_RECEPTION_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_tra') ?> </strong></td>
                <td id="DATE_TRANSMISSION_verifie" class="text-dark"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="edi" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
        <a onclick="save();hideButton()" id="conf" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  function save()
  {
    document.getElementById("myForm").submit();
  }
</script>
<script>
  function hideButton()
  {
    var element = document.getElementById("conf");
    element.style.display = "none";

    var elementmod = document.getElementById("edi");
    elementmod.style.display = "none";
  }
</script>