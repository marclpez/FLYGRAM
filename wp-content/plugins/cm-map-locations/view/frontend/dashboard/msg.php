<?php

use com\cminds\maplocations\model\Labels;

?>
<div class="cmloc-msg cmloc-msg-<?php echo $class; ?>">
	<div class="cmloc-msg-inner">
		<span><?php echo apply_filters('cmloc_dashboard_msg', Labels::getLocalized($msg), $msg, $class); ?></span>
		<div class="cmloc-msg-extra"><?php echo $extra; ?></div>
	</div>
</div>