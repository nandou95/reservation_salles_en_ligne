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
                    $recep_prise_en_charge ="btn";
                    $deja_recep_prise_en_charge ="btn";
                    $recep_dir ="btn active";
                    $deja_recep_dir ="btn";
                    $recepion_brb ="btn";
                    $deja_reception_brb ="btn";
                    $bordereau_dc ="btn";
                    $bordereau_deja_dc ="btn";
                    $bordereau_brb ="btn";
                    $bordereau_deja_brb="btn";
                    $valid_faire = "btn";
                    $valid_termnine = "btn";
                    
                    $valid_faire = "btn";
                    $valid_termnine = "btn";
                    $get_nbr_av_obr1="btn";
                    $get_nbr_av_pc1="btn";
                    ?>
                    <?php include  'includes/Menu_Paiement.php'; ?>
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
              <b style="font-size:20px" class="header-title text-black"><?=lang('messages_lang.titr_rec_bordereau')?></b><br><br>

              <div class="table-responsive container ">
                <div class="table-responsive" style="width: 100%;">
                  <table id="mytable" class=" table table-striped table-bordered">
                    <thead>
                      <tr>
                        <th><center><?=lang('messages_lang.rec_dir_comp_bord_trs')?></center></th>
                        <th><center><?=lang('messages_lang.nbre_titr_dec')?></center></th>
                        <th><center><?=lang('messages_lang.comptable_mont_dec')?></center></th>
                        <th><center><?=lang('messages_lang.rec_bordereau')?></center></th>
                        <th><center><?=lang('messages_lang.th_action')?></center></th>
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

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <div style="font-size:13px" id="libelle"></div>
      </div>
      <div class="modal-footer">
        <button class='btn btn-primary btn-md' data-dismiss='modal'>
         <?=lang('messages_lang.quiter_action')?>
       </button>
     </div>
   </div>
 </div>
</div>

<?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function ()
  {
    liste();
  });
</script>
<script>
  function liste()
  {
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
        url:"<?= base_url('double_commande_new/Receptio_Border_Dir_compt/listing')?>",
        type:"POST", 
        data:{} 
      },
      lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],

      order: [0,'desc'],
      dom: 'Bfrtlip',
      buttons: [],
      language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
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
</script>

<script>
  function get_detail_titre(id)
  {
    var id=id;
    $("#detail").modal("show");
    var row_count ="1000000";
       table=$("#mytable2").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "oreder":[],
          "ajax":{
            url:"<?= base_url('/double_commande_new/Receptio_Border_Dir_compt/liste_titre_decaissement')?>",
            type:"POST",
            data: {
              id:id
            
            }
        },
        lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
        pageLength: 10,
        "columnDefs":[{
            "targets":[],
            "orderable":false
        }],

        language: {
        "sProcessing":     "<?= lang('messages_lang.labelle_et_traitement')?>",
        "sSearch":         "<?= lang('messages_lang.labelle_et_rechercher')?>&nbsp;:",
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> _MENU_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> _START_ <?= lang('messages_lang.labelle_et_a')?> _END_ <?= lang('messages_lang.labelle_et_affichage_sur')?> _TOTAL_ <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoEmpty":      "<?= lang('messages_lang.labelle_et_affichage_element')?> 0 <?= lang('messages_lang.labelle_et_a')?> 0 <?= lang('messages_lang.labelle_et_affichage_sur')?> 0 <?= lang('messages_lang.labelle_et_element')?>",
        "sInfoFiltered":   "(<?= lang('messages_lang.labelle_et_affichage_filtre')?> MAX <?= lang('messages_lang.labelle_et_elementtotal')?>)",
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
</script>


<div class="modal fade" id="detail">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <b style="font-size:20px" class="modal-title header-title text-black" id="exampleModalLabel"><?=lang('messages_lang.liste_titre')?></b>
      </div>
      <div class="modal-body">


        <div class="table-responsive">
          <table id='mytable2' class="table table-bordered table-striped table-hover table-condensed " style="width: 100%;">
            <thead>
              <tr> 
                <th><?=lang('messages_lang.numer_titr')?></th>
                <th><?=lang('messages_lang.th_devise')?></th>
                <th><?=lang('messages_lang.comptable_mont_dec')?></th>
                <th><?=lang('messages_lang.labelle_et_statut')?></th> 
              </tr>

            </thead>

            <tbody id="table2">

            </tbody>
          </table>
          <div class="modal-footer">

            <button class="btn mb-1 btn-primary" class="close" data-dismiss="modal"><?=lang('messages_lang.quiter_action')?></button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

  <script type="text/javascript">
  function get_doc(id)
  {

    $.ajax(
    {
      url:"<?=base_url()?>/double_commande_new/Receptio_Border_Dir_compt/get_path_bord/"+id,
      type:"POST",
      dataType:"JSON",
      success: function(data)
      {
        var embed = data.documentBord;
        document.getElementById("documentBord").innerHTML=embed
      }
    });

    $('#doc_bord').modal('show')

  }
</script>

  <div class="modal fade" id="doc_bord">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <center><?=lang('messages_lang.rec_bordereau')?></center>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
       
            <div id="documentBord">
              
            </div>
           
          </div>
          <div class="modal-footer">
            <button class="btn btn-primary btn-md" data-dismiss="modal">
              <?= lang('messages_lang.quiter_action') ?>
            </button>
          </div>
        </div>
      </div>
    </div>

