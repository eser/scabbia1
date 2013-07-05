<?php
use Scabbia\Framework;

?>
</section>
<footer id="pageBottom">
	<?php echo _('Generated in'), ' ', number_format(microtime(true) - Framework::$timestamp, 5), ' msec.'; ?>
</footer>
</div>

<script type="text/javascript">
	$l.contentEnd();
</script>
</body>
</html>