<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>

  <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
  <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
            <h1 class="header-title">
              <!-- Agrement d'un ASBL - Détail -->
            </h1>
          </div>

          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                </div>
                <div class="card-body" style="overflow-x:auto;">
                  <div style="margin-top: -40px;" class="card">
                    <div class="card-header">
                    </div>
                    <div class="card-body" style="">
                      <div class="col-md-12">
                        <form>
                          <div class="row">
                            <div class="col-md-8" >
                              <a href="<?= base_url(''.$demande['LINK_ETAPE_SIGEFI'].'/'.$demande['DEM_EXEC_BUDGETAIRE_ADMINISTRATION_ID'])?>"  class="btn btn-primary">Traiter</a>
                            </div>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>  

                  <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;" class="card">
                    <div class="card-header">
                      <input type="hidden" name="ID_DEMANDE" id="ID_DEMANDE" value="<?=$demande['DEM_EXEC_BUDGETAIRE_ADMINISTRATION_ID']?>">
                      <h5 style="color:#187bad"><strong style='font-size: 18px;font-family: Georgia;font-style: oblique 40deg;'><?=$demande['NOM_PROCESS']?></strong><br> <i class="fa-solid fa-arrow-right"></i> <strong style="font-size: 18px;font-family: Georgia;"><?=$demande['DESC_ETAPE_SIGEFI']?></strong></h5>

                      <p class="card-text"></p>
                    </div>
                    <div class="card-body">
                      <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item" role="presentation">
                          <a onclick="show_nav(1)" class="nav-link active" id="detailleformation-tab" data-bs-toggle="tab" data-bs-target="#detailleformation"  role="tab" aria-controls="detailleformation" aria-selected="true"> <i class="fa fa-th-list" aria-hidden="true"></i> <?= lang('messages_lang.link_detail')?>  </a>
                        </li>

                        <li class="nav-item" role="presentation">
                          <a onclick="show_nav(2)" class="nav-link" id="histotraitement-tab" data-bs-toggle="tab" data-bs-target="#histotraitement"  role="tab" aria-controls="histotraitement" aria-selected="false"> <i class="fa fa-history" aria-hidden="true"></i> <?= lang('messages_lang.treatment_history')?></a>
                        </li>

                        <li class="nav-item" role="presentation">
                          <a onclick="show_nav(3)" class="nav-link" id="document-tab" data-bs-toggle="tab" data-bs-target="#document"  role="tab" aria-controls="document" aria-selected="false"> <i class="fa fa-file" aria-hidden="true"></i> <?= lang('messages_lang.label_droits_doc')?></a>
                        </li>
                      </ul>

                      <div   id="detaillepad">
                        <table class="table m-b-0 m-t-20">
                          <tbody>
                            <tr>
                              <tr>
                                <td><i class="fa-solid fa-house"></i> &nbsp<strong><?= lang('messages_lang.labelle_institution')?></strong></td>
                                <td><?=$demande['DESCRIPTION_INSTITUTION'];?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-brands fa-slack"></i> &nbsp<strong><?= lang('messages_lang.label_prog')?></strong></td>
                                <td><?=$demande['INTITULE_PROGRAMME'];?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-ticket"></i> &nbsp<strong><?= lang('messages_lang.label_action')?></strong></td>
                                <td><?=$demande['LIBELLE_ACTION'];?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-house"></i> &nbsp<strong><?= lang('messages_lang.labelle_Soustitre')?></strong></td>
                                <td><?=$demande['DESCRIPTION_SOUS_TUTEL'];?> FBU</td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-barcode"></i> &nbsp<strong><?= lang('messages_lang.labelle_code_budgetaire')?></strong></td>
                                <td><?= $demande['CODE_NOMENCLATURE_BUDGETAIRE'];?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-gears"></i> &nbsp<strong><?= lang('messages_lang.table_activite')?></strong></td>
                                <td><?= $demande['ACTIVITES'];?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-calendar-days"></i> &nbsp<strong><?= lang('messages_lang.label_date_demande')?></strong></td>
                                <td><?= date('d/m/Y H:i',strtotime($demande['DATE_INSERTION']))?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-money-bill"></i> &nbsp<strong><?= lang('messages_lang.mont_eng')?></strong></td>
                                <td><?=number_format($demande['MONTANT_ENGAGE'],2,","," ")?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-money-bill"></i> &nbsp<strong><?= lang('messages_lang.lab_mont_jurid')?></strong></td>
                                <td><?=number_format($demande['MONTANT_JURIDIQUE'],2,","," ")?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-money-bill"></i> &nbsp<strong><?= lang('messages_lang.lab_mont_liquid')?></strong></td>
                                <td><?=number_format($demande['MONTANT_LIQUIDATION'],2,","," ")?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-money-bill"></i> &nbsp<strong><?= lang('messages_lang.lab_mont_fiscal')?></strong></td>
                                <td><?=number_format($demande['MONTANT_FISCAL_ET_NON_FISCAL'],2,","," ")?></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-money-bill"></i> &nbsp<strong><?= lang('messages_lang.lab_mont_decais')?></strong></td>
                                <td><?=number_format($demande['MONTANT_TITRE_DECAISSEMENT'],2,","," ")?></td>
                              </tr>
                            </tr>
                          </tbody>
                        </table>                              
                      </div>

                      <div id="histotraitement">
                        <h1 class="header-title text-black">Historique</h1><?= lang('messages_lang.lab_mont_decais')?>
                        <div class="table-responsive container">
                          <table id="mytable" class="table table-bordered">
                            <thead>
                              <tr>
                                <th><?= lang('messages_lang.labelle_et_proce')?></th>
                                <th><?= lang('messages_lang.labelle_et_etape')?></th>      
                                <th><?= lang('messages_lang.th_nom')?></th>
                                <th><?= lang('messages_lang.lab_date_insert')?></th>
                              </tr>
                            </thead>
                          </table>
                        </div>
                      </div>

                      <div id="documents">
                        <table class="table m-b-0 m-t-20">
                          <tbody>
                            <tr>
                              <tr>
                                <td><i class="fa-solid fa-money-check"></i> &nbsp<strong><?= lang('messages_lang.lab_dem_bord')?></strong></td>
                                <td><a onclick="fichier1()"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></a></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-file"></i> &nbsp<strong><?= lang('messages_lang.lab_doc_ppm')?></strong></td>
                                <td><a onclick="fichier2()"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></a></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-file"></i> &nbsp<strong><?= lang('messages_lang.titre_decaissement')?></strong></td>
                                <td><a onclick="fichier3()"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></a></td>
                              </tr>

                              <tr>
                                <td><i class="fa-solid fa-file-invoice"></i> &nbsp<strong><?= lang('messages_lang.lab_fich_factur')?></strong></td>
                                <td><a onclick="fichier4()"><span class="fa fa-file-pdf" style="color:red;font-size: 200%;"></span></a></td>
                              </tr>
                            </tr>
                          </tbody>
                        </table>                       
                      </div>

                      <div class="modal modal-xl" id="mymodal1">
                        <div class="modal-dialog ">
                          <div class="modal-content">

                            <div class="modal-header btn btn-dark">
                              <h5 class="modal-title text-white" style="text-align: center;" id="title_modal"></h5>

                            </div>
                            <div class="modal-body"> 
                             <div class="col-md-12" id="fichier">
                              <embed  src="<?=base_url('uploads/doc_execution_budgetaire2/'.$demande['TITRE_DECAISSEMENT'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="modal modal-xl" id="mymodal2">
                        <div class="modal-dialog ">
                          <div class="modal-content">

                            <div class="modal-header btn btn-dark">
                              <h5 class="modal-title text-white" style="text-align: center;" id="title_modal"></h5>

                            </div>
                            <div class="modal-body"> 
                             <div class="col-md-12" id="fichier">
                              <embed  src="<?=base_url('uploads/doc_execution_budgetaire2/'.$demande['DOC_PPM'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="modal modal-xl" id="mymodal3">
                        <div class="modal-dialog ">
                          <div class="modal-content">

                            <div class="modal-header btn btn-dark">
                              <h5 class="modal-title text-white" style="text-align: center;" id="title_modal"></h5>

                            </div>
                            <div class="modal-body"> 
                             <div class="col-md-12" id="fichier">
                              <embed  src="<?=base_url('uploads/doc_execution_budgetaire2/'.$demande['TITRE_DECAISSEMENT'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="modal modal-xl" id="mymodal4">
                        <div class="modal-dialog ">
                          <div class="modal-content">

                            <div class="modal-header btn btn-dark">
                              <h5 class="modal-title text-white" style="text-align: center;" id="title_modal"></h5>

                            </div>
                            <div class="modal-body"> 
                             <div class="col-md-12" id="fichier">
                              <embed  src="<?=base_url('uploads/doc_execution_budgetaire2/'.$demande['FILE_FACTURE'])?>" scrolling="auto" height="500px" width="100%" frameborder="0">
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>


                    <!-- <div class="tab-content" id="myTabContent">
                      
                    </div> -->
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
      function fichier1()
      {
        $('#mymodal1').modal('show');
      }
      function fichier2()
      {
        $('#mymodal2').modal('show');
      }
      function fichier3()
      {
        $('#mymodal3').modal('show');
      }
      function fichier4()
      {
        $('#mymodal4').modal('show');
      }

      
      $(document).ready(function()
      {
        $('#histotraitement').prop('hidden',true);
        $('#documents').prop('hidden',true);

      });

    </script> 


    <script type="text/javascript">
  // liste historique
      function liste() {
       var id = $('#ID_DEMANDE').val()
       $("#mytable").DataTable({
        "destroy": true,
        "processing": true,
        "serverSide": true,
        "ajax": {
          url:"<?= base_url()?>/demande_new/Demande_list/listing_historique/"+id,
          type: "POST",
          data: {},
          beforeSend: function() {}
        },
        lengthMenu: [
          [5,10, 50, 100, -1],
          [5,10, 50, 100, "All"]
          ],
        pageLength: 5,
        "columnDefs": [{
          "targets": [],
          "orderable": false
        }],
        dom: 'Bfrtlip',
        order:[1,'desc'],
        buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
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
    function show_nav(tab) {
      var id = $('#ID_DEMANDE').val()
      if (tab==2) {
        $('#histotraitement').prop('hidden',false);
        $('#detaillepad').prop('hidden',true);
        $('#documents').prop('hidden',true);
        $('#histotraitement-tab').prop('class',"nav-link active");
        $('#detailleformation-tab').prop('class',"nav-link");
        $('#document-tab').prop('class',"nav-link");
        liste(id)
      }
      else if (tab==1)
      {
        $('#histotraitement').prop('hidden',true);
        $('#detaillepad').prop('hidden',false);
        $('#documents').prop('hidden',true);
        $('#histotraitement-tab').prop('class',"nav-link");
        $('#detailleformation-tab').prop('class',"nav-link active");
        $('#document-tab').prop('class',"nav-link");
      }else if(tab==3)
      {
        $('#histotraitement').prop('hidden',true);
        $('#detaillepad').prop('hidden',true);
        $('#documents').prop('hidden',false);
        $('#histotraitement-tab').prop('class',"nav-link");
        $('#detailleformation-tab').prop('class',"nav-link");
        $('#document-tab').prop('class',"nav-link active");
      }

    }
  </script>

