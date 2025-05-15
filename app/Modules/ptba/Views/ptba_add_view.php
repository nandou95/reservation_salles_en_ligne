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
         <br>
         <div class="col-12 d-flex">

          <div class="col-9" style="float: left;">
            <h1 class="header-title text-dark">
              <?=lang('messages_lang.add_activite')?> 
            </h1>
          </div>
          <div class="col-3" style="float: right;">
            <a href="<?=base_url('ptba/Liste_Ptba')?>" style="float: right;margin-right: 90px;margin: 10px" class="btn btn-primary"><span class="fa fa-list pull-right"></span>&nbsp;<?=lang('messages_lang.bouton_liste')?></a>   
          </div>
        </div>
        <br>
        <div class="card-body">


         <form id="my_form" action="<?= base_url('ptba/Ptba_contr/insert') ?>" method="POST">
          <div class="card-body">
           <div class="row">
             <div class="col-6">
              <p for="FName" style="font-weight: 900; color:#454545"><?=lang('messages_lang.autorisation_ministre')?></p>
              <div class="form-check">
               <input class="form-check-input" type="radio" name="FRAIS" id="FRAIS_OUI" value="1" onclick="show(this.value)" checked>
               <label class="form-check-label" for="FRAIS">
                 <?=lang('messages_lang.label_oui')?>
               </label>
             </div>

             <div class="form-check">
               <input class="form-check-input" type="radio" name="FRAIS" id="FRAIS_NON" value="2" onclick="show(this.value)">
               <label class="form-check-label" for="FRAIS">
                 <?=lang('messages_lang.label_non')?>
               </label>
             </div>
           </div>
           <br>
           <div class="col-6" id="rep" style="display: none;">
            <div class="form-group">
              <label><?=lang('messages_lang.labelle_mot')?><span style="color: red;">*</span></label>

              <select  class="form-control" name="MOTIF_ACTIVITE_ID" id="MOTIF_ACTIVITE_ID" onclick="hierarchie(this.value)">
               <option value=""><?=lang('messages_lang.selection_message')?></option>
               <?php  foreach ($motif as $key) { ?>
                 <option value="<?=$key->MOTIF_ACTIVITE_ID ?>">
                  <?=$key->DESCR_MOTIF_ACTIVITE?></option>
                <?php }?>
              </select>
              <?php if (isset($validation)) : ?>
                <?= $validation->getError('MOTIF_ACTIVITE_ID'); ?>
              <?php endif ?>
              <span id="error_MOTIF_ACTIVITE_ID" class="text-danger"></span>
            </div>

          </div>
        </div>
        <br>
        <div class="row" id="respo" style="display: none;">
          <div class="col-4">
            <div class="form-group">
              <label><?=lang('messages_lang.labelle_nom')?><span style="color: red;">*</span></label>
              <input type="text" name="NOM" id="NOM" class="form-control" value="<?= set_value('NOM')?>" >
              <span id="error_NOM" class="text-danger"></span>
            </div>
          </div>

          <div class="col-4">
            <div class="form-group">
              <label><?=lang('messages_lang.labelle_prenom')?><span style="color: red;">*</span></label>
              <input type="text" name="PRENOM" id="PRENOM" class="form-control" value="<?= set_value('PRENOM')?>" >
              <span id="error_PRENOM" class="text-danger"></span>
            </div>
          </div>

          <div class="col-4">
            <div class="form-group">
              <label><?=lang('messages_lang.poste')?><span style="color: red;">*</span></label>
              <input type="text" name="POSTE" id="POSTE" class="form-control" value="<?= set_value('POSTE')?>" >
              <span id="error_POSTE" class="text-danger"></span>
            </div>
          </div>
        </div>
        <br>
        <hr>
        <br>
        <div class="row">
          <input type="hidden" name="INSTITUTION_ID" id="INSTITUTION_ID" value="<?=$INSTITUTION_ID?>">
          <input type="hidden" name="CODE_INSTITUTION" id="CODE_INSTITUTION" value="<?=$code_instit?>">
          <input type="hidden" name="DESCR_INSTITUTION" id="DESCR_INSTITUTION" value="<?=$descr_instit?>">

          <div class="col-4">
           <div class="form-group">
            <label><?=lang('messages_lang.table_st')?><span style="color: red;">*</span></label>

            <select  class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" onchange="get_code();">
             <option value=""><?=lang('messages_lang.selection_message')?></option>
             <?php  foreach ($inst_sous_tutel as $inst) { ?>
               <option value="<?=$inst->SOUS_TUTEL_ID ?>">
                <?=$inst->DESCRIPTION_SOUS_TUTEL?></option>
              <?php }?>
            </select>
            <?php if (isset($validation)) : ?>
              <?= $validation->getError('SOUS_TUTEL_ID'); ?>
            <?php endif ?>
            <span id="error_DESCRIPTION_SOUS_TUTEL" class="text-danger"></span>
          </div>
        </div>
        <div class="col-4">
          <div class="form-group">
           <label><?=lang('messages_lang.table_Programme')?><span style="color: red;">*</span></label>
           <select class="form-control" name="PROGRAMME_ID" id="PROGRAMME_ID" onchange="get_action()">
            <option value=""><?=lang('messages_lang.selection_message')?></option>
            <?php  foreach ($inst_program as $progr) { ?>
              <option value="<?=$progr->PROGRAMME_ID ?>">
               <?=$progr->INTITULE_PROGRAMME?></option>
             <?php }?>
           </select>
           <?php if (isset($validation)) : ?>
            <?= $validation->getError('PROGRAMME_ID'); ?>
          <?php endif ?>
          <span id="error_INTITULE_PROGRAMME" class="text-danger"></span>
        </div>
      </div> 

      <div class="col-4">
        <label for="ministère" class="Form-label text-muted"><?=lang('messages_lang.table_Action')?> <font color="red" >*</font></label>
        <select class="form-control form-select bg-light select2" id="ACTION_ID" name="ACTION_ID" autocomplete="off" aria-label=".form-select-lg example"  >
         <option value=""><?=lang('messages_lang.selection_message')?> </option>
       </select>
       <?php if (isset($validation)) : ?>
        <?= $validation->getError('ACTION_ID'); ?>
      <?php endif ?>
      <div class="valid-feedback">
      </div>
      <span class="text-danger" id="error_ACTION_ID"></span>
    </div>

    <div class="col-4">
      <label for="ministère" class="Form-label text-muted"><?=lang('messages_lang.labelle_code_budgetaire')?><font color="red" >*</font></label>
      <select class="form-control form-select bg-light select2" id="CODE_NOMENCLATURE_BUDGETAIRE" name="CODE_NOMENCLATURE_BUDGETAIRE" autocomplete="off" aria-label=".form-select-lg example"  >
       <option value=""><?=lang('messages_lang.selection_message')?> </option>

     </select>
     <?php if (isset($validation)) : ?>
      <?= $validation->getError('CODE_NOMENCLATURE_BUDGETAIRE'); ?>
    <?php endif ?>
    <div class="valid-feedback">
    </div>
    <span class="text-danger" id="error_code_budget"></span>
  </div>

  <div class="col-4">
    <div class="form-group">
      <label><?=lang('messages_lang.labelle_code_programmatique')?></label>
      <input type="text" name="CODES_PROGRAMMATIQUE" id="CODES_PROGRAMMATIQUE" class="form-control" value="<?= set_value('CODES_PROGRAMMATIQUE')?>" >
    </div>
  </div>

  <div class="col-4">
    <div class="form-group">
     <label><?=lang('messages_lang.labelle_activites')?><span style="color: red;">*</span></label>
     <textarea type="text" name="ACTIVITES" id="ACTIVITES" class="form-control" value="<?= set_value('ACTIVITES')?>" rows="2"></textarea>
     <?php if (isset($validation)) : ?>
      <?= $validation->getError('ACTIVITES'); ?>
    <?php endif ?>
    <span class="text-danger" id="error_Activités"></span>
  </div>
