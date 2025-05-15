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
                    <a href="<?php echo base_url('double_commande_new/Menu_Engagement_Budgetaire/Eng_Budg_Deja_Valide')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.liste_btn') ?></a>
                  </div>
                  <div class="car-body">

                    <h4 style="margin-left:4%;margin-top:10px"> <?=$etape2?></h4>
                    <br>
                    <!-- debut -->

                    <div class="container" style="width:90%">
                      <div id="accordion">
                        <div class="card-header" id="headingThree" style="float: left;">
                          <h5 class="mb-0">
                            <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?= lang('messages_lang.histo_btn') ?></button>
                          </h5>
                        </div>  
                      </div><br><br>
                      <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                        <?php include  'includes/Detail_View.php'; ?>
                      </div>
                    </div>
                  </div>
                  <br><br>
                  <!-- fin -->
                  <div class=" container " style="width:90%">
                    <form enctype='multipart/form-data' name="myEtape2" id="myEtape2" action="<?=base_url('double_commande_new/Phase_Administrative_Budget/save_etape2/')?>" method="post" >
                      <div class="container">
                        <input type="hidden" name="TYPE_ENGAGEMENT_ID" id="TYPE_ENGAGEMENT_ID" value="<?=$get_date_eng['TYPE_ENGAGEMENT_ID']?>">
                        <input type="hidden" name="MARCHE_PUBLIQUE" id="MARCHE_PUBLIQUE" value="<?=$info['MARCHE_PUBLIQUE']?>">
                        
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

                              <input type="hidden" id="EXECUTION_BUDGETAIRE_ID" name="EXECUTION_BUDGETAIRE_ID" value="<?=$EXECUTION_BUDGETAIRE_ID?>">
                              <input type="hidden" name="ETAPE_DOUBLE_COMMANDE_ID" id="ETAPE_DOUBLE_COMMANDE_ID" value="<?=$info['ETAPE_DOUBLE_COMMANDE_ID']?>">

                              <input type="hidden" name="EXECUTION_BUDGETAIRE_DETAIL_ID" name="EXECUTION_BUDGETAIRE_DETAIL_ID" value="<?= $info['EXECUTION_BUDGETAIRE_DETAIL_ID']?>">

                              <input type="hidden" name="verifier" id="verifier" value="<?=$count_verifier?>">
                            </div>
                            <br>
                            <label class="form-label"><?= lang('messages_lang.label_verif') ?><font color="red">*</font></label>
                            <div class="row">
                              <?php foreach($get_verifie as $grande):?>
                               <div class='col-md-4'>
                                <div class="form-froup d-flex">
                                  <input type="checkbox" id="TYPE_ANALYSE_ID" name="grande[]" value="<?= $grande->TYPE_ANALYSE_ID ?>">
                                  <label class="ml-2" for=""><?= $grande->DESC_TYPE_ANALYSE?></label>
                                </div>
                              </div>
                            <?php endforeach ?>

                            <div class="col-md-12">
                              <div class="form-froup">
                                <font color="red" id="error_TYPE_ANALYSE_ID"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('grande[]'); ?>
                                <?php endif ?>
                              </div>
                            </div>
                          </div>
                          <br>
                          <div class="row">
                            <div class="col-md-6">
                              <label for=""><?= lang('messages_lang.label_date_rec') ?> <font color="red">*</font></label>
                              <input type="date" value="<?= date('Y-m-d')?>" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?= date('Y-m-d')?>" onchange="changeDate();" class="form-control" onkeypress="return false" name="DATE_RECEPTION" id="DATE_RECEPTION">
                              <font color="red" id="error_DATE_RECEPTION"></font>
                              <?php if (isset($validation)) : ?>
                                <?= $validation->getError('DATE_RECEPTION'); ?>
                              <?php endif ?>
                            </div>
                            <div class="col-md-6">
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
                                <div class="col-md-6" id="motive" hidden="true">
                                  <label for=""> <?= lang('messages_lang.label_retour') ?></label><font color="red">*</font><span id="loading_motif"></span>
                                  <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)'>
                                   <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                                   <?php
                                   foreach($get_motif as $value)
                                   { 
                                    if($value->TYPE_ANALYSE_MOTIF_ID==set_value('TYPE_ANALYSE_MOTIF_ID')){?>
                                      <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>" selected><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                                    <?php }else                                
                                    {
                                      ?>
                                      <option value="<?=$value->TYPE_ANALYSE_MOTIF_ID ?>"><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
                                      <?php
                                    }
                                  }
                                  ?>
                                </select>
                                <br>
                                <span id="autre_motif" class="col-md-12 row" style="display: none">
                                  <div class="col-md-9">
                                    <input type="text" class="form-control" id="DESCRIPTION_MOTIF" placeholder="Autre motif" name="DESCRIPTION_SERIE">
                                  </div>
                                  <div class="col-md-2" style="margin-left: 5px;">
                                    <button type="button" class="btn btn-success" onclick="save_newMotif()"><i class="fa fa-plus"></i></button>
                                  </div>
                                </span>
                                <font color="red" id="error_motif"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('motive'); ?>
                                <?php endif ?>
                              </div>
                              <div class="col-md-6">
                                <label for=""><?= lang('messages_lang.label_date_tra') ?> (GDC)<font color="red">*</font></label>
                                <input type="date" value="<?= date('Y-m-d')?>" max="<?= date('Y-m-d')?>"  class="form-control" onkeypress="return false" name="DATE_TRANSMISSION" id="DATE_TRANSMISSION">
                                <font color="red" id="error_DATE_TRANSMISSION"></font>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('DATE_TRANSMISSION'); ?>
                                <?php endif ?>
                                <br>
                              </div>
                              <div class="col-md-6">
                                <label for=""> <?= lang('messages_lang.label_observ') ?> <font color="red" id="required"></font></label>
                                <textarea maxlength="255" class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"></textarea>
                                <font color="red" id="error_COMMENTAIRE"></font>
                              </div>
                            </div>
                          </div>

                        </div> 
                        <div style="float: right;" class="col-md-2 mt-5 " >
                          <div class="form-group " >
                            <a onclick="saveEtape2()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.label_enre') ?></a>
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
  $(document).ready(function () {
   var ID_OPERATION =$('#ID_OPERATION').val() ;
   var MARCHE_PUBLIQUE =$('#MARCHE_PUBLIQUE').val() ;
   var TYPE_ENGAGEMENT_ID = $('#TYPE_ENGAGEMENT_ID').val();

   if (ID_OPERATION==1)
   {
    $('#motive').attr('hidden',false);
  }
  else
  {
    $('#motive').attr('hidden',true);
  }

  if (TYPE_ENGAGEMENT_ID == 1)
  {
    $('#salaire_doc').show();
  }
  else
  {
    $('#salaire_doc').hide();
  }

  if (MARCHE_PUBLIQUE == 1)
  {
    $('#docu_ppm12').show();
  }
  else
  {
    $('#docu_ppm12').hide();
  }
});
</script>
<script type="text/javascript">
  function changeDate()
  {
    $('#DATE_TRANSMISSION').prop('min', $('#DATE_RECEPTION').val());
  }
</script>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(30000);
</script >
<script type="text/javascript">
 function rejet(){
  var ID_OPERATION =$('#ID_OPERATION').val() ;

  if (ID_OPERATION==1)
  {
    $('#motive').attr('hidden',false);
  }
  else
  {
    $('#motive').attr('hidden',true);
  }

  if (ID_OPERATION==3)
  {
    $('#required').text("*");
  }else{
    $('#required').text("");
  }

  $('#TYPE_ANALYSE_MOTIF_ID').val([]).trigger('change');
  $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
  $('#autre_motif').delay(100).hide('show');

}
</script>

<script type="text/javascript">
  function saveEtape2()
  {

    var verifier = $('#verifier').val();
    var COMMENTAIRE= $('#COMMENTAIRE').val();
    var ID_OPERATION = $('#ID_OPERATION').val();
    $('#error_ID_OPERATION').html('');
    $('#error_COMMENTAIRE').html('');

    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();
    $('#error_motif').html('');

    var TYPE_ANALYSE_ID = $('#TYPE_ANALYSE_ID').val();
    $('#error_TYPE_ANALYSE_ID').html('');

    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    $('#error_DATE_TRANSMISSION').html('');
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();
    $('#error_DATE_RECEPTION').html('');

    var statut=2;

    var checkboxes = $('input[name="grande[]"]');
    var checkedCount = checkboxes.filter(':checked').length;
    var errorElement = $('#error_TYPE_ANALYSE_ID');

    if (checkedCount !== checkboxes.length) {
      errorElement.text("<?=lang('messages_lang.check_ver')?>");
      event.preventDefault();
      var statut=1;

    } else {
      errorElement.text('');
      var statut=2;

    }

    event.preventDefault();

    if (ID_OPERATION==1)
    {
      if (TYPE_ANALYSE_MOTIF_ID=='')
      {
        $('#error_motif').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }
    }

    if (ID_OPERATION==3)
    {
      if (COMMENTAIRE=='')
      {
        $('#error_COMMENTAIRE').html("<?=lang('messages_lang.input_oblige')?>");
        statut=1;
      }
    }

    if (TYPE_ANALYSE_ID=='')
    {
     $('#error_TYPE_ANALYSE_ID').html("<?=lang('messages_lang.input_oblige')?>");
     statut=1;
   }

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

 var url;
 if(statut == 2)
 {

   if (ID_OPERATION == 1)
   {
    $('#rej_eng').show();
  }
  else
  {
    $('#rej_eng').hide();
  }

  var DATE_RECEPTION = moment(DATE_RECEPTION, "YYYY/mm/DD");
  var DATE_RECEPTION = DATE_RECEPTION.format("DD/mm/YYYY");

  var DATE_TRANSMISSION = moment(DATE_TRANSMISSION, "YYYY/mm/DD");
  var DATE_TRANSMISSION = DATE_TRANSMISSION.format("DD/mm/YYYY");

  var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');

  var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${TYPE_ANALYSE_MOTIF_ID}</ol>`;

  $('#motif_verifie').html(orderedList);

  $('#ID_OPERATION_verifie').html($('#ID_OPERATION option:selected').text());
  $('#DATE_RECEPTION_verifie').html(DATE_RECEPTION);
  $('#DATE_TRANSMISSION_verifie').html(DATE_TRANSMISSION);

  if(COMMENTAIRE != '')
  {
    $('#COMMENTAIRE_verifie').html(COMMENTAIRE);
  }else{
    $('#COMMENTAIRE_verifie').html('<b>-</b>');
  }

  $("#etape2_modal").modal("show");
}

}
</script>


<div class="modal fade" id="etape2_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
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
            <tr id="rej_eng">
              <td><i class="fa fa-certificate"></i> &nbsp;<strong><?= lang('messages_lang.label_retour') ?></strong></td>
              <td id="motif_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_rec') ?> (GDC)</strong></td>
              <td id="DATE_RECEPTION_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.label_date_tra') ?> (GDC)</strong></td>
              <td id="DATE_TRANSMISSION_verifie" class="text-dark"></td>
            </tr>
            <tr>
              <td><i class="fa fa-list"></i> &nbsp;<strong><?= lang('messages_lang.label_observ') ?></strong></td>
              <td id="COMMENTAIRE_verifie" class="text-dark"></td>
            </tr>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
  <div class="modal-footer">
    <button type="button" id="edi" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.label_modify') ?></button>
    <a onclick="save_etap2();hideButton()" id="conf" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i> <?= lang('messages_lang.label_confir') ?></a>
  </div>
</div>
</div>
</div>


<script type="text/javascript">
  function save_etap2()
  {
    document.getElementById("myEtape2").submit();
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


<div class='modal fade' id='otb_val'>
  <div class='modal-dialog'>
    <div class='modal-content'>
     <div class="modal-header">
      <center>Note à l'OTB (Directeur comptabilité et trésor)</center>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class='modal-body'>
      <center>
        <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_LETTRE_OTB'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
        </center>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-primary btn-md' data-dismiss='modal'>
          Quitter
        </button>
      </div>
    </div>
  </div>
</div>
<div class='modal fade' id='tans_val'>
  <div class='modal-dialog'>
    <div class='modal-content'>
     <div class="modal-header">
      <center>Lettre de transmission</center>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class='modal-body'>
      <center>
        <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_LETTRE_TRANSMISSION'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
        </center>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-primary btn-md' data-dismiss='modal'>
          Quitter
        </button>
      </div>
    </div>
  </div>
</div>
<div class='modal fade' id='paie_val'>
  <div class='modal-dialog'>
    <div class='modal-content'>
     <div class="modal-header">
      <center>Liste de paie (Décomposition, mutuelle, INSS et impôt)</center>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class='modal-body'>
      <center>
        <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_LISTE_PAIE'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
        </center>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-primary btn-md' data-dismiss='modal'>
          Quitter
        </button>
      </div>
    </div>
  </div>
</div>

<div class='modal fade' id='ppm_corrige'>
  <div class='modal-dialog'>
    <div class='modal-content'>
     <div class="modal-header">
      <center>PPM</center>
      <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
      </button>
    </div>
    <div class='modal-body'>
      <center>
        <embed  src="<?=base_url('uploads/double_commande_new/'.$get_date_eng['PATH_PPM'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
        </center>
      </div>
      <div class='modal-footer'>
        <button class='btn btn-primary btn-md' data-dismiss='modal'>
          Quitter
        </button>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
  function getAutreMotif(id = 0)
  {
    var selectElement = document.getElementById("TYPE_ANALYSE_MOTIF_ID");
    if (id.includes("-1")) {
      $('#autre_motif').delay(100).show('hide');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      disableOptions(selectElement);

    }else{
      $('#autre_motif').delay(100).hide('show');
      $('#DESCRIPTION_MOTIF').val('');
      $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
      enableOptions(selectElement);
    }

  }

  function disableOptions(selectElement) {
    for (var i = 0; i < selectElement.options.length; i++) {
      if (selectElement.options[i].value !== "-1") {
        selectElement.options[i].disabled = true;
      }
    }
  }

  function enableOptions(selectElement) {
    for (var i = 0; i < selectElement.options.length; i++) {
      selectElement.options[i].disabled = false;
    }
  }

  function save_newMotif()
  {
    var DESCRIPTION_MOTIF = $('#DESCRIPTION_MOTIF').val();
    var MARCHE_PUBLIQUE = $('#MARCHE_PUBLIQUE').val();
    var statut = 2;
    if (DESCRIPTION_MOTIF == "") {
      $('#DESCRIPTION_MOTIF').css('border-color','red');
      statut = 1;
    }

    if(statut == 2)
    {
      $.ajax({
        url: "<?=base_url('')?>/double_commande_new/Phase_Administrative_Budget/save_newMotif",
        type: "POST",
        dataType: "JSON",
        data: {
          DESCRIPTION_MOTIF:DESCRIPTION_MOTIF,
          MARCHE_PUBLIQUE:MARCHE_PUBLIQUE
        },
        beforeSend: function() {
          $('#loading_motif').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data) {
          $('#TYPE_ANALYSE_MOTIF_ID').html(data.motifs);
          TYPE_ANALYSE_MOTIF_ID.InnerHtml=data.motifs;
          $('#loading_motif').html("");
          $('#TYPE_ANALYSE_MOTIF_ID').val([]).trigger('change');
          $('#DESCRIPTION_MOTIF').attr('placeholder','Autre motif');
          $('#autre_motif').delay(100).hide('show');
        }
      });
    }


  }
</script>

