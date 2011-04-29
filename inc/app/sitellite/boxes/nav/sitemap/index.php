<?php
// resolved tickets:
// #194 site map.

// BEGIN KEEPOUT CHECKING
// Add these lines to the very top of any file you don't want people to
// be able to access directly.
if (! defined ('SAF_VERSION')) {
  header ('HTTP/1.1 404 Not Found');
  echo "<!DOCTYPE HTML PUBLIC \"-//IETF//DTD HTML 2.0//EN\">\n"
		. "<html><head>\n<title>404 Not Found</title>\n</head><body>\n<h1>Not Found</h1>\n"
		. "The requested URL " . $PHP_SELF . " was not found on this server.<p>\n<hr>\n"
		. $_SERVER['SERVER_SIGNATURE'] . "</body></html>";
  exit;
}
// END KEEPOUT CHECKING

loader_box ('sitellite/nav/init');

// import any object we need from the global namespace
global $page, $menu;

// box logic begins here

if ($parameters['recursive'] == 'no') {
	$recur = false;
} else {
	$recur = true;
}

//page_id ('sitemap');
if ($context == 'action') {
	page_title (intl_get ('Site Map'));
}

page_add_script (site_prefix () . '/js/jquery-1.3.2.min.js');
page_add_script (site_prefix () . '/js/jquery.cookie.js');
page_add_script (site_prefix () . '/js/jquery-treeview/jquery.treeview.min.js');
page_add_style (site_prefix () . '/js/jquery-treeview/jquery.treeview.css');
page_add_script ('<script type="text/javascript">

$(function () {
	$("#sitemap").treeview ({
		animated: "medium",
		control: "#sidetreecontrol",
		persist: "location"
	});
});

</script>');

page_add_style ('<style type="text/css">

ul#sitemap {
	list-style-type: none;
    list-style-image: none;
}
ul#sitemap>li>a {
	font-weight: bold;
	list-style-type: none;
    list-style-image: none;
}

</style>');
#Start: SEMIAS. #194 site map.
# ------------------------
# echo preg_replace ('/^<ul>/', '<ul id="sitemap" class="treeview-gray">', $menu->display ('html', '<a href="{site/prefix}/index/{id}">{title}</a>', $recur));
# ------------------------
echo preg_replace ('/^<ul>/', '<ul id="sitemap" class="treeview-gray treeview">', $menu->display ('html', '<a href="{site/prefix}/index/{id}">{title}</a>', $recur));
#END: SEMIAS.
?>