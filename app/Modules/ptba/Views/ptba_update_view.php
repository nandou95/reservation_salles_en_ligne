<?php $validation = \Config\Services::validation(); ?>

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
        <div  style="float: right;">
         <a href="/ptba/ptba_contr/index/" style="float: right;margin: 4px" class="btn btn-primary"><i class="fa fa-reply-all" aria-hidden="true"></i> Retour </a>  

       </div>
       <div class="header">
         <h1 style="margin-left: 20px" class="header-title text-dark">
          Ajouter ptba
        </h1>
      </div>
      <div class="car-body">


      <form id="my_form" action="<?= base_url('/ptba/ptba_contr/modifier/') ?>" method="POST">
        <div class="card-body">
         <div class="row">
           <input type="hidden" name="PTBA_ID" value="<?=$ptb['PTBA_ID']?>">

           <div class="col-4">
            <div class="form-group">
             <label>Activités<span style="color: red;">*</span></label>
             <input type="text"  value="<?=$ptb['ACTIVITES']?>" name="ACTIVITES" id="ACTIVITES" class="form-control" value="<?= set_value('ACTIVITES')?>">
             <span class="text-danger" id="error_Activités"></span>
           </div>
         </div>


         <div class="col-4">
          <div class="form-group">
           <label>Resultats Attendus<span style="color: red;">*</span></label>
           <input type="text" value="<?=$ptb['RESULTATS_ATTENDUS']?>" name="RESULTATS_ATTENDUS" id="RESULTATS_ATTENDUS" value="<?= set_value('RESULTATS_ATTENDUS')?>" class="form-control">
           <span class="text-danger" id="error_Resultats_Attendus"></span>
         </div>
       </div>


       <div class="col-4">
         <div class="form-group">
          <label>Unite<span style="color: red;">*</span></label>
          <input type="float"  value="<?=$ptb['UNITE']?>" name="UNITE" id="UNITE"  class="form-control" value="<?= set_value('UNITE')?>">
          <span class="text-danger" id="error_Unite"></span>
        </div>
      </div>

      <div class="col-4">
       <div class="form-group">
        <label>Coût unitaire<span style="color: red;">*</span></label>
        <input type="float"  value="<?=$ptb['COUT_UNITAIRE_BIF']?>" name="COUT_UNITAIRE_BIF" id="COUT_UNITAIRE_BIF" class="form-control" value="<?= set_value('COUT_UNITAIRE_BIF')?>">
        <span class="text-danger" id="error_Cout_unitaire"></span>
      </div>
    </div>

    <div class="col-4">
     <div class="form-group">
      <label>Quantite T1<span style="color: red;">*</span></label>
      <input type="float" value="<?=$ptb['QT1']?>" name="QT1" id="QT1" class="form-control" value="<?= set_value('QT1')?>">
      <span class="text-danger" id="error_Quantite1"></span>
    </div>
  </div>


  <div class="col-4">
   <div class="form-group">
    <label>Quantite T2<span style="color: red;">*</span></label>
    <input type="float" value="<?=$ptb['QT2']?>" name="QT2" id="QT2" value="<?= set_value('QT2')?>" class="form-control">
    <span class="text-danger" id="error_Quantite2"></span>
  </div>
</div>
<div class="col-4">
  <div class="form-group">
   <label>Quantite T3<span style="color: red;">*</span></label>
   <input type="float"  value="<?=$ptb['QT3']?>" name="QT3" id="QT3" class="form-control" value="<?= set_value('QT4')?>">
   <span class="text-danger" id="error_Quantite3"></span>
 </div>
</div>


<div class="col-4">
  <div class="form-group">
   <label>Quantite T4<span style="color: red;">*</span></label>
   <input type="float"  value="<?=$ptb['QT4']?>" name="QT4" id="QT4" value="<?= set_value('QT4')?>" class="form-control">
   <span class="text-danger" id="error_Quantite4"></span>
 </div>
</div>

<div class="col-4">
  <div class="form-group">
   <label>Institution Grande Masse<span style="color: red;">*</span></label>
   <input type="text" value="<?=$ptb['INTITULE_DES_GRANDES_MASSES']?>"  name="INTITULE_DES_GRANDES_MASSES" id="INTITULE_DES_GRANDES_MASSES" class="form-control" value="<?= set_value('INTITULE_DES_GRANDES_MASSES')?>">
   <span class="text-danger" id="error_Institution_Grande_Masse"></span>
 </div>
</div>

<div class="col-4">
  <div class="form-group">
   <label>Grande Masse Budget Programme<span style="color: red;">*</span></label>
   <input type="text" value="<?=$ptb['GRANDE_MASSE_BP']?>" name="GRANDE_MASSE_BP" id="GRANDE_MASSE_BP" class="form-control" value="<?= set_value('GRANDE_MASSE_BP')?>">
   <span class="text-danger" id="error_Grande_Masse_Budget_Programme"></span>
 </div>
</div>

