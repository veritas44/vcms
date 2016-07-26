<!DOCTYPE html>
<html lang="de">
<?php
$pageTitle = '';
$pageCanonicalUrl = '';
$pageOgUrl = '';

if($libGlobal->pid == $libConfig->defaultHome){
	$pageTitle = $libConfig->verbindungName;
	$pageCanonicalUrl = 'http://' .$libConfig->sitePath. '/';
	$pageOgUrl = 'http://' .$libConfig->sitePath. '/';
} else {
	$pageTitle = $libConfig->verbindungName. ' - ' .$libGlobal->page->getTitle();
	$pageCanonicalUrl = 'http://' .$libConfig->sitePath. '/index.php?pid=' .$libGlobal->pid;
	$pageOgUrl = 'http://' .$libConfig->sitePath. '/';

	if($libGlobal->page->getPid() == 'semesterprogramm_event'
			&& isset($_REQUEST['eventid']) && is_numeric($_REQUEST['eventid'])){
		$pageCanonicalUrl .= '&amp;eventid=' .$_REQUEST['eventid'];
		$pageOgUrl .= 'index.php?pid=' .$libGlobal->pid. '&amp;eventid=' .$_REQUEST['eventid'];

		$stmt = $libDb->prepare("SELECT titel, datum FROM base_veranstaltung WHERE id=:id");
		$stmt->bindValue(':id', $_REQUEST['eventid'], PDO::PARAM_INT);
		$stmt->execute();
		$event = $stmt->fetch(PDO::FETCH_ASSOC);

		if($event['titel'] != ''){
			$pageTitle = $libConfig->verbindungName. ' - ' .$event['titel']. ' am ' .$libTime->formatDateString($event['datum']);
		}

		unset($event);
		unset($stmt);
	}
}

echo '  <head prefix="og: http://ogp.me/ns# fb: http://ogp.me/ns/fb# business: http://ogp.me/ns/business#">' . PHP_EOL;
echo '    <meta charset="utf-8" />' . PHP_EOL;
echo '    <meta http-equiv="X-UA-Compatible" content="IE=edge" />' . PHP_EOL;
echo '    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">' . PHP_EOL;
echo '    <title>' .$pageTitle. '</title>' . PHP_EOL;
echo '    <meta name="description" content="' .$libConfig->seiteBeschreibung. '" />' . PHP_EOL;
echo '    <meta name="keywords" content="' .$libConfig->seiteKeywords. '" />' . PHP_EOL;

/*
* stylesheets
*/
echo '    <link rel="stylesheet" href="vendor/bootstrap/css/bootstrap.min.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="vendor/font-awesome/css/font-awesome.min.css">' . PHP_EOL;
echo '    <link rel="stylesheet" href="styles/bootstrap-override.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="styles/screen.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="styles/calendar/calendar.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="styles/gallery/gallery.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="styles/navigation/navigation.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="styles/person/person.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="styles/timeline/timeline.css" />' . PHP_EOL;
echo '    <link rel="stylesheet" href="custom/styles/screen.css" />' . PHP_EOL;

/*
* scripts
*/
echo '    <script src="vendor/jquery/jquery.min.js"></script>' . PHP_EOL;
echo '    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>' . PHP_EOL;
echo '    <script src="styles/gallery/modal.js"></script>' . PHP_EOL;
echo '    <script src="styles/screen.js"></script>' . PHP_EOL;

if(is_array($libGlobal->module->getHeaderStrings())){
	foreach($libGlobal->module->getHeaderStrings() as $headerString){
		echo '    ' .$headerString. PHP_EOL;
	}
}

if($libGenericStorage->loadValue('base_core', 'showTrauerflor')){
	echo '    <style type="text/css">' . PHP_EOL;
	echo '      #container:before {' . PHP_EOL;
	echo '        content:url("data:image/svg+xml;utf8,<svg xmlns:svg=\'http://www.w3.org/2000/svg\' xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 100 100\' height=\'150\' width=\'150\'><path d=\'M 0,-25 125,100\' style=\'stroke:%23000;stroke-width:25\' /></svg>");' . PHP_EOL;
	echo '        position:absolute;' . PHP_EOL;
	echo '        right:0;' . PHP_EOL;
	echo '        top:0;' . PHP_EOL;
	echo '      }' . PHP_EOL;
	echo '    </style>' . PHP_EOL;
}

/*
* robots
*/
if($libGlobal->page->hasAccessRestriction()){
	echo '    <meta name="robots" content="noindex, nofollow, noarchive" />' . PHP_EOL;
} else {
	echo '    <meta name="robots" content="index, follow, noarchive" />' . PHP_EOL;
}

echo '    <link rel="canonical" href="' .$pageCanonicalUrl. '"/>' . PHP_EOL;

/*
* Opengraph / Facebook meta data
*/
if($libGenericStorage->loadValue('base_core', 'fbAppId')){
	echo '    <meta property="fb:app_id" content="' .$libGenericStorage->loadValue('base_core', 'fbAppId'). '"/>' . PHP_EOL;
}

echo '    <meta property="og:type" content="business.business"/>' . PHP_EOL;
echo '    <meta property="og:url" content="' .$pageOgUrl. '"/>' . PHP_EOL;
echo '    <meta property="og:title" content="' .$pageTitle. '"/>' . PHP_EOL;
echo '    <meta property="og:image" content="http://' .$libConfig->sitePath. '/custom/styles/og_image.jpg"/>' . PHP_EOL;
echo '    <meta property="og:image:type" content="image/jpeg" />' . PHP_EOL;
echo '    <meta property="og:image:height" content="265"/>' . PHP_EOL;
echo '    <meta property="og:image:width" content="265"/>' . PHP_EOL;
echo '    <meta property="og:site_name" content="' .$libConfig->sitePath. '"/>' . PHP_EOL;
echo '    <meta property="og:description" content="' .$libConfig->seiteBeschreibung. '"/>' . PHP_EOL;
echo '    <meta property="business:contact_data:street_address" content="' .$libConfig->verbindungStrasse. '"/>' . PHP_EOL;
echo '    <meta property="business:contact_data:locality" content="' .$libConfig->verbindungOrt. '"/>' . PHP_EOL;
echo '    <meta property="business:contact_data:postal_code" content="' .$libConfig->verbindungPlz. '"/>' . PHP_EOL;
echo '    <meta property="business:contact_data:country_name" content="' .$libConfig->verbindungLand. '"/>' . PHP_EOL;
echo '  </head>' . PHP_EOL;
echo '  <body>' . PHP_EOL;
echo '    <div id="container" class="container">' . PHP_EOL;

$libMenuRenderer = new \vcms\LibMenuRenderer($libAuth);
echo $libMenuRenderer->getMenuHtml($libMenuInternet, $libMenuIntranet, $libMenuAdministration, $libGlobal->pid, $libAuth->getGruppe(), $libAuth->getAemter());

echo '      <div class="row">' . PHP_EOL;
echo '        <div class="col-md-12">' . PHP_EOL;
echo '          <main id="content">' . PHP_EOL;