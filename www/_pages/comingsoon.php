<?php include_once(__DIR__ . "/_header.php") ?>

<div class="container-fluid text-center" data-ng-controller="ComingSoonCtrl">
	<?php

	use Michelf\MarkdownExtra;

	$fn = __DIR__ . "/" . str_replace(".php", "", basename(__FILE__)) . ".md";
	if (file_exists($fn)) {
		$md = file_get_contents($fn);
		$html = MarkdownExtra::defaultTransform($md);
		echo $html;
	} else {
		echo "<h1>No content here - yet</h1>";
	}
	?>
</div>

<?php include_once(__DIR__ . "/_footer.php") ?>