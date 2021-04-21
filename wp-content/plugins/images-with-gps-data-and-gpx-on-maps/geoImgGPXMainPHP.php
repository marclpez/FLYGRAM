<?php 
function GPSLongitudeDezi($exif_data) {
	if (isset($exif_data["GPS"]["GPSLongitudeRef"])) {
		$GPSLongitudeRef = $exif_data["GPS"]["GPSLongitudeRef"];
		if ($GPSLongitudeRef == "E") {
			$GPSLongfaktor = 1;
		} else {
			$GPSLongfaktor = -1;
		}
	} else {
		return False;
	}
	if (isset($exif_data["GPS"]["GPSLongitude"])) {
		$GPSLongitude = $exif_data["GPS"]["GPSLongitude"];
		$GPSLongitude_g = explode("/", $GPSLongitude[0]);
		$GPSLongitude_m = explode("/", $GPSLongitude[1]);
		$GPSLongitude_s = explode("/", $GPSLongitude[2]);
		$GPSLong_g = $GPSLongitude_g[0] / $GPSLongitude_g[1];
		$GPSLong_m = $GPSLongitude_m[0] / $GPSLongitude_m[1];
		$GPSLong_s = $GPSLongitude_s[0] / $GPSLongitude_s[1];
		$GPSLongGrad = $GPSLongfaktor * ($GPSLong_g + ($GPSLong_m + ($GPSLong_s / 60)) / 60);
		return $GPSLongGrad;
	} else {
		return False;
	}
}
function GPSLongitudeDeziExifTool($exif_data) {
	if (isset($exif_data["0"]["GPSLongitudeRef"])) {
		$GPSLongitudeRef = $exif_data["0"]["GPSLongitudeRef"];
		if ($GPSLongitudeRef == "East" or $GPSLongitudeRef == "E" ) {
			$GPSLongfaktor = 1;
		} else {
			$GPSLongfaktor = -1;
		}
	} else {
		return False;
	}
	if (isset($exif_data["0"]["GPSLongitude"])) {
		$GPSLongitude = $exif_data["0"]["GPSLongitude"];
		$GPSLongitude_g = explode("deg", $GPSLongitude);
		$GPSLongitude_m = explode("'", trim($GPSLongitude_g["1"]));
		$GPSLongitude_s = explode('"', trim($GPSLongitude_m["1"]));
		$GPSLong_g = trim($GPSLongitude_g[0]);
		$GPSLong_m = trim($GPSLongitude_m[0]);
		$GPSLong_s = trim($GPSLongitude_s[0]);
		$GPSLongGrad = $GPSLongfaktor * ($GPSLong_g + ($GPSLong_m + ($GPSLong_s / 60)) / 60);
		return $GPSLongGrad;
	} else {
		return False;
	}
}
function GPSLatitudeDezi($exif_data) {
	if (isset($exif_data["GPS"]["GPSLatitudeRef"])) {
		$GPSLatitudeRef = $exif_data["GPS"]["GPSLatitudeRef"];
		if ($GPSLatitudeRef == "N") {
			$GPSLatfaktor = 1;
		} else {
			$GPSLatfaktor = -1;
		}
	} else {
		return False;
	}
	if (isset($exif_data["GPS"]["GPSLongitude"])) {
		$GPSLatitude = $exif_data["GPS"]["GPSLatitude"];
		$GPSLatitude_g = explode("/", $GPSLatitude[0]);
		$GPSLatitude_m = explode("/", $GPSLatitude[1]);
		$GPSLatitude_s = explode("/", $GPSLatitude[2]);
		$GPSLat_g = $GPSLatitude_g[0] / $GPSLatitude_g[1];
		$GPSLat_m = $GPSLatitude_m[0] / $GPSLatitude_m[1];
		$GPSLat_s = $GPSLatitude_s[0] / $GPSLatitude_s[1];
		$GPSLatGrad = $GPSLatfaktor * ($GPSLat_g + ($GPSLat_m + ($GPSLat_s / 60)) / 60);
		return $GPSLatGrad;
	} else {
		return False;
	}
}
function GPSLatitudeDeziExifTool($exif_data) {
	if (isset($exif_data["0"]["GPSLatitudeRef"])) {
		$GPSLatitudeRef = $exif_data["0"]["GPSLatitudeRef"];
		if ($GPSLatitudeRef == "North" or $GPSLatitudeRef == "N" ) {
			$GPSLatfaktor = 1;
		} else {
			$GPSLatfaktor = -1;
		}
	} else {
		return False;
	}
	if (isset($exif_data["0"]["GPSLatitude"])) {
		$GPSLatitude = $exif_data["0"]["GPSLatitude"];
		$GPSLatitude_g = explode("deg", $GPSLatitude);
		$GPSLatitude_m = explode("'", trim($GPSLatitude_g["1"]));
		$GPSLatitude_s = explode('"', trim($GPSLatitude_m["1"]));
		$GPSLat_g = trim($GPSLatitude_g[0]);
		$GPSLat_m = trim($GPSLatitude_m[0]);
		$GPSLat_s = trim($GPSLatitude_s[0]);
		$GPSLatGrad = $GPSLatfaktor * ($GPSLat_g + ($GPSLat_m + ($GPSLat_s / 60)) / 60);
		return $GPSLatGrad;
	} else {
		return False;
	}
}
function GPSAltitudeExifTool($exif_data) {
	if (isset($exif_data["0"]["GPSAltitude"])) {
		$GPSAltitudeRef = $exif_data["0"]["GPSAltitudeRef"];
		if ($GPSAltitudeRef == "Above Sea Level" ) {
			$GPSAltfaktor = 1;
		} else {
			return false;
		}
		return trim(str_replace("m Above Sea Level", "", $exif_data["0"]["GPSAltitude"] ));
	} else {
		return False;
	}
}
function GPSLongitude($exif_data) {
	if (isset($exif_data["GPS"]["GPSLongitudeRef"])) {
		$GPSLongitudeRef = $exif_data["GPS"]["GPSLongitudeRef"];
		if ($GPSLongitudeRef == "E") {
			$GPSLongfaktor = 1;
		} else {
			$GPSLongfaktor = -1;
		}
	} else {
		return False;
	}
	if (isset($exif_data["GPS"]["GPSLongitude"])) {
		$GPSLongitude = $exif_data["GPS"]["GPSLongitude"];
		$GPSLongitude_g = explode("/", $GPSLongitude[0]);
		$GPSLongitude_m = explode("/", $GPSLongitude[1]);
		$GPSLongitude_s = explode("/", $GPSLongitude[2]);
		$GPSLong_g = $GPSLongitude_g[0] / $GPSLongitude_g[1];
		$GPSLong_m = $GPSLongitude_m[0] / $GPSLongitude_m[1];
		$GPSLong_s = $GPSLongitude_s[0] / $GPSLongitude_s[1];
		$GPSLongitude = $GPSLongitudeRef . " " . $GPSLong_g . "° " . $GPSLong_m . "' " . $GPSLong_s;
		return $GPSLongitude;
	} else {
		return False;
	}
}

