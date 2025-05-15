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
                    $eng_budg_a_corriger="btn active";
                    $eng_budg_a_valider="btn";
                    $eng_jurd_a_faire="btn";
                    $eng_jurd_a_corriger="btn";
                    $eng_jurd_a_valider="btn";
                    $liq_a_faire="btn";
                    $liq_a_corriger="btn";
                    $liq_a_valider="btn";
                    $ord_a_valider="btn";
                  ?>
                  <?php include  'includes/Menu_Montant_Exec_Par_Phase.php'; ?> 
                </div>
              </div>
            <div class="card-body">
              <div class="col-12" style="float: left;">
                <br>
                <h1 class="header-title text-dark">
                  Liste des tâches<br>
                </h1>
              </div>
              <div class="row">
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label"><?=lang('messages_lang.label_trimestre')?></label>
                    <select onchange="liste()" class="form-control" name="TRIMESTRE_ID" id="TRIMESTRE_ID">
                      <option value="">-<?=lang('messages_lang.labelle_selecte')?>-</option>
                      <?php
                        foreach ($trimestre as $key) 
                        {
                          if ($key->TRIMESTRE_ID==5) {
                            echo '<option value="'.$key->TRIMESTRE_ID.'" selected>'.$key->DESC_TRIMESTRE.'</option>';
                          }
                          else
                          {
                            echo '<option value="'.$key->TRIMESTRE_ID.'">'.$key->DESC_TRIMESTRE.'</option>';
                          }                          
                        }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label"><?=lang('messages_lang.labelle_institution')?></label>
                    <select onchange="get_prog()" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                      <option value="">-<?=lang('messages_lang.labelle_selecte')?>-</option>
                      <?php
                      foreach($institutions as $key)
                      {
                        echo "<option value='".$key->INSTITUTION_ID."' >".$key->CODE_INSTITUTION."&nbsp;&nbsp;-&nbsp;&nbsp;".$key->DESCRIPTION_INSTITUTION."</option>";
                      }
                      ?>
                    </select>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="form-group">
                    <label class="form-label"><?=lang('messages_lang.menu_programme')?></label>
                    <select onchange="liste()" class="form-control" name="PROGRAMME_ID" id="PROGRAMME_ID">
                      <option value="">-<?=lang('messages_lang.labelle_selecte')?>-</option>
                    </select>
                  </div>
                </div>
              </div>
              <div style="margin-left: 15px" class="row">
                <?php if (session()->getFlashKeys('alert')) : ?>
                <div class="w-100 bg-success text-white text-center" id="message" >
                  <?php echo session()->getFlashdata('alert')['message']; ?>
                </div>
              <?php endif; ?>
            </div>

            <div class="table-responsive container row">

              <div></div>
              <table id="mytable" class=" table table-bordered table-striped">
                <thead>
                  <tr>
                    <th><?=lang('messages_lang.col_activite')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    <th><?=lang('messages_lang.th_tache')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                    <th><?=lang('messages_lang.mont_eng_budg')?></th>
                    <th><?=lang('messages_lang.mont_eng_jur')?></th>                    
                    <th><?=lang('messages_lang.mont_liq')?></th>
                    <th><?=lang('messages_lang.mont_ord')?></th>
                    <th><?=lang('messages_lang.mont_pai')?></th>
                    <th><?=lang('messages_lang.mont_dec')?></th>
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
    var INSTITUTION_ID = $('#INSTITUTION_ID').val(); 
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var TRIMESTRE_ID=$('#TRIMESTRE_ID').val()
    change_count();
    var row_count = "1000000";
    $("#mytable").DataTable({
      "destroy": true,
      "processing": true,
      "serverSide": true,
      "ajax": {
        url: "<?= base_url('double_commande_new/Montant_Execution_Par_Tache/listing')?>",
        type: "POST",
        data: {
          TRIMESTRE_ID:TRIMESTRE_ID,
          INSTITUTION_ID: INSTITUTION_ID,
          PROGRAMME_ID:PROGRAMME_ID
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
<script type="text/javascript">
  function get_prog()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();

    $.post('<?=base_url('double_commande_new/Montant_Execution_Par_Tache/get_prog')?>',
    {
      INSTITUTION_ID:INSTITUTION_ID
    },
    function(data)
    {
      $('#PROGRAMME_ID').html(data.html);
      PROGRAMME_ID.InnerHtml=data.html;
      liste();
    })
  }
</script>
<script>
  function change_count()
  {
    var TRIMESTRE_ID=$('#TRIMESTRE_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();

    $.post('<?=base_url('double_commande_new/Montant_Execution_Par_Tache/change_count')?>',
    {
      TRIMESTRE_ID:TRIMESTRE_ID,
      INSTITUTION_ID:INSTITUTION_ID,
      PROGRAMME_ID:PROGRAMME_ID,
    },
    function(data)
    {
      $('#div_engag_budj').html("<span>"+data.ENG_BUDG+"<span>");
      div_engag_budj.InnerHtml=data.ENG_BUDG;
      $('#div_engag_jur').html("<span>"+data.ENG_JURD+"<span>");
      div_engag_jur.InnerHtml=data.ENG_JURD;
      $('#div_liqui').html("<span>"+data.LIQUIDATION+"<span>");
      div_liqui.InnerHtml=data.LIQUIDATION;
      $('#div_ordo').html("<span>"+data.ORDONNANCEMENT+"<span>");
      div_ordo.InnerHtml=data.ORDONNANCEMENT;
      $('#div_paiem').html("<span>"+data.PAIEMENT+"<span>");
      div_paiem.InnerHtml=data.PAIEMENT;
      $('#div_dec').html("<span>"+data.DECAISSEMENT+"<span>");
      div_dec.InnerHtml=data.DECAISSEMENT;
    })
  }
</script>