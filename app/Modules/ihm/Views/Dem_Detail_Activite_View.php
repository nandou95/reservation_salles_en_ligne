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
            </h1>
          </div>

          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">

                </div>
                <div class="card-body" style="overflow-x:auto;">
                  <div style="
                  margin-top: -40px;
                  " class="card">
                  <div class="card-header">
                  </div>
                  <div class="card-body" style="">

                    <div class="col-md-12">
                      <div class="row">
                        <div class="col-md-8">
                          <h3><?=lang('messages_lang.detail_activ')?></h3>
                          <h5><i class="fa fa-tag"></i> <?= $activite['ACTIVITES']?></h5>
                        </div>
                        <div class="col-md-4">
                          <a href="<?=base_url('ihm/Liste_Activites') ?>" style="float: right;margin: 4px;" class="btn btn-primary"><span class="fa fa-list pull-right"></span> Liste</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>  

                <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;" class="card">
                  <div class="card-header">
                    <input type="hidden" name="PTBA_ID" id="PTBA_ID" value="<?=$activite['PTBA_ID']?>">
                    <br>

                    <p class="card-text"></p>
                  </div>

                  <div class="card-body">
                    <div class=" table-responsive overflow-auto mt-2" style="max-height: 500px"> 
                      <table class="table m-b-0 m-t-20">
                        <tbody>
                          <tr>
                            <td><font style="float:left;"><i class="fa fa-history"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_activite')?> </font></td>
                            <td><strong><font style="float:left;"><?php if (!empty($activite['ACTIVITES'])){  echo $activite['ACTIVITES']; }else{ echo 'N/A';} ?> </font></strong></td>
                          </tr>
                          <tr>
                            <td><font style="float:left;"><i class="fa fa-landmark"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_institution')?></font></td>
                            <td><strong><font style="float:left;"><?php if (!empty($activite['INTITULE_MINISTERE'])){  echo $activite['INTITULE_MINISTERE']; }else{ echo 'N/A';} ?> - <?php if (!empty($activite['CODE_MINISTERE'])){  echo $activite['CODE_MINISTERE']; }else{ echo 'N/A';} ?></font></strong></td>
                          </tr>
                          <tr>
                            <td><font style="float:left;"><i class="fa fa-laptop-code"></i>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.table_Programme')?> </font></td>
                            <td><strong><font style="float:left;"><?php if (!empty($activite['INTITULE_PROGRAMME'])){  echo $activite['INTITULE_PROGRAMME']; }else{ echo 'N/A';} ?> - <?php if (!empty($activite['CODE_PROGRAMME'])){  echo $activite['CODE_PROGRAMME']; }else{ echo 'N/A';} ?></font></strong></td>
                          </tr>
                          <tr>
                            <td><font style="float:left;"><i class="fa fa-arrow-circle-right"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.table_Action')?> </font></td>
                            <td><strong><font style="float:left;"><?php if (!empty($activite['LIBELLE_ACTION'])){  echo $activite['LIBELLE_ACTION']; }else{ echo 'N/A';} ?> - <?php if (!empty($activite['CODE_ACTION'])){  echo $activite['CODE_ACTION']; }else{ echo 'N/A';} ?></font></strong></td>
                          </tr>
                          
                          <tr>
                            <td><font style="float:left;"><i class="fa fa-chart-bar"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.result_attendus')?></font></td>
                            <td><strong><font style="float:left;"><?php if (!empty($activite['RESULTATS_ATTENDUS'])){  echo $activite['RESULTATS_ATTENDUS']; }else{ echo 'N/A';} ?></font></strong></td>
                          </tr>
                          <tr>
                            <td><i class="fa fa-ruler"></i>&nbsp;&nbsp;&nbsp;&nbsp;<strong><?=lang('messages_lang.label_unity')?></strong></td>
                            <td><strong><font style="float:left;"><?php if (!empty($activite['UNITE'])){  echo $activite['UNITE']; }else{ echo 'N/A';} ?></font></strong></td>
                          </tr>
                          <tr>
                            <td><font style="float:left;"> <i class="fa fa-weight-hanging"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_grandes_masses')?></font></td>
                            <td><strong><font style="float:left;"><?php if (!empty($activite['INTITULE_DES_GRANDES_MASSES'])){  echo $activite['INTITULE_DES_GRANDES_MASSES']; }else{ echo 'N/A';} ?></font></strong></td>
                          </tr>
                          <tr>
                            <td><font style="float:left;"><i class="fa fa-code"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_code_budgetaire')?></font></td>
                            <td><strong><font style="float:left;"><?php if (!empty($activite['CODE_NOMENCLATURE_BUDGETAIRE'])){  echo $activite['CODE_NOMENCLATURE_BUDGETAIRE']; }else{ echo 'N/A';} ?></font></strong></td>
                          </tr>
                          <tr>
                           <td><font style="float:left;"><i class="fa fa-code"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.nouv_cod_budg')?></font></td>
                           <td><strong><font style="float:left;"><?php if (!empty($activite['CODE_NOMENCLATURE_BUDGETAIRE_NEW'])){  echo $activite['CODE_NOMENCLATURE_BUDGETAIRE_NEW']; }else{ echo 'N/A';} ?></font></strong></td>
                         </tr>
                         <tr>
                           <td><font style="float:left;"><i class="fa fa-code"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_code_programmatique')?></font></td>
                           <td><strong><font style="float:left;"><?php if (!empty($activite['CODES_PROGRAMMATIQUE'])){  echo $activite['CODES_PROGRAMMATIQUE']; }else{ echo 'N/A';} ?></font></strong></td>
                         </tr>
                         <tr>
                           <td><font style="float:left;"><i class="fa fa-code"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.nouv_cod_prog')?></font></td>
                           <td><strong><font style="float:left;"><?php if (!empty($activite['CODES_PROGRAMMATIQUE_NEW'])){  echo $activite['CODES_PROGRAMMATIQUE_NEW']; }else{ echo 'N/A';} ?></font></strong></td>
                         </tr>
                         <tr>
                           <td><font style="float:left;"><i class="fa fa-newspaper"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_article_economique')?></font></td>
                           <td><strong><font style="float:left;"><?php if (!empty($activite['INTITULE_ARTICLE_ECONOMIQUE'])){  echo $activite['INTITULE_ARTICLE_ECONOMIQUE']; }else{ echo 'N/A';} ?></font></strong></td>
                         </tr>
                         <tr>
                           <td><font style="float:left;"><i class="fa fa-shapes"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_nature_economique')?></font></td>
                           <td><strong><font style="float:left;"><?php if (!empty($activite['INTITULE_NATURE_ECONOMIQUE'])){  echo $activite['INTITULE_NATURE_ECONOMIQUE']; }else{ echo 'N/A';} ?></font></strong></td>
                         </tr>
                         <tr>
                           <td><font style="float:left;"><i class="fa fa-layer-group"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.labelle_intitule_division')?></font></td>
                           <td><strong><font style="float:left;"><?php if (!empty($activite['INTITULE_DIVISION_FONCTIONNELLE'])){  echo $activite['INTITULE_DIVISION_FONCTIONNELLE']; }else{ echo 'N/A';} ?></font></strong></td>
                         </tr>
                         <tr>
                           <td><font style="float:left;"><i class="fa fa-object-group"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.group_fonc')?></font></td>
                           <td><strong><font style="float:left;"><?php if (!empty($activite['INTITULE_GROUPE_FONCTIONNELLE'])){  echo $activite['INTITULE_GROUPE_FONCTIONNELLE']; }else{ echo 'N/A';} ?></font></strong></td>
                         </tr>
                         <tr>
                           <td><font style="float:left;"><i class="fa fa-tags"></i>&nbsp;&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.class_fonct')?></font></td>
                           <td><strong><font style="float:left;"><?php if (!empty($activite['INTITULE_CLASSE_FONCTIONNELLE'])){  echo $activite['INTITULE_CLASSE_FONCTIONNELLE']; }else{ echo 'N/A';} ?></font></strong></td>
                         </tr>
                       </tbody>
                     </table>          
                   </div>
                 </div>
                 <br><hr><br>
                 <div class="card-body">
                  <div class="row">
                     <div class="col-md-6">
                            <center><h4><?=lang('messages_lang.label_vote')?></h4></center>
                            <table class="table table-bordered">
                                <tr>
                                    <th><?=lang('messages_lang.labelle_tranche')?></th>
                                    <th><?=lang('messages_lang.labelle_montant')?></th>
                                    <th><?=lang('messages_lang.labelle_quantite')?></th>

                                </tr>
                                <tr>
                                    <td>T1</td>
                                    <td><?=number_format($montant_vote['T1'],0,","," ").' BIF' ?></td>
                                     <td><?=number_format($quant_vote['QT1'],0,","," ").' ' ?></td>
                                </tr>
                                <tr>
                                    <td>T2</td>
                                    <td><?=number_format($montant_vote['T2'],0,","," ").' BIF' ?></td>
                                     <td><?=number_format($quant_vote['QT2'],0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T3</td>
                                    <td><?=number_format($montant_vote['T3'],0,","," ").' BIF' ?></td>
                                     <td><?=number_format($quant_vote['QT3'],0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T4</td>
                                    <td><?=number_format($montant_vote['T4'],0,","," ").' BIF' ?></td>
                                     <td><?=number_format($quant_vote['QT4'],0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                  <td><?=lang('messages_lang.label_total_annuel')?></td>
                                  <td><?=number_format($montant_total,0,","," ").' BIF' ?></td>
                                   <td><?=number_format($quant_total,0,","," ").'' ?></td>
                                </tr>
                            </table>
                        </div>
                        
                        <div class="col-md-6">
                          <center><h4><?=lang('messages_lang.label_execute')?></h4></center>
                             <table class="table table-bordered">
                                <tr>
                                    <th><?=lang('messages_lang.labelle_tranche')?></th>
                                    <th><?=lang('messages_lang.labelle_montant')?></th>
                                    <th><?=lang('messages_lang.labelle_quantite')?></th>
                                </tr>
                                <tr>
                                    <td>T1</td>
                                    <td><?=number_format($executeMoney1,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($executeQuant1,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T2</td>
                                    <td><?=number_format($executeMoney2,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($executeQuant2,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T3</td>
                                    <td><?=number_format($executeMoney3,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($executeQuant3,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T4</td>
                                    <td><?=number_format($executeMoney4,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($executeQuant4,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                  <td><?=lang('messages_lang.label_total_annuel')?></td>
                                  <td><?=number_format($tot_exe,0,","," ").' BIF' ?></td>
                                  <td><?=number_format($tot_quant_exe,0,","," ").'' ?></td>
                                </tr>
                            </table>
                        </div>
                      </div>
                      <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <center><h4><?=lang('messages_lang.label_restant')?></h4></center>
                                <tr>
                                    <th><?=lang('messages_lang.labelle_tranche')?></th>
                                    <th><?=lang('messages_lang.labelle_montant')?></th>
                                    <th><?=lang('messages_lang.labelle_quantite')?></th>
                                </tr>
                                <tr>
                                    <td>T1</td>
                                    <td><?=number_format($reste1,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($quant_rest_t1,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td>T2</td>
                                    <td><?=number_format($reste2,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($quant_rest_t2,0,","," ").' ' ?></td>
                                </tr>
                                <tr>
                                    <td>T3</td>
                                    <td><?=number_format($reste3,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($quant_rest_t3,0,","," ").' ' ?></td>
                                </tr>
                                <tr>
                                    <td>T4</td>
                                    <td><?=number_format($reste4,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($quant_rest_t4,0,","," ").'' ?></td>
                                </tr>
                                <tr>
                                    <td><?=lang('messages_lang.label_total_annuel')?></td>
                                    <td><?=number_format($restant,0,","," ").' BIF' ?></td>
                                    <td><?=number_format($quant_restant,0,","," ").'' ?></td>
                                </tr>
                            </table>
                        </div>
                  </div>
                  <div class="tab-content" id="myTabContent">

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
