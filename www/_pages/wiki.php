<?php include_once (__DIR__ . "/_header.php") ?>
<div class="container-fluid wiki">
<?php
use Michelf\MarkdownExtra;

$fn = __DIR__ . "/../_wiki/" . @$args ["page"] . ".md";
if (! file_exists ( $fn )) {
	$fn = __DIR__ . "/../_wiki/home.md";
}

$md = file_get_contents ( $fn );
$html = MarkdownExtra::defaultTransform ( $md );
echo $html;
?>
</div>
<?php include_once (__DIR__ . "/_footer.php")?>
