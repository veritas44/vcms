<?php
/*
This file is part of VCMS.

VCMS is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

VCMS is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with VCMS. If not, see <http://www.gnu.org/licenses/>.
*/

if(!is_object($libGlobal) || !$libAuth->isLoggedin())
	exit();


/*
* deletion
*/
if(isset($_REQUEST['aktion']) && $_REQUEST['aktion'] == 'delete'){
	if($libGallery->hasFotowartPrivilege($libAuth->getAemter())){
		if(isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])){
			$pictures = $libGallery->getPictures($_REQUEST['id'], 2);

			foreach($pictures as $picture){
				$libImage->deleteVeranstaltungsFoto($_REQUEST['id'], $picture);
			}

			$libGlobal->notificationTexts[] = 'Die Galerie wurde gelöscht.';
		}
	}
}


echo '<h1>Foto-Verwaltung</h1>';

echo $libString->getErrorBoxText();
echo $libString->getNotificationBoxText();

echo '<h2>Galerie anlegen</h2>';

echo '<form action="index.php?pid=event_admin_galerie" method="post" class="form-horizontal">';
echo '<fieldset>';

$libForm->printVeranstaltungDropDownBox('id', 'Veranstaltung', '', false);
$libForm->printSubmitButton('Galerie anlegen &frasl; bearbeiten');

echo '</fieldset>';
echo '</form>';


echo '<h2>Bestehende Galerien</h2>';

$veranstaltungsFotosDir = 'custom/veranstaltungsfotos';

$files = array_diff(scandir($veranstaltungsFotosDir), array('..', '.'));
$folders = array();

foreach ($files as $file){
	if(is_dir($veranstaltungsFotosDir. '/' .$file)){
		$folders[] = $file;
	}
}

rsort($folders);
reset($folders);

// semester selection
$stmt = $libDb->prepare("SELECT id, DATE_FORMAT(datum, '%Y-%m-01') AS datum FROM base_veranstaltung ORDER BY datum DESC");
$stmt->execute();

$daten = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	if(in_array($row['id'], $folders)){
		$daten[] = $row['datum'];
	}
}

echo $libTime->getSemesterMenu($libTime->getSemestersFromDates($daten), $libGlobal->semester);


//list events
echo '<table class="table table-condensed table-striped table-hover">';
echo '<thead>';
echo '<tr><th>Bild</th><th>Titel</th><th>Datum</th><th></th></tr>';
echo '</thead>';

$zeitraum = $libTime->getZeitraum($libGlobal->semester);

$stmt = $libDb->prepare('SELECT * FROM base_veranstaltung WHERE datum = :datum_equal OR (DATEDIFF(datum, :semester_start) >= 0 AND DATEDIFF(datum, :semester_ende) < 0) ORDER BY datum DESC');
$stmt->bindValue(':datum_equal', $zeitraum[0]);
$stmt->bindValue(':semester_start', $zeitraum[0]);
$stmt->bindValue(':semester_ende', $zeitraum[1]);
$stmt->execute();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	// is there a gallery for the event?
	if(in_array($row['id'], $folders)){
		echo '<tr>';
		echo '<td class="imgColumn">';

		//are there images?
		if($libGallery->hasPictures($row['id'], 2)){
			echo '<div class="thumbnail">';
			echo '<div class="thumbnailOverflow">';
			echo '<a href="index.php?pid=event_admin_galerie&amp;id=' .$row['id']. '">';
			echo '<img class="img-responsive center-block';

			//are there pooled images?
    		if($libGallery->getPictures($row['id'], 2) > $libGallery->getPictures($row['id'], 1)){
    			echo ' private';
    		}

    		echo '" src="inc.php?iid=event_picture&amp;eventid=' .$row['id']. '&amp;id=' .$libGallery->getFirstVisiblePictureId($row['id'], 2). '" alt="Foto" />';
    		echo '</a>';
    		echo '</div>';
    		echo '</div>';
		}

		echo '</td>';
		echo '<td>' .$row['titel']. '</td>';
		echo '<td>' .$row['datum']. '</td>';
		echo '<td class="toolColumn">';
		echo '<a href="index.php?pid=event_admin_galerie&amp;id=' .$row['id']. '">';
		echo '<i class="fa fa-cog" aria-hidden="true"></i>';
		echo '</td>';
		echo '</tr>';
	}
}

echo '</table>';


//galleries without events
$stmt = $libDb->prepare('SELECT id FROM base_veranstaltung');
$stmt->execute();

$ids = array();

while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
	$ids[] = $row['id'];
}

$foldersWithoutEvent = array();

foreach($folders as $folder){
	if(!in_array($folder, $ids)){
		$foldersWithoutEvent[] = $folder;
	}
}

if(count($foldersWithoutEvent) > 0){
	echo '<h2>Galerien ohne Veranstaltung</h2>';
	echo '<table>';
	echo '<tr><th style="width:10%">Bild</th><th style="width:10%">Ordner</th><th style="width:50%">Titel</th><th style="width:20%">Veranstaltungsdatum</th><th style="width:10%">Aktion</th></tr>';

	foreach($foldersWithoutEvent as $folder){
		echo '<tr>';
		echo '<td>';

		// are there images in the folder?
		if($libGallery->hasPictures($folder, 2)){
			echo '<div class="thumbnail">';
			echo '<div class="thumbnailOverflow">';
			echo '<img class="img-responsive center-block img-column';

			//pooled images?
    		if($libGallery->getPictures($folder, 2) > $libGallery->getPictures($folder, 1)){
    			echo ' private';
    		}

    		echo '" src="inc.php?iid=event_picture&amp;eventid='.$folder.'&amp;id=' .$libGallery->getFirstVisiblePictureId($folder, 2). '" alt="Foto" />';
    		echo '</div>';
    		echo '</div>';
		}

		echo '</td>';
		echo '<td>'.$folder.'</td>';
		echo '<td>unbekannt</td>';
		echo '<td>unbekannt</td>';
		echo '<td><a href="index.php?pid=event_admin_galerie&amp;id=' .$folder. '">bearbeiten</a></td>';
		echo '</tr>';
	}

	echo '</table>';
}