<?php
	use Scabbia\Framework;
?>
					</div>
				</div>
			</section>

            <div id="push"></div>
		</div>

        <footer id="footer" class="wrapper">
            <div class="container-fluid">
                <?php echo _('Generated in') . ' ' . number_format(microtime(true) - Framework::$timestamp, 5) . ' msec.'; ?>
            </div>
        </footer>

		<script type="text/javascript">
			$l.contentEnd();
		</script>
	</body>
</html>