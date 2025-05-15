<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 <?php $validation = \Config\Services::validation(); ?>

<link rel="stylesheet" href="<?= base_url('template/css') ?>/sweetalert2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">

<style type="text/css">
   .modal-signature {
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    border-bottom-right-radius: .3rem;
    border-bottom-left-radius: .3rem;
   }
</style>

</head>
<body>
<!-- Votre contenu HTML ici -->
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-3d.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
<script type="text/javascript" src="monfichier.js"></script>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>

    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
       
          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
             
                </div>
                <div class="card-body" style="overflow-x:auto;">
                 <div class="row">

                   <div class="col-md-12">
                    <h1 class="header-title text-black">

                      <?=lang('messages_lang.pip_dashboard_avec')?>
                      
                     </h1>
                   </div>
          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
             
                </div>
                <div class="card-body" style="overflow-x:auto;">
                 <div class="row">
                    <div class="form-group col-md-3">
                      <label><b><?=lang('messages_lang.categorie_action')?></b></label>
                      <select class="form-control" onchange="get_i()" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                        <option value=""><?=lang('messages_lang.label_selecte')?></option>
                        <?php
                        foreach ($type_ministre as $value)
                        {
                          if ($value->TYPE_INSTITUTION_ID==$TYPE_INSTITUTION_ID)
                          { ?>
                          <option value="<?= $value->TYPE_INSTITUTION_ID ?>" selected><?=$value->Name ?></option>
                            <?php
                          } 
                          else
                          { 
                            ?>
                            <option value="<?=$value->TYPE_INSTITUTION_ID?>"><?= $value->Name ?></option>
                            <?php 
                           } 
                        } 
                        ?>
                      </select>        
                    </div>
                    <div class="form-group col-md-3">
                    <label><b><a id="idmin"></a></b></label>
                    <select class="form-control " onchange="get_m()" name="INSTITUTION_ID" id="INSTITUTION_ID">
                      <option value=""><?=lang('messages_lang.label_selecte')?></option>
                      </select>        
                    </div>
                    <div class="form-group col-md-3">
                      <label><b><a id="program"></a></b></label>
                      <select class="form-control" onchange="get_s()" name="PROGRAMME_ID" id="PROGRAMME_ID">
                        
                        <option value=""><?=lang('messages_lang.label_selecte')?></option>

                        
                      </select>        
                    </div>
                     <div class="form-group col-md-3">
                      <label><b>Actions</b></label>
                      <select class="form-control" onchange="get_rapport()" onchange="get_act()" name="ACTION_ID" id="ACTION_ID">
                        <option value=""><?=lang('messages_lang.label_selecte')?></option>
                        
                      </select>
                       </div>
                    <div class="modal fade" id="myModal" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width: 100%;" id='mytable' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><label>#</label></center></th>
                              <th style='width:120px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_pilier')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center>
                                <label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_projets')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label><?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_debut')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                               <th style='width:70px'><center><label><?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_fin')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                               
                              <th style='width:50px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbspou&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_program')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_planicateur')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_tel')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
                        </div>
                      </div>
                    </div>
                  </div>  

                   <div class="modal fade" id="myModal1" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre1" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width: 100%;" id='mytable1' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><label>#</label></center></th>
                               <th style='width:120px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_rapport_institutio_ax')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                         

                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_projets')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label><?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_debut')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label><?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_fin')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              
                              <th style='width:50px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbspou&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_program')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_planicateur')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_tel')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                               
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
                        </div>
                      </div>
                    </div>
                  </div>
               
                   <div class="modal fade" id="myModal2" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre2" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width: 100%;" id='mytable2' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><label>#</label></center></th>
                              <th style='width:120px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_projets')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                               <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_bailleurs')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label><?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_debut')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                               <th style='width:70px'><center><label><?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_fin')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                               
                              <th style='width:50px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbspou&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_program')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_planicateur')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_tel')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
                        </div>
                      </div>
                    </div>
                  </div>


                  <div class="modal fade" id="myModal3" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre3" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width:100%;" id='mytable3' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><label>#</label></center></th>
                              <th style='width:120px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_dashboar_statut')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center>
                              </th>
                               <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_projets')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_debut')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                               <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_fin')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                               
                              <th style='width:50px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbspou&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_program')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_planicateur')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_tel')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
                        </div>
                      </div>
                    </div>
                  </div>
                   <div class="modal fade" id="myModal4" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                          <h4 class="modal-title"><span id="titre4" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width:100%;" id='mytable4' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><label>#</label></center></th>
                              
                               <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_projets')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_debut')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                               <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_fin')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                               
                              <th style='width:50px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbspou&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_program')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_planicateur')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_tel')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
                        </div>
                      </div>
                    </div>
                  </div>


                   <div class="modal fade" id="myModal6" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                          <h4 class="modal-title"><span id="titre6" style="color: black"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width:100%;" id='mytable6' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><label>#</label></center></th>

                               <th style='width:120px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_provinces')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center>
                              </th>
                              </th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_communes')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_projets')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_debut')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                               <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_fin')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                               
                              <th style='width:50px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbspou&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_program')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_planicateur')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_tel')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="modal fade" id="myModal_etude" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content  ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre_etude" style="color: black"></span></h4>
                           </div>
                           <div class="modal-body">
                            <table style="width: 100%;" id='mytable_etude' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><label>#</label></center></th>
                              <th style='width:120px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_pilier')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_projets')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_debut')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                               <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.date_fin')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                              <th style='width:50px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_inst_min')?>&nbspou&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_program')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.label_droit_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_planicateur')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.pip_tel')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>
                              <th style='width:70px'><center><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.th_date')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></center></th>

                             </thead>
                           </table>  
                         </div>
                         <div class="modal-footer">
                          <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary"><?=lang('messages_lang.label_ferm')?></button>
                        </div>
                      </div>
                    </div>
                  </div>  
                 </div>
                </div>
                </div>
                </div>
                <div class="row">
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                <div id="container"  class="col-md-6"></div>
                <div id="container4"  class="col-md-6"></div>
                <div class="col-md-12" style="margin-bottom: 30px"></div>
                <div id="container6"  class="col-md-6"></div>
                <div id="container2"  class="col-md-6"></div>
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                 <div id="container3"  class="col-md-6"></div>
                <div id="container_etude"  class="col-md-6"></div>
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                 <div id="container5"  class="col-md-12"></div>
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                </div>
                <div id="nouveau"></div>
                <div id="nouveau2"></div>
                <div id="nouveau3"></div>
                <div id="nouveau4"></div>
                <div id="nouveau5"></div>
                <div id="nouveau6"></div>
                <div id="nouveau7"></div>
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
 $( document ).ready(function() 
     {
    get_rapport();
     });   
