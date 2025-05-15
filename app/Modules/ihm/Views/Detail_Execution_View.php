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
                      <h1 class="header-title text-dark">Détail du TD <?= !empty($data['TITRE_DECAISSEMENT']) ? $data['TITRE_DECAISSEMENT'].'&nbsp;&nbsp;' : 'N/A' ;?></h1>
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
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Institution</font></td>
                            <td><strong><font style="float:left;"> <?= !empty($data['DESCRIPTION_INSTITUTION']) ? $data['DESCRIPTION_INSTITUTION'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                           
                          
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Sous tutel</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESCRIPTION_SOUS_TUTEL']) ? $data['DESCRIPTION_SOUS_TUTEL'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
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
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Ligne budgétaire</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['CODE_NOMENCLATURE_BUDGETAIRE']) ? $data['CODE_NOMENCLATURE_BUDGETAIRE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Activité</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_PAP_ACTIVITE']) ? $data['DESC_PAP_ACTIVITE'].'&nbsp;&nbsp;' : 'N/A' ;?> </font></strong></td>
                          </tr>
                            <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Tâche</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['DESC_TACHE']) ? $data['DESC_TACHE'].'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                         
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Montant Voté</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['MONTANT_VOTE']) ? number_format($data['MONTANT_VOTE'],0,'',' ').'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                          <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Montant executé</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['ENG_BUDGETAIRE']) ? number_format($data['ENG_BUDGETAIRE'],0,'',' ').'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>
                           <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Montant restant</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['MONTANT_VOTE']) ? number_format($data['MONTANT_RESTANT'],0,'',' ').'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr>

                      <!--     <tr>
                            <td style="width:250px ;"><font style="float:left;">&nbsp;Budget annuel</font></td>
                            <td><strong><font style="float:left;"><?= !empty($data['BUDGET_ANNUEL']) ? number_format($data['BUDGET_ANNUEL'],0,'',' ').'&nbsp;&nbsp;' : 'N/A' ;?></font></strong></td>
                          </tr> -->
                         
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
 