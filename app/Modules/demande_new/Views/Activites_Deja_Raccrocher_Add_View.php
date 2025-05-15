<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
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
                <div style="float: right;">
                  <a href="<?php echo base_url('demande_new/Activites_Deja_Raccroche/')?>" style="float: right;margin-right: 20px;margin-top:5px" class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i><?=lang('messages_lang.liste_bouton')?></a>
                </div>
                <div class="car-body">

                  <h4 style="margin-left:4%;margin-top:10px"><?=$titre?></h4>
                  <div class="table-responsive container " style="margin:15px">
                    <form method="post" name="myDoc" id="myDoc" action="<?= base_url('demande_new/Activites_Deja_Raccroche/enregistre_document') ?>" class="form-group row needs-validation p-5" enctype="multipart/form-data">

                      <div class="container">
                        <div class="row">
                          <input type="hidden" name="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID"  id="EXECUTION_BUDGETAIRE_RACCROCHAGE_ID" value="<?=$EXECUTION_BUDGETAIRE_RACCROCHAGE_ID?>">
                        </div>

                        <div class="row">
                          <div class="col-md-6">
                            <div class="form-group">
                              <label class="form-label"> <?=lang('messages_lang.th_type_document')?><font color="red">*</font></label>
                              <select name="TYPE_DOCUMENT_ID" id="TYPE_DOCUMENT_ID"  class="form-control">
                                <option value=""><?=lang('messages_lang.labelle_selecte')?></option>
                                <?php foreach($get_doc as $document):?>
                                  <option value="<?= $document->TYPE_DOCUMENT_ID ?>"><?= $document->DESCR_DOCUMENT ?></option>
                                <?php endforeach ?>
                              </select>
                              <font color="red" id="error_type"></font>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for=""> <?=lang('messages_lang.nume_document')?><font color="red">*</font></label>
                              <input type="text" class="form-control" id="NUMERO_DOCUMENT" name="NUMERO_DOCUMENT">
                              <font color="red" id="error_num"></font>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="" id="identifiant"> <?=lang('messages_lang.telever')?><font color="red">*</font></label>
                              <input type="file" class="form-control" id="PATH_DOCUMENT"   name="PATH_DOCUMENT">
                              <font color="red" id="error_docu"></font>
                            </div>
                          </div>
                          <div class="col-md-6">
                            <div class="form-group">
                              <label for="" id="identifiant"><?=lang('messages_lang.date_doc')?><font color="red">*</font></label>
                              <input type="date" class="form-control" id="DATE_DOCUMENT" max="<?= date('Y-m-d')?>" onkeydown="return false"  name="DATE_DOCUMENT">
                              <font color="red" id="error_date"></font>
                            </div>
                          </div>
                        </div>
                        <br>
                        <div class="row">
                          <div class="col-12">
                           <div class="col-10" style="float: left;">
                            <h1 class="header-title text-dark">
                            </h1>
                          </div>
                          <div class="col-2" style="float: right;">
                            <a onclick="save_doc();" style="float: right;margin: 2px" class="btn btn-primary"><i class="fa fa-plus" aria-hidden="true"></i> <?=lang('messages_lang.bouton_ajouter')?></a>
                          </div>
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
  function save_doc()
  {
    var statut = 2;
    var TYPE_DOCUMENT_ID = $('#TYPE_DOCUMENT_ID').val();
    var NUMERO_DOCUMENT = $('#NUMERO_DOCUMENT').val();
    var PATH_DOCUMENT = $('#PATH_DOCUMENT').val();
    var DATE_DOCUMENT = $('#DATE_DOCUMENT').val();
    $('#error_type').html('');
    $('#error_num').html('');
    $('#error_docu').html('');
    $('#error_date').html('');   

    if (TYPE_DOCUMENT_ID == '') 
    {
      $('#error_type').html('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if (NUMERO_DOCUMENT == '') 
    {
      $('#error_num').html('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if (PATH_DOCUMENT == '') 
    {
      $('#error_docu').html('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }
    if (DATE_DOCUMENT == '') 
    {
      $('#error_date').html('<?=lang('messages_lang.message_champs_obligatoire')?>');
      statut = 1;
    }

    var url;
    var path = PATH_DOCUMENT;
    var doc = path.split("\\");
    var documen= doc[doc.length-1];

    if(statut == 2)
    {
      $('#TYPE_DOCUMENT_ID_valide').html($('#TYPE_DOCUMENT_ID option:selected').text());
      $('#NUMERO_DOCUMENT_valide').html(NUMERO_DOCUMENT);
      
      
      $('#PATH_DOCUMENT_valide').html(documen);

      $('#DATE_DOCUMENT_valide').html(DATE_DOCUMENT);
      $("#document").modal("show");

    }
  }
</script>

<div class="modal fade" id="document" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog  modal-xl">
    <div class="modal-content">
      <div class="modal-body">
        <div class="table-responsive  mt-3">
          <table class="table table-bordered">
            <thead class="bg-dark text-white">
              <th><?=lang('messages_lang.th_type_document')?></th>
              <th><?=lang('messages_lang.nume_document')?></th>
              <th>Document</th>
              <th><?=lang('messages_lang.date_doc')?></th>
            </thead>
            <tbody>
              <tr>
                <td id="TYPE_DOCUMENT_ID_valide"></td>
                <td id="NUMERO_DOCUMENT_valide"></td>
                <td id="PATH_DOCUMENT_valide"></td>
                <td id="DATE_DOCUMENT_valide"></td>
                
              </tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal"> <i class="fa fa-edit" aria-hidden="true"></i> <?=lang('messages_lang.bouton_modifier')?></button>
        <a onclick="save_final()" style="float: right;margin: 2px" class="btn btn-info"><i class="fa fa-save" aria-hidden="true"></i> <?=lang('messages_lang.bouton_enregistrer')?></a>
      </div>
    </div>
  </div>
</div>


<script type="text/javascript">
  function save_final()
  {
   document.getElementById("myDoc").submit();
 }
</script>


