  
  <div id="map"></div>
                                                 
 
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

        marker.bindPopup("<div class=\'panel panel-primary\' style=\'border-color:#fff;width:100%;margin: 0;\'><div class=\'panel-heading text-center\' style=\'background-color:#000;padding: 7px;\'><b style=\'color:#fff\'>"+a[3]+"</b><br> <font color=\'#fff\'><i class=\'fa fa-institution\'></i></font> <b style=\'color:#fff\'>"+a[4]+"</b></div></div>");
        markercluster.addLayer(marker)
        // table_data1.push(marker);
      }
      map.addLayer(markercluster);
</script>
