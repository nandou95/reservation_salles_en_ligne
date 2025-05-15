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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js"></script>
    <script src="/DataTables/datatables.js"></script>
    <script src="<?= base_url('template/js') ?>/sweetalert2.all.min.js"></script>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php'); ?>
      <main class="content">
        <div class="container-fluid">
          <div class="header">
            <h1 class="header-title text-white"></h1>
          </div>
          <div class="row">
            <div class="col-12">
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px" class="card">
                <div class="card-header">
                  <div class="col-md-12 d-flex">
                    <div class="col-md-9" style="float: left;">
                      <h1 class="header-title text-black"><?=lang('messages_lang.add_user')?></h1>
                    </div>
                    <div class="col-md-3" style="float: right;">
                      <a href="<?=base_url('Administration/Gestion_Utilisateurs')?>" style="float: right;margin-right: 80px;margin-top:15px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i> <?=lang('messages_lang.link_list')?></a>
                    </div>
                  </div>
                </div>
                <div class="card-body">
                  <form action="<?=base_url('Administration/Gestion_Utilisateurs/insert')?>" id="Myform" method="POST" enctype="multipart/form-data">
                    <br>
                    <ul class="nav nav-tabs" role="tablist">
                      <li class="nav-item">
                        <a class="nav-link " id="tab1" data-toggle="tab" href="#"><i class="fa fa-user" aria-hidden="true"></i>
                        <?=lang('messages_lang.tab_infos')?></a>
                      </li>

                      <li class="nav-item">
                        <a class="nav-link" id="tab2" data-toggle="tab" href="#"><i class="fa fa-info" aria-hidden="true"></i>
                        <?=lang('messages_lang.labelle_institution')?></a>
                      </li>
                    </ul>
                    <div class="tab-content">
                      <div id="info" class="container tab-pane fade show active">
                       <div class="table-responsive container " style="margin-top:50px">
                        <div class="card-body">
                          <div class="row">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label for=""><?=lang('messages_lang.labelle_nom')?><span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="NOM" name="NOM" value="<?=set_value('NOM')?>" autofocus onpaste="return false" autocomplete="off">
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('NOM'); ?>
                                <?php endif ?>
                                <span class="text-danger" id="error_NOM"></span>
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="form-group">
                                <label for=""><?=lang('messages_lang.labelle_prenom')?><span style="color: red;">*</span></label>
                                <input type="text" class="form-control" id="PRENOM" name="PRENOM" value="<?=set_value('PRENOM')?>" onpaste="return false" autocomplete="off">
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('PRENOM'); ?>
                                <?php endif ?>
                                <span class="text-danger" id="error_PRENOM"></span>
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="form-group">
                                <label><?=lang('messages_lang.labelle_email')?><span style="color: red;">*</span></label>
                                <input type="text" class="form-control" name="EMAIL" id="EMAIL" value="<?=set_value('EMAIL')?>" onpaste="return false" autocomplete="off">
                                <span id="error_EMAIL" class="text-danger"></span>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('EMAIL'); ?>
                                <?php endif ?>
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="form-group">
                                <label><?=lang('messages_lang.label_mot_de_passe')?><span style="color: red;">*</span></label>
                                <input type="text" class="form-control" name="PASSWORD" id="PASSWORD" value="<?=set_value('PASSWORD')?>" onpaste="return false" autocomplete="off">
                                <span id="error_PASSWORD" class="text-danger"></span>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('PASSWORD'); ?>
                                <?php endif ?>
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="form-group">
                                <label><?=lang('messages_lang.labelle_phone1')?><span style="color: red;">*</span></label>
                                <input type="text" class="form-control" name="TELEPHONE1" id="TELEPHONE1" value="<?=set_value('TELEPHONE1')?>" onpaste="return false" autocomplete="off">
                                <span id="error_TELEPHONE1" class="text-danger"></span>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('TELEPHONE1'); ?>
                                <?php endif ?>
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="form-group">
                                <label><?=lang('messages_lang.labelle_phone2')?></label>
                                <input type="text" class="form-control" name="TELEPHONE2" id="TELEPHONE2" value="<?=set_value('TELEPHONE2')?>" onpaste="return false" autocomplete="off">
                                <span id="error_TELEPHONE2" class="text-danger"></span>
                              </div>
                            </div>

                            <div class="col-md-6">
                              <div class="form-group">
                                <label><?=lang('messages_lang.labelle_et_mod_prof')?><span style="color: red;">*</span></label>
                                <select class="form-control" name="PROFIL_ID" id="PROFIL_ID" onchange="get_visualition()">
                                  <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>
                                  <?php
                                  foreach($profil as $value)
                                  { 
                                        
                                    if($value->PROFIL_ID==set_value('PROFIL_ID'))
                                    {
                                      ?>
                                      <option value="<?=$value->PROFIL_ID ?>" selected><?=$value->PROFIL_DESCR?></option>
                                      <?php  
                                    }
                                    else
                                    {
                                      ?>
                                      <option value="<?=$value->PROFIL_ID ?>"><?=$value->PROFIL_DESCR?></option>
                                      <?php
                                    }
                                  }
                                  ?>
                                </select>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('PROFIL_ID'); ?>
                                <?php endif ?>
                                <span id="error_PROFIL_ID" class="text-danger"></span>
                              </div>
                            </div>

                            <div class="col-md-8"></div>
                            <div class="col-md-4" >
                              <button type="button"  style="float:right;" class="btn btn-primary float-end envoi" id="btnSave"  onclick="save_tab1()"><?=lang('messages_lang.btn_suivant')?>&nbsp;<i class="fa fa-arrow-right" aria-hidden="true"></i><span id="loading_tab1"></span></button>
                            </div>
                          </div>
                        </div>  
                      </div>
                    </div>

                    <div id="instit" class="container tab-pane fade ">
                      <div class="table-responsive container " style="margin-top:50px">
                        <div class="card-body">
                          <input type="hidden" name="NIVEAU_VISUALISATION_ID" id="NIVEAU_VISUALISATION_ID">
                          <div class="row">
                            <input type="hidden" name="NOM_1" id="NOM_1">
                            <input type="hidden" name="PRENOM_1" id="PRENOM_1">
                            <input type="hidden" name="TELEPHONE1_1" id="TELEPHONE1_1">
                            <input type="hidden" name="TELEPHONE2_1" id="TELEPHONE2_1">
                            <input type="hidden" name="PROFIL_ID_1" id="PROFIL_ID_1">
                            <input type="hidden" name="EMAIL_1" id="EMAIL_1">
                            <input type="hidden" name="PASSWORD_1" id="PASSWORD_1">
                            <div class="col-md-6">
                              <div class="form-group">
                                <label><?=lang('messages_lang.labelle_inst_min')?><span style="color: red;">*</span><span id="loading_inst"></span></label>
                                <select class="form-control" name="INSTITUTION_ID" id="INSTITUTION_ID"  onchange="get_tutel()">
                                  <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>
                                </select>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('PROFIL_ID'); ?>
                                <?php endif ?>
                                <span id="error_INSTITUTION_ID" class="text-danger"></span>
                              </div>
                            </div>

                            <div class="col-md-6" id="est_st" style="display: none;">
                              <div class="form-group">
                                <label><?=lang('messages_lang.labelle_is_soutut')?><span style="color: red;">*</span></label>
                                <select class="form-control" name="IS_SOUS_TUTEL" id="IS_SOUS_TUTEL" onchange="est_tutel()">
                                  <option value="-1">--<?=lang('messages_lang.labelle_selecte')?>--</option>
                                  <option value="1" <?= set_select('IS_SOUS_TUTEL', '1', (set_value('IS_SOUS_TUTEL') == '1')); ?>><?=lang('messages_lang.label_oui')?></option>
                                  <option value="0" <?= set_select('IS_SOUS_TUTEL', '0', (set_value('IS_SOUS_TUTEL') == '0')); ?>><?=lang('messages_lang.label_non')?></option>
                                </select>
                                <span id="error_SOUS_IS_SOUS_TUTEL" class="text-danger"></span>
                              </div>
                            </div>

                            <div class="col-md-6" id="div_sous_tut" style="display: none;">
                              <div class="form-group">
                                <label id="label_sous_tutel"><?=lang('messages_lang.table_st')?></label>
                                <select class="form-control" name="SOUS_TUTEL_ID" id="SOUS_TUTEL_ID" value="<?=set_value('SOUS_TUTEL_ID')?>">
                                  <option value="">--<?=lang('messages_lang.labelle_selecte')?>--</option>
                                </select>
                                <?php if (isset($validation)) : ?>
                                  <?= $validation->getError('SOUS_TUTEL_ID'); ?>
                                <?php endif ?>
                                <span id="error_SOUS_TUTEL_ID" class="text-danger"></span>
                              </div>
                            </div>

                            <br>
                            <br>
                            <div class="col-md-12">
                              <div  id="bouton_retour" class="col-md-3">
                                <button id="bouton_prev" onclick="retour_tab1()" type="button" class="btn btn-primary float-end" style="float: left;" ><i class="fa fa-arrow-left" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.labelle_et_precedent')?></span></button>
                              </div>
                            
                              <div  id="bouton_cart" class="col-md-12">
                                <button id="bouton_envoyer" onclick="addToCart()" type="button" class="btn btn-primary float-end" style="float: right;" ><i class="fa fa-plus" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.bouton_ajouter')?> <span id="loading_cart"></span></button>
                              </div>
                            </div>
                            <br>
                            <br>
                            <div class="col-md-12 table table-responsive" id="CART_FILE"></div>
                            <br>
                            <br>

                            <div class="col-md-8"></div>
                            <div class="col-md-4"  id="SAVE" style="display: none;">
                              <button type="button"  style="float:right;" class="btn btn-primary float-end envoi" id="btnSave"  onclick="save_user()"> <i class="fa fa-save" aria-hidden="true"></i>&nbsp;<?=lang('messages_lang.bouton_enregistrer')?></button>
                            </div>
                          </div>
                        </div> 
                      </div>

                    </div>
                  </form>
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
</body>
</html>
<script type="text/javascript">
  $(document).ready(function()
  {
    $('#info').addClass('container tab-pane active');
    $('#tab1').addClass('nav-link active btn btn-primary');
  });
