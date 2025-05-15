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
               <br>
               <div class="col-12 d-flex">

                <div class="col-9" style="float: left;">
                  <h1 class="header-title text-dark">
                    Liste des activités
                  </h1>
                </div>
                <div class="col-3" style="float: right;">
                </div>
              </div>
              <br>
              
              <div class="car-body">

               <div style="margin-left: 15px" class="row">
                <?php if (session()->getFlashKeys('alert')) : ?>
                  <div class="alert alert-success" id="message">
                    <?php echo session()->getFlashdata('alert')['message']; ?>
                  </div>
                <?php endif; ?>
              </div>

              <div class="table-responsive container ">
                
                <div></div>
                <table id="mytable" class=" table table-bordered table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>ACTION</th>
                      <th>CODE&nbsp;BUDGETAIRE</th>
                      <th>ACTIVITE</th>
                      <th>RESULTATS&nbsp;ATTENDUS</th>
                      <th>UNITE</th>
                      <th>Q&nbsp;TOTAL</th>
                      <th>COUT&nbsp;UNITAIRE</th>
                      <th>QT1</th>
                      <th>QT2 </th>
                      <th>QT3</th>
                      <th>QT4</th>
                      <th>GRANDE&nbsp;MASSE</th>
                      <!-- <th>OPTION</th> -->
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
  </main>
</div>
</div>
<?php echo view('includesbackend/scripts_js.php');?>


</body>

</html>


<script>
  $('#message').delay('slow').fadeOut(3000);
  $(document).ready(function()
  {
    var row_count ="1000000";
    $("#mytable").DataTable({
      "processing":true,
      "destroy" : true,
      "serverSide":true,
      "targets":[],
      "order":[[ 0, 'desc' ]],
      "ajax":{

        url:"<?=base_url('ptba/ptba_contr/listing')?>",
        type:"POST", 
      },
      lengthMenu: [[5,10,50, 100, row_count], [5,10,50, 100, "All"]],
      pageLength: 10,
      "columnDefs":[{
        "targets":[],
        "orderable":false
      }],

      dom: 'Bfrtlip',
      buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print'  ],
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
  });
</script>

