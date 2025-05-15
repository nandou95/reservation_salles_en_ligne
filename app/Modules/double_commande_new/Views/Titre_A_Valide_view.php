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
                  <a href="<?php echo base_url('double_commande_new/Validation_Titre/liste_valide_faire')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                </div>
                <div class="car-body">

                  <h4 style="margin-left:4%;margin-top:10px"> <?=$etape_titre?></h4>
                  <br>
                  <!-- debut -->
                  
                  <div class="container" style="width:90%;">
                    <div id="accordion">
                      <div  class="card-header" id="headingThree" style="float: left;">
                        <h5 class="mb-0">
                          <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?= lang('messages_lang.histo_btn') ?></button>
                        </h5>
                      </div>  
                    </div><br><br>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                      <?php include  'includes/Detail_View.php'; ?>
                    </div>
                  </div>
                  <!-- fin -->
                  <br><br>
                  <div class=" container " style="width:90%">
                    <form enctype='multipart/form-data' name="titre_val" id="titre_val" action="<?=base_url('double_commande_new/Validation_Titre/save_titre_valider/')?>" method="post" >
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
                      } ?>
                      <div class="row" style="border:1px solid #ddd;border-radius:5px">
                        <div class="col-md-12 mt-2" style="margin-bottom:50px">
                          <div class="row">
                            <input type="hidden" id="id_exec_titr_dec" name="id_exec_titr_dec" value="<?=$info['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                            <input type="hidden" id="EXECUTION_BUDGETAIRE_ID" name="EXECUTION_BUDGETAIRE_ID" value="<?=$info['EXECUTION_BUDGETAIRE_ID']?>">
                            <input type="hidden" id="EXECUTION_BUDGETAIRE_DETAIL_ID" name="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?=$info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">
                            <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" id="ETAPE_DOUBLE_COMMANDE_ID" value="<?=$info['ETAPE_DOUBLE_COMMANDE_ID']?>">

                            <div class="col-md-4">
                              <label for=""><?= lang('messages_lang.label_date_rec') ?> ( <?= $retVal = ($info['MONTANT_PAIEMENT'] > 100000000) ? 'Ministre des finances' : 'DG finances publiques' ;?>)<font color="red">*</font></label>
                              <input type="date" value="<?=date('Y-m-d')?>" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?= date('Y-m-d')?>" onchange="changeDate();" class="form-control" onkeypress="return false" name="DATE_RECEPTION" id="DATE_RECEPTION">
                              <font color="red" id="error_DATE_RECEPTION"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_RECEPTION'); ?>
                              <?php endif ?>
                            </div>
                            <div class="col-md-4">
                              <label for=""><?= lang('messages_lang.titre_decaissement') ?></label>
                              <input type="text" class="form-control" onkeypress="return false" name="titre" id="titre" value="<?=$info['TITRE_DECAISSEMENT']?>" readonly>
                              <br>
                            </div>

                            <div class="col-md-4">
                              <label for=""><?=lang('messages_lang.col_eng_budg')?></label>
                              <input type="text" class="form-control" onkeypress="return false" name="bugdet" id="bugdet" value="<?= number_format($info['ENG_BUDGETAIRE'],2,',',' ')?>" readonly>
                              
                            </div>

                            <div class="col-md-4">
                              <label for=""><?= lang('messages_lang.col_eng_jur') ?></label>
                              <input type="text" class="form-control" onkeypress="return false" name="juri" id="juri" value="<?= number_format($info['ENG_JURIDIQUE'],2,',',' ')?>"readonly>
                              <br>
                            </div>

                            <div class="col-md-4">
                              <label for=""><?= lang('messages_lang.col_liquid') ?> </label>
                              <input type="text" class="form-control" onkeypress="return false" name="liquid" id="liquid" value="<?= number_format($info['MONTANT_LIQUIDATION'],2,',',' ')?>" readonly>
                              <br>
                            </div>

                            <div class="col-md-4">
                              <label for=""><?= lang('messages_lang.labelle_ordonan') ?> </label>
                              <input type="text" class="form-control" onkeypress="return false" name="ord" id="ord" value="<?= number_format($info['MONTANT_ORDONNANCEMENT'],2,',',' ')?>" readonly>
                              <br>
                            </div>

                            <div class="col-md-4">
                              <label for=""><?= lang('messages_lang.labelle_paiement') ?></label>
                              <input type="text" class="form-control" onkeypress="return false" name="paie" id="paie" value="<?= number_format($info['MONTANT_PAIEMENT'],2,',',' ')?>" readonly>
                              <br>
                            </div>

                              <div class="col-md-4">
                              <label for=""><?= lang('messages_lang.dat_val') ?> <font color="red">*</font></label>
                              <input type="date" class="form-control" onkeypress="return false" name="DATE_VALIDE_TITRE" value="<?= date('Y-m-d') ?>" id="DATE_VALIDE_TITRE" max="<?= date('Y-m-d')?>">
                              <font color="red" id="error_DATE_VALIDE_TITRE"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_VALIDE_TITRE'); ?>
                              <?php endif ?>
                            </div>

                            <div class="col-md-4">
                              <label for=""><?= lang('messages_lang.label_date_tra') ?><font color="red">*</font></label>
                              <input type="date" class="form-control" onkeypress="return false" name="DATE_TRANSMISSION" value="<?= date('Y-m-d') ?>" id="DATE_TRANSMISSION" max="<?= date('Y-m-d')?>">
                              <font color="red" id="error_DATE_TRANSMISSION"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_TRANSMISSION'); ?>
                              <?php endif ?>
                            </div>

                          </div>
                        </div>

                      </div> 
                      <div style="float: right;" class="col-md-2 mt-5 " >
                      <div class="form-group " >
                        <a onclick="save_titre_valide()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.label_enre') ?></a>
                      </div>
                    </div>         
                    </div> 
                    <br>
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
  function changeDate()
  {
    $('#DATE_TRANSMISSION').prop('min', $('#DATE_RECEPTION').val()); 
    $('#DATE_VALIDE_TITRE').prop('min', $('#DATE_RECEPTION').val()); 

  }
