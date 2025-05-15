<!DOCTYPE html>
<html lang="en">
<head>
 <?php echo view('includesbackend/header.php');?>
 <?php $validation = \Config\Services::validation(); ?>
 <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
 <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
 <style type="text/css">
   .modal-signature {
    flex-wrap: wrap;
    align-items: center;
    justify-content: flex-end;
    border-bottom-right-radius: .3rem;
    border-bottom-left-radius: .3rem
  }
</style>
</head>
  <script src="https://code.highcharts.com/highcharts.js"></script>
  <script src="https://code.highcharts.com/highcharts-3d.js"></script>
  <script src="https://code.highcharts.com/modules/exporting.js"></script>
  <script src="https://code.highcharts.com/modules/export-data.js"></script>
  <script src="https://code.highcharts.com/modules/accessibility.js"></script>
  <script type="text/javascript" src="monfichier.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"></script> 
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
             <h1 class="header-title text-black">
             <?=lang('messages_lang.perform_raccrochag')?>
             </h1>
             </div>
          <div class="row"  style="margin-top: -5px">
         <input type="hidden" onchange="get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE1" value="1"  checked> 
          <input type="hidden" onchange="get_rapport()" name="IS_PRIVATE" id="IS_PRIVATE2" value="2" >
        </div>
          <div class="row" style="margin-top: -5px">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                    <div class="modal fade" id="myModal" role="dialog">
                      <div class="modal-dialog" style ="max-width: 70%;">
                        <div class="modal-content ">
                          <div class="modal-header">
                            <h4 class="modal-title"><span id="titre" style="color: black;"></span></h4>
                          </div>
                          <div class="modal-body">
                            <table style="width: 100%;" id='mytable' class='table table-bordered table-striped table-hover table-condensed table-responsive'>
                              <thead>
                               <th style='width:30px'><center><font color="white" size="3"><label>#</label></font></center></th>
                                <th style='width:100px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.admin_perso')?>/<?=lang('messages_lang.minister')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.entit_respo')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_programme')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                              <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_action')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                            <th style='width:70px'><center><font color="white" size="3"><label>&nbsp&nbsp<?=lang('messages_lang.label_resultant_attendus')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                             <th style='width:90px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.labelle_activites')?>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>

                             <th style='width:90px'><center><font color="white" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?=lang('messages_lang.budget_vote')?>&nbsp&nbsp&nbsp</label></font></center></th>
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
                <div class="row">
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                <div id="container"  class="col-md-12"></div>
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                <div class="col-md-12" style="margin-bottom: 20px"></div>
                <div id="container1"  class="col-md-12"></div>
                <div id="nouveau"></div>
                <div id="nouveau1"></div>
               </div>
              </div>
          <p>
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
 $( document ).ready(function() {
    get_rapport();
   
});   
</script>
<script type="text/javascript">
  function get_rapport(){
    if (document.getElementById('IS_PRIVATE1').checked) {
   var IS_PRIVATE = document.getElementById('IS_PRIVATE1').value;
   }else if (document.getElementById('IS_PRIVATE2').checked) {
   var IS_PRIVATE = document.getElementById('IS_PRIVATE2').value;
    }
    var TYPE_OPERATION_ID=$('#TYPE_OPERATION_ID').val();
    var inst_conn=$('#inst_conn').val();
    $.ajax({
      url : "<?=base_url('dashboard/Dashboard_Performance_Decrochage/get_performance_raccrochage')?>",
      type : "GET",
      dataType: "JSON",
      cache:false,
      data:{
       TYPE_OPERATION_ID:TYPE_OPERATION_ID,
       inst_conn:inst_conn,
       IS_PRIVATE:IS_PRIVATE, 
         },
     success:function(data){
      $('#container').html("");             
      $('#nouveau').html(data.rapp);
      $('#container1').html("");             
      $('#nouveau1').html(data.rapp1);
      },            
  });  
  }
  function saveData()
  {
  $('#myModal').modal('hide');
 }
</script>