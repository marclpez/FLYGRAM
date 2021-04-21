<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo the_title(); ?></title>
		<style>
			body {
				padding: 0;
				margin: 0;
			}
			html, body, #leaflet-container {
				height: 100%;
			}
			#container {
				height: 100%;
			}
		</style>
	</head>
	<body>
		<div id="container">
			<?php
			if ( have_posts() ) {
				while ( have_posts() ) {
					the_post();
					the_content();
				}
			}
			?>
		</div>
	</body>
</html>
