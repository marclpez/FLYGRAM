<?php

/**
 * Template for admin notices
 *
 * @link       http://wordpress.org/plugins/rate-my-post/
 * @since      3.3.0
 *
 * @package    Rate_My_Post
 * @subpackage Rate_My_Post/admin/partials
 */
?>

<?php
  if ( ! defined( 'WPINC' ) ) {
  	die;
  }
  $admin_notices = get_option( 'rmp_admin_notices' );
?>

<?php if( !$admin_notices['ls'] && class_exists( 'LiteSpeed_Cache_API' ) && (3 < 2) ): ?>
  <div class="js-rmp-admin-notice rmp-admin-notice notice notice-warning is-dismissible" data-admin-notice-key="ls">
    <h2>Rate my Post <?php echo esc_html__('Notice', 'rate-my-post'); ?>: LiteSpeed Cache</h2>
    <p><?php echo sprintf( esc_html__( 'We detected %s plugin installed on your website. %s works best if %s is configured as shown %shere%s.', 'rate-my-post' ), 'LiteSpeed Cache', 'Rate my Post', 'LiteSpeed cache', '<a href="https://blazzdev.com/wp-content/uploads/2019/09/LiteSpeed-Settings-for-RMP.jpg" target="_blank">', '</a>' ); ?></p>
  </div>
<?php endif; ?>

<?php if( !$admin_notices['ampforwp'] && function_exists( 'ampforwp_is_amp_endpoint' ) ): ?>
  <div class="js-rmp-admin-notice rmp-admin-notice notice notice-warning is-dismissible" data-admin-notice-key="ampforwp">
    <h2>Rate my Post <?php echo esc_html__('Notice', 'rate-my-post'); ?>: AMP for WP</h2>
    <p><?php echo sprintf( esc_html__( 'We detected %s plugin installed on your website. If you want to use %s on %sAMP pages%s, additional configuration is required. Learn more %shere%s.', 'rate-my-post' ), 'AMP for WP', 'Rate my Post', '<b>', '</b>', '<a href="https://blazzdev.com/documentation/rate-my-post-documentation/#amp" target="_blank">', '</a>' ); ?></p>
  </div>
<?php endif; ?>

<?php if( ! class_exists( 'Rate_My_Post_Pro' ) && !$admin_notices['pro'] && ( ( time() - $admin_notices['installed'] ) > 864000 ) ): ?>
  <div class="js-rmp-admin-notice rmp-admin-notice notice notice-info is-dismissible" data-admin-notice-key="pro">
    <h2><?php echo sprintf( esc_html__( '%s: Are you ready to boost your SEO with %s?', 'rate-my-post' ), 'Rate my Post', 'Rate my Post PRO' ); ?></h2>
    <p><?php echo sprintf( esc_html__( 'You\'ve been using %s for a while now. Maybe it\'s time to check out the PRO version which comes some neat features such as %sadvanced structured data%s and %scustom rating widgets%s. If not, keep enjoying the free version :)', 'rate-my-post' ), 'Rate my Post', '<b>', '</b>', '<b>', '</b>' ); ?></p>
      <a target="_blank" href="https://blazzdev.com/products/rate-my-post-pro/" class="rmp-btn rmp-btn--info">
        <?php echo esc_html__('See Rate my Post PRO', 'rate-my-post' ); ?>
      </a>
  </div>
<?php endif; ?>