</div>
<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_resultant_attendus')?><span style="color: red;">*</span></label>
   <textarea type="text" name="RESULTATS_ATTENDUS" id="RESULTATS_ATTENDUS" value="<?= set_value('RESULTATS_ATTENDUS')?>" class="form-control" rows="2"></textarea>
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('RESULTATS_ATTENDUS'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Resultats_Attendus"></span>
</div>
</div>

<div class="col-4">
 <div class="form-group">
  <label><?=lang('messages_lang.labelle_article_economique')?><span style="color: red;">*</span></label>
  <input type="text" name="ARTICLE_ECONOMIQUE" id="ARTICLE_ECONOMIQUE" class="form-control" value="<?= set_value('ARTICLE_ECONOMIQUE')?>">
  <?php if (isset($validation)) : ?>
    <?= $validation->getError('ARTICLE_ECONOMIQUE'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_ARTICLE_ECONOMIQUE"></span>
</div>
</div>

<div class="col-4">
 <div class="form-group">
  <label><?=lang('messages_lang.labelle_intitule_economique')?><span style="color: red;">*</span></label>
  <textarea  type="text" name="INTITULE_ARTICLE_ECONOMIQUE" id="INTITULE_ARTICLE_ECONOMIQUE" class="form-control"><?= set_value('INTITULE_ARTICLE_ECONOMIQUE')?></textarea>
  <?php if (isset($validation)) : ?>
    <?= $validation->getError('INTITULE_ARTICLE_ECONOMIQUE'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_INTITULE_ARTICLE_ECONOMIQUE"></span>
</div>
</div>

<div class="col-4">
 <div class="form-group">
  <label><?=lang('messages_lang.labelle_nature_economique')?><span style="color: red;">*</span></label>
  <input type="text" name="NATURE_ECONOMIQUE" id="NATURE_ECONOMIQUE" class="form-control" value="<?= set_value('NATURE_ECONOMIQUE')?>">
  <?php if (isset($validation)) : ?>
    <?= $validation->getError('NATURE_ECONOMIQUE'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_NATURE_ECONOMIQUE"></span>
</div>
</div>

<div class="col-4">
 <div class="form-group">
  <label><?=lang('messages_lang.labelle_intitule_nature_economique')?><span style="color: red;">*</span></label>
  <textarea type="text" name="INTITULE_NATURE_ECONOMIQUE" id="INTITULE_NATURE_ECONOMIQUE" class="form-control"><?= set_value('INTITULE_ARTICLE_ECONOMIQUE')?></textarea>
  <?php if (isset($validation)) : ?>
    <?= $validation->getError('INTITULE_NATURE_ECONOMIQUE'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_INTITULE_NATURE_ECONOMIQUE"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
    <label for="Nom" class="form-label"><?=lang('messages_lang.label_division')?><span style="color: red;">*</span></label>
    <select onchange="get_groupes(this.value)" class="form-control" name="DIVISION_ID" id="DIVISION_ID">
      <option value=""><?=lang('messages_lang.selection_message')?></option>
      <?php
      foreach($get_division as $key)
      {
        if($key->DIVISION_ID==set_value('DIVISION_ID'))
        {
          echo "<option value='".$key->DIVISION_ID."'  selected>".$key->CODE_DIVISION." - ".$key->LIBELLE_DIVISION."</option>";
        }
        else
        {
          echo "<option value='".$key->DIVISION_ID."' >".$key->CODE_DIVISION." - ".$key->LIBELLE_DIVISION."</option>";
        }
      }
      ?>
    </select>
    <?php if (isset($validation)) : ?>
      <?= $validation->getError('DIVISION_ID'); ?>
    <?php endif ?>
    <span id="error_DIVISION_ID" class="text-danger"></span>
  </div>
</div>
<div class="col-4">
  <div class="form-group">
    <label for="Nom" class="form-label"><?=lang('messages_lang.label_groupe')?><span style="color: red;">*</span></label>
    <select onchange="get_classes(this.value)" class="form-control" name="GROUPE_ID" id="GROUPE_ID">
      <option value=""><?=lang('messages_lang.selection_message')?></option>
    </select>
    <?php if (isset($validation)) : ?>
      <?= $validation->getError('GROUPE_ID'); ?>
    <?php endif ?>
    <span id="error_GROUPE_ID" class="text-danger"></span>
  </div>
</div>
<div class="col-4">
  <div class="form-group">
    <label for="Nom" class="form-label"><?=lang('messages_lang.label_classe')?><span style="color: red;">*</span></label>
    <select class="form-control" name="CLASSE_ID" id="CLASSE_ID">
      <option value=""><?=lang('messages_lang.selection_message')?></option>
    </select>
    <?php if (isset($validation)) : ?>
      <?= $validation->getError('CLASSE_ID'); ?>
    <?php endif ?>
    <span id="error_CLASSE_ID" class="text-danger"></span>
  </div>
</div>

<div class="col-4">
 <div class="form-group">
  <label><?=lang('messages_lang.label_unity')?><span style="color: red;">*</span></label>
  <input type="text" name="UNITE" id="UNITE"  class="form-control" value="<?= set_value('UNITE')?>">
  <?php if (isset($validation)) : ?>
    <?= $validation->getError('UNITE'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Unite"></span>
</div>
</div>

<div class="col-4">
 <div class="form-group">
  <label><?=lang('messages_lang.labelle_cout_unitaire')?><span style="color: red;">*</span></label>
  <input type="text" name="COUT_UNITAIRE_BIF" id="COUT_UNITAIRE_BIF" class="form-control" value="<?= set_value('COUT_UNITAIRE_BIF')?>">
  <?php if (isset($validation)) : ?>
    <?= $validation->getError('COUT_UNITAIRE_BIF'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Cout_unitaire"></span>
</div>
</div>

<div class="col-4">
 <div class="form-group">
  <label><?=lang('messages_lang.labelle_quantite')?> T1<span style="color: red;">*</span></label>
  <input type="text" name="QT1" id="QT1" class="form-control" value="<?= set_value('QT1')?>">
  <?php if (isset($validation)) : ?>
    <?= $validation->getError('QT1'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Quantite1"></span>
</div>
</div>


<div class="col-4">
 <div class="form-group">
  <label><?=lang('messages_lang.labelle_quantite')?> T2<span style="color: red;">*</span></label>
  <input type="text" name="QT2" id="QT2" value="<?= set_value('QT2')?>" class="form-control">
  <?php if (isset($validation)) : ?>
    <?= $validation->getError('QT2'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Quantite2"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_quantite')?> T3<span style="color: red;">*</span></label>
   <input type="text" name="QT3" id="QT3" class="form-control" value="<?= set_value('QT3')?>">
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('QT3'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Quantite3"></span>
</div>
</div>


<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_quantite')?> T4<span style="color: red;">*</span></label>
   <input type="text" name="QT4" id="QT4" value="<?= set_value('QT4')?>" class="form-control">
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('QT4'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Quantite4"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_montant')?> T1<span style="color: red;">*</span></label>
   <input type="text" name="T1" id="T1" value="<?= set_value('T1')?>" class="form-control" readonly>
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('T1'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_T1"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_montant')?> T2<span style="color: red;">*</span></label>
   <input type="text" name="T2" id="T2" value="<?= set_value('T2')?>" class="form-control" readonly>
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('T2'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_T2"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_montant')?> T3<span style="color: red;">*</span></label>
   <input type="text" name="T3" id="T3" value="<?= set_value('T3')?>" class="form-control" readonly>
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('T3'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_T3"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_montant')?> T4<span style="color: red;">*</span></label>
   <input type="text" name="T4" id="T4" value="<?= set_value('T4')?>" class="form-control" readonly>
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('T4'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_T4"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.intitule_GM')?><span style="color: red;">*</span></label>
   <textarea type="text" name="INTITULE_DES_GRANDES_MASSES" id="INTITULE_DES_GRANDES_MASSES" class="form-control"><?=set_value('INTITULE_DES_GRANDES_MASSES')?></textarea>
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('INTITULE_DES_GRANDES_MASSES'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Institution_Grande_Masse"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.label_gm_bp')?><span style="color: red;">*</span></label>
   <input type="text" name="GRANDE_MASSE_BP" id="GRANDE_MASSE_BP" class="form-control" value="<?= set_value('GRANDE_MASSE_BP')?>">
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('GRANDE_MASSE_BP'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Grande_Masse_Budget_Programme"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.label_gm_bm')?> 1<span style="color: red;">*</span></label>
   <input type="text" name="GRANDE_MASSE_BM1" id="GRANDE_MASSE_BM1" value="<?= set_value('GRANDE_MASSE_BM1')?>" class="form-control">
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('GRANDE_MASSE_BM1'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Grande_Masse_Budget_Moyen_1"></span>
</div>
</div>


<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.label_gm_bm')?><span style="color: red;">*</span></label>
   <input type="text" name="GRANDE_MASSE_BM" id="GRANDE_MASSE_BM" class="form-control" value="<?= set_value('GRANDE_MASSE_BM')?>">
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('GRANDE_MASSE_BM'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Masse_Budget_Moyen"></span>
</div>
</div>

<div class="col-4">
  <div class="form-group">
   <label><?=lang('messages_lang.labelle_responsable')?><span style="color: red;">*</span></label>
   <input type="text" name="RESPONSABLE" id="RESPONSABLE" value="<?= set_value('RESPONSABLE')?>" class="form-control">
   <?php if (isset($validation)) : ?>
    <?= $validation->getError('RESPONSABLE'); ?>
  <?php endif ?>
  <span class="text-danger" id="error_Responsable"></span>
</div>

</div>


<div class="col-12">
  <button style="float: right;" id="btnSave" type="button" onclick="insert()" class="btn btn-primary float-end envoi"><i class="fa fa-sign-in" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.bouton_enregistrer')?></button>
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
  $(document).ready(function()
  {

   $("#COUT_UNITAIRE_BIF, #QT1, #QT2, #QT3, #QT4").on('input', function()
   {
    $(this).val($(this).val().replace(/[^0-9.]*$/gi, ''));

    var COUT_UNITAIRE_BIF = $('#COUT_UNITAIRE_BIF').val();

    var quantites = ['#QT1', '#QT2', '#QT3', '#QT4'];
    var resultats = ['#T1', '#T2', '#T3', '#T4'];

    for (var i = 0; i < quantites.length; i++) {
      var inputVal = $(quantites[i]).val();
      var resultVal = parseFloat(inputVal) * parseFloat(COUT_UNITAIRE_BIF) || 0;
      $(resultats[i]).val(resultVal);
    }
  });
 });
</script>

<script type="text/javascript">
 $("#ARTICLE_ECONOMIQUE, #NATURE_ECONOMIQUE").on('input', function()
 {
  $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));

});

 $("#CODES_PROGRAMMATIQUE, #GRANDE_MASSE_BM, #GRANDE_MASSE_BM1, #GRANDE_MASSE_BP").on('input', function()
 {
  $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));

});


 $("#NOM, #PRENOM, #RESPONSABLE, #POSTE").on('input', function()
 {
  $(this).val($(this).val().replace(/[^a-z|A-Z ]*$/gi, '').toUpperCase());

});

</script>


<script type="text/javascript">
 
 function get_code()
 {
  var INSTITUTION_ID = $('#INSTITUTION_ID').val(); 
  var CODE_INSTITUTION = $('#CODE_INSTITUTION').val();
  var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();

  if(SOUS_TUTEL_ID=='')
  {
   $('#CODE_NOMENCLATURE_BUDGETAIRE').html('<option value=""><?=lang('messages_lang.selection_message')?></option>');
   $('#ACTIVITES').html('<option value=""><?=lang('messages_lang.selection_message')?></option>');
 }
 else
 {

   $('#CODE_NOMENCLATURE_BUDGETAIRE').html('<option value=""><?=lang('messages_lang.selection_message')?></option>');
   var url = "<?=base_url('/ptba/Ptba_contr/get_code')?>";


   $.post('<?=base_url('ptba/Ptba_contr/get_code')?>',
   {
    INSTITUTION_ID:INSTITUTION_ID,
    CODE_INSTITUTION:CODE_INSTITUTION,
    SOUS_TUTEL_ID:SOUS_TUTEL_ID

  },
  function(data)
  {
    $('#CODE_NOMENCLATURE_BUDGETAIRE').html(data.codeBudgetaire);
    CODE_NOMENCLATURE_BUDGETAIRE.InnerHtml=data.codeBudgetaire;
    
  })

 }
}
</script>

<script type="text/javascript">

  function get_action(){

    var PROGRAMME_ID=$('#PROGRAMME_ID').val();

    $.ajax({
      url : "<?=base_url('/ptba/Ptba_contr/get_action')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:{
        PROGRAMME_ID:PROGRAMME_ID

      },

      success:function(data){   
        $('#ACTION_ID').html(data.tutel);

      },            

    });  

  }
</script>


<script type="text/javascript">

  function insert()
  {  
    var MOTIF_ACTIVITE_ID = $('#MOTIF_ACTIVITE_ID').val();
    var NOM = $('#NOM').val();
    var PRENOM = $('#PRENOM').val();
    var POSTE = $('#POSTE').val();
    var SOUS_TUTEL_ID  = $('#SOUS_TUTEL_ID').val();
    var PROGRAMME_ID  = $('#PROGRAMME_ID').val();
    var ACTION_ID  = $('#ACTION_ID').val();
    var CODE_NOMENCLATURE_BUDGETAIRE  = $('#CODE_NOMENCLATURE_BUDGETAIRE').val();
    var ACTIVITES  = $('#ACTIVITES').val();
    var RESULTATS_ATTENDUS  = $('#RESULTATS_ATTENDUS').val();
    var UNITE = $('#UNITE').val();
    var COUT_UNITAIRE_BIF  = $('#COUT_UNITAIRE_BIF').val();
    var QT1  = $('#QT1').val();
    var QT2  = $('#QT2').val();
    var QT3  = $('#QT3').val();
    var QT4  = $('#QT4').val();
    var T1  = $('#T1').val();
    var T2  = $('#T2').val();
    var T3  = $('#T3').val();
    var T4  = $('#T4').val();
    var INTITULE_DES_GRANDES_MASSES  = $('#INTITULE_DES_GRANDES_MASSES').val();
    var GRANDE_MASSE_BP  = $('#GRANDE_MASSE_BP').val();
    var GRANDE_MASSE_BM1 = $('#GRANDE_MASSE_BM1').val();
    var GRANDE_MASSE_BM  = $('#GRANDE_MASSE_BM').val();
    var RESPONSABLE  = $('#RESPONSABLE').val();

    var ARTICLE_ECONOMIQUE = $('#ARTICLE_ECONOMIQUE').val();
    var INTITULE_ARTICLE_ECONOMIQUE = $('#INTITULE_ARTICLE_ECONOMIQUE').val();

    var NATURE_ECONOMIQUE = $('#NATURE_ECONOMIQUE').val();
    var INTITULE_NATURE_ECONOMIQUE = $('#INTITULE_NATURE_ECONOMIQUE').val();


    var DIVISION_ID = $('#DIVISION_ID').val();
    var GROUPE_ID = $('#GROUPE_ID').val();
    var CLASSE_ID = $('#CLASSE_ID').val();

    $('#error_MOTIF_ACTIVITE_ID').html('');
    $('#error_NOM').html('');
    $('#error_PRENOM').html('');
    $('#error_POSTE').html('');
    $('#error_DESCRIPTION_SOUS_TUTEL').html('');
    $('#error_INTITULE_PROGRAMME').html('');
    $('#error_ACTION_ID').html('');
    $('#error_code_budget').html('');
    $('#error_Activités').html('');
    $('#error_Resultats_Attendus').html('');
    $('#error_Unite').html('');
    $('#error_Cout_unitaire').html('');
    $('#error_Quantite1').html('');
    $('#error_Quantite2').html('');
    $('#error_Quantite3').html('');
    $('#error_Quantite4').html('');
    $('#error_T1').html('');
    $('#error_T2').html('');
    $('#error_T3').html('');
    $('#error_T4').html('');
    $('#error_Institution_Grande_Masse').html('');
    $('#error_Grande_Masse_Budget_Programme').html('');
    $('#error_Grande_Masse_Budget_Moyen_1').html('');
    $('#error_Masse_Budget_Moyen').html('');
    $('#error_Responsable').html('');

    $('#error_ARTICLE_ECONOMIQUE').html('');
    $('#error_INTITULE_ARTICLE_ECONOMIQUE').html('');

    $('#error_NATURE_ECONOMIQUE').html('');
    $('#error_INTITULE_NATURE_ECONOMIQUE').html('');

    $('#error_DIVISION_ID').html('');
    $('#error_GROUPE_ID').html('');
    $('#error_CLASSE_ID').html('');

    var statut = 2;

    if ($("#FRAIS_NON").prop("checked"))
    {
      if (MOTIF_ACTIVITE_ID=="")
      {
        $('#error_MOTIF_ACTIVITE_ID').html('<?=lang('messages_lang.error_sms')?>');
        statut = 1;
      }else{

        if(MOTIF_ACTIVITE_ID == 2 || MOTIF_ACTIVITE_ID == 3)
        {
          if(NOM =="")
          {
            $('#error_NOM').html('<?=lang('messages_lang.error_sms')?>');
            statut = 1;
          }

          if(PRENOM =="")
          {
            $('#error_PRENOM').html('<?=lang('messages_lang.error_sms')?>');
            statut = 1;
          }

          if(POSTE =="")
          {
            $('#error_POSTE').html('<?=lang('messages_lang.error_sms')?>');
            statut = 1;
          }
        }
      }
    }


    if(SOUS_TUTEL_ID  == '')
    {
      $('#error_DESCRIPTION_SOUS_TUTEL').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;

    }

    if(PROGRAMME_ID  == '')
    {
      $('#error_INTITULE_PROGRAMME').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(ACTION_ID == '')
    {
      $('#error_ACTION_ID').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(CODE_NOMENCLATURE_BUDGETAIRE == '')
    {
      $('#error_code_budget').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(ACTIVITES == '')
    {
      $('#error_Activités').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(RESULTATS_ATTENDUS == '')
    {
      $('#error_Resultats_Attendus').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    
    if(ARTICLE_ECONOMIQUE == '')
    {
      $('#error_ARTICLE_ECONOMIQUE').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(INTITULE_ARTICLE_ECONOMIQUE == '')
    {
      $('#error_INTITULE_ARTICLE_ECONOMIQUE').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(NATURE_ECONOMIQUE == '')
    {
      $('#error_NATURE_ECONOMIQUE').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(INTITULE_NATURE_ECONOMIQUE == '')
    {
      $('#error_INTITULE_NATURE_ECONOMIQUE').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }


    if(DIVISION_ID == '')
    {
      $('#error_DIVISION_ID').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(GROUPE_ID == '')
    {
      $('#error_GROUPE_ID').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(CLASSE_ID == '')
    {
      $('#error_CLASSE_ID').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }

    if(UNITE == '')
    {
      $('#error_Unite').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(COUT_UNITAIRE_BIF == '')
    {
      $('#error_Cout_unitaire').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(QT1 == '')
    {
      $('#error_Quantite1').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(QT2 == '')
    {
      $('#error_Quantite2').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(QT3 == '')
    {
      $('#error_Quantite3').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(QT4 == '')
    {
      $('#error_Quantite4').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(T1 == '')
    {
      $('#error_T1').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(T2 == '')
    {
      $('#error_T2').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(T3 == '')
    {
      $('#error_T3').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(T4 == '')
    {
      $('#error_T4').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(INTITULE_DES_GRANDES_MASSES == '')
    {
      $('#error_Institution_Grande_Masse').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;
    }
    if(GRANDE_MASSE_BP == '')
    {
      $('#error_Grande_Masse_Budget_Programme').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;

    }
    if(GRANDE_MASSE_BM1 == '')
    {
      $('#error_Grande_Masse_Budget_Moyen_1').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;

    }
    if(GRANDE_MASSE_BM == '')
    {
      $('#error_Masse_Budget_Moyen').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;

    }
    if(RESPONSABLE == '')
    {
      $('#error_Responsable').html('<?=lang('messages_lang.error_sms')?>');
      statut = 1;

    }

    if(statut == 2)
    {
      document.getElementById("my_form").submit();

    }
  }


</script>


<script type="text/javascript">
  function get_groupes()
  {

    var DIVISION_ID=$('#DIVISION_ID').val();
    $('#GROUPE_ID').html('<option value =""><?=lang('messages_lang.selection_message')?></option>');
    $('#CLASSE_ID').html('<option value =""><?=lang('messages_lang.selection_message')?></option>');


    $.post('<?=base_url('ptba/Ptba_contr/get_groupes')?>',
    {
      DIVISION_ID : DIVISION_ID,
    },
    function(data)
    {
      $('#GROUPE_ID').html(data.div);
      GROUPE_ID.InnerHtml=data.div;
    })
  }

  function get_classes()
  {
    var GROUPE_ID=$('#GROUPE_ID').val();
    $('#CLASSE_ID').html('<option value =""><?=lang('messages_lang.selection_message')?></option>');


    $.post('<?=base_url('ptba/Ptba_contr/get_classes')?>',
    {
      GROUPE_ID:GROUPE_ID
    },
    function(data)
    {
      $('#CLASSE_ID').html(data.classes);
      CLASSE_ID.InnerHtml=data.classes;
    })
  }

</script>

<script type="text/javascript">


  function show() {    
    if($("#FRAIS_OUI").prop("checked")) {   

      $('#error_MOTIF_ACTIVITE_ID').html('');
      $('#MOTIF_ACTIVITE_ID').val('');
      $("#rep").css("display", "none");    
    } 
    if($("#FRAIS_NON").prop("checked")) {   

      $('#error_MOTIF_ACTIVITE_ID').html('');
      $('#MOTIF_ACTIVITE_ID').val('');
      $("#rep").css("display", "block");    
    }   

  }


  function hierarchie() {    
    
    var MOTIF_ACTIVITE_ID = $('#MOTIF_ACTIVITE_ID').val();

    if (MOTIF_ACTIVITE_ID == 2 || MOTIF_ACTIVITE_ID == 3)
    {
      $('#NOM').val('');
      $('#PRENOM').val('');
      $('#POSTE').val('');

      $('#error_NOM').html('');
      $('#error_PRENOM').html('');
      $('#error_POSTE').html('');
      $('#respo').show();  
    }else {

      $('#NOM').val('');
      $('#PRENOM').val('');
      $('#POSTE').val('');

      $('#error_NOM').html('');
      $('#error_PRENOM').html('');
      $('#error_POSTE').html(''); 
      $('#respo').hide();
    }   
    

  }     
</script>