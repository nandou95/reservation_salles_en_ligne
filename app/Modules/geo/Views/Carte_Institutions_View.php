<!DOCTYPE html>
<html lang="en">
<head>
  <?php echo view('includesbackend/header.php');?>

<script src='https://api.mapbox.com/mapbox.js/v3.2.0/mapbox.js'></script>
<link href='https://api.mapbox.com/mapbox.js/v3.2.0/mapbox.css' rel='stylesheet' />
 <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/leaflet.markercluster.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.css' rel='stylesheet' />
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-markercluster/v1.0.0/MarkerCluster.Default.css' rel='stylesheet' />

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-zoomslider/v0.7.0/L.Control.Zoomslider.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-zoomslider/v0.7.0/L.Control.Zoomslider.css' rel='stylesheet'/>

<script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/Leaflet.fullscreen.min.js'></script>
<link href='https://api.mapbox.com/mapbox.js/plugins/leaflet-fullscreen/v1.0.1/leaflet.fullscreen.css' rel='stylesheet' />

<style>
     #map {bottom:0; width:100%;height:550px; }
     
  .mapbox-improve-map{
    display: none;
  }
  
   .leaflet-control-attribution{
    display: none !important;
  }
  .leaflet-control-attribution{
    display: none !important;
  }
  .search-ui {
  
  }

    .circle-green {
background-color: #2d09e1;
border-radius: 50%
}

.circle {
border-radius: 20%
}

</style>

<style type="text/css">
    .scroller {
        height: 400px;
        overflow-y: scroll;
        border-radius: 10px;
    }
</style>

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
              <div style="box-shadow: rgba(100, 100, 111, 0.25) 0px 7px 29px 0px;padding: 13px !important" class="card">
                <div class="car-body">
                  <h1 class="header-title text-black">
                    <?= lang('messages_lang.labelle_titre_geo')?>
                  </h1><br>

                  <div class="row">
                    <form id="myform" method="POST" action="<?=base_url('geo/Carte_Institutions/index')?>">
                    <div class="col-4">
                      <label class="form-label"><?= lang('messages_lang.table_institution')?></label>
                        <select onchange="submit_data(this.value)" class="form-control" id="INSTITUTION_ID" name="INSTITUTION_ID">
                            <option value=""><?= lang('messages_lang.labelle_select')?></option>
                            <?php   
                            foreach ($institution as $keyinstitution) {
                              if ($keyinstitution->INSTITUTION_ID==$INSTITUTION_ID) {
                              ?>
                              <option selected value="<?= $keyinstitution->INSTITUTION_ID ?>" ><?= $keyinstitution->DESCRIPTION_INSTITUTION ?></option>
                              <?php
                              }else{
                              ?>
                              <option value="<?= $keyinstitution->INSTITUTION_ID ?>" ><?= $keyinstitution->DESCRIPTION_INSTITUTION ?></option>
                              <?php
                              }}
                            ?> 
                        </select>
                    </div>
                    </form>
                  </div><br>
                 
                 <div class="row">
                   <div class="col-8">
                     <div id="map"></div>
                   </div>

                   <div class="col-4">
                    <div class="" style="top:-30px;background-color: #153d77;padding: 5px;">
                      <center><b style="color: #c5932c"><?= lang('messages_lang.labelle_legende')?></b></center>
                    </div>

                    <div class="scroller">
                      <?=$legende?>
                    </div><br>
                    

                    <table class="table table-bordered table-condensed">
                    <tr>
                       <td>
                        <label style="opacity: 2.9;width: 18px;height: 18px; background:#07784d"><font color="black"></font></label>
                       </td>
                       <td>
                        <span class="ml-2 font-weight-bold"><?= lang('messages_lang.labelle_institutio')?></span>
                       </td>
                    </tr>
                    <tr>
                       <td>
                        <label style="opacity: 2.9;width: 18px;height: 18px; background:#c021bb"><font color="black"></font></label>
                       </td>
                       <td>
                        <span class="ml-2 font-weight-bold"><?= lang('messages_lang.labelle_ministre')?></span>
                       </td>
                    </tr>
                  </table>
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
  <?php echo view('includesbackend/scripts_js.php');?>
</body>
</html>

<script>
    L.mapbox.accessToken = 'pk.eyJ1IjoibWFydGlubWJ4IiwiYSI6ImNrMDc1MmozajAwcGczZW1sMjMwZWxtZDQifQ.u8xhrt1Wn4A82X38f5_Iyw';
    
    var coordinates = '<?= $coordinates; ?>';
    var coordinates = coordinates.split(",");
    var zooms = '<?= $zoom; ?>';

    var map = L.mapbox.map('map')
    .setView([coordinates[0],coordinates[1]], zooms);
    
    
    var layers = {
      Nuit: L.mapbox.styleLayer('mapbox://styles/mapbox/dark-v10'),
      Sombre: L.mapbox.styleLayer('mapbox://styles/mapbox/navigation-guidance-night-v4'),
      Streets: L.mapbox.styleLayer('mapbox://styles/mapbox/streets-v11'),
      Satellite: L.mapbox.styleLayer('mapbox://styles/mapbox/satellite-streets-v11'),
    };

    layers.Streets.addTo(map);
    L.control.layers(null,layers,{position: 'topleft'}).addTo(map);
    L.control.fullscreen().addTo(map);

    var markercluster= new L.MarkerClusterGroup();
    // var markercluster= L.featureGroup()

    var get_data = '<?php echo $get_data; ?>';
    var get_data = get_data.split("@");
    
    // var table_data1 = [];
              
     for (var i = 0; i < (get_data.length)-1; i++)
     {

        var a = get_data[i].split("<>");
        var marker = L.marker (new L.LatLng(a[0], a[1]), {
          icon: L.mapbox.marker.icon({'marker-symbol': 'home','marker-color': ''+a[2]+'', 'marker-size': 'small'}),
          title:  ''+a[4]+'',
          myID: ''+a[6]+''
        });

        // "+a[5]+" <div class=\'panel-body\' style=\'background-color:#fff;padding: 8px;\'> </div>

        marker.bindPopup("<div class=\'panel panel-primary\' style=\'border-color:#fff;width:100%;margin: 0;\'><div class=\'panel-heading text-center\' style=\'background-color:#000;padding: 7px;\'><b style=\'color:#fff\'>"+a[3]+"</b><br> <font color=\'#fff\'><i class=\'fa fa-institution\'></i></font> <b style=\'color:#fff\'>"+a[4]+"</b></div></div>");
        markercluster.addLayer(marker)
        // table_data1.push(marker);
      }
      map.addLayer(markercluster);
    
</script>

<script type="text/javascript">
  
  function submit_data() { 
    myform.submit();
  }
</script>
