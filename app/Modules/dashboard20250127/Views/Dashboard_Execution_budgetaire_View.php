
<!DOCTYPE html>
<html lang="en">
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/highcharts-more.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<?php echo view('includesbackend/header.php');?> 
</head>
<body >
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <main class="content">
        <div class="container-fluid">
          <!-- <div class="header"> -->
           <!--  <h1 class="header-title text-white">
            taux d'execution
           </h1> -->
         </div>
         <div class="row">
          <div class="col-md-12">
            <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card" >
            <div class="row">
              <div class="form-group col-md-3">
                <label><b>Catégorie</b></label>
                <select class="form-control" onchange="get_i()" name="TYPE_INSTITUTION_ID" id="TYPE_INSTITUTION_ID">
                  <option value="">sélectionner</option>

                  <?php
                  foreach ($type_ministre as $value)
                  {
                    if ($value->TYPE_INSTITUTION_ID== $TYPE_INSTITUTION_ID)
                    {
                      ?>
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
                <select class="form-control" onchange="get_m()" name="INSTITUTION_ID" id="INSTITUTION_ID">
                  
                  <option value="">Sélectionner</option>
                </select>        
              </div>
              
              <div class="form-group col-md-2">
                <label><b>Programmes</b></label>
                <select class="form-control" onchange="get_s()" name="PROGRAMME_ID" id="PROGRAMME_ID">
                  
                  <option value="">Sélectionner</option>
                </select>        
              </div>
              <div class="form-group col-md-2">
                <label><b>Actions</b></label>
                <select class="form-control" onchange="get_g()" name="ACTION_ID" id="ACTION_ID">                 
                  <option value="">Sélectionner</option>
                </select>        
              </div>
            <div class="form-group col-md-2">
              <label><b>Grande masse</b></label>
               <select class="form-control" onchange="get_rapport()" name="GRANDE_MASSE_ID" id="GRANDE_MASSE_ID">                      
                <option value="">Selectionner</option>
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
                       <th style='width:30px'><center><font color="black" size="3"><label>#</label></font></center></th>
                      <th style='width:90px'><center><font color="black" size="3"><label>&nbsp&nbspMinistère&nbsp&nbsp<a id='idpro'><a>&nbspp&nbsp</label></font></center></th>
                       <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbspProgramme&nbsp&nbsp&nbsp<a id='idcod'><a>&nbspObjectif&nbspdu&nbspprogramme&nbsp</label></font></center></th>
                        <th style='width:70px'><center><font color="black" size="3"><label>&nbsp&nbspAction&nbsp&nbsp<a id='idobj'><a>&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                       
                       <th style='width:120px'><center><font color="black" size="3"><label>&nbspBudget&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>   
                       <th style='width:50px'><center><font color="black" size="3"><label>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbspInstitution&nbspou&nbspMinistere&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</label></font></center></th>
                     
                     </thead>
                   </table>  
                 </div>
                 <div class="modal-footer">
                  <button type="button" id="btnSave" onclick="saveData()" class="btn btn-primary">Close</button>
                </div>
              </div>
            </div>
          </div>  

        </div>
        

      </div>
      <div class="row">
      <div class="col-md-12" style="margin-bottom: 20px"></div>
      <div id="container"  class="col-md-12"></div> 
            
    </div>
    </div>

   </div>
  </main>
  </div>
  </div>
<?php echo view('includesbackend/scripts_js.php');?>

</body>
</html>

<div id="nouveau"></div>
<link rel="stylesheet" href="//cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="//cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript">
    let table = new DataTable('#myTable');
  </script>

<script type="text/javascript">
 $( document ).ready(function() {
    get_rapport();
    // alert();
});   
function get_i() {

 $('#INSTITUTION_ID').html('');
$('#PROGRAMME_ID').html('');
$('#ACTION_ID').html('');
 get_rapport();
   
}
</script>

<script type="text/javascript">
function get_m() {

  $('#PROGRAMME_ID').html('');
  $('#ACTION_ID').html('');
  $('#GRANDE_MASSE_ID').html('');
    get_rapport(); 
}
function get_s() {

  $('#ACTION_ID').html('');
  $('#GRANDE_MASSE_ID').html('');
    get_rapport();  
}
function get_ma() {
   $('#GRANDE_MASSE_ID').html('');
    get_rapport(); 
}
</script> 
   
<script type="text/javascript">
  function get_rapport(){
    var TYPE_INSTITUTION_ID=$('#TYPE_INSTITUTION_ID').val();
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    var PROGRAMME_ID=$('#PROGRAMME_ID').val();
    var ACTION_ID=$('#ACTION_ID').val();
    var GRANDE_MASSE_ID=$('#GRANDE_MASSE_ID').val();

    $.ajax({
      url : "<?=base_url('dashboard/Dashboard_Execution_budgetaire/get_budgetaire')?>",
      type : "GET",
      dataType: "JSON",
      cache:false,
      data:{
       TYPE_INSTITUTION_ID:TYPE_INSTITUTION_ID,
       INSTITUTION_ID:INSTITUTION_ID,
       PROGRAMME_ID:PROGRAMME_ID,
       ACTION_ID:ACTION_ID,
       GRANDE_MASSE_ID:GRANDE_MASSE_ID,

     },

     success:function(data){
      $('#container').html("");             
      $('#nouveau').html(data.rapp);
      $('#INSTITUTION_ID').html(data.inst);
      $('#PROGRAMME_ID').html(data.program);
      $('#ACTION_ID').html(data.actions);
      $('#GRANDE_MASSE_ID').html(data.masse);
      
      if (TYPE_INSTITUTION_ID==1) {
        $("#idmin").html("Institution");
      }
      else if (TYPE_INSTITUTION_ID==2) {
        $("#idmin").html("Ministere");
      }else{
        $("#idmin").html("Institution ou Ministere");
      } 
      
    },            
  });  
  }
  function saveData()
  {

   $('#myModal').modal('hide');
 } 
</script>