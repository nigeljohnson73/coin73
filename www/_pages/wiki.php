<?php include_once (__DIR__ . "/_header.php") ?>
<div class="container-fluid text-center wiki">
<?php
use Michelf\MarkdownExtra;

$fn = __DIR__ . "/../_wiki/" . strtolower ( @$args ["page"] );
if (isset ( $args ["sub_page"] )) {
	$fn .= "_" . strtolower ( $args ["sub_page"] );
}
if (isset ( $args ["sub_sub_page"] )) {
	$fn .= "_" . strtolower ( $args ["sub_sub_page"] );
}
if (isset ( $args ["sub_sub_sub_page"] )) {
	$fn .= "_" . strtolower ( $args ["sub_sub_sub_page"] );
}
$fn .= ".md";

$show_index = false;
// $show_index = strtolower(@$args ["page"]) == "home.md";
if (! file_exists ( $fn )) {
	$show_index = true;
	$fn = __DIR__ . "/../_wiki/home.md";
}

$md = processSendableFile ( file_get_contents ( $fn ) );
$html = MarkdownExtra::defaultTransform ( $md );
$is_api_doc = strpos ( basename ( $fn ), "api_" ) === 0;
if ($is_api_doc) {
	echo "<div class='text-start'>";
}
echo $html;
if ($is_api_doc) {
	echo "</div>";
}

$mt = 0;

if ($show_index) {
	$files = directoryListing ( __DIR__ . "/../_wiki", ".md" );
	if ($files and count ( $files ) > 1) {
		echo "<p>";
		foreach ( $files as $file ) {
			$file = basename ( $file );
			list ( $file, $ext ) = explode ( ".", $file );
			if ($ext == "md") {
				if ($file != "home") {
					$wiki = "/wiki/" . str_replace ( "_", "/", $file );
					$text = ucfirst ( str_replace ( "_", " ", $file ) );
					echo "<a href='" . $wiki . "'>" . $text . "</a><br />\n";
				}
			}
		}
		echo "</p>";
		$mt = newestFile ( __DIR__ . "/../_wiki" ) [0];
	}
} else {
	$mt = filemtime ( $fn );
}

// This doesn't work on the server :(
// $t = date ( "Y/m/d H:i:s", $mt );
// echo "<p class='updated'>This page was last updated on " . $t . "</p>";

?>
</div>
<?php include_once (__DIR__ . "/_footer.php")?>
