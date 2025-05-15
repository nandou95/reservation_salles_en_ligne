<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <!-- <div  style="float: right;">
                  <a href="<?=base_url('ihm/Classification_Economique/importer')?>" style="float: right;" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> Importer </a>
                </div> -->
                <div class="car-body">
                  <h1 class="header-title text-black"><?=lang('messages_lang.title_classification_economique')?>                    
                  </h1><br>
                  <?= session()->getFlashdata('message') ?>
                  <div class="table-responsive container " style="margin-top:">
                    <form action="<?= base_url('ihm/Classification_Economique/filterData');?>" id="Myform" method="POST" enctype="multipart/form-data">
                      <div class="card-body">
                        <!-- filtre niveau 1 -->
                        <div class="row">

                          <div class="col-md-2">
                            <label class="form-label"><?=lang('messages_lang.label_article')?></label>
                            <select onchange="get_dep();listing()" class="form-control" id="ARTICLE_ID" name="ARTICLE_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php foreach ($article as $keyarticle) { ?>
                                <option value="<?=$keyarticle->ARTICLE_ID?>">
                                    <?=$keyarticle->LIBELLE_ARTICLE?></option>
                                <?php }?>
                            </select>
                          </div>

                          <div class="col-md-2">
                            <label class="form-label"><?=lang('messages_lang.label_paragraph')?></label>
                            <select onchange="listing();get_dep();" class="form-control" id="PARAGRAPHE_ID" name="PARAGRAPHE_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            </select>
                          </div>

                          <div class="col-md-2">
                            <label class="form-label"><?=lang('messages_lang.label_litera')?></label>
                            <select onchange="listing()" class="form-control" id="LITTERA_ID" name="LITTERA_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                
                            </select>
                          </div>
                        <!-- </div> -->

                        <!-- filtre niveau 2 -->
                        <!-- <div class="row"> -->
                          <div class="col-md-2">
                            <label class="form-label"><?=lang('messages_lang.label_institution')?></label>
                            <select onchange="get_dep();listing();getinstution()" class="form-control" id="INSTITUTION_ID" name="INSTITUTION_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php foreach ($institution as $keyinstitution) { ?>
                                <option value="<?=$keyinstitution->INSTITUTION_ID?>">
                                    <?=$keyinstitution->DESCRIPTION_INSTITUTION?></option>
                                <?php }?>
                            </select>
                          </div>

                          <div class="col-md-2">
                            <label id="label_programme" class="form-label"><?=lang('messages_lang.labelle_programme')?></label>
                            <select onchange="get_dep();listing()" class="form-control" id="PROGRAMME_ID" name="PROGRAMME_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            </select>
                          </div>

                          <div class="col-md-2" id="div_action" style="display: block;">
                            <label class="form-label"><?=lang('messages_lang.labelle_action')?></label>
                            <select onchange="listing()" class="form-control" id="ACTION_ID" name="ACTION_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                            </select>
                          </div>

                          <div class="col-md-3">
                            <label class="form-label"><?=lang('messages_lang.label_trimestre')?> </label>
                            <select onchange="listing()" class="form-control" id="TRANCHE_ID" name="TRANCHE_ID">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php foreach ($op_tranches as $keyop_tranches) { ?>
                                <option value="<?=$keyop_tranches->TRANCHE_ID?>">
                                    <?=$keyop_tranches->DESCRIPTION_TRANCHE?></option>
                                <?php }?>
                            </select>
                          </div>
                          <div class="col-md-9" style="float: right;">
                                <!-- <a href="<?=base_url('ihm/Classification_Economique/exporter') ?>" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span> Exporter</a> -->
                                <a href="#" id="btnexport" onclick="exporter()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span> <?=lang('messages_lang.bouton_exporter')?></a>
                            </div>
                        </div>
                      </div>
                    </form>
                  <!-- </div>
                  <div class="row"> -->
                    <?php
                    if(session()->getFlashKeys('alert'))
                    {
                      ?>
                      <div class="alert alert-success" id="message">
                        <?php echo session()->getFlashdata('alert')['message']; ?>
                      </div>
                      <?php
                    }
                    ?>
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <!-- <th><center>#</center></th> -->
                            <th><center><?=lang('messages_lang.th_institution')?></center></th>
                            <th><center><?=lang('messages_lang.th_programme')?></center></th>
                            <th><center><?=lang('messages_lang.th_action')?></center></th>
                            <th><center><?=lang('messages_lang.th_activite')?></center></th>
                            <th><center><?=lang('messages_lang.th_budget_vote')?></center></th>
                            <th><center><?=lang('messages_lang.th_transfert_credit')?></center></th>
                            <th><center><?=lang('messages_lang.th_credit_transfert')?></center></th>
                            <th><center><?=lang('messages_lang.th_engag_budg')?></center></th>
                            <th><center><?=lang('messages_lang.th_engag_jurd')?></center></th>
                            <th><center><?=lang('messages_lang.th_liquidation')?></center></th>
                            <th><center><?=lang('messages_lang.th_ordonancement')?></center></th>
                            <th><center><?=lang('messages_lang.th_paiement')?></center></th>
                            <th><center><?=lang('messages_lang.th_decaissement')?></center></th>
                          </tr>
                        </thead>
                        <tbody>
                        </tbody>
                      </table>
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
  function get_dep()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var ARTICLE_ID = $('#ARTICLE_ID').val();
    var PARAGRAPHE_ID = $('#PARAGRAPHE_ID').val();
    var LITTERA_ID = $('#LITTERA_ID').val();

    $.ajax({
      url : "<?=base_url('/ihm/Classification_Economique/get_dep')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:{
        ARTICLE_ID:ARTICLE_ID,
        PARAGRAPHE_ID:PARAGRAPHE_ID,
        LITTERA_ID:LITTERA_ID,
        INSTITUTION_ID:INSTITUTION_ID,
        PROGRAMME_ID:PROGRAMME_ID,
        ACTION_ID:ACTION_ID,  
      },
      success:function(data){       

        $('#PROGRAMME_ID').html(data.prog);
        $('#ACTION_ID').html(data.act); 
        $('#PARAGRAPHE_ID').html(data.paragraphe);
        $('#LITTERA_ID').html(data.littera);
      },            
    });  
  }
