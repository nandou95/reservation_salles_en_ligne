
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
                                    <div class="row col-md-12">
                                        <div class="col-md-8">
                                            <h1 class="header-title text-black">
                                                Rapport de suivi évaluation
                                            </h1>
                                        </div>
                                    </div>
                                </div><br>
                               
                            <div class="car-body">

                            <div class="row">

                                <div class="col-md-3">
                                    <label>Institution</label>

                                    <select class="form-control select2" name="CODE_INSTITUTION" id="CODE_INSTITUTION" onchange="liste(this.value);get_sous_tutelle();" >
                                        <option value="">Sélectionner</option>
                                          <?php
                                        foreach($institutions as $key)
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
                                <div class="col-md-3">
                                    <label>Sous tutelle</label>

                                    <select class="form-control select2" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" onchange="liste(this.value);get_programme()" >
                                        <option value="">Sélectionner</option>
                                          
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label>Programme</label>
                                    <select class="form-control" name="CODE_PROGRAMME" id="CODE_PROGRAMME" onchange="liste(this.value);get_action()" >
                                        <option value="">Sélectionner</option>
                                      
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label>Action</label>
                                    <select class="form-control" name="CODE_ACTION" id="CODE_ACTION" onchange="liste(this.value);" >
                                        <option value="">Sélectionner</option>
                                      
                                    </select>
                                </div>
                            </div><br>

                            <div class="col-md-6" style="float: right;">
                                <a href="<?=base_url('dashboard/Suivi_Rapport_Evaluation/exporter') ?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span> Exporter</a>
                                <!-- <button onclick="exporter_test()" type="button" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-file-excel-o pull-right"></span> Exporter test</button> -->
                            </div>
                               
                                <div class="table-responsive" style="width: 100%;">
                                        
                                    <table id="mytable" class=" table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>INSTITUTION</th>
                                                <th>SOUS&nbsp;TUTELLE</th>
                                                <th>PROGRAMME</th>
                                                <th>ACTION</th>
                                                <th>CODE&nbsp;BUDGETAIRE</th>
                                                <th>ACTIVITE&nbsp;PREVUE</th>
                                                <th>RESULTAT&nbsp;ATTENDU</th>
                                                <th>BUDGET&nbsp;VOTE</th>
                                                <th>TRANSFERT&nbsp;CREDIT</th>
                                                <th>CREDIT&nbsp;APRES&nbsp;TRANSFERT</th>
                                                <th>ENGAGEMENT&nbsp;BUDGETAIRE</th>
                                                <th>ENGAGEMENT&nbsp;JURIDIQUE</th>
                                                <th>LIQUIDATION</th>
                                                <th>ORDONNANCEMENT</th>
                                                <th>DECAISSEMENT</th>
                                                <th>ECART&nbsp;BUDGETAIRE</th>
                                                <th>ECART&nbsp;JURIDIQUE</th>
                                                <th>ECART&nbsp;LIQUIDATION</th>
                                                <th>ECART&nbsp;ORDONNANCEMENT</th>
                                                <th>ECART&nbsp;DECAISSEMENT</th>

                                                <th>TAUX&nbsp;BUDGETAIRE</th>
                                                <th>TAUX&nbsp;JURIDIQUE</th>
                                                <th>TAUX&nbsp;LIQUIDATION</th>
                                                <th>TAUX&nbsp;ORDONNANCEMENT</th>
                                                <th>TAUX&nbsp;DECAISSEMENT</th>
                                            </tr>
                                        </thead>
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
  $(document).ready(function ()
  {
    liste();
  });
</script>


