<!DOCTYPE html>
<html lang="en">

<head>
 <?php echo view('includesbackend/header.php'); ?>
 <?php $validation = \Config\Services::validation();
 $session  = \Config\Services::session();
 $user_id = $session->get('SESSION_SUIVIE_PTBA_USER_ID');

 if (empty($user_id)) {
  return redirect('Login_Ptba');
 }
 ?>
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

<body>
 <div class="wrapper">
  <?php echo view('includesbackend/navybar_menu.php'); ?>

  <div class="main">
   <?php echo view('includesbackend/navybar_topbar.php'); ?>
   <main class="content">

    <div class="container-fluid">

     <div class="row" style="margin-top: -5px">
      <div class="col-12">
       <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
        <div class="card-header">

        </div>

        <div class="card-body">

         <?php
         if (session()->getFlashKeys('alert')) {
          ?>
          <div class="alert alert-danger" id="message">
           <?php echo session()->getFlashdata('alert')['message']; ?>
          </div>
          <?php
         }
         ?>
         <div style="margin-top: -25px;" class="card">
         </div>
         <div class="card-body" style="margin-top: -20px">

          <div style="float: right;">
           <a href="<?php echo base_url('double_commande_new/Liste_Trans_Deja_Fait_PC') ?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i>
            <?= lang('messages_lang.list_phase_comptable_prise_en_charge') ?>
           </a>
          </div>
          <div>
           <h4>
            <font style="font-size:18px">
             <?= lang('messages_lang.phase_comptable_phase_comptable_prise_en_charge') ?> : <?php if (!empty($etapes)) { ?>
              <?= $etapes['DESC_ETAPE_DOUBLE_COMMANDE'] ?>
             <?php    } ?>

            </font>
           </h4>
          </div>
          <hr>
          <form action="<?= base_url('double_commande_new/Phase_comptable/save_prise_en_charge') ?>" id="MyFormData" method="post" enctype="multipart/form-data">
           <input type="hidden" name="etape_en_cour_id" value="<?= $id_detail ?>">
           <input type="hidden" name="detail_id" value="<?= $detail_id ?>">
           <input type="hidden" name="titr_dec_id" value="<?= $titr_dec_id ?>">

           <input type="hidden" id="DATE_TRANSMISSION" name="date_insertion_check" value="<?= $date_trans['DATE_TRANSMISSION'] ?>">

           <input type="hidden" id="type_bene" name="type_bene" value="<?=$infosup['TYPE_BENEFICIAIRE_ID'] ?>">

           <input type="hidden" name="BORDEREAU_TRANSMISSION_ID" value="<?= $numero_bordereau_trans_data["BORDEREAU_TRANSMISSION_ID"] ?>">

           <div class="col-md-12 mb-3" style="border-radius:10px">
            <div class="row mt-3">
             <div class="col-md-6">
              <label for=""> <?= lang('messages_lang.numero_de_burdereau_phase_comptable_prise_en_charge') ?> </label>
              <div class="">
               <input type="search" class="form-control" placeholder="Numero du borderaux de transmission" value="<?= $numero_bordereau_trans_data['NUMERO_BORDEREAU_TRANSMISSION'] ?>" disabled>
              </div>
             </div>

             <div class="col-md-6" style="display: none;">
              <label> <?= lang('messages_lang.Bon_engagement') ?> </label>
              <select name="bon_engagement[]" class="form-control select2" id="bon_engagement" multiple>
               <?php  foreach ($bon_engagements as $keys) { ?>
                  <option value="<?=$keys->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID?>" selected>
                    <?=$keys->NUMERO_DOCUMENT.' ('. lang("messages_lang.labelle_montant").' = '. $ord = ($keys->DEVISE_TYPE_ID == 1) ? number_format($keys->MONTANT_ORDONNANCEMENT,2,',',' ') : number_format($keys->MONTANT_ORDONNANCEMENT_DEVISE,2,',',' ') ; ?>)</option>
                  
                  <?php }?>
                </select>
             </div>
             <div class="col-md-6">
              <label> <?= lang('messages_lang.Bon_engagement') ?> </label>
              <select name="bon_engagement1[]" class="form-control select2" id="bon_engagement1" multiple disabled>
               <?php  foreach ($bon_engagements as $keys) { ?>
                  <option value="<?=$keys->EXECUTION_BUDGETAIRE_TITRE_DECAISSEMENT_ID ?>" selected>
                    <?=$keys->NUMERO_DOCUMENT.' ('. lang("messages_lang.labelle_montant").' = '. $ord = ($keys->DEVISE_TYPE_ID == 1) ? number_format($keys->MONTANT_ORDONNANCEMENT,2,',',' ') : number_format($keys->MONTANT_ORDONNANCEMENT_DEVISE,2,',',' ') ; ?>)</option>
                  <?php }?>
                </select>
             </div>
             <div class="col-md-6">
              <br>
              <div class="">
               <label for=""><?= lang('messages_lang.date_reception_phase_comptable_prise_en_charge') ?> (Ordonnateur) <font color="red"> * 
               </font>
              </label>
              <input type="date" name="date_reception" value="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d', strtotime($date_trans['DATE_TRANSMISSION'])) ?>" max="<?= date('Y-m-d') ?>" onkeypress="return false" onblur="this.type='date'" onchange="get_min_trans(this.value)" id="date_reception_id" class="form-control">
              <font color="red" id="date_reception_error"></font>
             </div>
            </div>

            <div class="col-md-6">
             <br>
             <div class="">
              <label for=""><?= lang('messages_lang.label_date_transmission_') ?> <?= $infosup['TYPE_BENEFICIAIRE_ID'] == 1 ? '(OBR)' : '(Prise en charge)' ?><font color="red"> *</font>
              </label>
              <input type="date" value="<?= date('Y-m-d') ?>" name="date_transmission" onkeypress="return false" max="<?= date('Y-m-d') ?>" min="<?= date('Y-m-d') ?>" id="date_transmission_id" class="form-control">
              <font color="red" id="date_transmission_error">
              </font>
             </div>
            </div>
           </div>
          </div>

          <div style="float:right">
           <a id="valid_btn" class="btn btn-primary" onclick="save_dossier();" class="form-control"><?= lang('messages_lang.enregistre_phase_comptable_prise_en_charge') ?></a>
          </div>
         </form>
        </div>
       </div>
      </div>
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