</script>

<script type="text/javascript">
  function number()
  {
    var NUMERO_BON_ENGAGEMENT = $('#NUMERO_BON_ENGAGEMENT').val();
    $('#error_NUMERO_BON_ENGAGEMENT').html('');

    if (NUMERO_BON_ENGAGEMENT.length > 20)
    {
     $('#error_NUMERO_BON_ENGAGEMENT').html("<?=lang('messages_lang.numer_eng')?>");
     statut=1;
   }
 }
</script>

<script type="text/javascript">
  $('#message').delay('slow').fadeOut(3000);
</script >

<script type="text/javascript">
  function save_titre_valide()
  {

    var titre = $('#titre').val();
    var bugdet = $('#bugdet').val();
    var juri = $('#juri').val();
    var liquid = $('#liquid').val();
    var ord = $('#ord').val();
    var paie = $('#paie').val();

    var DATE_VALIDE_TITRE = $('#DATE_VALIDE_TITRE').val();
    $('#error_DATE_VALIDE_TITRE').html('');
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    $('#error_DATE_TRANSMISSION').html('');

    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    $('#error_DATE_RECEPTION').html('');

    var statut=2;

    if (DATE_RECEPTION=='') 
    {
      $('#error_DATE_RECEPTION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

    if (DATE_VALIDE_TITRE=='') 
    {
      $('#error_DATE_VALIDE_TITRE').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }
    if (DATE_TRANSMISSION=='')
    {
      $('#error_DATE_TRANSMISSION').html("<?=lang('messages_lang.input_oblige')?>");
      statut=1;
    }

   var url;
   if(statut == 2)
   {
      var DATE_RECEPTION = moment(DATE_RECEPTION, "YYYY/mm/DD");
      var DATE_RECEPTION = DATE_RECEPTION.format("DD/mm/YYYY");
      var DATE_VALIDE_TITRE = moment(DATE_VALIDE_TITRE, "YYYY/mm/DD");
      var DATE_VALIDE_TITRE = DATE_VALIDE_TITRE.format("DD/mm/YYYY");
      var DATE_TRANSMISSION = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
      var DATE_TRANSMISSION = DATE_TRANSMISSION.format("DD/mm/YYYY");

      $('#titre_verifie').html(titre);
      $('#bugdet_verifie').html(bugdet);
      $('#juri_verifie').html(juri);
      $('#liquid_verifie').html(liquid);
      $('#ord_verifie').html(ord);
      $('#paie_verifie').html(paie);

      $('#DATE_VALIDE_TITRE_verifie').html(DATE_VALIDE_TITRE);
      $('#DATE_TRANSMISSION_verifie').html(DATE_TRANSMISSION);
      $('#DATE_RECEPTION_verifie').html(DATE_RECEPTION);

      $("#titre_vali").modal("show");
   }

 }
</script>


<div class="modal fade" id="titre_vali" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
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
              <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_rec') ?> ( <?= $retVal = ($info['MONTANT_PAIEMENT'] > 100000000) ? 'Ministre de la finance' : 'DG finances publiques' ;?>)</strong></td>
              <td id="DATE_RECEPTION_verifie" class="text-dark"></td>
            </tr>
          <tr>
            <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.dat_val') ?></strong></td>
            <td id="DATE_VALIDE_TITRE_verifie" class="text-dark"></td>
          </tr>
            <tr>
             <td style="width:400px ;"><i class="fa fa-certificate"></i>&nbsp;<strong><?= lang('messages_lang.titre_decaissement') ?></strong></td>
             <td id="titre_verifie" class="text-dark"></td>
           </tr>
           <tr>
             <td style="width:400px ;"><i class="fa fa-credit-card"></i>&nbsp;<strong><?= lang('messages_lang.col_eng_budg') ?></strong></td>
             <td id="bugdet_verifie" class="text-dark"></td>
           </tr>
           <tr>
             <td style="width:400px ;"><i class="fa fa-credit-card"></i>&nbsp;<strong><?= lang('messages_lang.col_eng_jur') ?></strong></td>
             <td id="juri_verifie" class="text-dark"></td>
           </tr>
           <tr>
             <td style="width:400px ;"><i class="fa fa-credit-card"></i>&nbsp;<strong><?= lang('messages_lang.col_liquid') ?></strong></td>
             <td id="liquid_verifie" class="text-dark"></td>
           </tr>
           <tr>
             <td style="width:400px ;"><i class="fa fa-credit-card"></i>&nbsp;<strong><?= lang('messages_lang.labelle_ordonan') ?></strong></td>
             <td id="ord_verifie" class="text-dark"></td>
           </tr>
           <tr>
            <td><i class="fa fa-credit-card"></i> &nbsp;<strong><?= lang('messages_lang.labelle_paiement') ?></strong></td>
            <td id="paie_verifie" class="text-dark"></td>
          </tr>
          <tr>
            <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_tra') ?></strong></td>
            <td id="DATE_TRANSMISSION_verifie" class="text-dark"></td>
          </tr>
        </tr>
      </tbody>
    </table>
  </div>
</div>
<div class="modal-footer">
  <button id="mod" type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
  <a id="myElement" onclick="save_etap2();hideButton()" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
</div>
</div>
</div>
</div>


<script type="text/javascript">
  function save_etap2()
  {
    document.getElementById("titre_val").submit();
  }
</script>

<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
  }
</script>