<script>
  
    function liste()
    {
        var CODE_INSTITUTION=$('#CODE_INSTITUTION').val()
        var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()
        var CODE_PROGRAMME=$('#CODE_PROGRAMME').val()
        var CODE_ACTION=$('#CODE_ACTION').val()

        var row_count ="1000000";
        $("#mytable").DataTable({
          "processing":true,
          "destroy" : true,
          "serverSide":true,
          "targets":[0,4],
          "oreder":[[ 0, 'desc' ]],
          "ajax":{
            url:"<?= base_url('dashboard/Suivi_Rapport_Evaluation/liste')?>",
            type:"POST", 
            data:
            {
              CODE_INSTITUTION:CODE_INSTITUTION,
              SOUS_TUTEL_ID:SOUS_TUTEL_ID,
              CODE_PROGRAMME:CODE_PROGRAMME,
              CODE_ACTION:CODE_ACTION,
            } 
          },

          lengthMenu: [[10,50, 100, row_count], [10,50, 100, "All"]],
          pageLength:5,
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

<script>
    

    function get_sous_tutelle()
    {
        var CODE_INSTITUTION=$('#CODE_INSTITUTION').val();

        if(CODE_INSTITUTION=='')
        {
          $('#SOUS_TUTEL_ID').html('<option value="">Sélectionner</option>');
        }
        else
        {
          $.ajax(
          {
            url:"<?=base_url()?>/dashboard/Suivi_Rapport_Evaluation/get_sous_tutelle/"+CODE_INSTITUTION,
            type:"GET",
            dataType:"JSON",
            success: function(data)
            {
              $('#SOUS_TUTEL_ID').html(data.sous_tutel);
            }
          });

        }
    }

    function get_programme()
    {
        var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();

        if(SOUS_TUTEL_ID=='')
        {
            $('#CODE_PROGRAMME').html('<option value="">Sélectionner</option>');
        }
        else
        {
            $.ajax(
            {
                url:"<?=base_url()?>/dashboard/Suivi_Rapport_Evaluation/get_programme/"+SOUS_TUTEL_ID,
                type:"GET",
                dataType:"JSON",
                success: function(data)
                {
                  $('#CODE_PROGRAMME').html(data.programs);
                }


            });

        }
    }

    function get_action()
    {
        var CODE_PROGRAMME=$('#CODE_PROGRAMME').val();

        if(CODE_PROGRAMME=='')
        {
            $('#CODE_ACTION').html('<option value="">Sélectionner</option>');
        }
        else
        {
            $.ajax(
            {
                url:"<?=base_url()?>/dashboard/Suivi_Rapport_Evaluation/get_action/"+CODE_PROGRAMME,
                type:"GET",
                dataType:"JSON",
                success: function(data)
                {
                  $('#CODE_ACTION').html(data.actions);
                }
            });

        }
    }
</script>

<script>
    function exporter()
    {
        var CODE_INSTITUTION=$('#CODE_INSTITUTION').val()
        var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()
        var CODE_PROGRAMME=$('#CODE_PROGRAMME').val()
        var CODE_ACTION=$('#CODE_ACTION').val()

        // alert(CODE_INSTITUTION)

        $.ajax(
        {
            url:"<?=base_url()?>/dashboard/Suivi_Rapport_Evaluation/exporter/",
            type:"GET",
            dataType:"JSON",
            data:{
                CODE_INSTITUTION:CODE_INSTITUTION,
                SOUS_TUTEL_ID:SOUS_TUTEL_ID,
                CODE_PROGRAMME:CODE_PROGRAMME,
                CODE_ACTION:CODE_ACTION,
            },
            success: function(data)
            {
              // alert('ok')
            }
        });
    }
</script>

<script type="text/javascript">
    function exporter_test()
    {
        var CODE_INSTITUTION=$('#CODE_INSTITUTION').val()
        var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val()

        $.ajax(
        {
            url:"<?=base_url()?>/dashboard/Suivi_Rapport_Evaluation/exporter_filtre/",
            type:"POST",
            dataType:"JSON",
            data:{
                CODE_INSTITUTION:CODE_INSTITUTION,
                SOUS_TUTEL_ID:SOUS_TUTEL_ID,
            },
            success: function(data)
            {
              // alert('ok')
            }
        });
    }
</script>