<div class="col-4">
  <div class="form-group">
   <label>Grande Masse Budget Moyen 1<span style="color: red;">*</span></label>
   <input type="text" value="<?=$ptb['GRANDE_MASSE_BM1']?>" name="GRANDE_MASSE_BM1" id="GRANDE_MASSE_BM1" value="<?= set_value('GRANDE_MASSE_BM1')?>" class="form-control">
   <span class="text-danger" id="error_Grande_Masse_Budget_Moyen_1"></span>
 </div>
</div>


<div class="col-4">
  <div class="form-group">
   <label>Grande Masse Budget Moyen<span style="color: red;">*</span></label>
   <input type="text" value="<?=$ptb['GRANDE_MASSE_BM']?>" name="GRANDE_MASSE_BM" id="GRANDE_MASSE_BM" class="form-control" value="<?= set_value('GRANDE_MASSE_BM')?>">
   <span class="text-danger" id="error_Grande Masse_Budget_Moyen"></span>
 </div>
</div>

<div class="col-4">
  <div class="form-group">
   <label>Responsable<span style="color: red;">*</span></label>
   <input type="text" value="<?=$ptb['RESPONSABLE']?>" name="RESPONSABLE" id="RESPONSABLE" value="<?= set_value('RESPONSABLE')?>" class="form-control">
   <span class="text-danger" id="error_Responsable"></span>
 </div>
 
</div>


<div class="col-12">
  <button style="float: right;" id="btnSave" type="button" onclick="update()" class="btn btn-primary float-end envoi"<i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;Modifier</button>
</div>
</div>
</div>
</form>

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





  function update()
  {  

    var ACTIVITES  = $('#ACTIVITES').val();
    var RESULTATS_ATTENDUS  = $('#RESULTATS_ATTENDUS').val();
    var UNITE = $('#UNITE').val();
    var COUT_UNITAIRE_BIF  = $('#COUT_UNITAIRE_BIF').val();
    var QT1  = $('#QT1').val();
    var QT2  = $('#QT2').val();
    var QT3  = $('#QT3').val();
    var QT4  = $('#QT4').val();
    var INTITULE_DES_GRANDES_MASSES  = $('#INTITULE_DES_GRANDES_MASSES').val();
    var GRANDE_MASSE_BP  = $('#GRANDE_MASSE_BP').val();
    var GRANDE_MASSE_BM1 = $('#GRANDE_MASSE_BM1').val();
    var GRANDE_MASSE_BM  = $('#GRANDE_MASSE_BM').val();
    var RESPONSABLE  = $('#RESPONSABLE').val();

    $('#error_Activités').html('');
    $('#error_Resultats_Attendus').html('');
    $('#error_Unite').html('');
    $('#error_Cout_unitaire').html('');
    $('#error_Quantite1').html('');
    $('#error_Quantite2').html('');
    $('#error_Quantite3').html('');
    $('#error_Quantite4').html('');
    $('#error_Institution_Grande_Masse').html('');
    $('#error_Grande_Masse_Budget_Programme').html('');
    $('#error_Grande_Masse_Budget_Moyen_1').html('');
    $('#Masse_Budget_Moyen').html('');
    $('#error_Responsable').html('');


    var statut = 2;




    if(ACTIVITES == '')
    {
      $('#error_Activités').html('Le champ est obligatoire');
      statut = 1;
    }
    if(RESULTATS_ATTENDUS == '')
    {
      $('#error_Resultats_Attendus').html('Le champ est obligatoire');
      statut = 1;
    }
    if(UNITE == '')
    {
      $('#error_Unite').html('Le champ est obligatoire');
      statut = 1;
    }
    if(COUT_UNITAIRE_BIF == '')
    {
      $('#error_Cout_unitaire').html('Le champ est obligatoire');
      statut = 1;
    }
    if(QT1 == '')
    {
      $('#error_Quantite1').html('Le champ est obligatoire');
      statut = 1;
    }
    if(QT2 == '')
    {
      $('#error_Quantite2').html('Le champ est obligatoire');
      statut = 1;
    }
    if(QT3 == '')
    {
      $('#error_Quantite3').html('Le champ est obligatoire');
      statut = 1;
    }
    if(QT4 == '')
    {
      $('#error_Quantite4').html('Le champ est obligatoire');
      statut = 1;
    }
    if(INTITULE_DES_GRANDES_MASSES == '')
    {
      $('#error_Institution_Grande_Masse').html('Le champ est obligatoire');
      statut = 1;
    }
    if(GRANDE_MASSE_BP == '')
    {
      $('#error_Grande_Masse_Budget_Programme').html('Le champ est obligatoire');
      statut = 1;

    }
    if(GRANDE_MASSE_BM1 == '')
    {
      $('#error_Grande_Masse_Budget_Moyen_1').html('Le champ est obligatoire');
      statut = 1;

    }
    if(GRANDE_MASSE_BM == '')
    {
      $('#Masse_Budget_Moyen').html('Le champ est obligatoire');
      statut = 1;

    }
    if(RESPONSABLE == '')
    {
      $('#error_Responsable').html('Le champ est obligatoire');
      statut = 1;

    }



    if(statut == 2)
    {
      document.getElementById("my_form").submit();

    }
  }


</script>