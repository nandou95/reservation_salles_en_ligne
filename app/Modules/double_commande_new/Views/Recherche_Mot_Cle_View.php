<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>
</head>
<body>
  <div class="wrapper" >
    <?php echo view('includesbackend/navybar_menu.php');?>
    <div class="main">
      <?php echo view('includesbackend/navybar_topbar.php');?>
      <div class="col-md-12 d-flex" style="float:left;">
          <div class="col-md-12" >
            <br>
            <form>
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" class="form-control" placeholder="Recherche..." autofocus oninput="search(this.value)"><br>
              </div>
            </form>
          </div>
        </div>

        <div class="trombinoscope">
          <div class="membre">
          </div>
        </div>
    </div>
  </div>
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>

<script>
  function search(value)
  {
    $.ajax(
      {
        url:"<?=base_url('/double_commande_new/Recherche_Mot_Cle/getInfo')?>",
        type:"POST",
        dataType:"JSON",
        data: {value:value},
        beforeSend:function() {
          // $('#loading_btn').html("<i class='fa fa-spinner fa-pulse fa-1x fa-fw'></i>");
        },
        success: function(data)
        {
          if (data.html !='')
          {
            $('.membre').html(data.html);            
          }
          else
          {
            $('.membre').html(''); 
          }
        }
      });
  }
</script>