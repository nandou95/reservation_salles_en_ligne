  <!DOCTYPE html>
  <html lang="en">
  <head>
    <?php echo view('includesbackend/header.php'); ?>
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
                  <div class="card-header">
                    <div class="row col-md-12">
                      <div class="col-md-9">
                        <h3><?=$get_step_title['DESC_ETAPE_DOUBLE_COMMANDE']?></h3>
                      </div>
                      <div class="col-md-3">
                        <a href="<?php echo base_url('double_commande_new/Menu_Engagement_Budgetaire/rejete_interface')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?=lang('messages_lang.liste_bouton')?></a>
                      </div>
                    </div>
                  </div>
                  <div class="card-body">
                    <br>
                    <div class="container" style="width:100%">
                      <div id="accordion">
                        <div class="card-header" id="headingThree" style="float: left;">
                          <h5 class="mb-0">
                            <button style="background:#061e69; color:#fff; padding:.3rem 2rem; font-weight: 500; text-decoration: none;" class="btn btn-link collapsed dropdown-toggle" data-toggle="collapse" data-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree"> <?=lang('messages_lang.histo_btn')?>
                          </button>
                        </h5>
                      </div>  
                    </div><br><br>
                    <div id="collapseThree" class="collapse" aria-labelledby="headingThree" data-parent="#accordion">
                      <?php include  'includes/Detail_View.php'; ?>
                    </div>
                  </div><br><br>

                  <div class=" container " style="width:100%">
                    <?php $validation = \Config\Services::validation(); ?>
                    <form enctype='multipart/form-data' id="MyFormData" action="<?=base_url('double_commande_new/Etapes_Annulations/save_rejeter_budget/')?>" method="post" >
                      <div class="container">
                        <div class="row" style="border:1px solid #ddd;border-radius:5px">
                          <div class="col-md-12 mt-3 ml-2"style="margin-bottom:50px" >
                            <div class="row">
                              <div class="col-6">
                                <label><b><?=lang('messages_lang.label_motif')?><hr></b></label>
                                <ol>
                                  <?php
                                  foreach ($motif_rejet as $key) {
                                    ?>
                                    <li><?=$key->DESC_TYPE_ANALYSE_MOTIF?></li>
                                    <?php
                                  }
                                  ?>
                                </ol>
                                <br>
                              </div>  
                            </div>
                            <div class="row">
                              <input type="hidden" name="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" id="EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID" value="<?=$details['EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID']?>">
                              <input type="hidden" name="EXEC_BUDGET_RAC_ID" id="EXEC_BUDGET_RAC_ID" value="<?=$EXEC_BUDGET_RAC_ID?>">
                              <input type="hidden" name="EXEC_BUDGET_RAC_DET_ID" id="EXEC_BUDGET_RAC_DET_ID" value="<?=$EXEC_BUDGET_RAC_DET_ID?>">
                              <input type="hidden" name="ETAPE_ID" id="ETAPE_ID" value="<?=$ETAPE_ID?>">

                              <div class="col-md-6">
                                <br>
                                <label for=""><?=lang('messages_lang.lab_d_rec')?><span style="color: red;">*</span></label>
                                <input type="date" class="form-control" id="DATE_RECEPTION" name="DATE_RECEPTION"   value="<?=date('Y-m-d')?>" min="<?=date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION']))?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)">
                                <?php if (isset($validation)) : ?>
                                  <font color="red" id="error_DATE_RECEPTION"><?= $validation->getError('DATE_RECEPTION'); ?></font>
                                <?php endif ?>

                              </div>

                              <div class="col-md-6">
                                <br>
                                <label for=""><?=lang('messages_lang.label_motif')?><font color="red">*</font><span id="loading_motif"></span></label>
                              <select class="form-control select2" name="TYPE_ANALYSE_MOTIF_ID[]" id="TYPE_ANALYSE_MOTIF_ID" multiple onchange='getAutreMotif(this.value)'>
                                 <option value="-1"><?=lang('messages_lang.selection_autre')?></option>
                                 <?php
                                 foreach($motif as $value)
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
                              <?php if (isset($validation)) : ?>
                                <font color="red" id="error_TYPE_ANALYSE_MOTIF_ID"><?= $validation->getError('TYPE_ANALYSE_MOTIF_ID'); ?></font>
                              <?php endif ?>
                              <br>
                              <span id="autre_motif" class="col-md-12 row" style="display: none">
                                <div class="col-md-9">
                                  <input type="text" class="form-control" id="DESCRIPTION_MOTIF" placeholder="Autre motif" name="DESCRIPTION_SERIE">
                                </div>
                                <div class="col-md-2" style="margin-left: 5px;">
                                  <button type="button" class="btn btn-success" onclick="save_newMotif()"><i class="fa fa-plus"></i></button>
                                </div>
                              </span>
                            </div>
                            <div class="col-md-6">
                              <br>
                              <label for=""><?=lang('messages_lang.lab_d_trans')?><span style="color: red;">*</span></label>
                              <input type="date" class="form-control" id="DATE_TRANSMISSION" name="DATE_TRANSMISSION"   value="<?=date('Y-m-d')?>" max="<?=date('Y-m-d')?>" onkeypress="return false" onblur="this.type='date'">
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
    var validationSet = <?php echo isset($validation) ? 'true' : 'false'; ?>;
    if(validationSet)
    {
      get_rejet();
    }


    $('#MOTIF_REJET').on('input', function(){

     $(this).val(this.value.substring(0,255));

   })

  });
