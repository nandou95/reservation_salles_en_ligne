  <!DOCTYPE html>
  <html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
  </head>
  <style>
    hr.vertical {
      border:none;
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
                 <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-9">
                      <h3><?=$get_step_title['DESC_ETAPE_DOUBLE_COMMANDE']?></h3>
                    </div>
                    <div class="col-md-3">
                      <a href="<?php echo base_url('double_commande_new/Ordonnancement_Double_Commande/get_ordon_Afaire')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?=lang('messages_lang.liste_bouton')?></a>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <br>
                  <div class="container" style="width:100%">
                    <div id="accordion">
                      <div class="card-header" id="headingThree" style="float: left;">
                        <h5 class="mb-0">
                          <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"><?=lang('messages_lang.lab_hist')?>
                          </button>
                        </h5>
                      </div>
                    </div><br><br>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                      <?php include  'includes/Detail_View.php'; ?>
                    </div> <br><br>

                    <div class=" container " style="width:100%">
                      <?php $validation = \Config\Services::validation(); ?>
                      <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('double_commande_new/Ordonnancement/update_etapeDG/')?>" method="post" >
                        <div class="container">

                          <div class="row" style="border:1px solid #ddd;border-radius:5px">
                            <div class="col-md-12 mt-3 ml-2"style="margin-bottom:50px" >
                              <div class="row">
                                
                                <input type="hidden" name="EXEC_BUDGET_ID" id="EXEC_BUDGET_ID" value="<?=$EXEC_BUDGET_ID?>">
                                <input type="hidden" name="EXEC_BUDGET_DET_ID" id="EXEC_BUDGET_DET_ID" value="<?=$EXEC_BUDGET_DET_ID?>">
                                <input type="hidden" name="MONTANT_LIQ" id="MONTANT_LIQ" value="<?=$details['MONTANT_LIQUIDATION']?>">
                                <input type="hidden" name="ETAPE_ID" id="ETAPE_ID" value="<?=$ETAPE_ID?>">
                                <input type="hidden" name="MONNAIE" id="MONNAIE" value="<?=$details['DEVISE_TYPE_ID']?>">
                                <!-- <input type="hidden" name="COUR_DEVISE" id="COUR_DEVISE" value="<?//=$details['COUR_DEVISE']?>"> -->
                                <input type="hidden" name="LIQUIDATION_TYPE_ID" id="LIQUIDATION_TYPE_ID" value="<?=$details['LIQUIDATION_TYPE_ID']?>">

                                <div class="col-md-6">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_date_recep_ordo')?><span style="color: red;">*</span></label>
                                  <input type="date" class="form-control" id="DATE_RECEPTION" name="DATE_RECEPTION"  value="<?=set_value('DATE_RECEPTION')?>" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)">
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_DATE_RECEPTION"><?= $validation->getError('DATE_RECEPTION'); ?></font>
                                  <?php endif ?>

                                </div>
                                <div class="col-md-6">
                                  <br>
                                  <label class="form-label"><?=lang('messages_lang.label_decision')?><font color="red">*</font></label>
                                  <select name="OPERATION_ID" id="OPERATION_ID" class="form-control">
                                    <option value=""><?=lang('messages_lang.selection_message')?></option>
                                    <?php 
                                    foreach($operation as $key) { 
                                      if ($key->ID_OPERATION==set_value('OPERATION_ID')) { 
                                        echo "<option value='".$key->ID_OPERATION."' selected>".$key->DESCRIPTION."</option>";
                                      }else{
                                        echo "<option value='".$key->ID_OPERATION."' >".$key->DESCRIPTION."</option>"; 
                                      } 
                                    }?>
                                  </select>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_OPERATION_ID"><?= $validation->getError('OPERATION_ID'); ?></font>
                                  <?php endif ?>

                                </div>

                                <div class="col-md-6" id="show_step_correct" style="display:none;">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_etape_corriger')?><font color="red">*</font></label>
                                  <select class="form-control" name="ETAPE_RETOUR_CORRECTION_ID" id="ETAPE_RETOUR_CORRECTION_ID">
                                    <option value=""><?=lang('messages_lang.selection_message')?></option>  
                                    <?php
                                    foreach($get_correct as $value)
                                    { 
                                      if($value->ETAPE_RETOUR_CORRECTION_ID==set_value('ETAPE_RETOUR_CORRECTION_ID')){?>
                                        <option value="<?=$value->ETAPE_RETOUR_CORRECTION_ID ?>" selected><?=$value->DESCRIPTION_ETAPE_RETOUR?></option>
                                      <?php }else                                
                                      {
                                        ?>
                                        <option value="<?=$value->ETAPE_RETOUR_CORRECTION_ID ?>"><?=$value->DESCRIPTION_ETAPE_RETOUR?></option>
                                        <?php
                                      }
                                    }
                                    ?>
                                  </select>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_ETAPE_RETOUR_CORRECTION_ID"><?= $validation->getError('ETAPE_RETOUR_CORRECTION_ID'); ?></font>
                                  <?php endif ?>   

                                </div>

                                <div class="col-md-6" id="show_motif" style="display:none;">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_motif')?><font color="red">*</font></label>
                                  <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)'>
                                    <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                                    <?php
                                    foreach($motif as $value)
                                    { 
                                      if($value->TYPE_ANALYSE_MOTIF_ID==set_value('TYPE_ANALYSE_MOTIF_ID')){?>
                                        <option value="<?=$value->TYPE_ANALYSE_ID ?>" selected><?=$value->DESC_TYPE_ANALYSE_MOTIF?></option>
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
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_TYPE_ANALYSE_MOTIF_ID"><?= $validation->getError('TYPE_ANALYSE_MOTIF_ID'); ?></font>
                                  <?php endif ?>   

                                </div>

                                <div class="col-md-6" id="mont_devise" style="display: none;">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_mont_devise')?></label>
                                  
                                  <input type="text" class="form-control" name="MONTANT_DEVISE_ORDONNANCEMENT" id="MONTANT_DEVISE_ORDONNANCEMENT"  value="<?php echo $details['MONTANT_LIQUIDATION_DEVISE']?>">
                                  

                                </div>

                               
                                <div class="col-md-6" id="mont_en_bif" style="display: none;">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_mont_ordo')?></label>
                                  <input type="text" class="form-control" name="MONTANT_EN_BIF" id="MONTANT_EN_BIF" value="<?php echo $details['MONTANT_LIQUIDATION']?>">
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_MONTANT_EN_BIF"><?= $validation->getError('MONTANT_EN_BIF'); ?></font>
                                  <?php endif ?>

                                </div>

                                <div class="col-md-6" id="date_ordo" style="display: none;">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_date_ordonnance')?><span style="color: red;">*</span></label>
                                  <input type="date" class="form-control" id="DATE_HEURE_ORDONNANCE" name="DATE_HEURE_ORDONNANCE"  value="<?=set_value('DATE_HEURE_ORDONNANCE')?>" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_DATE_HEURE_ORDONNANCE"><?= $validation->getError('DATE_HEURE_ORDONNANCE'); ?></font>
                                  <?php endif ?>

                                </div>

                                <div class="col-md-6" id="bordereau" style="display: none;">
                                  <br>
                                  <label><?=lang('messages_lang.label_bon')?><span style="color: red;">*</span></label>
                                  <input type="file" class="form-control " id="PATH_DOCUMENT" name="PATH_DOCUMENT" placeholder="...." accept=".pdf" value="<?=set_value('PATH_DOCUMENT')?>">
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_PATH_DOCUMENT"><?=$file_error?></font>
                                  <?php endif ?>

                                </div>

                                <div class="col-md-6">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_observ')?></label>
                                  <textarea class="form-control" name="COMMENTAIRE" id="COMMENTAIRE"><?=set_value('COMMENTAIRE')?></textarea>
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_COMMENTAIRE"><?= $validation->getError('COMMENTAIRE'); ?></font>
                                  <?php endif ?>

                                </div>

                                <div class="col-md-6">
                                  <br>
                                  <label for=""><?=lang('messages_lang.label_date_trans_ordo')?><span style="color: red;">*</span></label>
                                  <input type="date" class="form-control" id="DATE_TRANSMISSION" name="DATE_TRANSMISSION"  value="<?=set_value('DATE_TRANSMISSION')?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
                                  <?php if (isset($validation)) : ?>
                                    <font color="red" id="error_DATE_TRANSMISSION"><?= $validation->getError('DATE_TRANSMISSION'); ?></font>
                                  <?php endif ?>

                                </div>

                              </div>
                              <br><br>
                              <div style="float: right;" class="col-md-2 mt-5 ">
                                <div class="form-group " >
                                  <a onclick="save()" id="btn_save"  class="btn" style="float:right;background:#061e69;color:white"><?=lang('messages_lang.enregistrer_bouton')?></a>
                                </div>
                              </div>
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
    function get_min_trans()
    {
     $("#DATE_TRANSMISSION").prop('min',$("#DATE_RECEPTION").val());
   }
 </script>

 <script>
  $(document).ready(function ()
  {

    function DoPrevent(e)
    {
      e.preventDefault();
      e.stopPropagation();
    }

    var OPERATION_ID = $('#OPERATION_ID').val();

    if(OPERATION_ID == '')
    {
      $('#show_motif').hide();
      $('#mont_devise').hide();
      $('#mont_en_bif').hide();
      $('#date_ordo').hide();
      $('#bordereau').hide();

    }

    $('#OPERATION_ID').on('change', function(){

      var OPERATION_ID = $(this).val();

      if(OPERATION_ID == '')
      {
        $('#show_motif').hide();
        $('#mont_devise').hide();
        $('#mont_en_bif').hide();
        $('#date_ordo').hide();
        $('#bordereau').hide();

      }
      else
      {

        if(OPERATION_ID == 1)
        {
          $('#show_motif').show();
          $('#show_step_correct').show();
          $('#mont_en_bif').hide();
          $('#mont_devise').hide();
          $('#date_ordo').hide();
          $('#bordereau').hide();

          var MONTANT_EN_BIF = $('#MONTANT_EN_BIF').val();
          var MONTANT_EN_BIF = MONTANT_EN_BIF.replace(/ /g, '');
          $('#MONTANT_EN_BIF').val(MONTANT_EN_BIF);
        }
        else
        {
          $('#show_motif').hide();
          $('#show_step_correct').hide();
          $('#date_ordo').show();
          $('#bordereau').show();

          var MONNAIE = $('#MONNAIE').val();
          if(MONNAIE == 19)
          {

            $('#MONTANT_EN_BIF').prop('readonly', true);
            $('#MONTANT_DEVISE_LIQUIDATION').prop('readonly', true);
            $('#mont_en_bif').show();
            $('#date_eng').html("<?=lang('messages_lang.label_date_ordonnance')?>"+"<span style='color: red;'>*</span>");
            $('#mont_devise').hide();

            //Montant en bif formatté
            var MONTANT_EN_BIF = $('#MONTANT_EN_BIF').val();
            var MONTANT_EN_BIF = parseFloat(MONTANT_EN_BIF);
            var MONTANT_EN_BIF = MONTANT_EN_BIF.toLocaleString("en-US",{useGrouping: true});
            var MONTANT_EN_BIF = MONTANT_EN_BIF.replace(/,/g, ' ');
            $('#MONTANT_EN_BIF').val(MONTANT_EN_BIF);
          }
          else
          {
            $('#MONTANT_EN_BIF').prop('readonly', true);
            $('#MONTANT_DEVISE_LIQUIDATION').prop('readonly', true);
            $('#MONTANT_DEVISE_ORDONNANCEMENT').prop('readonly', true);            
            $('#mont_en_bif').show();
            $('#mont_devise').show();
            $('#date_eng').html("<?=lang('messages_lang.label_date_ordo_devise')?>"+"<span style='color: red;'>*</span>");

            //Montant en bif formatté
            var MONTANT_EN_BIF = $('#MONTANT_EN_BIF').val();
            var MONTANT_EN_BIF = parseFloat(MONTANT_EN_BIF);
            var MONTANT_EN_BIF = MONTANT_EN_BIF.toLocaleString("en-US",{useGrouping: true});
            var MONTANT_EN_BIF = MONTANT_EN_BIF.replace(/,/g, ' ');
            $('#MONTANT_EN_BIF').val(MONTANT_EN_BIF);

            //Montant en bif formatté
            var MONTANT_DEVISE_ORDONNANCEMENT = $('#MONTANT_DEVISE_ORDONNANCEMENT').val();
            var MONTANT_DEVISE_ORDONNANCEMENT = parseFloat(MONTANT_DEVISE_ORDONNANCEMENT);
            var MONTANT_DEVISE_ORDONNANCEMENT = MONTANT_DEVISE_ORDONNANCEMENT.toLocaleString("en-US",{useGrouping: true});
            var MONTANT_DEVISE_ORDONNANCEMENT = MONTANT_DEVISE_ORDONNANCEMENT.replace(/,/g, ' ');
            $('#MONTANT_DEVISE_ORDONNANCEMENT').val(MONTANT_DEVISE_ORDONNANCEMENT);


          }

        }

      }


    });

});
</script>
<script type="text/javascript">
  function save()
  {
   var OPERATION_ID = $('#OPERATION_ID').val();
   var ETAPE_RETOUR_CORRECTION_ID = $('#ETAPE_RETOUR_CORRECTION_ID').val();
   var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();
   var TYPE_MONNAIE = $('#MONNAIE').val();
   var MONTANT_LIQ = $('#MONTANT_LIQ').val();
   var DATE_HEURE_ORDONNANCE = $('#DATE_HEURE_ORDONNANCE').val();
   var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
   var DATE_RECEPTION = $('#DATE_RECEPTION').val();

   var value_en_bif = $('#MONTANT_EN_BIF').val();
   var MONTANT_EN_BIF = value_en_bif.replace(/\s/g, '');
   //alert(MONTANT_EN_BIF)
   var value_en_devise = $('#MONTANT_DEVISE_ORDONNANCEMENT').val();
   var MONTANT_DEVISE_ORDONNANCEMENT = value_en_devise.replace(/\s/g, '');

   var PATH_DOCUMENT = document.getElementById('PATH_DOCUMENT');
   var maxFileSize = 300*1024;

   var COMMENTAIRE = $('#COMMENTAIRE').val();

   var status = 2;

   $('#error_OPERATION_ID, #error_MOTIF_REJET, #error_ETAPE_RETOUR_CORRECTION_ID,#error_DATE_TRANSMISSION, #error_DATE_RECEPTION, #error_DATE_HEURE_ORDONNANCE, #error_MONTANT_EN_BIF, #error_PATH_DOCUMENT').html('');

   if(OPERATION_ID=='')
   {
     $('#error_OPERATION_ID').html('Le champ est obligatoire!');
     status=1;
   }else{
    if(OPERATION_ID == 1)
    {
      if(ETAPE_RETOUR_CORRECTION_ID == '')
      {
        $('#error_ETAPE_RETOUR_CORRECTION_ID').html('Le champ est obligatoire!');
        status = 1;
      }

      if(TYPE_ANALYSE_MOTIF_ID == '')
      {
        $('#error_TYPE_ANALYSE_MOTIF_ID').html('Le champ est obligatoire!');
        status = 1;
      }

    }else{

      if(PATH_DOCUMENT.files.length === 0)
      {
        $('#error_PATH_DOCUMENT').html('Le champ est obligatoire!');
        status=1;
      }else{

        if(PATH_DOCUMENT.files[0].size > maxFileSize)
        {
          $('#error_PATH_DOCUMENT').html('La taille du document ne doit pas dépasser 200 KB!');
          status = 1;
        }
      }


      if(DATE_HEURE_ORDONNANCE == '')
      {
        $('#error_DATE_HEURE_ORDONNANCE').html('Le champ est obligatoire!');
        status = 1;
      }


      if(TYPE_MONNAIE == 19)
      {
        if(MONTANT_EN_BIF == '')
        {
          $('#error_MONTANT_EN_BIF').html('Le champ est obligatoire!');
          status = 1;
        }
        else
        {
          if(parseInt(MONTANT_EN_BIF) > parseInt(MONTANT_LIQ))
          {
            $('#error_MONTANT_EN_BIF').html('Le montant doit être inférieur ou égal à '+ MONTANT_LIQ +'.');
            status = 1;
          }
        }

      }

    }

  }




  if(DATE_TRANSMISSION == '')
  {
    $('#error_DATE_TRANSMISSION').html('Le champ est obligatoire!');
    status = 1;
  }

  if(DATE_RECEPTION == '')
  {
    $('#error_DATE_RECEPTION').html('Le champ est obligatoire!');
    status = 1;
  }


  if(status == 2){

    $('#operation__verifie').html($('#OPERATION_ID option:selected').text());

    if(OPERATION_ID == 1)
    {
      $('#showing_mont_ordo').hide();
      $('#showing_devise').hide();
      $('#showing_change').hide();
      $('#showing_bordero').hide();
      $('#showing_date_ordo').hide();

      $('#showing_etap_corrig').show();
      var ETAPE_RETOUR_CORRECTION_ID = $('#ETAPE_RETOUR_CORRECTION_ID option:selected').toArray().map(item => item.text).join();
      $('#etap_corrig_verifie').html(ETAPE_RETOUR_CORRECTION_ID);

      $('#showing_motif').show();
      $('#showing_motif').show();
      var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');
      var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${TYPE_ANALYSE_MOTIF_ID}</ol>`;

      $('#motif_verifie').html(orderedList);
    }else{
      $('#showing_etap_corrig').hide();
      $('#showing_motif').hide();
      $('#motif_verifie').html('');

      $('#showing_mont_ordo').show();
      $('#showing_bordero').show();
      $('#showing_date_ordo').show();

      $('#mont_ordo_verifie').html(value_en_bif);
      const format_date_ordo = moment(DATE_HEURE_ORDONNANCE).format('DD/MM/YYYY');
      $('#date_ordo_verifie').html(format_date_ordo);

      var BORDEREAU = document.getElementById('PATH_DOCUMENT');
      var BORD_NAME = BORDEREAU.files[0].name;

      $('#bordereau_verifie').html(BORD_NAME);

      if(TYPE_MONNAIE != 19){

        $('#showing_devise').show();
        
        var dev = $('#MONTANT_DEVISE_ORDONNANCEMENT').val();
        var dev1=dev.replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        $('#mont_en_devise_verifie').html(dev1); 
        
      }
      else{

        $('#showing_devise').hide();
        
      }

    }


    const format_date_recep = moment(DATE_RECEPTION).format('DD/MM/YYYY');
    $('#date_recep_verifie').html(format_date_recep);

    const format_date_trans = moment(DATE_TRANSMISSION).format('DD/MM/YYYY');
    $('#date_trans_verifie').html(format_date_trans);


    if(COMMENTAIRE =='')
    {
     $('#showing_observ').hide();
   }else{
    $('#showing_observ').show();
    $('#observ_verifie').html(COMMENTAIRE);
  }

  $('#engager_juridique').modal('show');
}
}

</script>

<script type="text/javascript">
  function confirm()
  {
    $("#MyFormData").submit();
  }
</script>
<!--******************* Modal pour confirmer les infos saisies ***********************-->
<div class="modal fade" id="engager_juridique" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel"><?=lang('messages_lang.vouloir_confirmer')?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div class="table-responsive  mt-3">
          <table class="table m-b-0 m-t-20">
            <tbody>
              <tr>
                <td><i class="fa fa-cogs"></i>&nbsp;<strong><?=lang('messages_lang.label_decision')?></strong></td>
                <td id="operation__verifie" class="text-dark"></td>
              </tr>
               <tr id="showing_etap_corrig">
                <td><i class="fa fa-bar-chart"></i>&nbsp;<strong><?=lang('messages_lang.label_etape_corriger')?></strong></td>
                <td id="etap_corrig_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_motif">
                <td><i class="fa fa-cube"></i>&nbsp;<strong><?=lang('messages_lang.label_motif')?></strong></td>
                <td id="motif_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_mont_ordo">
                <td><i class="fa fa-credit-card"></i> &nbsp;<strong><?=lang('messages_lang.label_mont_ordo')?></strong></td>
                <td id="mont_ordo_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_devise">
                <td style="width:250px;"><i class="fa fa-credit-card"></i> &nbsp;<strong><?=lang('messages_lang.label_mont_devise')?></strong></td>
                <td>
                  <span id="mont_en_devise_verifie" class="text-dark"></span>
                </td>
              </tr>
              <tr id="showing_date_ordo">
                <td><i class="fa fa-calendar"></i> &nbsp;<strong><?=lang('messages_lang.label_date_ordonnance')?></strong></td>
                <td id="date_ordo_verifie" class="text-dark"></td>
              </tr>
               <tr id="showing_bordero">
                <td><i class="fa fa-file-pdf"></i>&nbsp;<strong><?=lang('messages_lang.label_bon')?></strong></td>
                <td id="bordereau_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td style="width:350px ;"><i class="fa fa-calendar"></i> &nbsp;<strong><?=lang('messages_lang.label_date_recep_ordo')?></strong></td>
                <td id="date_recep_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-calendar"></i> &nbsp;<strong><?=lang('messages_lang.label_date_trans_ordo')?></strong></td>
                <td id="date_trans_verifie" class="text-dark"></td>
              </tr>
              <tr id="showing_observ">
                <td><i class="fa fa-list"></i>&nbsp;<strong><?=lang('messages_lang.label_observ')?></strong></td>
                <td id="observ_verifie" class="text-dark"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="mode1" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i><?=lang('messages_lang.bouton_modifier')?></button>
        <a onclick="confirm();hideButton()" id="ord_dg" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i><?=lang('messages_lang.bouton_confirmer')?></a>
      </div>
    </div>
  </div>
</div>
<!--******************* Modal pour confirmer les infos saisies ***********************-->

<script>
  function hideButton()
  {
    var element = document.getElementById("ord_dg");
    element.style.display = "none";

    var elementmod = document.getElementById("mode1");
    elementmod.style.display = "none";
  }
</script>
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
    var statut = 2;
    if (DESCRIPTION_MOTIF == "") {
      $('#DESCRIPTION_MOTIF').css('border-color','red');
      statut = 1;
    }

    if(statut == 2)
    {
      $.ajax({
        url: "<?=base_url('')?>/double_commande_new/Ordonnancement/save_newMotif",
        type: "POST",
        dataType: "JSON",
        data: {
          DESCRIPTION_MOTIF:DESCRIPTION_MOTIF
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