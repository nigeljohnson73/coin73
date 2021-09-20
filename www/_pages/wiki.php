<?php include_once (__DIR__ . "/_header.php") ?>
<div class="container-fluid text-start wiki">
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
echo $html;

if ($show_index) {
	$files = directoryListing ( __DIR__ . "/../_wiki", ".md" );
	if ($files and count ( $files ) > 1) {
		echo "<ul>";
		foreach ( $files as $file ) {
			$file = basename ( $file );
			list ( $file, $ext ) = explode ( ".", $file );
			if ($ext == "md") {
				if ($file != "home") {
					$wiki = "/wiki/" . str_replace ( "_", "/", $file );
					$text = ucfirst ( str_replace ( "_", " ", $file ) );
					echo "<li><a href='" . $wiki . "'>" . $text . "</a></li>\n";
				}
			}
		}
		echo "</ul>";
	}
}

?>
</div>
<?php include_once (__DIR__ . "/_footer.php")?>
