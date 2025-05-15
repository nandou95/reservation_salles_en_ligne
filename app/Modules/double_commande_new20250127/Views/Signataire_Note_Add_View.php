<!DOCTYPE html>
<html lang="en">

<head>
  <?php echo view('includesbackend/header.php'); ?>
  <?php $validation = \Config\Services::validation(); ?>
</head>

<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="row col-md-12">
                    <div class="col-md-9">
                      <h3> Ajout des signataires sur la note</h3>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?php echo base_url('double_commande_new/Signataire_Note/liste') ?>" style="float: right;margin-right: 20px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?= lang('messages_lang.list_transmission_du_bordereau') ?> </a>
                    </div>

                  </div>
                </div>
                <hr>
                <div class="card-body">

                  <form id="my_form" action="<?= base_url('double_commande_new/Signataire_Note/save') ?>" method="POST" enctype="multipart/form-data">
                    <div class="row col-md-12">
                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.table_institution') ?> <span style="color: red;">*</span></label>
                        <select class="form-control select2" name="INSTITUTION_ID" id="INSTITUTION_ID" onchange="getSousTutel()">
                          <option value=""><?= lang('messages_lang.selectionner_transmission_du_bordereau') ?></option>
                          <?php
                            foreach ($institution as $value)
                            {
                              if (!empty($bind_data))
                              {
                                if($value->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                                {
                                  echo '<option value="'.$value->INSTITUTION_ID.'" selected>'.$value->CODE_INSTITUTION.' '.$value->DESCRIPTION_INSTITUTION.'</option>';                                  
                                }
                                else
                                {
                                  if($value->INSTITUTION_ID==$bind_data[0]->INSTITUTION_ID_TEMPO)
                                  {
                                    echo '<option value="'.$value->INSTITUTION_ID.'" selected>'.$value->CODE_INSTITUTION.' '.$value->DESCRIPTION_INSTITUTION.'</option>';
                                  }
                                }                                
                              }
                              else
                              {
                                if($value->INSTITUTION_ID==set_value('INSTITUTION_ID'))
                                {
                                  echo '<option value="'.$value->INSTITUTION_ID.'" selected>'.$value->CODE_INSTITUTION.' '.$value->DESCRIPTION_INSTITUTION.'</option>';                                  
                                }
                                else
                                {
                                  echo '<option value="'.$value->INSTITUTION_ID.'">'.$value->CODE_INSTITUTION.' '.$value->DESCRIPTION_INSTITUTION.'</option>';
                                }
                              }                              
                            }
                          ?>
                        </select>
                        <span class="text-danger" id="error_INSTITUTION_ID"></span>
                          <?= $validation->getError('INSTITUTION_ID'); ?>
                      </div>

                      <div class="col-md-6">
                        <br>
                        <div class="">
                          <label for=""> <?= lang('messages_lang.table_st') ?><span id="loading_sous_tutel"></span></label>
                          <select class="form-control select2" id="SOUS_TUTEL_ID" name="SOUS_TUTEL_ID">
                            <option></option>
                            <?php
                            if (!empty($bind_data[0]->SOUS_TUTEL_ID_TEMPO))
                            {
                              echo '<option selected>'.$bind_data[0]->DESCRIPTION_SOUS_TUTEL.'</option>';
                            }
                            ?>
                          </select>
                          <span class="text-danger" id="error_SOUS_TUTEL_ID"></span>
                          <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                        </div>
                      </div>

                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.poste') ?><span style="color: red;">*</span></label>
                        <textarea class="form-control" name="DESC_POSTE" id="DESC_POSTE" value=""></textarea>
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('DESC_POSTE'); ?>
                        <?php endif ?>
                        <span id="error_DESC_POSTE" class="text-danger"></span>
                      </div>

                      <div class="col-md-6">
                        <br>
                        <label><?= lang('messages_lang.labelle_nom') ?><span style="color: red;">*</span></label>
                        <input type="text" class="form-control" name="NOM_PRENOM" id="NOM_PRENOM">
                        <?php if (isset($validation)) : ?>
                          <?= $validation->getError('NOM_PRENOM'); ?>
                        <?php endif ?>
                        <span id="error_NOM_PRENOM" class="text-danger"></span>
                      </div>
                  </div>
                    </form>
                    <div class="card-footer">
                      <div style="float:right;">
                        <a onclick="save();" class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_ajouter') ?></a>
                      </div>
                    </div>
                    <div class="col-md-12 table table-responsive" id="CART_FILE"><?=$html?></div>
                    <div class="card-footer" id="btn_save" hidden="true">
                      <div style="float:right;">
                        <a onclick="save_etap2();" class="btn" style="float:right;background:#061e69;color:white"><?= lang('messages_lang.bouton_enregistrer') ?></a>
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
    <?php echo view('includesbackend/scripts_js.php'); ?>

    <div class="modal fade" id="prep_projet" data-backdrop="static" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-body">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel"><?= lang('messages_lang.confirmation_modal_transmission_du_bordereau') ?></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="table-responsive  mt-3">
              <table class="table m-b-0 m-t-20">
                <tbody>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.table_institution') ?></strong></td>
                    <td id="INSTITUTION_ID_VERIFY" class="text-dark"></td>
                  </tr>

                  <tr id='st' hidden="true">
                    <td style="width:350px;"><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.table_st') ?></strong></td>
                    <td id="SOUS_TUTEL_ID_VERIFY" class="text-dark"></td>
                  </tr>

                  <tr>
                    <td style="width:350px;"><i class="fa fa-cogs"></i> &nbsp;<strong><?= lang('messages_lang.poste') ?></strong></td>
                    <td id="DESC_POSTE_VERIFY" class="text-dark"></td>
                  </tr>
                  <tr>
                    <td style="width:350px;"><i class="fa fa-calendar"></i> &nbsp;<strong><?= lang('messages_lang.labelle_nom') ?></strong></td>
                    <td id="NOM_PRENOM_VERIFY" class="text-dark"></td>
                  </tr>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="modal-footer">
          <div class="row">
            <button id="mod" type="button" class="btn btn-primary" style="margin-top:10px" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?= lang('messages_lang.modifier_transmission_du_bordereau') ?></button>
            <a id="myElement" onclick="save_tempo();hideButton()" style="float: right; margin-top:10px" class="btn btn-info"><i class="fa fa-check" aria-hidden="true"></i> <?= lang('messages_lang.confirmer_transmission_du_bordereau') ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>

</html>

<script>
  $(document).ready(function()
  {
    if(<?=($nbr_cart)?>>0)
    {
      $('#INSTITUTION_ID').attr('disabled',true);
      $('#SOUS_TUTEL_ID').attr('disabled',true);
      $('#btn_save').attr('hidden',false);
    }
  });
  function getSousTutel()
  {
    var INSTITUTION_ID=$('#INSTITUTION_ID').val();
    
    $.ajax(
    {
      url : "<?=base_url('/double_commande_new/Signataire_Note/getSousTutel')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:
      {
        INSTITUTION_ID:INSTITUTION_ID
      },
      beforeSend:function() {
        $('#loading_sous_tutel').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      },
      success:function(data)
      {   
        $('#SOUS_TUTEL_ID').html(data.tutel);
        $('#loading_sous_tutel').html("");
      }
    });
  }

  // function hideButton()
  // {
  //   var element = document.getElementById("myElement");
  //   element.style.display = "none";

  //   var elementmod = document.getElementById("mod");
  //   elementmod.style.display = "none";
  // }
</script>

<script type="text/javascript">
  function save() 
  {
    var statut = 2;

    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var DESC_POSTE = $('#DESC_POSTE').val();
    var NOM_PRENOM = $('#NOM_PRENOM').val();

    $('#error_INSTITUTION_ID').html('');
    $('#error_DESC_POSTE').html('');
    $('#error_NOM_PRENOM').html('');


    if (INSTITUTION_ID == '') {
      statut = 1;
      $('#error_INSTITUTION_ID').html("<?= lang('messages_lang.champ_obligatoire_transmission_du_bordereau') ?>");
    } else {
      $('#error_INSTITUTION_ID').html("");
    }


    if (DESC_POSTE == '') {
      $('#error_DESC_POSTE').html('<?= lang('messages_lang.champ_obligatoire_transmission_du_bordereau') ?>');
      statut = 1;
    }

    if (NOM_PRENOM == '') {
      $('#error_NOM_PRENOM').html('<?= lang('messages_lang.champ_obligatoire_transmission_du_bordereau') ?>');
      statut = 1;
    }

    if (statut == 2) 
    {
      var INSTITUTION_ID_VERIFY=$('#INSTITUTION_ID option:selected').text();
      $('#INSTITUTION_ID_VERIFY').html(INSTITUTION_ID_VERIFY)

      if ($('#SOUS_TUTEL_ID').val()!='')
      {
        $('#st').attr('hidden',false)
        var SOUS_TUTEL_ID_VERIFY=$('#SOUS_TUTEL_ID option:selected').text();
        $('#SOUS_TUTEL_ID_VERIFY').html(SOUS_TUTEL_ID_VERIFY)
      }
      $('#DESC_POSTE_VERIFY').html(DESC_POSTE)
      $('#NOM_PRENOM_VERIFY').html(NOM_PRENOM)
      
      $('#prep_projet').modal('show')
    }
  }

  function save_tempo()
  {
    var INSTITUTION_ID = $('#INSTITUTION_ID').val();
    var DESC_POSTE = $('#DESC_POSTE').val();
    var NOM_PRENOM = $('#NOM_PRENOM').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();

    var url = "<?=base_url()?>/double_commande_new/Signataire_Note/save_tempo";
    $.ajax({
      url:url,
      type:"POST",
      data:{
        INSTITUTION_ID:INSTITUTION_ID,
        DESC_POSTE:DESC_POSTE,
        NOM_PRENOM:NOM_PRENOM,
        SOUS_TUTEL_ID:SOUS_TUTEL_ID
      },
      // beforeSend:function() 
      // {
      //   $('#loading_sous_tutel').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
      // },
      success: function(data)
      {
        $('#prep_projet').modal('hide')
        $('#CART_FILE').html(data.cart);
        CART_FILE.innerHTML=data.cart;
        if (data.nbr>0)
        {
          $('#INSTITUTION_ID').attr('disabled',true);
          $('#SOUS_TUTEL_ID').attr('disabled',true);
          $('#btn_save').attr('hidden',false);
        }
        else
        {
          $('#INSTITUTION_ID').attr('disabled',false);
          $('#SOUS_TUTEL_ID').attr('disabled',false);
          $('#btn_save').attr('hidden',true);
        }
      }
    });
  }
</script>

<script type="text/javascript">
  function save_etap2()
  {
    $('#my_form').submit()
  }
  //Fonction pour supprimer dans la table tempo
  function remove_cart()
  {
    var id=$('#del_id').val();
    var rowid=$('#rowid'+id).val();

    $.post('<?=base_url('double_commande_new/Signataire_Note/delete')?>',
    {
      rowid:rowid,
      id:id

    },function(data)
    {
      if (data) 
      {
        
        if(data.nbr>0)
        {
          $('#CART_FILE').html('');
          $('#INSTITUTION_ID').attr('disabled',true);
          $('#SOUS_TUTEL_ID').attr('disabled',true);
          $('#btn_save').attr('hidden',false);
        }
        else
        {
          $('#CART_FILE').html(data.cart);
          CART_FILE.innerHTML=data.cart;
          $('#INSTITUTION_ID').attr('disabled',false);
          $('#SOUS_TUTEL_ID').attr('disabled',false);
          $('#btn_save').attr('hidden',true);
        }

        $('#mydelete').modal('hide');

      }
      else
      {
        $('#SHOW_FOOTER').show();
      }
    })
  }

  function show_modal(id)
  {
    var DEL_CIBLE=$('#DEL_CIBLE'+id).html();
    $('#CIBLES').html(DEL_CIBLE);
    $('#del_id').val(id);
    $('#mydelete').modal('show');
  }
</script>
<!--******************* Modal pour supprimer dans le cart ***********************-->
<div class="modal fade" id="mydelete" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-body">
        <center>
          <h5><strong><?=lang('messages_lang.message_suppression')?></strong><br><b style="background-color:prink;color:green;" id="CIBLES"></b>
          </h5>
        </center>
      </div>
      <div class="modal-footer">
        <input type="hidden" name="del_id" id="del_id" >
        <button class="btn btn-primary btn-md" data-dismiss="modal" style="background-color: #a80;">
          <?=lang('messages_lang.quiter_action')?>
        </button>
        <a href="javascript:void(0)" class="btn btn-danger btn-md" onclick="remove_cart()"><?=lang('messages_lang.supprimer_action')?></a>
      </div>
    </div>
  </div>
</div>