<!-- ############################ Modal ############################## -->
<div class="modal fade" id="detail" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
 <div class="modal-dialog modal-lg" role="document">
  <div class="modal-content">
   <div class="modal-header">
    <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.confirmation_modal_phase_comptable_prise_en_charge') ?></h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
     <span aria-hidden="true">&times;</span>
    </button>
   </div>
   <div class="modal-body">
    <div class="table-responsive overflow-auto mt-2">
     <table class=" table  m-b-0 m-t-20">
      <tbody>

       <tr>
        <td> <i class="fa fa-file-text"></i>
         <?= lang('messages_lang.numero_de_burdereau_phase_comptable_prise_en_charge') ?>
        </td>
        <td id="numero_modal"> <?= $numero_bordereau_trans_data['NUMERO_BORDEREAU_TRANSMISSION'] ?> </td>
       </tr>
      </tr>

      <tr>
       <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.date_reception_phase_comptable_prise_en_charge') ?></td>
       <td id="date_reception_id_modal"></td>
      </tr>
     </tr>

     <tr>
      <td> <i class="fa fa-calendar"></i> <?= lang('messages_lang.date_transmission_phase_comptable_prise_en_charge') ?></td>
      <td id="date_transmission_id_modal"></td>
     </tr>

     <tr>
      <td> <i class="fa fa-sticky-note"></i> <?= lang('messages_lang.numero_bon_engagement_phase_comptable_prise_en_charge') ?></td>
      <td id="name_bon_angagement_modal"></td>
     </tr>
    </tbody>
   </table>
  </div>
 </div>
 <div class="modal-footer">
  <button id="mod" type="button" class="btn btn-secondary" style="border-radius: .2rem;" data-dismiss="modal"> <i class=" fa fa-edit"></i>
   <?= lang('messages_lang.modifier_phase_comptable_prise_en_charge') ?></button>
   <button onclick="save_info();hideButton()" id="myElement" type="button" class="btn btn-info"><i class="fa fa-check"></i>
    <?= lang('messages_lang.confirmer_phase_comptable_prise_en_charge') ?></button>
   </div>
  </div>
 </div>
</div>
<?php echo view('includesbackend/scripts_js.php'); ?>

</body>

</html>

<script>
  function hideButton()
  {
    var element = document.getElementById("myElement");
    element.style.display = "none";

    var elementmod = document.getElementById("mod");
    elementmod.style.display = "none";
  }
</script>

<!-- Save dossier -->
<script>

  function save_dossier() {

    var statut = true;
    var date_reception_id = $('#date_reception_id').val();
    var date_transmission_id = $('#date_transmission_id').val();
    var num_bordereau = $('#num_bordereau').val();

    if (date_reception_id == "") {
     statut = false;
     $("#date_reception_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
    } else {

     $("#date_reception_error").html("");
    }

    if (date_transmission_id == "") {
     statut = false;
     $("#date_transmission_error").html("<?= lang('messages_lang.champ_obligatoire_phase_comptable_prise_en_charge') ?>");
    } else {
     $("#date_transmission_error").html("");
    }


    if (statut == true) {

      var date = moment(date_reception_id, "YYYY/mm/DD")
      var reception_date = date.format('DD/mm/YYYY')
      var date1 = moment(date_transmission_id, "YYYY/mm/DD")
      var transmission_date = date1.format('DD/mm/YYYY')

      let ALL_ANALYSE = $("#bon_engagement option:selected").toArray().map(items => `<li> ${items.text} </li>`).join('');
      $("#name_bon_angagement_modal").html(`<ul> ${ALL_ANALYSE} </ul>`)
      
      $('#date_reception_id_modal').html(reception_date);
      $('#date_transmission_id_modal').html(transmission_date);
      $('#numero_modal').html(num_bordereau);
      $('#detail').modal('show')

    }
  }
</script>

<script type="text/javascript">
function DoPrevent(e) {
 e.preventDefault();
 e.stopPropagation();
}

function get_min_trans() {
 $("#date_transmission_id").prop('min', $("#date_reception_id").val());
}

$('#message').delay('slow').fadeOut(3000);

function save_info() {
 $('#MyFormData').submit()
}

</script>