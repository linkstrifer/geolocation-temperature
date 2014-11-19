<?php
	include('libs/api.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <style type="text/css">
      html { height: 100% }
      body { height: 100%; margin: 0; padding: 0 }
      #map_canvas { height: 100% }
      .Chart {
      	background: rgba(255,255,255,.8);
      	box-shadow: 0 0 5px 0 rgba(0,0,0,.2);
      	bottom: 0;
      	height: 300px;
      	left: 0;
      	position: absolute;
      	right: 0;
      	text-align: center;
      	width: 100%;
      	z-index: 10000;
      }
    </style>
	<title>Maps</title>
	<script type="text/javascript"
      src="http://maps.googleapis.com/maps/api/js">
    </script>
</head>
<body onload="initialize()">
	<div class="Chart">
		<canvas id="Chart" width="1000" height="300"></canvas>
	</div>
    <div id="map_canvas" style="width:100%; height:100%"></div>
	<!-- js -->
	<script src="static/js/jquery-1.11.1.min.js"></script>
	<script src="static/js/Chart.js"></script>	 
	<script>
		var data = {
		    labels: [
		    	<?php
					for ($i=0; $i < count($result); $i++) { 
						echo '"' . $result[$i]['fecha'] . '",';
					}
				?>
		    ],
		    datasets: [
		        {
		            label: "My First dataset",
		            fillColor: "rgba(255,0,0,0.2)",
		            strokeColor: "rgba(163,0,6,.5)",
		            pointColor: "rgba(163,0,6,.5)",
		            pointStrokeColor: "#fff",
		            pointHighlightFill: "#9c0",
		            pointHighlightStroke: "rgba(220,220,220,1)",
		            data: [
		            	<?php
							for ($i=0; $i < count($result); $i++) { 
								echo '"' . $result[$i]['temperatura'] . '",';
							}
						?>
		            ]
		        }
		    ]
		};

		var options = {
		    scaleShowGridLines : true,
		    scaleGridLineColor : "rgba(0,0,0,.2)",
		    scaleGridLineWidth : 1,
		    bezierCurve : true,
		    bezierCurveTension : 0.4,
		    pointDot : true,
		    pointDotRadius : 4,
		    pointDotStrokeWidth : 1,
		    pointHitDetectionRadius : 20,
		    datasetStroke : true,
		    datasetStrokeWidth : 2,
		    datasetFill : true,
		    legendTemplate : "<ul class=\"<%=name.toLowerCase()%>-legend\"><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>"
		};

		var ctx = document.getElementById("Chart").getContext("2d");
		var Chart = new Chart(ctx).Line(data, options);
	</script>
	<script type="text/javascript">
		var marker;
		<?php 
			$current = end($result);
		?> 
		var current_position = new google.maps.LatLng(<?php echo $current['latitud']; ?>, <?php echo $current['longitud']; ?>);
		var map;
		var route_coords;
		var route;
		var route_array = [];
		var sensor = "<?php echo $id_sensor; ?>";
		var last_update = "<?php echo end($result)['fecha']; ?>";
		function initialize() {
			var mapOptions = {
			  center: current_position,
			  zoom: 16,
			  mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);

			marker = new google.maps.Marker({
				position: current_position,
				map: map,
				title: "Última ubicación"
			});

			<?php
				for ($i=0; $i < count($result); $i++) { 
					echo "route_array.push(" . $result[$i]['id_track'] . ");";
				}
			?>

			route_coords = [
				<?php
					for ($i=0; $i < count($result); $i++) { 
						echo "new google.maps.LatLng(" . $result[$i]['latitud'] . ", " . $result[$i]['longitud'] . ")";
						if($i != count($result) - 1) {
							echo ", ";
						}
					}
				?>
			];

			route = new google.maps.Polyline({
				path: route_coords,
				strokeColor: '#9c0',
				strokeOpacity: 1.0,
				strokeWeight: 2
			});

			route.setMap(map);
		}
		function updateData() {
			$.ajax({
				url: "api.php",
				data: {
					last_track_id: route_array[route_array.length - 1],
					id_sensor: sensor,
					date: last_update
				},
				type: 'POST',
			}).success(function(data) {
				console.log(data);
				data = $.parseJSON(data);
				data.forEach(
					function(row) {
						current_position = new google.maps.LatLng(row.latitud, row.longitud);
						route_coords.push(current_position);
						route.setPath(route_coords);
						marker.setPosition(current_position);
						map.setCenter(current_position);
						route_array.push(row.id_track);
						last_update = row.fecha;
						Chart.addData([row.temperatura], row.fecha)
					}
				);
			});
		}

		var timeout = setInterval(updateData, 2 * 1000)
    </script>
</body>
</html>
