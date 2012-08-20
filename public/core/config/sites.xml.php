<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<applicationList>
		<application name="interesd" path="{base}application/" development="1" runExtensions="1">
			<bindList>
				<bind port="80" /> <!-- host="localhost" -->
				<bind port="443" secure="1" />
			</bindList>
		</application>

		<application
			name="blog"
			path="{base}blog/"
			port="81"
			secureport="444"
			development="1"
			runExtensions="1"
		/>  <!-- host="localhost" -->
	</applicationList>
</scabbia>