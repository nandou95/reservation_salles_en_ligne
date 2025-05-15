<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
  <?php
    function get_precision($value=0)
    {
      $string = strval($value);
      $number=explode('.',$string)[1] ?? '';
      $precision='';
      for($i=1;$i<=strlen($number);$i++)
      {
        $precision=$i;
      }
      if(!empty($precision)) 
      {
        return $precision;
      }
      else
      {
        return 0;
      }    
    }
  ?>
</head>
<body>
  <div class="wrapper" >
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <div class="col-md-12 d-flex" style="float:left;">
        <div class="col-md-12">
          <br>
          <div style="float: left;">
            <a href="javascript:history.back()" style="float: left;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-chevron-left" aria-hidden="true"></i> <?= lang('messages_lang.action_retour') ?></a>
          </div>
          <center><h3>Détail de la ligne budgétaire:<?=$get_det_vote['LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE']?><hr></h3></center>         
          <div class="row">
            <div class="col-md-4">
              <div class="col-md-12">
                <table class="table table-bordered">
                  <thead><tr><th colspan="2"><center>VOTE</center></th></tr></thead>
                  <tr class="text-uppercase text-nowrap">
                    <th>Trimestre</th>
                    <th><?= lang('messages_lang.montant_intitution_detail') ?></th>
                  </tr>
                  <tr>
                    <td>T1</td>
                    <td><?=number_format($get_det_vote['BUDGET_T1'],get_precision($get_det_vote['BUDGET_T1']),',',' ')?></td>
                  </tr>
                  <tr>
                    <td>T2</td>
                    <td><?=number_format($get_det_vote['BUDGET_T2'],get_precision($get_det_vote['BUDGET_T2']),',',' ')?></td>
                  </tr>
                  <tr>
                    <td>T3</td>
                    <td><?=number_format($get_det_vote['BUDGET_T3'],get_precision($get_det_vote['BUDGET_T3']),',',' ')?></td>
                  </tr>
                  <tr>
                    <td>T4</td>
                    <td><?=number_format($get_det_vote['BUDGET_T4'],get_precision($get_det_vote['BUDGET_T4']),',',' ')?></td>
                  </tr>
                </table>
              </div>
              <div class="col-md-12">
                <table class="table table-bordered">
                  <tr class="text-uppercase text-nowrap">
                    <th>Activité</th>
                  </tr>
                  <tr>
                    <td>Nombre</td>
                    <td><?=number_format($get_det_vote['NBR_ACTIVITE'],get_precision($get_det_vote['NBR_ACTIVITE']),',',' ')?></td>
                  </tr>
                </table>
              </div>
            </div>
            <div class="col-md-8">
              <div class="row">
                <div class="col-md-12">
                  <table class="table table-bordered">
                    <tr class="text-uppercase text-nowrap">
                      <th>Exécution</th>
                      <th>T1</th>
                      <th>T2</th>
                      <th>T3</th>
                      <th>T4</th>
                    </tr>
                    <tr>
                      <td>Eng. budgétaire</td>
                      <td><?=number_format($get_det_exec1['ENG_BUDGETAIRE'],get_precision($get_det_exec1['ENG_BUDGETAIRE']),',',' ')?></td>
                      <td><?=number_format($get_det_exec2['ENG_BUDGETAIRE'],get_precision($get_det_exec2['ENG_BUDGETAIRE']),',',' ')?></td>
                      <td><?=number_format($get_det_exec3['ENG_BUDGETAIRE'],get_precision($get_det_exec3['ENG_BUDGETAIRE']),',',' ')?></td>
                      <td><?=number_format($get_det_exec4['ENG_BUDGETAIRE'],get_precision($get_det_exec4['ENG_BUDGETAIRE']),',',' ')?></td>
                    </tr>

                    <tr>
                      <td>Eng. juridique</td>
                      <td><?=number_format($get_det_exec1['ENG_JURIDIQUE'],get_precision($get_det_exec1['ENG_JURIDIQUE']),',',' ')?></td>
                      <td><?=number_format($get_det_exec2['ENG_JURIDIQUE'],get_precision($get_det_exec2['ENG_JURIDIQUE']),',',' ')?></td>
                      <td><?=number_format($get_det_exec3['ENG_JURIDIQUE'],get_precision($get_det_exec3['ENG_JURIDIQUE']),',',' ')?></td>
                      <td><?=number_format($get_det_exec4['ENG_JURIDIQUE'],get_precision($get_det_exec4['ENG_JURIDIQUE']),',',' ')?></td>
                    </tr>
                    <tr>
                      <td>Liquidation</td>
                      <td><?=number_format($get_det_exec1['LIQUIDATION'],get_precision($get_det_exec1['LIQUIDATION']),',',' ')?></td>
                      <td><?=number_format($get_det_exec2['LIQUIDATION'],get_precision($get_det_exec2['LIQUIDATION']),',',' ')?></td>
                      <td><?=number_format($get_det_exec3['LIQUIDATION'],get_precision($get_det_exec3['LIQUIDATION']),',',' ')?></td>
                      <td><?=number_format($get_det_exec4['LIQUIDATION'],get_precision($get_det_exec4['LIQUIDATION']),',',' ')?></td>
                    </tr>
                    <tr>
                      <td>Ordonnancement</td>
                      <td><?=number_format($get_det_exec1['ORDONNANCEMENT'],get_precision($get_det_exec1['ORDONNANCEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec2['ORDONNANCEMENT'],get_precision($get_det_exec2['ORDONNANCEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec3['ORDONNANCEMENT'],get_precision($get_det_exec3['ORDONNANCEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec4['ORDONNANCEMENT'],get_precision($get_det_exec4['ORDONNANCEMENT']),',',' ')?></td>
                    </tr>
                    <tr>
                      <td>Paiement</td>
                      <td><?=number_format($get_det_exec1['PAIEMENT'],get_precision($get_det_exec1['PAIEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec2['PAIEMENT'],get_precision($get_det_exec2['PAIEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec3['PAIEMENT'],get_precision($get_det_exec3['PAIEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec4['PAIEMENT'],get_precision($get_det_exec4['PAIEMENT']),',',' ')?></td>
                    </tr> 
                    <tr>
                      <td>Décaissement</td>
                      <td><?=number_format($get_det_exec1['DECAISSEMENT'],get_precision($get_det_exec1['DECAISSEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec2['DECAISSEMENT'],get_precision($get_det_exec2['DECAISSEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec3['DECAISSEMENT'],get_precision($get_det_exec3['DECAISSEMENT']),',',' ')?></td>
                      <td><?=number_format($get_det_exec4['DECAISSEMENT'],get_precision($get_det_exec4['DECAISSEMENT']),',',' ')?></td>
                    </tr>
                  </table>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <table class="table table-bordered">
                    <tr class="text-uppercase text-nowrap">
                      <th>Tâche</th>
                    </tr>
                    <tr>
                      <td>Voté</td>
                      <td><?=number_format($activ_vote['NBT_VOTE'],get_precision($activ_vote['NBT_VOTE']),',',' ')?></td>
                    </tr>
                    <tr>
                      <td>Nouveau</td>
                      <td><?=number_format($activ_nouveau['NBT_NOUVEAU'],get_precision($activ_nouveau['NBT_NOUVEAU']),',',' ')?></td>
                    </tr>
                  </table>
                </div>
                <div class="col-md-6">
                  <table class="table table-bordered">
                    <tr class="text-uppercase text-nowrap">
                      <th>Transfert</th>
                    </tr>
                    <tr>
                      <td>Recéption</td>
                      <td><?=number_format($recu['total'],get_precision($recu['total']),',',' ')?></td>
                    </tr>
                    <tr>
                      <td>Transfert</td>
                      <td><?=number_format($trans['total'],get_precision($trans['total']),',',' ')?></td>
                    </tr>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>
