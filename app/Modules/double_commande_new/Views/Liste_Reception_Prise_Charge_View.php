<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 
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

                    $recep_prise_en_charge ="btn active";
                    $deja_recep_prise_en_charge ="btn";
                    $recep_dir ="btn";
                    $deja_recep_dir ="btn";
                    $recepion_brb ="btn";
                    $deja_reception_brb ="btn";
                    $bordereau_dc ="btn";
                    $bordereau_deja_dc ="btn";
                    $bordereau_brb ="btn";
                    $bordereau_deja_brb="btn";
                    $valid_faire = "btn";
                    $valid_termnine = "btn";
                    $get_nbr_av_obr1="btn";
                    $get_nbr_av_pc1="btn";
                    ?>
                    <?php include  'includes/Menu_Paiement.php'; ?>
                  </div>
                </div>
                <div class="card-body">
                  <div style="margin-left: 15px;" class="row">
                   <br>
                   <h3 class="header-title text-dark">
                    <?=lang('messages_lang.list_recep_prise_charge')?><br><br>
                  </h3>
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
                  <div class="col-md-4">
                    <label class="form-label"><?=lang('messages_lang.date_debut_du')?>:</label>
                    <input type="date" name="DATE_DEBUT" id="DATE_DEBUT" onchange=";get_date();liste()" max="<?=date('Y-m-d')?>" class="form-control">
                  </div>
                  <div class="col-md-4">
                    <label class="form-label"><?=lang('messages_lang.date_fin_au')?>:</label>
                    <input type="date" name="DATE_FIN" id="DATE_FIN" onchange="liste()" max="<?=date('Y-m-d')?>" class="form-control" disabled>
                  </div>
                </div>
                <div style="margin-left: 15px" class="row">
                  <?php if (session()->getFlashKeys('alert')) : ?>
                  <div class="w-100 bg-success text-white text-center" id="message" >
                    <?php echo session()->getFlashdata('alert')['message']; ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="col-md-12 table-responsive container ">
                <table id="mytable" class=" table table-responsive table-bordered table-striped" style="width:100%">
                  <thead>
                    <tr>
                      <th><?=lang('messages_lang.list_num_bord')?></th>
                      <th><?=lang('messages_lang.list_nbre_bon')?></th>
                      <th><?=lang('messages_lang.list_somme_ordo')?></th>
                      <th><?=lang('messages_lang.list_fichier_bord')?></th>
                      <th><?=lang('messages_lang.labelle_et_action')?></th>
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
    </main>
  </div>
</div>
<?php echo view('includesbackend/scripts_js.php');?>


</body>

</html>
<script type="text/javascript">
  $('#message').delay('slow').fadeOut(30000);
  $(document).ready(function()
  {
    liste();
  });
</script >