function get_i() {

 $('#INSTITUTION_ID').html('');
$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#SOUS_TUTEL_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');

 get_rapport();
   
}
</script>
<script type="text/javascript">
function get_m() {
  $('#SOUS_TUTEL_ID').html('');
  $('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
}
function get_add() {
$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
 get_rapport(); 
}
function get_s() {
$('#ACTION_ID').html('');
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
}
function get_act() {
$('#LIGNE_BUDGETAIRE').html('');
    get_rapport();
}
</script>
<script type="text/javascript">
  function get_rapport(){

    var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
     var inst_conn=$('#inst_conn').val();
    $.ajax({
      url : "<?=base_url('dashboard/Dashbord_General_PIP/get_general_pip')?>",
      type : "GET",
      dataType: "JSON",
      cache:false,
      data:{
       TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
       INSTITUTION_ID:INSTITUTION_ID,
       PROGRAMME_ID:PROGRAMME_ID,
       ACTION_ID:ACTION_ID,
       inst_conn:inst_conn,
         },

     success:function(data){
      $('#container').html("");             
      $('#container2').html("");
      $('#nouveau').html(data.rapp);
      $('#nouveau2').html(data.rapp2);
      $('#container3').html("");             
      $('#nouveau3').html(data.rapp3);
      $('#container4').html("");             
      $('#nouveau4').html(data.rapp4);
      $('#container5').html("");             
      $('#nouveau5').html(data.rapp5);
      $('#container6').html("");             
      $('#nouveau6').html(data.rapp6);
      $('#container_etude').html("");             
      $('#nouveau7').html(data.rapp_etude);
      $('#INSTITUTION_ID').html(data.inst);
      $('#PROGRAMME_ID').html(data.program);
      $('#ACTION_ID').html(data.actions);
      $('#LIGNE_BUDGETAIRE').html(data.ligne_budgetaires);
      if (TYPE_INSTITUTION_ID==1) {
        $("#idmin").html("Institutions");
        $("#program").html("Dotations");
        id_action.style.display='none';
        }
      else if (TYPE_INSTITUTION_ID==2)
       {
        $("#idmin").html("<?=lang('messages_lang.pip_dashboar_min')?>");
        $("#program").html("<?=lang('messages_lang.pip_dashboar_programm')?>");
        id_action.style.display='block';
         }else{
        $("#idmin").html("<?=lang('messages_lang.labelle_inst_min')?>");
         $("#program").html("<?=lang('messages_lang.pip_dashboar_progra_dotation')?>");
        id_action.style.display='none';
        $("#program").html("<?=lang('messages_lang.pip_dashboar_programm')?>");
      } 
    },            
  });  
  }
  function saveData()
  {
  $('#myModal').modal('hide');
  $('#myModal1').modal('hide');
  $('#myModal2').modal('hide');
  $('#myModal3').modal('hide');
  $('#myModal4').modal('hide');
  $('#myModal6').modal('hide');
  $('#myModal_phase').modal('hide');
  $('#myModal_masse').modal('hide');
  $('#myModal_transfert').modal('hide');
  $('#myModal_recu').modal('hide');
  $('#myModal_etude').modal('hide');
 }
</script>