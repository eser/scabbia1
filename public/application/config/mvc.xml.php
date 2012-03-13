<?xml version="1.0" encoding="utf-8" ?>
<!-- <?php exit(); ?> -->
<scabbia>
	<mvc>
		<routing
			defaultController="home" defaultAction="index"
			notfoundController="home" notfoundAction="notfound"
			controllerUrlKey="1" actionUrlKey="2" />

		<view filenames="{name}{device}{language}"
			/>
	</mvc>
</scabbia>