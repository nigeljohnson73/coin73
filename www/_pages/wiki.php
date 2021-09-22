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
if (file_exists ( $fn )) {
	$md = processSendableFile ( file_get_contents ( $fn ) );
	$html = MarkdownExtra::defaultTransform ( $md );
	echo $html;
} else {
	$fn = __DIR__ . "/" . str_replace ( ".php", "", basename ( __FILE__ ) ) . ".md";
	if (file_exists ( $fn )) {
		$md = processSendableFile ( file_get_contents ( $fn ) );
		$html = MarkdownExtra::defaultTransform ( $md );
		echo $html;
	} else {
		echo "<h1>No content here - yet</h1>";
	}

	$documented = [ ];
	$documented [] = "account_recovery.md";
	$documented [] = "account_validation.md";
	$documented [] = "api_job_request.md";
	$documented [] = "api_job_submit.md";
	$documented [] = "links.md";
	$documented [] = "mining_difficulty.md";
	$documented [] = "mining_rewards.md";
	$documented [] = "mining_script.md";
	
	$files = directoryListing ( __DIR__ . "/../_wiki", ".md" );
	$str = "";
	if ($files and count ( $files ) > 1) {
		foreach ( $files as $file ) {
			$file = basename ( $file );
			if (! in_array ( $file, $documented )) {
				list ( $file, $ext ) = explode ( ".", $file );
				if ($ext == "md") {
					if ($file != "home") {
						$wiki = "/wiki/" . str_replace ( "_", "/", $file );
						$text = ucfirst ( str_replace ( "_", " ", $file ) );
						$str .= "<li><a href='" . $wiki . "'>" . $text . "</a></li>\n";
					}
				}
			}
		}
	}
	if (strlen ( $str )) {
		echo "<h2>Unlisted pages</h2><ul>" . $str . "</ul>";
	}
}

?>
</div>
<?php include_once (__DIR__ . "/_footer.php")?>
