<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
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

                                <div class="card-header">
                                    <div class="row col-md-12">
                                        <div class="col-md-8">
                                            <h1 class="header-title text-black"><?=lang('messages_lang.title_classification_adiministrative')?>
                                            </h1>
                                        </div>
                                    </div>
                                </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.label_institution')?></label>
                                        <select class="form-control" 
                                        onchange="liste(this.value);get_prog(this.value); test()" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                            <?php
                                            foreach($instit as $key)
                                            {
                                              if($key->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                                              {
                                                echo "<option value='".$key->INSTITUTION_ID."'  selected>".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                                              }
                                              else
                                              {
                                                echo "<option value='".$key->INSTITUTION_ID."' >".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                                              }
                                            }
                                            ?>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <div class="form-group">
                                        <label id="id_pgm"></label>
                                        <select class="form-control" onchange="liste(this.value); get_action(this.value);test()" class="form-control" name="PROGRAMME_ID" id="PROGRAMME_ID">
                                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>

                                        </select>
                                      </div>
                                    </div>
                                    <div class="col-md-3" id="div_action">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.labelle_action')?></label>
                                        <select class="form-control" onchange="liste(this.value);test()" class="form-control" name="ACTION_ID" id="ACTION_ID">
                                            <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                            
                                        </select>
                                      </div>
                                    </div>
                                    <div class="col-md-3" id="">
                                      <div class="form-group">
                                        <label for="Nom" class="form-label"><?=lang('messages_lang.select_anne_budget')?></label>
                                        <select class="form-control" onchange="liste(this.value);test();get_date_limit()" class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
                                            <?php
                                            foreach($annee_budgetaire as $key)
                                            {
                                              if($key->ANNEE_BUDGETAIRE_ID==set_value('ANNEE_BUDGETAIRE_ID'))
                                              {
                                                echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."'  selected>".$key->ANNEE_DESCRIPTION."</option>";
                                              }
                                              if($key->ANNEE_BUDGETAIRE_ID==$annee_budgetaire_en_cours)
                                              {
                                                echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."'  selected>".$key->ANNEE_DESCRIPTION."</option>";
                                              }
                                              else
                                              {
                                                echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."' >".$key->ANNEE_DESCRIPTION."</option>";
                                              }
                                            }
                                            ?>
                                        </select>
                                      </div>
                                    </div>
                                    <div class="col-md-3">
                                      <label class="form-label"><?=lang('messages_lang.date_debut_du')?>:</label>
                                      <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange="liste();get_date()" max="<?=date('Y-m-d')?>" class="form-control">
                                    </div>
                                    <div class="col-md-3">
                                      <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                                      <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="liste()" max="<?=date('Y-m-d')?>" class="form-control">
                                    </div>

                                    <div class="col-md-3">
                                      <label for="form-label"><?=lang('messages_lang.inclus_tache')?></label>
                                      <div>
                                        <input type="radio" name="activ" id="activ" value="0" onclick="test();exporter_word();exporter_pdf();" checked> <?=lang('messages_lang.label_non')?>
                                        <input type="radio" name="activ" id="activ" value="1" onclick="test();exporter_word();exporter_pdf();"> <?=lang('messages_lang.label_oui')?>
                                      </div> 
                                    </div>
                                  </div>
                                   <div class="col-md-6" style="float: right;">
                                      <a href="" id="btnexport" onclick="test()" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-excel pull-right"></span></span>&nbsp;Excel<?//=lang('messages_lang.bouton_exporter')?></a>&nbsp;&nbsp;

                                      <a href="#" id="btnexportword" onclick="exporter_word()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-word pull-right"></span>&nbsp;Word <?//=lang('messages_lang.bouton_exporter')?></a>&nbsp;&nbsp;

                                      <a href="#" id="btnexportpdf" onclick="exporter_pdf()" type="button" style="float: right;margin-top: 20px;" class="btn btn-primary"><span class="fa fa-file-pdf pull-right"></span>&nbsp;PDF <?//=lang('messages_lang.bouton_exporter')?></a>
                                    </div>
                                  <div class="table-responsive" >
                                    <table id="mytable" class=" table table-striped table-bordered" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_institution')?></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_programme')?></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_action')?></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.ligne_budgetaire_institution_detail')?></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.label_taches')?></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><?=lang('messages_lang.th_program_financiere')?></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><center><?=lang('messages_lang.th_transfert_credit')?></center></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><center><?=lang('messages_lang.th_credit_transfert')?></center></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><center><?=lang('messages_lang.th_engag_budg')?></center></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><center><?=lang('messages_lang.th_engag_jurd')?></center></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><center><?=lang('messages_lang.th_liquidation')?></center></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><center><?=lang('messages_lang.th_ordonancement')?></center></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><center><?=lang('messages_lang.th_paiement')?></center></th>
                                                <th class="text-uppercase" style="white-space: nowrap;" ><center><?=lang('messages_lang.th_decaissement')?></center></th>
                                          
                                                
                                            </tr>
                                        </thead>
                                    </table>
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
  $(document).ready(function ()
  {
    liste();
    get_date_limit();
    $("#id_pgm").html('<?=lang('messages_lang.labelle_programme')?>');
    $("#div_action").show();
    //get_soutut();
    //get_prog();
    //get_action();
    test();
    exporter_word()
    exporter_pdf()
  });
</script>

