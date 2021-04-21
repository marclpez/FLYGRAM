<?php
function geoimggpx_uninstall(){
	// https://codex.wordpress.org/Function_Reference/register_uninstall_hook
	if ( ! defined('WP_UNINSTALL_PLUGIN')  )
	return;

	delete_option( 'geoimggpx_options' );

}
geoimggpx_uninstall();
?>
