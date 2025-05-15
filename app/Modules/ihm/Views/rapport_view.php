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
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="header">
                  <h1 style="margin-left: 20px" class="header-title text-dark">Rapport de classification administrative</h1>
                </div>
                <div class="car-body">
                  <form id="my_form" action="<?= base_url('ihm/Rapport_contr/index') ?>" method="POST">
                    <div class="card-body">
                      <div class="row">
                        <div class="col-3">
                          <div class="form-group">
                            <label>Institution</label> <!-- select2 -->
                            <select class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID" onchange="liste();get_dep();submit_inst();test();">
                              <option value="">Séléctionner</option>
                              <?php
                              foreach($institution as $value)
                              {
                                ?>
                                <option value="<?=$value->CODE_INSTITUTION ?>"><?= $value->DESCRIPTION_INSTITUTION ?></option>
                                <?php
                              }
                              ?>
                            </select>
                          </div>
                        </div>

                        <div class="col-3">
                          <div class="form-group">
                            <label>Sous Tutelle</label>
                            <select class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" value="<?=set_value('SOUS_TUTEL_ID')?>" onchange="liste();get_dep();submit_sous_tutel();test();">
                              <option value="">Séléctionner</option>
                            </select>
                          </div>
                        </div>

                        <div class="col-3">
                          <div class="form-group">
                            <label id="id_pgm"></label>
                            <select class="form-control" name="PROGRAMME_ID" id="PROGRAMME_ID" onchange="liste();get_dep();submit_progr();test();">
                              <option value="">Séléctionner</option>
                            </select>
                          </div>
                        </div>

                        <div id="div_action" class="col-3">
                          <div class="form-group">
                            <label>Action</label>
                            <select class="form-control" name="ACTION_ID" id="ACTION_ID"  onchange="liste();test();">
                              <option value="">Séléctionner</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div style="margin-left:92%; margin-bottom:50px;margin-top:12px">
                      <a href="" id="btnexport" onclick="test()" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span> Exporter</a>
                    </div>
                  </form>

                  <div class="table-responsive" style="width: 100%;">
                    <table id="mytable" class=" table table-bordered table-striped">
                      <thead>
                        <tr>
                          <th>#</th>
                          <th>INSTITUTION</th>
                          <th>SOUS&nbsp;TUTELLE</th>
                          <th>PROGRAMME</th>
                          <th>ACTION</th>
                          <th>ACTIVITE</th>
                          <th>BUDGET&nbsp;T1</th>
                          <th>BUDGET&nbsp;T2</th>
                          <th>BUDGET&nbsp;T3</th>
                          <th>BUDGET&nbsp;T4</th>
                          <th>PROGRAMMATION&nbsp;FINANCIERE&nbsp;BIF</th>
                          <th>BUDGET&nbsp;EXCECUTE&nbsp;T1</th>
                          <th>BUDGET&nbsp;EXCECUTE&nbsp;T2</th>
                          <th>BUDGET&nbsp;EXCECUTE&nbsp;T3</th>
                          <th>BUDGET&nbsp;EXCECUTE&nbsp;T4</th>
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
      </main>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
<script type="text/javascript">
  $(document).ready(function()
  {
    liste();
    $("#id_pgm").html('Programme');
    $("#div_action").show();
  });

  function submit_inst()
  {
    var $SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').html('');
    var $PROGRAMME_ID=$('#PROGRAMME_ID').html('');
    var $ACTION_ID=$('#ACTION_ID').html('');
  }

  function submit_sous_tutel()
  {
    var $PROGRAMME_ID=$('#PROGRAMME_ID').html('');
    var $ACTION_ID=$('#ACTION_ID').html('');
  }

  function submit_progr()
  {
    var $ACTION_ID=$('#ACTION_ID').html('');
  }
</script>

<script type="text/javascript">
  function liste()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();
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
        url:"<?= base_url('/ihm/Rapport_contr/listing')?>",
        type:"POST", 
        data:
        {
          INSTITUTION_ID:INSTITUTION_ID,
          SOUS_TUTEL_ID:SOUS_TUTEL_ID,
          PROGRAMME_ID:PROGRAMME_ID,
          ACTION_ID:ACTION_ID
        },
      },
      lengthMenu: [[10,50,100, row_count], [10,50,100, "All"]],
      pageLength: 10,
      "columnDefs":[
      {
        "targets":[],
        "orderable":false
      }],
      dom: 'Bfrtlip',
      order: [4,'desc'],
      buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print'  ],
      language:
      {
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
        "oPaginate":
        {
          "sFirst":      "Premier",
          "sPrevious":   "Pr&eacute;c&eacute;dent",
          "sNext":       "Suivant",
          "sLast":       "Dernier"
        },
        "oAria":
        {
          "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
          "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
        }
      }
    });
  }
</script>
<script type="text/javascript">
  function get_dep()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var SOUS_TUTEL_ID = $('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID = $('#PROGRAMME_ID').val();
    var ACTION_ID = $('#ACTION_ID').val();

    $.ajax(
    {
      url : "<?=base_url('/ihm/Rapport_contr/get_dep')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:
      {
        INSTITUTION_ID:INSTITUTION_ID,
        SOUS_TUTEL_ID:SOUS_TUTEL_ID,
        PROGRAMME_ID:PROGRAMME_ID,
        ACTION_ID:ACTION_ID
      },
      success:function(data)
      {
        $('#SOUS_TUTEL_ID').html(data.sous_t);
        $('#PROGRAMME_ID').html(data.prog);
        $('#ACTION_ID').html(data.act);
        if (data.TYPE_INSTITUTION_ID==1)
        {
          $("#id_pgm").html('Dotation');
          $("#div_action").hide();
        }else{
          $("#id_pgm").html('Programme');
          $("#div_action").show();
        }
      }
    });  
  }
</script>
<script type="text/javascript">
  function test()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val()
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()
    var PROGRAMME_ID=$('#PROGRAMME_ID').val()
    var ACTION_ID=$('#ACTION_ID').val()

    if (INSTITUTION_ID == '' || INSTITUTION_ID == null) {INSTITUTION_ID = 0}
    if (SOUS_TUTEL_ID == '' || SOUS_TUTEL_ID == null) {SOUS_TUTEL_ID = 0}
    if (PROGRAMME_ID == '' || PROGRAMME_ID == null) {PROGRAMME_ID = 0}
    if (ACTION_ID == '' || ACTION_ID == null) {ACTION_ID = 0}

    document.getElementById("btnexport").href =url="<?=base_url('ihm/Rapport_contr/export/')?>"+'/'+INSTITUTION_ID+'/'+SOUS_TUTEL_ID+'/'+PROGRAMME_ID+'/'+ACTION_ID;
  }
</script>


