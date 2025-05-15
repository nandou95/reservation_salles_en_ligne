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

                <div class="card-header" >
                  <div class="row col-md-12">
                    <div class="col-md-8">
                      <h1 class="header-title text-dark">Détail</h1>
                    </div>
                    <div class="col-md-4">
                      <a href="<?=base_url('ihm/Execution')?>" style="float: right;margin-right: 90px;margin: 10px" class="btn btn-primary"><span class="fa fa-plus pull-right"></span>Liste</a> 
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class=" table-responsive">
                      <table class="table m-b-0 m-t-20">
                        <tbody>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Titre de decaissement</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['TITRE_DECAISSEMENT']) ? $data['TITRE_DECAISSEMENT'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Bon d'engagement</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['NUMERO_BON_ENGAGEMENT']) ? $data['NUMERO_BON_ENGAGEMENT'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Engagement budgetaire</font></td>
                            <td><strong><font style="float:left;"><?=number_format($data['ENG_BUDGETAIRE'],0,","," ")?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Engagement juridique</font></td>
                            <td><strong><font style="float:left;"><?=number_format($data['ENG_JURIDIQUE'],0,","," ")?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Liquidation</font></td>
                            <td><strong><font style="float:left;"><?=number_format($data['LIQUIDATION'],0,","," ");?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Ordonnancement</font></td>
                            <td><strong><font style="float:left;"><?=number_format($data['ORDONNANCEMENT'],0,","," ")?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Paiement</font></td>
                            <td><strong><font style="float:left;"><?=number_format($data['PAIEMENT'],0,","," ")?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Decaissement</font></td>
                            <td><strong><font style="float:left;"><?=number_format($data['DECAISSEMENT'],0,","," ")?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Code budgetaire</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['CODE_NOMENCLATURE_BUDGETAIRE']) ? $data['CODE_NOMENCLATURE_BUDGETAIRE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Code programmatique</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['CODES_PROGRAMMATIQUE']) ? $data['CODES_PROGRAMMATIQUE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Tâche</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_TACHE']) ? $data['DESC_TACHE'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Resultat attendus</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['RESULTAT_ATTENDUS_TACHE']) ? $data['RESULTAT_ATTENDUS_TACHE'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          
                         
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget T1</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_T1']) ? $data['BUDGET_T1'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget utilisé T1</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_UTILISE_T1']) ? $data['BUDGET_UTILISE_T1'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget restant T1</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_RESTANT_T1']) ? $data['BUDGET_RESTANT_T1'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget T2</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_T2']) ? $data['BUDGET_T2'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget utilisé T2</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_UTILISE_T2']) ? $data['BUDGET_UTILISE_T2'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget restant T2</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_RESTANT_T2']) ? $data['BUDGET_RESTANT_T2'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget T3</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_T3']) ? $data['BUDGET_T3'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget utilisé T3</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_UTILISE_T3']) ? $data['BUDGET_UTILISE_T3'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget restant T3</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_RESTANT_T3']) ? $data['BUDGET_RESTANT_T3'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget T4</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_T4']) ? $data['BUDGET_T4'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget utilisé T4</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_UTILISE_T4']) ? $data['BUDGET_UTILISE_T4'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget restant T4</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_RESTANT_T4']) ? $data['BUDGET_RESTANT_T4'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget annuel</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_ANNUEL']) ? $data['BUDGET_ANNUEL'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Institution</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESCRIPTION_INSTITUTION']) ? $data['DESCRIPTION_INSTITUTION'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Institution</font></td>
                            <td><strong><font style="float:left;"><?= !empty($get_info['CODE_INSTITUTION']) ? $get_info['CODE_INSTITUTION'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Sous tutel</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESCRIPTION_SOUS_TUTEL']) ? $data['DESCRIPTION_SOUS_TUTEL'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Pilier</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESCR_PILIER']) ? $data['DESCR_PILIER'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Objectif vision</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_OBJECTIF_VISION']) ? $data['DESC_OBJECTIF_VISION'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Axe PND</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESCR_AXE_PND']) ? $data['DESCR_AXE_PND'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Programme</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['INTITULE_PROGRAMME']) ? $data['INTITULE_PROGRAMME'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Action</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['LIBELLE_ACTION']) ? $data['LIBELLE_ACTION'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Programme prioritaire</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_PROGRAMME_PRIORITAIRE']) ? $data['DESC_PROGRAMME_PRIORITAIRE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Code budget</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE']) ?$data['LIBELLE_CODE_NOMENCLATURE_BUDGETAIRE'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Article</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['LIBELLE_ARTICLE']) ? $data['LIBELLE_ARTICLE'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Sous lettera</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['LIBELLE_SOUS_LITTERA']) ? $data['LIBELLE_SOUS_LITTERA'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Division</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['LIBELLE_DIVISION']) ? $data['LIBELLE_DIVISION'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Groupe</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['LIBELLE_GROUPE']) ? $data['LIBELLE_GROUPE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Classe</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['LIBELLE_CLASSE']) ? $data['LIBELLE_CLASSE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Activité PAP</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_PAP_ACTIVITE']) ? $data['DESC_PAP_ACTIVITE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Activité COSTAB</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_COSTAB_ACTIVITE']) ? $data['DESC_COSTAB_ACTIVITE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Indicateur PND</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_INDICATEUR_PND']) ? $data['DESC_INDICATEUR_PND'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Responsable tache</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_STRUTURE_RESPONSABLE_TACHE']) ? $data['DESC_STRUTURE_RESPONSABLE_TACHE'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Grande masse</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESCRIPTION_GRANDE_MASSE']) ? $data['DESCRIPTION_GRANDE_MASSE'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Anneé</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['ANNEE_DESCRIPTION']) ? $data['ANNEE_DESCRIPTION'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
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
 