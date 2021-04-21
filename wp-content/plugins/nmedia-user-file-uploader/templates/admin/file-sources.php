<?php

if( ! defined('ABSPATH' ) ){
	exit;
}
$group_meta_value = get_post_meta( $post->ID, 'source_group', true);
$dafault_groups = isset($group_meta_value) && $group_meta_value != '' ? $group_meta_value : array();


$role_meta_value = get_post_meta( $post->ID, 'source_roles', true);
$dafault_roles = isset($role_meta_value) && $role_meta_value != '' ? $role_meta_value : array();

$users_meta_value = get_post_meta( $post->ID, 'source_users', true);
$dafault_users = isset($users_meta_value) && $users_meta_value != '' ? $users_meta_value : array();

$current_user_value = get_post_meta( $post->ID, 'current_user', true);

$file_sources = array(
	'source_group' => array(
		'title' 		=> __('Select Groups', 'wpfm'),
		'description' 	=> __('Select file groups to include files on download page','wpfm'),
		'data' 			=> wpfm_source_groups( $dafault_groups ),
		'defaults'		=> $dafault_groups,
		),
	'source_roles' => array(
		'title' 		=> __('Select User Roles', 'wpfm'),
		'description' 	=> __('Select user roles to include their files on download page','wpfm'),
		'data' 			=> wpfm_source_roles( $dafault_roles ),
		'defaults'		=> $dafault_roles,
		),
	'source_users' => array(
		'title' 		=> __('Selec Users', 'wpfm'),
		'description' 	=> __('Select users to include their files on download page','wpfm'),
		'data' 			=> wpfm_source_users( $dafault_users ),
		'defaults'		=> $dafault_users,
		),
);
?>

<table class="form-table">
	<tbody>
		<?php foreach ($file_sources as $key => $value) { ?>
		<tr>
			<th><?php echo $value['title']; ?></th>
			<td  width="80%">
				<?php echo $value['data']; ?>
				<br>
				<?php echo $value['description']; ?>
			</td>
		</tr>
		<?php } ?>
		
		<tr>
			<th scope="row"><?php _e("Current User Files", 'wpfm');?></th>
			<td  width="80%">
				<input type="checkbox" <?php checked( $current_user_value, 'yes' ); ?> id="wpfm_current_user" name="current_user" value="yes">
				<br>
				<?php _e("It will also include current user files.", 'wpfm');
				?>
			</td>
		</tr>
	</tbody>
</table>