</script>



<script type="text/javascript">
  function save()
  {
    var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID').val();
    var DATE_TRANSMISSION = $('#DATE_TRANSMISSION').val();
    var DATE_RECEPTION = $('#DATE_RECEPTION').val();

    var status = 2;

    $('#error_MOTIF_REJET,#error_DATE_TRANSMISSION,#error_DATE_RECEPTION, #error_COMMENTAIRE').html('');

    if(DATE_TRANSMISSION == '')
    {
      $('#error_DATE_TRANSMISSION').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status = 1;
    }

    if(DATE_RECEPTION == '')
    {
      $('#error_DATE_RECEPTION').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status = 1;
    }

    if(TYPE_ANALYSE_MOTIF_ID == '')
    {
      $('#error_TYPE_ANALYSE_MOTIF_ID').html("<?=lang('messages_lang.champ_obligatoire')?>");
      status = 1;
    }

    if(status == 2){

      var TYPE_ANALYSE_MOTIF_ID = $('#TYPE_ANALYSE_MOTIF_ID option:selected').toArray().map(item => `<li>${item.text}</li>`).join('');
      var orderedList = `<ol style="margin: 0; padding: 0; list-style-position: inside;">${TYPE_ANALYSE_MOTIF_ID}</ol>`;
      $('#motif_verifie').html(orderedList); 
      

      const format_date_recep = moment(DATE_RECEPTION).format('DD/MM/YYYY');
      $('#date_recep_verifie').html(format_date_recep);

      const format_date_trans = moment(DATE_TRANSMISSION).format('DD/MM/YYYY');
      $('#date_trans_verifie').html(format_date_trans);
      $('#engager_annulation').modal('show');
  }
}


</script>

<script type="text/javascript">
  function confirm()
  {
    $("#MyFormData").submit();
  }
</script>


<!--*** Modal pour confirmer les infos saisies ***-->
<div class="modal fade" id="engager_annulation" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true" data-backdrop="static">
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
                <td style="width:350px ;"><i class="fa fa-calendar"></i>&nbsp;<strong><?=lang('messages_lang.lab_d_rec')?></strong></td>
                <td id="date_recep_verifie" class="text-dark"></td>
              </tr>
              <tr >
                <td><i class="fa fa-cube"></i>&nbsp;<strong><?=lang('messages_lang.label_motif')?></strong></td>
                <td id="motif_verifie" class="text-dark"></td>
              </tr>
              <tr>
                <td><i class="fa fa-calendar"></i>&nbsp;<strong><?=lang('messages_lang.lab_d_trans')?></strong></td>
                <td id="date_trans_verifie" class="text-dark"></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" id="mode1" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i><?=lang('messages_lang.bouton_modifier')?></button>
        <a onclick="confirm();hideButton()" id="conf" style="float: right;" class="btn btn-info"><i class="fa fa-check"></i><?=lang('messages_lang.bouton_confirmer')?></a>
      </div>
    </div>
  </div>
</div>
<!--*** Modal pour confirmer les infos saisies ***-->


<script>
  function hideButton()
  {
    var element = document.getElementById("conf");
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
        url: "<?=base_url('')?>/double_commande_new/Etapes_Annulations/save_newMotif",
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





