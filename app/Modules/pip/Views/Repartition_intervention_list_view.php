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
                  <div class="col-md-12 d-flex">
                    <!-- <div class="col-md-9" style="float: left;"> -->
                    <div class="col-md-12" style="text-align: center;">
                      <h1 class="header-title text-black">
                      Répartition de l’investissement public selon les Axes d’Intervention du PND (en BIF)
                      </h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                    </div>
                  </div>
                </div>
                
                <div class="card-body">
                  <div class="row">
                    
                  </div>
                  <div class="row">
                    <?php
                    if(session()->getFlashKeys('alert'))
                    {
                      ?>
                      <div class="col-md-12">
                        <div class="w-100 bg-success text-white text-center" id="message">
                          <?php echo session()->getFlashdata('alert')['message']; ?>
                        </div>
                      </div>
                      <?php
                    }
                    ?>
                    <div class="table-responsive" style="width: 100%;">
                      <table id="mytable" class=" table table-striped table-bordered">
                        <thead>
                          <tr>
                            <td rowspan="2">#</thd>
                            <td rowspan="2">AXE&nbsp;&nbsp;D'INTERVENTION&nbsp;&nbsp;PND</td>
                            <td colspan="5" style="text-align:center;">REPARTITION DES BESOINS FINANCIERS EXPRIMES PAR ANNEE (BIF)</td>
                          </tr>
                          <tr>
                            <th>2023-2024</th>
                            <th>2024-2025 </th>
                            <th>2025-2026</th>
                            <th>TOTAL</th>
                            <th>%</th>
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
    list_intervention();
    $('#message').delay('slow').fadeOut(3000);
  });
</script>

<script>
function list_intervention()
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
      url:"<?= base_url('pip/Repartition_intervention_pnd/list_intervention')?>",
      type:"POST", 
      data:
      {
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
    language:
    {
      "sProcessing":     "Traitement en cours...",
      "sSearch":         "Rechercher&nbsp;:",
      "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
      "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
      "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
      "sInfoFiltered":   "(filtr&eacute; de MAX &eacute;l&eacute;ments au total)",
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