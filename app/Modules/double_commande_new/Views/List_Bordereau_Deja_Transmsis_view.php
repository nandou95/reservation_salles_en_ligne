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
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                    <?php
                      $reception_obr = "btn";
                      $prise_charge_compt = "btn";
                      $etab_titre = "btn";
                      $sign_dir_compt = "btn";
                      $sign_dir_dgfp = "btn";
                      $sign_dir_min = "btn";
                      
                      $bordereau_dc ="btn";
                      $bordereau_deja_dc ="btn active";
                      $bordereau_brb ="btn";
                      $bordereau_deja_brb ="btn";
                      $recep_prise_en_charge ="btn";
                      $deja_recep_prise_en_charge ="btn";
                      $recep_dir ="btn";
                      $deja_recep_dir ="btn";
                      $recepion_brb ="btn";
                      $deja_reception_brb="btn";
                      $valid_faire = "btn";
                      $valid_termnine = "btn";
                      $get_nbr_av_obr1="btn";
                      $get_nbr_av_pc1="btn";
                    ?>
                    <?php include 'includes/Menu_Paiement.php';?>
                  </div>
                </div>
                <div class="card-body" style="margin-top: -20px"></div>
              </div>
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="car-body">
                  <div class="row col-md-12">
                    <div class="col-md-10">
                      <h3 class="text-black"><?=$title;?></h3>
                    </div>
                  </div>
                  <div style="margin-left: 15px;" class="row">
                    <div class="col-md-4">
                    <div class="form-group">
                      <label class="form-label"><?=lang('messages_lang.select_anne_budget')?></label>
                      <select onchange="liste()" class="form-control" name="ANNEE_BUDGETAIRE_ID" id="ANNEE_BUDGETAIRE_ID">
                        <?php
                        foreach($annee as $key)
                        {
                          if($key->ANNEE_BUDGETAIRE_ID==$annee_encours)
                          {
                            echo "<option value='".$key->ANNEE_BUDGETAIRE_ID."' selected>".$key->ANNEE_DESCRIPTION."</option>";
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
                      <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange=";get_date();liste()" max="<?=date('Y-m-d')?>" class="form-control">
                      <font color="red" id="error_DATE_DEBUT"></font>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                      <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="liste()" max="<?=date('Y-m-d')?>" class="form-control" disabled>
                    </div>
                  </div>
                  <div class="mt-2 ml-4" style ="max-width: 15%;">
                    <a href="#" id="btnexportexcel" onclick="exporter_excel()" type="button" style="float: center;margin-top: 0px;" class="btn btn-primary"><span class="fa fa-file-excel pull-center"></span> Excel</a>
                  </div>
                  <div class="table-responsive container ">
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <tr>
                                <th><center>#</center></th>
                                  <th><center><?=lang('messages_lang.th_num_titre')?></center></th>
                                  <th><center><?=lang('messages_lang.list_num_bord')?></center></th>
                                  <th><center><?=lang('messages_lang.th_devise')?></center></th>
                                  <th><center><?=lang('messages_lang.th_montant_decais')?></center></th>
                                  <th><center><?=lang('messages_lang.pip_rapport_institutio_filtre')?></center></th>
                              </tr>
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
  $(document).ready(function() {
    liste();
  });

  function liste()
  {
    change_count()
    var row_count ="1000000";
    var DATE_DEBUT=$('#DATE_DEBUT').val();
    var DATE_FIN=$('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,2],
      "oreder":[[ 0, 'desc' ]],
      "ajax":{
        url:"<?= base_url('double_commande_new/List_Bordereau_Deja_Transmsis/listing')?>",
        type:"POST",
        data: {
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
      buttons: [],
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
        },
        "oAria": {
          "sSortAscending":  ": <?= lang('messages_lang.labelle_et_trier_colone')?>",
          "sSortDescending": ": <?= lang('messages_lang.labelle_et_trier_activer_trier')?>"
        }
      }
    });
  }

  function get_date()
  {
    $("#DATE_FIN").val('');
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    $("#DATE_FIN").attr('disabled',false)
  }

  function exporter_excel() 
  {
    var DATE_DEBUT=$('#DATE_DEBUT').val();
    var DATE_FIN=$('#DATE_FIN').val();
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val()

    if (DATE_DEBUT == '' || DATE_DEBUT == null) {DATE_DEBUT = 0}
    if (DATE_FIN == '' || DATE_FIN == null) {DATE_FIN = 0}
    if (ANNEE_BUDGETAIRE_ID == '' || ANNEE_BUDGETAIRE_ID == null) {ANNEE_BUDGETAIRE_ID = 0}

    DATE_DEBUT == 0 ? 
      $('#error_DATE_DEBUT').text("Le champ est obligatoire")
      : $('#error_DATE_DEBUT').text("");

    if(DATE_DEBUT == 0) return

    document.getElementById("btnexportexcel").href = "<?=base_url('double_commande_new/List_Bordereau_Deja_Transmsis/exporter_Excel')?>/"+ANNEE_BUDGETAIRE_ID+"/"+DATE_DEBUT+"/"+DATE_FIN;
  }
</script>
<script>
  function change_count()
  {
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    ANNEE_BUDGETAIRE_ID=(ANNEE_BUDGETAIRE_ID!='')?ANNEE_BUDGETAIRE_ID:0
    var DATE_DEBUT=$('#DATE_DEBUT').val();
    DATE_DEBUT=(DATE_DEBUT!='')?DATE_DEBUT:0
    var DATE_FIN=$('#DATE_FIN').val();
    DATE_FIN=(DATE_FIN!='')?DATE_FIN:0

    $.post('<?=base_url('double_commande_new/Liste_Paiement/change_count')?>',
    {
      ANNEE_BUDGETAIRE_ID,
      INSTITUTION_ID:0,
      SOUS_TUTEL_ID:0,
      DATE_DEBUT,
      DATE_FIN
    },
    function(data)
    {
      $('#recep_prise_charge').html(data.recep_prise_charge);
      $('#deja_recep_prise_charge').html(data.deja_recep_prise_charge);
      $('#get_nbr_av_obr').html(data.get_nbr_av_obr);      
      $('#get_recep_obr').html(data.get_recep_obr);      
      $('#get_nbr_av_pc').html(data.get_nbr_av_pc);      
      $('#get_prise_charge').html(data.get_prise_charge);      
      $('#get_prise_charge_corr').html(data.get_prise_charge_corr);     
      $('#get_etape_reject_pc').html(data.get_etape_reject_pc);      
      $('#get_etape_corr').html(data.get_etape_corr);      
      $('#get_etab_titre').html(data.get_etab_titre);      
      $('#get_recep_td_corriger').html(data.get_recep_td_corriger);     
      $('#get_etab_titre_corr').html(data.get_etab_titre_corr);      
      $('#get_bord_dc').html(data.get_bord_dc);      
      $('#get_bord_deja_dc').html(data.get_bord_deja_dc);      
      $('#recep_dir_comptable').html(data.recep_dir_comptable);      
      $('#deja_recep_dir_comptable').html(data.deja_recep_dir_comptable);      
      $('#get_sign_dir_compt').html(data.get_sign_dir_compt);      
      $('#get_sign_dir_dgfp').html(data.get_sign_dir_dgfp);      
      $('#get_sign_ministre').html(data.get_sign_ministre);      
      $('#get_titre_valide').html(data.get_titre_valide);      
      $('#get_titre_termine').html(data.get_titre_termine);      
      $('#get_bord_brb').html(data.get_bord_brb);      
      $('#get_bord_deja_trans_brb').html(data.get_bord_deja_trans_brb); 
    })
  }
</script>