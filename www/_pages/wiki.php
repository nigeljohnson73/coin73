<?php include_once (__DIR__ . "/_header.php") ?>
<div class="container-fluid text-center wiki">
<?php
use Michelf\MarkdownExtra;

$fn = __DIR__ . "/../_wiki/" . strtolower(@$args ["page"]);
if(isset($args["sub_page"])) {
	$fn .= "_".strtolower($args["sub_page"]);
}
if(isset($args["sub_sub_page"])) {
	$fn .= "_".strtolower($args["sub_page"]);
}
if(isset($args["sub_sub_sub_page"])) {
	$fn .= "_".strtolower($args["sub_page"]);
}
$fn .= ".md";

if (! file_exists ( $fn )) {
	$fn = __DIR__ . "/../_wiki/home.md";
}

$md = processSendableFile(file_get_contents ( $fn ));
$html = MarkdownExtra::defaultTransform ( $md );
echo $html;

$mt = filemtime ( $fn );
$t = date ( "Y/m/d H:i:s", $mt );

echo "<p class='updated'>This page was last updated on ".$t."</p>";

?>
</div>
<?php include_once (__DIR__ . "/_footer.php")?>
