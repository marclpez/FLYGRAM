<?php

if( ! defined('ABSPATH' ) ){
	exit;
}

$role_meta_value = get_post_meta( $post->ID, 'access_roles', true);
$dafault_roles = isset($role_meta_value) && $role_meta_value != '' ? $role_meta_value : array();

$user_meta_value = get_post_meta( $post->ID, 'access_users', true);
$dafault_users = isset($user_meta_value) && $user_meta_value != '' ? $user_meta_value : array();

$access_guests_value = get_post_meta( $post->ID, 'access_guests', true);
// var_dump($access_guests_value);
// var_dump($dafault_users);

$file_access = array(
	'access_roles' => array(
		'title' 		=> __('Select User Roles', 'wpfm'),
		'description' 	=> __('Leave blank to allow access every user including guests', 'wpmf'),
		'data' 			=> wpfm_access_roles($dafault_roles),
		'defaults'		=> $dafault_roles,
		),
	'access_users' => array(
		'title' 		=> __('Selec User','wpfm'),
		'description' 	=> __('Select users one by one', 'wpfm'),
		'data' 			=> wpfm_access_users( $dafault_users ),
		'defaults'		=> $dafault_users,
		),
);
?>

<table class="form-table">
	<tbody>
		
		<tr>
			<th scope="row"><?php _e("Allow Guests", 'wpfm');?></th>
			<td  width="80%">
				<input type="checkbox" <?php checked( $access_guests_value, 'yes' ); ?> id="wpfm_guest_access" name="access_guests" value="yes">
				<br>
				<?php _e("Allow all public users to access these downloads", 'wpfm');
				?>
			</td>
		</tr>
		
		<?php foreach ($file_access as $key => $value) { ?>
		<tr class="wpfm-file-sources-options">
			<th scope="row"><?php echo $value['title']; ?></th>
			<td  width="80%">
				<?php echo $value['data']; ?>
				<br>
				<?php echo $value['description']; ?>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<script type="text/javascript">
	
jQuery(function(tt) {
		
		
	wpfm_manage_access_options();
	
	tt("#wpfm_guest_access").on('change', function(e){
		
		wpfm_manage_access_options();
	});
	
	function wpfm_manage_access_options() {
		
		if( tt("#wpfm_guest_access").prop('checked') ) {
			
			tt('.wpfm-file-sources-options').hide();
		} else {
			
			tt('.wpfm-file-sources-options').show();
		}
	}
});
	
</script>