<script type="text/javascript">
  function liste() 
  {
    change_count()
    var ANNEE_BUDGETAIRE_ID=$('#ANNEE_BUDGETAIRE_ID').val();
    var DATE_DEBUT=$('#DATE_DEBUT').val();
    var DATE_FIN=$('#DATE_FIN').val();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Liste_Reception_Prise_Charge/listing')?>",
        type: "POST",
        data: {
          ANNEE_BUDGETAIRE_ID,
          DATE_DEBUT,
          DATE_FIN
        }
      },

      lengthMenu: [[10, 50, 100, row_count], [10, 50, 100, "All"]],
      pageLength: 10,
      "columnDefs": [{
        "targets": [],
        "orderable": false
      }],

      dom: 'Bfrtlip',
      order: [],
      buttons: [],
      language: {
        "sProcessing": "<?= lang('messages_lang.labelle_et_traitement') ?>",
        "sSearch": "<?= lang('messages_lang.search_button') ?>&nbsp;:",
        "sLengthMenu": "<?= lang('messages_lang.sLengthMenu_enjeux') ?>",
        "sInfo": "<?= lang('messages_lang.sInfo_enjeux') ?>",
        "sInfoEmpty": "<?= lang('messages_lang.sInfo_enjeux_0') ?>",
        "sInfoFiltered": "(<?= lang('messages_lang.filtre_max_total_enjeux') ?>)",
        "sInfoPostFix": "",
        "sLoadingRecords": "<?= lang('messages_lang.labelle_et_chargement') ?>",
        "sZeroRecords": "<?= lang('messages_lang.aucun_element_afficher_enjeux') ?>",
        "sEmptyTable": "<?= lang('messages_lang.labelle_et_aucun_donnee_disponible') ?>",
        "oPaginate": {
          "sFirst": "<?= lang('messages_lang.labelle_1') ?>",
          "sPrevious": "<?= lang('messages_lang.btn_precedent') ?>",
          "sNext": "<?= lang('messages_lang.btn_suivant') ?>",
          "sLast": "<?= lang('messages_lang.labelle_et_dernier') ?>"
        },
        "oAria": {
          "sSortAscending": "<?= lang('messages_lang.sSortAscending_enjeux') ?>",
          "sSortDescending": "<?= lang('messages_lang.sSortDescending_enjeux') ?>"
        }
      }
    });
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


<!--MODAL POUR LES INSTITUTIONS ------>

<div class="modal" id="bon_engagement" role="dialog">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h3 class="modal-title text-center" id="exampleModalLabel">
          <?=lang('messages_lang.list_bon_eng')?>
        </h3>
        <button type="button"  class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="bord_id" id="bord_id">
        <div class="table-responsive">
          <table id='mytable3' class="table table-bordered table-striped table-hover table-condensed " style="width: 100%;">
            <thead>
             <tr class="text-uppercase" >
              <th><?=lang('messages_lang.list_num_bon_eng')?></th>
              <th><?=lang('messages_lang.labelle_devise')?></th>
              <th><?=lang('messages_lang.label_mont_ordonnance')?></th>
              <th><?=lang('messages_lang.list_fichier_bon_eng')?></th>

          </thead>
          <tbody id="table3">

          </tbody>
        </table>

      </div>
      <div class="modal-footer">

        <button class="btn mb-1 btn-secondary" class="close" data-dismiss="modal"><?=lang('messages_lang.quiter_action')?></button>
      </div>

    </div>
  </div>
</div>
</div>



<!--SCRIPT POUR LES INSTITUTIONS------>

<script type="text/javascript">
 function get_bon(id)
 {

   $('#bord_id').val(id);
   var bord_id = $('#bord_id').val();

   $("#bon_engagement").modal("show");

   var row_count ="1000000";
   table=$("#mytable3").DataTable({
    "processing":true,
    "destroy" : true,
    "serverSide":true,
    "oreder":[],
    "ajax":{
      url:"<?= base_url('/double_commande_new/Liste_Reception_Prise_Charge/detail_bons')?>",
      type:"POST",
      data: {
        bord_id:bord_id

      }
    },
    lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
    pageLength: 10,
    "columnDefs":[{
      "targets":[],
      "orderable":false
    }],

    language: {
      "sProcessing":     "Traitement en cours...",
      "sSearch":         "Rechercher&nbsp;:",
      "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
      "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
      "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
      "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
      "sInfoPostFix":    "",
      "sLoadingRecords": "Chargement en cours...",
      "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
      "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
      "oPaginate": {
        "sFirst":      "Premier",
        "sPrevious":   "Pr&eacute;c&eacute;dent",
        "sNext":       "Suivant",
        "sLast":       "Dernier"
      },
      "oAria": {
        "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
        "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
      }
    }

  });

 } 
</script>
<script type="text/javascript">
  function get_date()
  { 
    $("#DATE_FIN").val('');
    $("#DATE_FIN").prop('min',$("#DATE_DEBUT").val());
    $("#DATE_FIN").attr('disabled',false)
  }
</script>