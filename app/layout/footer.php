<?php
/**
* LoadAvg - Server Monitoring & Analytics
* http://www.loadavg.com
*
* Main footer file
* 
* @link https://github.com/loadavg/loadavg
* @author Karsten Becker
* @copyright 2014 Sputnik7
*
* This file is licensed under the Affero General Public License version 3 or
* later.
*/
?>	

			<?php if ($loadavg->isLoggedIn()) { ?>

			<div class="well lh70-style-top margin-none center footer">
				<a href="http://www.loadavg.com/">LoadAVG v <?php echo LoadAvg::$_settings->general['version']; ?></a> &copy;  <?php echo date("Y"); ?> Sputnik7 Ltd<br />
				For comments and suggestions please <a href="http://www.loadavg.com/forums/">visit our forums</a><br />


				<?php if (!isset($_SESSION['support_loadavg'])) { ?>
				<div class="left pull-left">
					Like LoadAvg ? <a href="http://www.loadavg.com/donate/" title="Make a donation, support LoadAvg">Please donate</a>
				</div>
				<?php } ?>

				<?php
				//page is not deifned for index and index is currently charts so show time here
				if (!isset($_GET['page']) ) { ?>
					HTML graphs generated in <?php echo $page_load; ?> sec.		
				<?php } ?>

				<!-- only check if check for updates is on -->
				<?php if ( ( LoadAvg::$_settings->general['checkforupdates'] == "true" ) && (isset($_SESSION['download_url'])) )  {  ?>
					<div class="right pull-right">
						<!--
						Update available <a href="<?php echo $_SESSION['download_url']; ?>" title="Download the new version of LoadAvg">click to download</a>
						-->
						Update available <a href="http://www.loadavg.com/download/" title="Download the new version of LoadAvg">click to download</a>
					</div>
				<?php } ?>
			</div>
			<?php } 

			//not logged in show this footer
			else { ?>

			<div class="well lh70-style-top margin-none center footer">
			<center>
				<a href="http://www.loadavg.com/">LoadAVG v <?php echo $settings['version']; ?></a> &copy;  <?php echo date("Y"); ?> Sputnik7 Ltd<br />
				For comments and suggestions please <a href="http://www.loadavg.com/forums/">visit our forums</a><br />
			</center>
			</div>

			<?php } ?>

		<!-- End Content -->
		</div>

		<!-- End Wrapper -->
		</div>
		
		
	</div>
	
	
	<!-- JQueryUI v1.11.1 -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/system/jquery-ui-1.11.1.custom/jquery-ui.min.js"></script>

	<!-- Javascript for Period -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/demo/period.js"></script>
	
	<!-- JQueryUI Touch Punch -->
	<!-- small hack that enables the use of touch events on sites using the jQuery UI user interface library -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/system/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js"></script>
	
	<!-- Colors -->
	<script>
	var primaryColor = '#4a8bc2';
	</script>
	
	<!--  Flot (Charts) JS -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/charts/flot/jquery.flot.js" type="text/javascript"></script>
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/charts/flot/jquery.flot.time.js" type="text/javascript"></script>
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/charts/flot/plugins/tooltip/jquery.flot.tooltip.min.js"></script>
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/plugins/charts/flot/jquery.flot.stack.min.js"></script>

	<!-- Bootstrap Script -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/bootstrap/js/bootstrap.min.js"></script>

	<!-- Bootstrap Toggle Buttons Script -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/bootstrap/extend/bootstrap-toggle-buttons/static/js/jquery.toggle.buttons.js"></script>

	<script>$(function () { $('.toggle-button').toggleButtons(); })</script>

	<!-- Common script -->
	<script src="<?php echo SCRIPT_ROOT ?>public/assets/theme/scripts/demo/common.js"></script>

<?php
//if they are flooding the login screen we sleep here
if ($flooding) {
 die;
    sleep(3);
}
?>

</body>
</html>
