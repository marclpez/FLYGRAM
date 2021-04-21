<div class="wrap">
    <h2><?php echo $title; ?></h2>
    <?php
    echo do_shortcode( '[cminds_free_activation id="cmloc"]' );
    ?>
    <div id="cminds_settings_container">
        <?php echo $nav; ?>
        <?php echo $content; ?>
    </div>
</div>