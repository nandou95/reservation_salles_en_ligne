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
                  $get_ordon_Afaire1="btn";
                  $get_ordon_Afaire_sup1="btn";
                  $get_ordon_deja_fait1="btn";
                  $bordereau_spe ="btn";
                  $bordereau_deja_spe ="btn";
                  $get_ordon_AuCabinet1="btn active";
                  $get_ordon_BorderCabinet1="btn";
                  $get_ordon_BonCED1="btn";
                  ?>
                  <?php include  'includes/Menu_Ordonnancement.php'; ?>
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
                <!-- <b style="font-size:20px" class="header-title text-black"><?//=$titre?></b><br><br> -->
                <div style="margin-right: 20px; float: right;">
                  <a href="<?= base_url("double_commande_new/Ordonnancement_Ministre/add") ?>" class='btn btn-primary' style="float: right;"> <?= lang("messages_lang.Transmission_TD") ?></h1> </a>
                </div>

                <div class="table-responsive container ">
                  <div class="table-responsive" style="width: 100%;">
                    <table id="mytable" class=" table table-bordered table-striped">
                      <thead>
                        <tr class="text-uppercase text-nowrap">
                          <th> <?= lang("messages_lang.bon_engagement_transmission_du_bordereau") ?> </th>
                          <th> <?= lang("messages_lang.menu_taux_change") ?> </th>
                          <th> <?= lang("messages_lang.table_mont_ord") ?> </th>
                          <th> <?= lang("messages_lang.label_inst") ?> </th>
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
        url:"<?= base_url('double_commande_new/Ordonnancement_Ministre/listing')?>",
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
      language:
      {
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
</script>

<script>
  function get_trans_titre(id)
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
        url:"<?= base_url('/double_commande_new/Transmission_Deja_Reception_BRB/liste_reception_decaissement')?>",
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
        "sLengthMenu":     "<?= lang('messages_lang.labelle_et_afficher')?> MENU <?= lang('messages_lang.labelle_et_element')?>",
        "sInfo":           "<?= lang('messages_lang.labelle_et_affichage_element')?> START <?= lang('messages_lang.labelle_et_a')?> END <?= lang('messages_lang.labelle_et_affichage_sur')?> TOTAL <?= lang('messages_lang.labelle_et_element')?>",
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
              <th><?=lang('messages_lang.comptable_mont_dec')?></th>
            </tr>

          </thead>

          <tbody id="table2">

          </tbody>
        </table>
        <div class="modal-footer">

          <button class="btn mb-1 btn-primary" class="close" data-dismiss="modal"> <?= lang('messages_lang.quiter_action') ?></button>
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<script type="text/javascript">
  function get_docum(id)
  {

    $.ajax(
    {
      url:"<?=base_url()?>/double_commande_new/Transmission_Deja_Reception_BRB/get_path_bordereau/"+id,
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

