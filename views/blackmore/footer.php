<?php
	use Scabbia\Framework;
?>
					</div>
				</div>
			</section>
			<footer id="pageBottom" class="wrapper">
				<div class="inner">
					<?php echo _('Generated in') . ' ' . number_format(microtime(true) - Framework::$timestamp, 5) . ' msec.'; ?>
				</div>
			</footer>
		</div>

		<script type="text/javascript">
			$l.contentEnd();
		</script>
	</body>
</html>