</script>
<script type="text/javascript">
  function retour_tab1()
  {
    $('#info').show();
    $('#instit').hide();
    $('#info').addClass('show active');
    $('#instit').removeClass('show active');
    $('#tab1').addClass('active btn btn-primary');
    $('#tab2').removeClass('active btn btn-primary');
  }
</script>

<script type="text/javascript">
  function save_tab1()
  {
    var NOM=$('#NOM').val();
    var PRENOM=$('#PRENOM').val();
    var EMAIL=$('#EMAIL').val();
    var PASSWORD=$('#PASSWORD').val();
    var TELEPHONE1=$('#TELEPHONE1').val();
    var TELEPHONE2=$('#TELEPHONE2').val();
    var PROFIL_ID  = $('#PROFIL_ID').val();
    var statut = 2;
    $('#error_NOM,#error_PRENOM,#error_EMAIL,#error_TELEPHONE1,#error_PROFIL_ID').html('');

    if (NOM=='') 
    {
      $('#error_NOM').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
      statut= 1;
    }

    if (PRENOM=='') 
    {
      $('#error_PRENOM').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
      statut= 1;
    }
    
    if (EMAIL=='') 
    {
      $('#error_EMAIL').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
      statut= 1;
    }

    if (PASSWORD=='') 
    {
      $('#error_PASSWORD').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
      statut= 1;
    }

    if (TELEPHONE1=='') 
    {
      $('#error_TELEPHONE1').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
      statut= 1;
    }
    
    if (PROFIL_ID=='') 
    {
      $('#error_PROFIL_ID').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
      statut= 1;
    }

    if(statut== 2)
    {
      $.ajax({
        url: '<?=base_url()?>/Administration/Gestion_Utilisateurs/insert_tab1',
        type: "POST",
        dataType: "JSON",
        data: {
          NOM:NOM,
          PRENOM:PRENOM,
          EMAIL:EMAIL,
          PASSWORD:PASSWORD,
          TELEPHONE1:TELEPHONE1,
          TELEPHONE2:TELEPHONE2,
          PROFIL_ID: PROFIL_ID
        },
        beforeSend: function() {
          $('#loading_tab1').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data)
        {
          $('#loading_tab1').html("");

          if(data.status)
          {
            $('#SOUS_TUTEL_ID').html(data.tutel);
            if (data.PROFIL_ID==9 || data.PROFIL_ID==10)
            {
              $('#INSTITUTION_ID').html(data.select_inst);
              $('#est_st').show();
              var IS_SOUS_TUTEL=0;
              $('#IS_SOUS_TUTEL').val(IS_SOUS_TUTEL);
              
              $('#div_sous_tut').hide();
              $('#div_vide').removeClass('col-md-6');
            }
            else if(data.niv == 1)
            {
              $('#INSTITUTION_ID').html(data.select_inst);
              $('#est_st').hide();

              var IS_SOUS_TUTEL=1;
              $('#IS_SOUS_TUTEL').val(IS_SOUS_TUTEL);
              
              $('#div_sous_tut').show();
              $('#div_vide').removeClass('col-md-6');
            }
            else if(data.niv == 2)
            {
              $('#INSTITUTION_ID').html(data.select_inst);
              var IS_SOUS_TUTEL=-1;
              $('#IS_SOUS_TUTEL').val(IS_SOUS_TUTEL);
              
              $('#est_st').show();
              $('#div_sous_tut').hide();
            }
            else if(data.niv == 3)
            {
              $('#INSTITUTION_ID').html(data.select_inst);
              $('#est_st').hide();
              $('#IS_SOUS_TUTEL').html("<option value=''>"+"--<?=lang('messages_lang.labelle_selecte')?>--"+"</option>");
            }
            else if(data.niv == 4)
            {
              $('#INSTITUTION_ID').html(data.select_inst);
              $('#est_st').show();
              $('#div_sous_tut').hide();
              var IS_SOUS_TUTEL=-1;
              $('#IS_SOUS_TUTEL').val(IS_SOUS_TUTEL);
            }

            $('#info').hide();
            $('#instit').show();
            $('#info').removeClass('show active');
            $('#instit').addClass('show active');
            $('#tab1').removeClass('active btn btn-primary');
            $('#tab2').addClass('active btn btn-primary');

            $('#NOM_1').val(data.NOM);
            $('#PRENOM_1').val(data.PRENOM);
            $('#EMAIL_1').val(data.EMAIL);
             $('#PASSWORD_1').val(data.PASSWORD);
            $('#TELEPHONE1_1').val(data.TELEPHONE1);
            $('#TELEPHONE2_1').val(data.TELEPHONE2);
            $('#PROFIL_ID_1').val(data.PROFIL_ID1);
          }
          else
          {
            $('#error_EMAIL').html("<?=lang('messages_lang.email_existe')?>");
          }
        }
      });
    }
  }
