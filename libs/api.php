<?php
	$conf = parse_ini_file('config.ini');

	$mysqli = new mysqli($conf['host'], $conf['username'], $conf['password'], $conf['db']);

	if ($mysqli->connect_error) {
		die('Error en la conexión: ' . $mysqli->error);
	}

	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if(isset($_GET['id_sensor'])) {
			$id_sensor = $_GET['id_sensor'];
		} else {
			$id_sensor = 'f000aa01-0451-4000-b000-000000000000';
		}
		
		$query = "select * from trackings where id_sensor='" . $id_sensor . "' ORDER BY fecha DESC LIMIT 10";
		$data = $mysqli->query($query);
		$result = array();
		while($row = $data->fetch_assoc()) {
			$result[] = $row;
		}

		$result = array_reverse($result);
	}
	if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
		if(isset($_POST['last_track_id']) && isset($_POST['id_sensor']) && isset($_POST['date'])) {
			$last_track_id = $_POST['last_track_id'];
			$id_sensor = $_POST['id_sensor'];
			$date = $_POST['date'];
		} else {
			return '404';
		}

		$query = "select * from trackings where id_sensor='" . $id_sensor . "' and fecha > '" . $date . "' and id_track > '" . $id_sensor . "' ORDER BY fecha ASC";
		$result = array();
		if($data = $mysqli->query($query)) {
			while($row = $data->fetch_assoc()) {
				$result[] = $row;
			}
		}
		echo json_encode($result);
	}
?>
