<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <?php 
  $session  = \Config\Services::session();
  $quantite = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_QUANTITE');
  ?>
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
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-12 d-flex">
                   <?php
                   $racrochet="btn";
                   $deja_racrochet="btn"; 
                   $pas_qte_phys="btn active"; 
                   $qte_phys="btn";
                   ?>
                   <?php include  'includes/Menu_Ligne_Budget_includes.php'; ?> 
                 </div>

               </div>

               <div class="card-body" style="margin-top: -20px">


               </div>                       
             </div>
             <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
              <div class="car-body">

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

                <b style="font-size:20px" class="header-title text-black"><?=lang('messages_lang.list_sns_indic')?></b><br><br>
                
                <div class="col-md-12">
                  <div class="row">
                    <div class="col-md-4">
                      <label><?=lang('messages_lang.label_inst')?></label>
                      <select class="form-control select2" name="CODE_INSTITUTION" id="CODE_INSTITUTION" onchange="liste(this.value);get_sous_tutelle()" >
                        <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                        <?php
                        foreach($institutions_user as $key)
                        {
                          if($key->CODE_INSTITUTION==set_value('CODE_INSTITUTION'))
                          {
                            echo "<option value='".$key->CODE_INSTITUTION."'  selected>".$key->DESCRIPTION_INSTITUTION."</option>";
                          }
                          else
                          {
                            echo "<option value='".$key->CODE_INSTITUTION."' >".$key->DESCRIPTION_INSTITUTION."</option>";
                          }
                        }
                        ?>
                      </select>
                    </div>
                    <div class="col-md-4">
                      <label><?=lang('messages_lang.table_st')?></label>

                      <select class="form-control select2" name="CODE_SOUS_TUTEL" id="CODE_SOUS_TUTEL" onchange="liste(this.value);" >
                        <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                      </select>
                    </div>
                  </div>
                </div><br><br>
                <div class="table-responsive container ">
                  <div class="table-responsive" style="width: 100%;">
                    <table id="mytable" class=" table table-striped table-bordered">
                      <thead>
                        <tr>
                          <th><?=lang('messages_lang.code_prog')?></th>
                          <th><?=lang('messages_lang.col_activite')?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</th>
                          <th><?=lang('messages_lang.col_imputation')?></th> 
                          <th><?=lang('messages_lang.etat_avancement')?></th>
                          <th><?=lang('messages_lang.mont_eng_budg')?></th>
                          <th><?=lang('messages_lang.mont_eng_budg')?></th>
                          <th><?=lang('messages_lang.mont_liq')?></th>
                          <th><?=lang('messages_lang.mont_ord')?></th>
                          <th><?=lang('messages_lang.mont_pai')?></th>
                          <th><?=lang('messages_lang.mont_dec')?></th>
                          <?php if($quantite==1){?>
                            <th><center><?=lang('messages_lang.option_action')?></center></th>
                             <?php } ?>
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
  $(document).ready(function ()
  {
    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(3000);
    liste();
  });
</script>

<script>
  function liste()
  {
    var CODE_INSTITUTION=$('#CODE_INSTITUTION').val();
    var CODE_SOUS_TUTEL=$('#CODE_SOUS_TUTEL').val();

    var row_count ="1000000";
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,4],
      "oreder":[[ 0, 'desc' ]],
      "ajax":
      {
        url:"<?= base_url('demande_new/Exec_Budget_Raccrocher/listing')?>",
        type:"POST", 
        data:
        {
          CODE_INSTITUTION:CODE_INSTITUTION,
          CODE_SOUS_TUTEL:CODE_SOUS_TUTEL
        } 
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],

      order: [0,'desc'],
      dom: 'Bfrtlip',
      buttons: [
        'copy', 'csv', 'excel', 'pdf', 'print'
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

<script>
  function get_sous_tutelle()
  {
    var CODE_INSTITUTION=$('#CODE_INSTITUTION').val();
    if(CODE_INSTITUTION=='')
    {
      $('#CODE_SOUS_TUTEL').html('<option value=""><?=lang('messages_lang.labelle_selecte')?></option>');
    }
    else
    {
      $.ajax(
      {
        url:"<?=base_url()?>/demande_new/Proc_Demande_Budget_Corriger/get_sous_tutelle/"+CODE_INSTITUTION,
        type:"GET",
        dataType:"JSON",
        success: function(data)
        {
          $('#CODE_SOUS_TUTEL').html(data.sous_tutel);
        }
      });
    }
  }
</script>