</script>
<script type="text/javascript">
  //fonction inserer dans la table tempo
  function addToCart()
  {
    
    var NOM_1=$('#NOM_1').val();
    //alert(NOM);
    var PRENOM_1=$('#PRENOM_1').val();
    var EMAIL_1=$('#EMAIL_1').val();
    var PASSWORD_1=$('#PASSWORD_1').val();
    var TELEPHONE1_1=$('#TELEPHONE1_1').val();
    var TELEPHONE2_1=$('#TELEPHONE2_1').val();
    var PROFIL_ID_1  = $('#PROFIL_ID_1').val();

    var INSTITUTION_ID  = $('#INSTITUTION_ID').val();
    var IS_SOUS_TUTEL  = $('#IS_SOUS_TUTEL').val();
    var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();      
    var NIVEAU_VISUALISATION_ID=$('#NIVEAU_VISUALISATION_ID').val();
    var PROFIL_ID=$('#PROFIL_ID').val();
    var statut = 2;
    
    $('#error_INSTITUTION_ID,#error_SOUS_IS_SOUS_TUTEL,#error_SOUS_TUTEL_ID').html('');

    if(INSTITUTION_ID=='') 
    {
      $('#error_INSTITUTION_ID').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
      statut = 1;
    }

    if(INSTITUTION_ID==12 && PROFIL_ID!=9 && PROFIL_ID!=10)
    {
      document.getElementById("Myform").submit();        
    }

    if(IS_SOUS_TUTEL == '')
    {
      $('#error_SOUS_IS_SOUS_TUTEL').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
      statut = 1;
    }

    if(IS_SOUS_TUTEL==1)
    {
      if(SOUS_TUTEL_ID=='')
      {
        $('#error_SOUS_TUTEL_ID').html("<?=lang('messages_lang.message_champs_obligatoire')?>");
        statut = 1;
      }
    }

    if (statut == 2)
    {
      $.ajax(
      {
        url: '<?=base_url()?>/Administration/Gestion_Utilisateurs/insert_tab_tempo',
        type: "POST",
        dataType: "JSON",
        data:
        {
          NOM_1:NOM_1,
          PRENOM_1:PRENOM_1,
          EMAIL_1:EMAIL_1,
          PASSWORD_1:PASSWORD_1,
          TELEPHONE1_1:TELEPHONE1_1,
          TELEPHONE2_1:TELEPHONE2_1,
          PROFIL_ID_1: PROFIL_ID_1,
          INSTITUTION_ID: INSTITUTION_ID,
          IS_SOUS_TUTEL: IS_SOUS_TUTEL,
          SOUS_TUTEL_ID: SOUS_TUTEL_ID
        },
        beforeSend: function()
        {
          $('#loading_cart').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data)
        {
          $('#loading_cart').html("");
          $('#CART_FILE').html(data.cart);
          CART_FILE.innerHTML=data.cart;
          $('#SHOW_FOOTER').show();
          $('#SAVE').show();
        }
      });
    }
  }

  //Fonction pour supprimer dans la table tempo
  function remove_cart()
  {
    var id=$('#del_id').val();
    var rowid=$('#rowid'+id).val();

    $.post('<?=base_url('Administration/Gestion_Utilisateurs/delete')?>',
    {
      rowid:rowid,
      id:id

    },function(data)
    {
      if (data) 
      {
        
        if(data.cart.trim() === "")
        {
          $('#CART_FILE').html('');
          $('#SHOW_FOOTER').hide();
          $('#SAVE').hide();
        }
        else
        {
          $('#CART_FILE').html(data.cart);
          CART_FILE.innerHTML=data.cart;
          $('#SHOW_FOOTER').show();
          $('#SAVE').show();

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
<script type="text/javascript">
  function est_tutel()
  {
    var IS_SOUS_TUTEL = $('#IS_SOUS_TUTEL').val();

    if(IS_SOUS_TUTEL == 1)
    {
      $('#div_sous_tut').show();
      $('#div_vide').removeClass('col-md-6');
      get_tutel();
    }
    else if(IS_SOUS_TUTEL == 0)
    {
      $('#div_sous_tut').hide();
      $('#div_vide').addClass('col-md-6');
    }
  }
</script>
  <script type="text/javascript">
    function save_user()
    {
      var statut = true;
      var NOM  = $('#NOM').val();
      var PRENOM  = $('#PRENOM').val();
      var EMAIL  = $('#EMAIL').val();
      var TELEPHONE1=$('#TELEPHONE1').val();
      var PROFIL_ID  = $('#PROFIL_ID').val();
      var INSTITUTION_ID  = $('#INSTITUTION_ID').val();
      var IS_SOUS_TUTEL  = $('#IS_SOUS_TUTEL').val();
      var SOUS_TUTEL_ID=$('#SOUS_TUTEL_ID').val();

      if (NOM=='') 
      {
        $('#error_NOM').text("<?=lang('messages_lang.message_champs_obligatoire')?>");
        return false;
      }
      else
      {
        $('#error_NOM').text('');
      }
      if (PRENOM=='') 
      {
        $('#error_PRENOM').text("<?=lang('messages_lang.message_champs_obligatoire')?>");
        return false;
      }else{
        $('#error_PRENOM').text('');
      }
      if (EMAIL=='') 
      {
        $('#error_EMAIL').text("<?=lang('messages_lang.message_champs_obligatoire')?>");
        return false;
      }else{
        $('#error_EMAIL').text('');
      }
      if (TELEPHONE1=='') 
      {
        $('#error_TELEPHONE1').text("<?=lang('messages_lang.message_champs_obligatoire')?>");
        return false;
      }else{
        $('#error_TELEPHONE1').text('');
      }
      
      if(statut==true)
      {
        document.getElementById("Myform").submit();
      }       
    }

    function get_tutel()
    {
      var INSTITUTION_ID=$('#INSTITUTION_ID').val(); 
      var IS_SOUS_TUTEL=$('#IS_SOUS_TUTEL').val();
      if (INSTITUTION_ID!='')
      {
        if(IS_SOUS_TUTEL==1)
        {
          $.ajax(
          {
            url : "<?=base_url('Administration/Gestion_Utilisateurs/get_tutel')?>",
            type : "POST",
            dataType: "JSON",
            cache:false,
            data:
            {
              INSTITUTION_ID:INSTITUTION_ID,
              IS_SOUS_TUTEL:IS_SOUS_TUTEL
            },
            success:function(data)
            {   
              $('#SOUS_TUTEL_ID').html(data.tutel);
            }
          });
          $('#label_sous_tutel').html("<?=lang('messages_lang.table_st')?>"+"<span style='color: red;'>*</span>");
        }
        else
        {
          $('#error_SOUS_TUTEL_ID').html('');
          $('#label_sous_tutel').html("<?=lang('messages_lang.table_st')?>");
          $('#SOUS_TUTEL_ID').html("<option value=''>"+"--<?=lang('messages_lang.labelle_selecte')?>--"+"</option>");
        }
      }
      else
      {
        $('#error_SOUS_TUTEL_ID').html('');
        $('#label_sous_tutel').html("<?=lang('messages_lang.table_st')?>");
        $('#SOUS_TUTEL_ID').html("<option value=''>"+"--<?=lang('messages_lang.labelle_selecte')?>--"+"</option>");
      }
    }

    function DoPrevent(e)
    {
      e.preventDefault();
      e.stopPropagation();
    }

    //pour tester la validité du telephone
    function isValid(str) {
      return !/[~`!@#$%\^&*()+=\-\[\]\\';,/{}|\\":<>\?]/g.test(str);
    }
    $('#TELEPHONE1').on('input paste change keyup keydown',function()
    {
      $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
      $(this).val($(this).val().replace(' ', ''));
      var subStr = this.value.substring(0,1);
      if(subStr != '+') 
      {
        $('[name = "TELEPHONE1"]').val('+257');
      }

      if(this.value.substring(0,4)=="+257")
      {
       if($(this).val().length == 12)
       {
        $('#error_TELEPHONE1').text('');
       }
       else
       {
          $('#error_TELEPHONE1').text("<?=lang('messages_lang.numero_invalide')?>");
          if($(this).val().length > 12)
          {
            $(this).val(this.value.substring(0,12));
            $('#error_TELEPHONE1').text('');
          }
       }
      }
      else{

       if ($(this).val().length > 12)
       {
        $('#error_TELEPHONE1').text('');
      }
      else
      {
        $('#error_TELEPHONE1').text("<?=lang('messages_lang.numero_invalide')?>");
      } 
    }

  });

    $('#TELEPHONE2').on('input paste change keyup keydown',function()
    {
      $(this).val($(this).val().replace(/[^0-9]*$/gi, ''));
      $(this).val($(this).val().replace(' ', ''));
      var subStr = this.value.substring(0,1);
      if(subStr != '+') {

        $('[name = "TELEPHONE2"]').val('+257');

      }

      if(this.value.substring(0,4)=="+257")
      {
       if($(this).val().length == 12)
       {
        $('#error_TELEPHONE2').text('');
      }
      else
      {
        $('#error_TELEPHONE2').text("<?=lang('messages_lang.numero_invalide')?>");
        if($(this).val().length > 12)
        {
          $(this).val(this.value.substring(0,12));
          $('#error_TELEPHONE2').text('');
        }

      }
    }
    else{

     if ($(this).val().length > 12)
     {
      $('#error_TELEPHONE2').text('');
    }
    else
    {
      $('#error_TELEPHONE2').text("<?=lang('messages_lang.numero_invalide')?>");
    } 
  }

});

</script>
<script type="text/javascript">
 function getSouhait()
 {
  var SOUHAITER = $('#SOUHAITER').val();

  if(SOUHAITER == 1){

    $('#bouton_cart').show();
    $('#table-responsive').show();
    $('#div_vide').removeClass('col-md-6');
  }
  else if(SOUHAITER == 0){
    $('#bouton_cart').hide();
    $('#div_vide').addClass('col-md-6');
  }
}
</script>
<script>
  function get_visualition()
  {
    var PROFIL_ID=$('#PROFIL_ID').val(); 
    $.ajax(
    {
      url : "<?=base_url('Administration/Gestion_Utilisateurs/get_visualisation')?>",
      type : "POST",
      dataType: "JSON",
      cache:false,
      data:
      {
        PROFIL_ID:PROFIL_ID
      },
      success:function(data)
      {   
        $('#NIVEAU_VISUALISATION_ID').val(data.visualisation_id);            }
    });        
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
<!--******************* Modal pour confirmer les infos saisies ***********************-->