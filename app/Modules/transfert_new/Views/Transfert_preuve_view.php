<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php'); ?>
  <style>
    hr.vertical {
      border: none;
      border-left: 1px solid hsla(200, 2%, 12%, 100);
      height: 55vh;
      width: 1px;
      color: #ddd
    }
  </style>
</head>
<body>
  <div class="wrapper">
    <?php echo view('includesbackend/navybar_menu.php'); ?>
    <link rel='stylesheet' href='<?= base_url('template/css') ?>/sweetalert2.min.css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.1/js/buttons.print.min.js">
    </script>
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
              <a href="<?php echo base_url('transfert_new/Transfert_list/')?>"
                style="float: right;margin-right: 20px;margin-top:5px"
                class="btn btn-primary"><i class="fa fa-list" aria-hidden="true"></i>
                <?=lang('messages_lang.link_list')?></a>
              </div>
              <div class="car-body">
               <?php
               if(session()->getFlashKeys('alert'))
               {
                ?>
                <center class="ml-5" style="height=100px;width:90%">
                 <div class="alert alert-success" id="message">
                  <?php echo session()->getFlashdata('alert')['message']; ?>
                </div>
              </center>
              <?php
            } ?>
            <h4 style="margin-left:4%;margin-top:10px"><?=lang('messages_lang.ajou_preuv')?></h4>
            <br>

            <div class=" container" style="width:90%">
             <form id="Myform" action="<?=base_url('transfert_new/Transfert_list/save_preuve')?>"method="post" enctype="multipart/form-data" >
              <input type="hidden" name="id" value="<?= $id?>">
              <div>
                <p class="mt-5 text-center">
                  <div class="form-group w-75 " >
                    <label for=""><i class="fa fa-file-o fa-2x text-info"></i>&nbsp;&nbsp;&nbsp;<?=lang('messages_lang.piec_just')?> <font color="red">*</font></label>
                    <input type="file" name="file[]" accept=".pdf" id="attachment" class="form-control w-75 " style="visibility:visible; position: absolute " multiple/>
                   
                  </div>
                </p>
                

                <p id="files-area" style="margin-top:60px;display:flex">
                  <span id="filesList" class="w-75 " style="margin-left:100px">
                    <span id="files-names"></span>
                  </span>
                </p>
                <font color="red" id="error_attachment"></font>
              </div>
            </form>
            <div style="margin-bottom:30px">
              <!-- <a onclick="save()" id="btn_save"  class="btn btn-primary" style="float:right;background:#061e69;color:white"><?=lang('messages_lang.donne_preuv')?></a> -->
              <button onclick="save()" id="btn_save" class="btn btn-primary"><?=lang('messages_lang.donne_preuv')?></button>
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
</body>

</html>

<style>

  .center{
    display: flex;
    text-align:center;
    align-items:center;
    justify-content:center;
    min-height:50vh;

  }
  #mesg{
    color:white;
    font-size:23px
  }
  .patienter{
    height:50%;
  }
  input:hover,select:hover{
    background:#4981f2;
    transition:.5s;
    transition-duration:.75s;

  }
  .ring{
    position:absolute;
    width:300px;
    height:300px;
    border-radius:50%;
    animation: ring 2s linear infinite;
  }

  .modal-body{
    background:black;
  }
  @keyframes ring {
    0%{
      transform:rotate(0deg);
      box-shadow: 1px 5px 2px #e65c80;
    }

    50%{
      transform:rotate(180deg);
      box-shadow: 1px 5px 2px #18b201;
    }
    100%{
      transform:rotate(360deg);
      box-shadow: 1px 5px 2px #8456c8;
    }
  }
  .ring:before{
    position: absolute;
    content:"";
    left:0;
    height:100%;
    width:100%;
    border-radius:50%;
    box-shadow:0 0 5px  rgba(255,255,255..3)
  }





  .container{
    margin-left:14%
  }
  #files-area{
    width: 30%;
  }
  .file-block{
    border-radius: 10px;
    background-color: rgba(144, 163, 203, 0.2);
    margin: 5px;
    color: initial;
    display: inline-flex;
    & > span.name{
      padding-right: 10px;
      width: max-content;
      display: inline-flex;
    }
  }
  .file-delete{
    display: flex;
    width: 24px;
    color: initial;
    background-color: #6eb4ff00;
    font-size: large;
    justify-content: center;
    margin-right: 3px;
    cursor: pointer;
    &:hover{
      background-color: rgba(144, 163, 203, 0.2);
      border-radius: 10px;
    }
    & > span{
      transform: rotate(45deg);
    }
  }

</style>
<script type="text/javascript">
  $(document).ready(function()
  {
    $('#message').delay('slow').fadeOut(3000);
  });

  const dt = new DataTransfer();
  $("#attachment").on('change', function(e)
  {
    
    if(this.files.length != 0)
    {
      $('#error_attachment').html("");
    }

    for(var i = 0; i < this.files.length; i++)
    {

      let fileBloc = $('<span/>', {class: 'file-block'}),
      fileName = $('<span/>', {class: 'name', text: this.files.item(i).name});
      fileBloc.append('<span class="file-delete"><span><font style="background:red;color:white;width:120px;border-radius:50px">X</font></span></span>')
      .append(fileName);
      $("#filesList > #files-names").append(fileBloc);
    };

    for (let file of this.files)
    {
      dt.items.add(file);
    }
    this.files = dt.files;
    console.log(this.files)
    $('span.file-delete').click(function()
    {
      let name = $(this).next('span.name').text();
      $(this).parent().remove();
      for(let i = 0; i < dt.items.length; i++){
       if(name === dt.items[i].getAsFile().name)
       {
        dt.items.remove(i);
        continue;
      }
    }
    document.getElementById('attachment').files = dt.files;
  });
  });
// end
</script>
<script type="text/javascript">
  function save()
  {
    var attachment = document.getElementById('attachment');
    var statuts=2;
    if(attachment.files.length === 0)
    {
      $('#error_attachment').html("<?=lang('messages_lang.champ_obligatoire')?>");
      statuts=1;
    }

    if(statuts == 2)
    {
      $("#Myform").submit();
    }
    

  }
</script>
