<!--**********************************
            Footer start
        ***********************************-->
		<div class="footer">
			<div class="copyright">
				<p>Copyright © Designed &amp; Developed by <a href="https://villanovenasur.com/" target="_blank">Villa Novena Sur</a> <span class="current-year">2026</span></p>
			</div>
		</div>
		<!--**********************************
            Footer end
        ***********************************-->

		<!--**********************************
           Support ticket button start
        ***********************************-->

		<!--**********************************
           Support ticket button end
        ***********************************-->


	</div>
	<!--**********************************
        Main wrapper end
    ***********************************-->

	<!--**********************************
        Scripts
    ***********************************-->
	<!-- Required vendors -->
	<script src="<?= Url::asset('vendor/global/global.min.js') ?>"></script>
	<script src="<?= Url::asset('vendor/chart-js/chart.bundle.min.js') ?>"></script>
	<script src="<?= Url::asset('vendor/bootstrap-select/dist/js/bootstrap-select.min.js') ?>"></script>
	<script src="<?= Url::asset('vendor/apexchart/apexchart.js') ?>"></script>
	
	<!-- Dashboard 1 -->
	<script src="<?= Url::asset('js/dashboard/dashboard-1.js') ?>"></script>
	<script src="<?= Url::asset('vendor/draggable/draggable.js') ?>"></script>
	<script src="<?= Url::asset('vendor/swiper/js/swiper-bundle.min.js') ?>"></script>
	
	<script src="<?= Url::asset('vendor/datatables/js/jquery.dataTables.min.js') ?>"></script>
	<script src="<?= Url::asset('vendor/datatables/js/dataTables.buttons.min.js') ?>"></script>
	<script src="<?= Url::asset('vendor/datatables/js/buttons.html5.min.js') ?>"></script>
	<script src="<?= Url::asset('vendor/datatables/js/jszip.min.js') ?>"></script>
	<script src="<?= Url::asset('js/plugins-init/datatables.init.js') ?>"></script>
	
	<script src="<?= Url::asset('vendor/bootstrap-datetimepicker/js/moment.js') ?>"></script>
	<script src="<?= Url::asset('vendor/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js') ?>"></script>
	
	<!-- Vectormap -->
	<script src="<?= Url::asset('vendor/jqvmap/js/jquery.vmap.min.js') ?>"></script>
	<script src="<?= Url::asset('vendor/jqvmap/js/jquery.vmap.world.js') ?>"></script>
	<script src="<?= Url::asset('vendor/jqvmap/js/jquery.vmap.usa.js') ?>"></script>
	<script src="<?= Url::asset('js/custom.min.js') ?>"></script>
	<script src="<?= Url::asset('js/deznav-init.js') ?>"></script>
	<script src="<?= Url::asset('js/demo.js') ?>"></script>
	<script src="<?= Url::asset('js/styleSwitcher.js') ?>"></script>
	<script>
		jQuery(document).ready(function () {
			setTimeout(function () {
				dzSettingsOptions.version = 'dark';
				new dzSettings(dzSettingsOptions);

				setCookie('version', 'dark');
			}, 1500)
		});
	</script>
</body>

</html>