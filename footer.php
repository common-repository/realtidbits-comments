<?php if (get_option('rtb_disable_ssr')): ?>
<?php else: ?>
	<!-- RealTidbits Comments -->
	<script type="text/javascript" src="http://cdn.realtidbits.com/libs/v1/comments/comments.js"></script>
	<script type="text/javascript">
	new RealTidbits.Comments({
		"target" : document.getElementById("realtidbits-comments"),
	  "appkey": "<?php echo strtolower(get_option('rtb_comments_appkey')); ?>",
	  "backplane": {
		"busName": "<?php echo strtolower(get_option('rtb_comments_backplane_busname')); ?>",
		"rpx": "<?php echo strtolower(get_option('rtb_comments_rpx')); ?>"
	  }
	});
	</script>
<?php endif; ?>