<script>  
  function liste()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var row_count ="1000000";
    $("#mytable").DataTable({
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "ajax":{
        url:"<?= base_url('ihm/rapport_classification_admnistrative/listing')?>",
        type:"POST", 
        data:
        {
          INSTITUTION_ID:INSTITUTION_ID,
          SOUS_TUTEL_ID:SOUS_TUTEL_ID,
          PROGRAMME_ID:PROGRAMME_ID,
          ACTION_ID:ACTION_ID,
          DATE_DEBUT:DATE_DEBUT,
          DATE_FIN:DATE_FIN,
          ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID             
        } 
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "orderable":false
      }],
      dom: 'Bfrtlip',
        buttons: [
           
        ],
        language: {
          "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
          "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
          "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
          "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
          "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
          "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> _MAX_ <?= lang('messages_lang.labelle_et_elementtotal')?>)",
          "sInfoPostFix":    "",
          "sLoadingRecords": " <?= lang('messages_lang.labelle_et_chargement')?>",
          "sZeroRecords":    " <?= lang('messages_lang.labelle_et_aucun_element')?>",
          "sEmptyTable":     "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible')?>",
          "oPaginate": {
            "sFirst":      "<?= lang('messages_lang.labelle_et_premier')?>",
            "sPrevious":   "<?= lang('messages_lang.labelle_et_precedent')?>",
            "sNext":       "<?= lang('messages_lang.labelle_et_suivant')?>",
            "sLast":       "<?= lang('messages_lang.labelle_et_dernier')?>"
          },        "oAria": {
            "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
            "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
          }
        }

      });
  }
</script>

<script type="text/javascript">
  function get_date()
  {
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
  }
</script>
<script type="text/javascript">  
  /***********   Script pour la sélection des sous tutelles   ***********/
  function get_soutut()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();

    $.post('<?=base_url('ihm/rapport_classification_admnistrative/get_soutut')?>',
    {
      INSTITUTION_ID : INSTITUTION_ID,
    },
    function(data)
    {
      $('#SOUS_TUTEL_ID').html(data.html);
      if (data.TYPE_INSTITUTION_ID==1)
                {
                    $("#id_pgm").html('<?=lang('messages_lang.labelle_dotation')?>');
                    $("#div_action").hide();
                }else{
                    $("#id_pgm").html('<?=lang('messages_lang.labelle_programme')?>');
                    $("#div_action").show();
                }
      SOUS_TUTEL_ID.InnerHtml=data.html;
      
    })
  }
  
  /***********   Script pour la sélection des programmes   ***********/
  function get_prog()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    // var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();

    $.post('<?=base_url('ihm/rapport_classification_admnistrative/get_prog')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID,
      // SOUS_TUTEL_ID:SOUS_TUTEL_ID
    },
    function(data)
    {
      //alert(data);
      $('#PROGRAMME_ID').html(data.html);
      PROGRAMME_ID.InnerHtml=data.html;
      
    })
  }
  
  /**************   Script pour la sélection des actions   ****************/
  function get_action()
  {
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();

    $.post('<?=base_url('ihm/rapport_classification_admnistrative/get_action')?>',
    {
      PROGRAMME_ID:PROGRAMME_ID
    },
    function(data)
    {
      //alert(data);
      $('#ACTION_ID').html(data.html);
      ACTION_ID.InnerHtml=data.html;
      
    })
  }
</script>
<script type="text/javascript">
  function test()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val()
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()
    var PROGRAMME_ID=$('#PROGRAMME_ID').val()
    var ACTION_ID=$('#ACTION_ID').val()
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var activ = $("input[name='activ']:checked").val();

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}


    document.getElementById("btnexport").href =url="<?=base_url('ihm/rapport_classification_admnistrative/export/')?>"+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+ANNEE_BUDGETAIRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+activ;
  }
</script>
<script type="text/javascript">
  function exporter_word()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val()
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()
    var PROGRAMME_ID=$('#PROGRAMME_ID').val()
    var ACTION_ID=$('#ACTION_ID').val()
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var activ = $("input[name='activ']:checked").val();

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

    document.getElementById("btnexportword").href =url="<?=base_url('ihm/rapport_classification_admnistrative/export_word/')?>"+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+ANNEE_BUDGETAIRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+activ;
  }
</script>
<script type="text/javascript">
  function exporter_pdf()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT = $('#DATE_DEBUT').val();
    var DATE_FIN = $('#DATE_FIN').val();
    var activ = $("input[name='activ']:checked").val();

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}
    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}

    document.getElementById("btnexportpdf").href =url="<?=base_url('ihm/rapport_classification_admnistrative/export_pdf/')?>"+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+PROGRAMME_ID+'/'+ACTION_ID+'/'+ANNEE_BUDGETAIRE_ID+'/'+DATE_DEBUT+'/'+DATE_FIN+'/'+activ;
  }
</script>

<script type="text/javascript">
  function get_date_limit()
  {
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    
    $.ajax({
      url: "<?=base_url('')?>/ihm/rapport_classification_admnistrative/get_date_limit",
      type: "POST",
      dataType: "JSON",
      data: {
        ANNEE_BUDGETAIRE_ID:ANNEE_BUDGETAIRE_ID
      },
      success: function(data) {

        $("#DATE_DEBUT").prop('min',data.datedebut);
        $("#DATE_DEBUT").prop('max',data.datefin);
        $("#DATE_FIN").prop('min',data.datedebut);
        $("#DATE_FIN").prop('max',data.datefin);
      }
    });

  }
</script>