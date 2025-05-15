<!DOCTYPE html>
<html lang="en">
<head>
  <?php 
  $session  = \Config\Services::session();
  $userfiancier = $session->get('SESSION_SUIVIE_PTBA_RACCROCHAGE_FINANCIER');
  echo view('includesbackend/header.php');
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
                   $pas_qte_phys="btn"; 
                   $qte_phys="btn";
                   $infer="btn active";
                   $super="btn";?>
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

                <div class="card-header">
                 <b style="font-size:20px" class="header-title text-black"><?= $titre;?></b><br><br>            
                 <div class="row col-md-12">
                  <div class="form-group">
                    <label for="Nom" class="form-label"><?=lang('messages_lang.labelle_institution')?></label>
                    <select onchange="liste_inf(this.value)" class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID">
                      <option value=""><?=lang('messages_lang.label_selecte')?></option>
                      <?php
                      foreach($get_institution as $key)
                      {
                        if($key->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                        {
                          echo "<option value='".$key->INSTITUTION_ID."'  selected>".$key->CODE_INSTITUTION." - ".$key->DESCRIPTION_INSTITUTION."</option>";
                        }
                        else
                        {
                          echo "<option value='".$key->INSTITUTION_ID."' >".$key->CODE_INSTITUTION." - ".$key->DESCRIPTION_INSTITUTION."</option>";
                        }
                      }
                      ?>
                    </select>
                  </div>
                </div>
              </div>
              <div class="table-responsive container ">
                <div class="table-responsive" style="width: 100%;">
                  <table id="mytable" class=" table table-striped table-bordered">
                    <thead>
                      <tr>
                        <!-- <th><center>#</center></th> -->
                        <th><center><?=lang('messages_lang.titre_code_budg')?></center></th>
                        <th><center><?=lang('messages_lang.commentaire')?></center></th>
                        <th><center><?=lang('messages_lang.th_montant_engage')?></center></th>
                        <th><center><?=lang('messages_lang.th_montant_transfert_avant_credit')?></center></th>
                        <th><center><?=lang('messages_lang.th_montant_tranfert_apres_credit')?> </center></th>
                        <th><center><?=lang('messages_lang.th_montant_juridique')?></center></th>
                        <th><center><?=lang('messages_lang.th_montant_liquidation')?></center></th>
                        <th><center><?=lang('messages_lang.th_montant_ordonancement')?></center></th>
                        <th><center><?=lang('messages_lang.th_montant_paiement')?></center></th>
                        <th><center><?=lang('messages_lang.th_montant_decaissemnt')?></center></th>
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
    liste_inf();
  });
</script>
<script type="text/javascript">
  function liste_inf()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();

    var row_count ="1000000";
    $('#message').delay('slow').fadeOut(3000);
    $("#mytable").DataTable(
    {
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[0,2],
      "oreder":[[ 0, 'desc' ]],
      "ajax":{
        url:"<?= base_url('demande_new/MontantVote_Inf_MontantRaccroche/liste_vote_inf_racc')?>",
        type:"POST",
        data:
        {
         
          INSTITUTION_ID:INSTITUTION_ID
        }
      },

      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[0,3],
        "orderable":false
      }],

      dom: 'Bfrtlip',
      order: [2,'desc'],
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
  };
</script>