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
                    <div class="col-md-12" style="text-align: center;">
                      <h1 class="header-title text-black">
                        Répartition détaillée des projets par ministère et institution selon leur statut 
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
                            <td rowspan="2">#</td>
                            <td rowspan="2">MINISTERES/INSTITUTIONS</td>
                            <td rowspan="2">NOM&nbsp;&nbsp;PROJET</td>
                            <td colspan="7" style="text-align:center;">TOATL DU BUDGET PAR PROJET</td>
                          </tr>
                          <tr>
                            <th>ENCOURS </th>
                            <th>EN&nbsp;&nbsp;PREPARATION</th>
                            <th>NOUVEAU/APPROUVE</th>
                            <th>IDEE&nbsp;&nbsp;PROJET</th>
                            <th>TERMINE</th>
                            <th>TOTAL&nbsp;&nbsp;PROJETS</th>
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
    list_projet();
    $('#message').delay('slow').fadeOut(3000);
  });
</script>

<script>
function list_projet()
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
      url:"<?= base_url('pip/Repartition_projet/list_projet')?>",
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