</script>

<script type="text/javascript">
   function getinstution()
   {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();

    $.ajax({
          url : "<?=base_url('ihm/Classification_Economique/getinstution')?>",
          type: "POST",
          dataType: "JSON",
          data: {INSTITUTION_ID:INSTITUTION_ID},
          success: function(data)
          {
            if (data.TYPE_INSTITUTION_ID==1) {
              $('#label_programme').text('Dotation');
              document.getElementById('div_action').style.display="none";
            }else if (data.TYPE_INSTITUTION_ID==2) {
              $('#label_programme').text('Programme');
              document.getElementById('div_action').style.display="block";
            }
          }
      });
  }
</script>


<script type="text/javascript">
  $(document).ready(function ()
  {
    listing()
  });

  function listing(argument) {

    var ARTICLE_ID = $('#ARTICLE_ID').val();
    var PARAGRAPHE_ID = $('#PARAGRAPHE_ID').val();
    var LITTERA_ID = $('#LITTERA_ID').val();
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var TRANCHE_ID = $('#TRANCHE_ID').val();

    // alert(INSTITUTION_ID)

    var row_count ="1000000";
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,4],
      "ajax":
      {
        url:"<?=base_url('ihm/Classification_Economique/listing')?>",
        type:"POST",
        data: {
          ARTICLE_ID: ARTICLE_ID,
          PARAGRAPHE_ID: PARAGRAPHE_ID,
          LITTERA_ID: LITTERA_ID,
          INSTITUTION_ID: INSTITUTION_ID,
          PROGRAMME_ID: PROGRAMME_ID,
          ACTION_ID: ACTION_ID,
          TRANCHE_ID: TRANCHE_ID
        },
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],
      dom: 'Bfrtlip',
      order:[2,'desc'],
      buttons: [
        
        ],
        language: {
        "sProcessing":     "<?=lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?=lang('messages_lang.search_button')?>&nbsp;:",
        "sLengthMenu":     "<?=lang('messages_lang.labelle_et_afficher')?> _MENU_ <?=lang('messages_lang.labelle_et_affichage_element')?>",
        "sInfo":  "<?=lang('messages_lang.labelle_et_affichage_element')?> _START_ <?=lang('messages_lang.labelle_et_a')?> _END_ <?=lang('messages_lang.labelle_et_a')?> _END_ sur _TOTAL_ <?=lang('messages_lang.labelle_et_affichage_filtre')?> _TOTAL_ <?=lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?=lang('messages_lang.labelle_et_affichage_element')?> 0  <?=lang('messages_lang.labelle_et_a')?> 0 <?=lang('messages_lang.labelle_et_affichage_filtre')?> 0 <?=lang('messages_lang.labelle_et_element')?>",      
        "sInfoFiltered":   "(<?=lang('messages_lang.labelle_et_elementtotal')?>)",
        "sInfoPostFix":    "",
        "sLoadingRecords": "<?=lang('messages_lang.labelle_et_chargement')?>...",
        "sZeroRecords":    "<?=lang('messages_lang.labelle_et_aucun_element')?>",
        "sEmptyTable":     "<?=lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
        "oPaginate": {
          "sFirst":      "<?=lang('messages_lang.labelle_et_premier')?>",
          "sPrevious":   "<?=lang('messages_lang.labelle_et_precedent')?>",
          "sNext":       "<?=lang('messages_lang.labelle_et_suivant')?>",
          "sLast":       "<?=lang('messages_lang.labelle_et_dernier')?>"
        },
          "oAria": {
            "sSortAscending":  ": <?=lang('messages_lang.labelle_et_trier_colone')?>",
            "sSortDescending": ": <?=lang('messages_lang.labelle_et_trier_activer_trier')?>"
          }
        }

      });
  }
  
</script>

<script type="text/javascript">
function exporter()
{
    var ARTICLE_ID = $('#ARTICLE_ID').val();
    var PARAGRAPHE_ID = $('#PARAGRAPHE_ID').val();
    var LITTERA_ID = $('#LITTERA_ID').val();
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
    var TRANCHE_ID = $('#TRANCHE_ID').val();

    if (ARTICLE_ID == '' || ARTICLE_ID == null) {ARTICLE_ID = 0}
    if (PARAGRAPHE_ID == '' || PARAGRAPHE_ID == null) {PARAGRAPHE_ID = 0}
    if (LITTERA_ID == '' || LITTERA_ID == null) {LITTERA_ID = 0}
    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (TRANCHE_ID == '' || TRANCHE_ID == null) {TRANCHE_ID = 0}
    document.getElementById("btnexport").href = "<?=base_url('ihm/Classification_Economique/exporter/')?>"+'/'+ARTICLE_ID+'/'+PARAGRAPHE_ID+'/'+LITTERA_ID+'/'+INSTITUTION_ID+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+TRANCHE_ID;
}
</script>