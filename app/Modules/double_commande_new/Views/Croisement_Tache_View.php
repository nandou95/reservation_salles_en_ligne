<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-9">
                      <h3> Croisement des tâches</h3>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Liste_croisement_ptba_ptba_revise') ?>" style="float: right;margin-right: 20px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.list_transmission_du_bordereau') ?> </a>
                    </div>
                  </div>
                </div>
                <hr>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-12"><center>Ligne budgétaire : <?=$get_tache ? $get_tache_revise[0]->CODE_NOMENCLATURE_BUDGETAIRE:'-'?></center></div>
                    <input type="hidden" id="CODE_NOMENCLATURE_BUDGETAIRE" value="<?=$get_tache ? $get_tache_revise[0]->CODE_NOMENCLATURE_BUDGETAIRE:'-'?>">
                    <div class="col-md-4 table table-responsive">
                      <caption>Liste tâches PTBA</caption>
                      <table>                        
                        <thead>
                          <tr>
                            <th>Code</th>
                            <th>Tâche</th>
                            <th>Détail</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php
                          foreach($get_tache as $key) {
                            echo '<tr>';
                            echo "<td>".$key->PTBA_TACHE_ID."</td>";
                            echo "<td>".$key->DESC_TACHE."</td>";
                            // $action ="<td><a class='btn btn-primary btn-sm' title='Détail' onclick=get_info('".$key->PTBA_TACHE_ID."') ><span class='fa fa-plus'></span></a></td>";
                            $html= '<td><div>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Résultat attendu</font> : '.$key->RESULTAT_ATTENDUS_TACHE.'</div>
                            </div>
                            <br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Unité</font> : '.$key->UNITE.'</div>
                            </div>
                            
                            <div><br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité Total</font> : '.$key->Q_TOTAL.'</div>
                            </div>
                            
                            <div><br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité T1</font> : '.$key->QT1.'</div>
                            </div>
                            <br>

                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité T2</font> : '.$key->QT2.'</div>
                            </div>
                            <br>

                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité T3</font> : '.$key->QT3.'</div>
                            </div>
                            <br>

                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité T4</font> : '.$key->QT4.'</div>
                            </div>
                            <br>
                            <div>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Coût unitaire</font> : '.$key->COUT_UNITAIRE.'</div>                           
                            </div>
                            <br>
                            <div>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Budget T1</font> : '.$key->BUDGET_T1.'</div>
                            </div>
                            <br>
                            <div>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Budget T2</font> : '.$key->BUDGET_T2.'</div>
                            </div>
                            <br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Budget T3</font> : '.$key->BUDGET_T3.'</div>
                            </div>
                            <br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Budget T4</font> : '.$key->BUDGET_T4.'</div>
                            </div></td>';
                            echo $html;
                            echo '</tr>';
                          }
                          ?>
                        </tbody>                        
                      </table>
                    </div>
                    <div class="col-md-8 table table-responsive">
                      <caption>Tâches PTBA révisé</caption>
                      <table>
                        <thead>    
                          <th>Tâche révisé</th>
                          <th>Code PTBA</th>
                          <th>Action</th>
                          <th>Detail</th>
                        </thead>
                        <tbody>
                          <?php
                          foreach($get_tache_revise as $key)
                          {
                            echo "<tr>";
                            echo "<td>".$key->DESC_TACHE."</td>";
                            ?>
                            <td>
                              <input type="number" id="tache1<?=$key->PTBA_TACHE_REVISE_ID?>" min="1" placeholder="Code ptba">
                              <font color="red" id="error_tache1<?=$key->PTBA_TACHE_REVISE_ID?>"></font>

                              <input type="number" id="tache2<?=$key->PTBA_TACHE_REVISE_ID?>" min="1" placeholder="confirmer Code ptba">
                              <font color="red" id="error_tache2<?=$key->PTBA_TACHE_REVISE_ID?>"></font>
                            </td>

                            <td><button onclick="save(<?=$key->PTBA_TACHE_REVISE_ID?>)">Valider</button></td>
                            <?php
                            $html= '<td><div>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Résultat attendu</font> : '.$key->RESULTAT_ATTENDUS_TACHE.'</div>
                            </div>
                            <br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Unité</font> : '.$key->UNITE.'</div>
                            </div>
                            
                            <div><br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité Total</font> : '.$key->Q_TOTAL.'</div>
                            </div>
                            
                            <div><br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité T1</font> : '.$key->QT1.'</div>
                            </div>
                            <br>

                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité T2</font> : '.$key->QT2.'</div>
                            </div>
                            <br>

                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité T3</font> : '.$key->QT3.'</div>
                            </div>
                            <br>

                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Quantité T4</font> : '.$key->QT4.'</div>
                            </div>
                            <br>
                            <div>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Coût unitaire</font> : '.$key->COUT_UNITAIRE.'</div>                           
                            </div>
                            <br>
                            <div>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Budget T1</font> : '.$key->BUDGET_T1.'</div>
                            </div>
                            <br>
                            <div>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Budget T2</font> : '.$key->BUDGET_T2.'</div>
                            </div>
                            <br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Budget T3</font> : '.$key->BUDGET_T3.'</div>
                            </div>
                            <br>
                            <div style="width:250px ;"><font style="float:left;">-&nbsp;Budget T4</font> : '.$key->BUDGET_T4.'</div>
                            </div></td>';
                            echo $html;
                            echo '</tr>';                            
                          }
                          ?>
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
    <?php echo view('includesbackend/scripts_js.php'); ?>
    <script type="">
      function save(id)
      {
        var CODE_NOMENCLATURE_BUDGETAIRE=$('#CODE_NOMENCLATURE_BUDGETAIRE').val();
        var tache1=$('#tache1'+id).val();
        var tache2=$('#tache2'+id).val();
        $('#error_tache1'+id).text('');
        $('#error_tache2'+id).text('');
        var PTBA_TACHE_REVISE_ID=id;
        var statut=1;
        if(tache1=='' || tache1==0)
        {
          $('#error_tache1'+id).text('Ce champ est obligatoire');
          statut=0;
        }

        if(tache2=='' || tache2==0)
        {
          $('#error_tache2'+id).text('Ce champ est obligatoire');
          statut=0;
        }

        if(tache1!=tache2)
        {
          $('#error_tache2'+id).text('code incorrect');
          statut=0;
        }
        if(statut==1)
        {
          $.ajax({
            url: "<?=base_url('')?>/double_commande_new/Croisement_Tache/save",
            type: "POST",
            dataType: "JSON",
            data: {
              CODE_NOMENCLATURE_BUDGETAIRE,
              tache1,
              tache2,
              PTBA_TACHE_REVISE_ID
            },
            beforeSend: function() {

            },
            success: function(data) {
              if(data.status)
              {
                window.location.href="<?= base_url('/double_commande_new/Liste_croisement_ptba_ptba_revise')?>";
              }
              else
              {
                $('#error_tache1'+id).text('Une erreur s\'est produit');
              }
            }
          });
        }
      }

      function get_info(id_tache)
      {
        $.ajax({
          url: "<?=base_url('')?>/double_commande_new/Croisement_Tache/get_info",
          type: "POST",
          dataType: "JSON",
          data: {
            id_tache
          },
          beforeSend: function() {

          },
          success: function(data) {
            if(data.html)
            {
              $('#data_plus').html(data.html)
              $('#prep_projet').modal('show')
            }
          }
        });
      }
    </script>
    <div class="modal fade" id="prep_projet" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Plus d'information sur la tâche</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div id="data_plus">
            </div>
          </div>
          <div class="modal-footer">
          </div>
        </div>
      </div>
    </div>