function GPSLatitude($exif_data) {
	if (isset($exif_data["GPS"]["GPSLatitudeRef"])) {
		$GPSLatitudeRef = $exif_data["GPS"]["GPSLatitudeRef"];
		if ($GPSLatitudeRef == "N") {
			$GPSLatfaktor = 1;
		} else {
			$GPSLatfaktor = -1;
		}
	} else {
		return False;
	}
	if (isset($exif_data["GPS"]["GPSLongitude"])) {
		$GPSLatitude = $exif_data["GPS"]["GPSLatitude"];
		$GPSLatitude_g = explode("/", $GPSLatitude[0]);
		$GPSLatitude_m = explode("/", $GPSLatitude[1]);
		$GPSLatitude_s = explode("/", $GPSLatitude[2]);
		$GPSLat_g = $GPSLatitude_g[0] / $GPSLatitude_g[1];
		$GPSLat_m = $GPSLatitude_m[0] / $GPSLatitude_m[1];
		$GPSLat_s = $GPSLatitude_s[0] / $GPSLatitude_s[1];
		$GPSLatitude = $GPSLatitudeRef . " " . $GPSLat_g . "° " . $GPSLat_m . "' " . $GPSLat_s;
		return $GPSLatitude;
	} else {
		return False;
	}
}

if( !function_exists ( 'qg_var_dump' ) ){
	function qg_var_dump($var, $ignore_debug = 0){
		if(WP_DEBUG OR $ignore_debug){
			echo '<div style="text-align:left; font-size:11px; color:black; background-color:rgb(255,255,244)">';
			echo '<pre>';
				var_dump($var);
			echo '</pre>';
			echo '</div>';
		}
	}
}
?>
