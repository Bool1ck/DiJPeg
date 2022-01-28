<?php

include_once "../classes/Dijpeg.php";

if (isset($_POST['id'])) {
	Dijpeg::migrateToGallery($_POST['id'], $_POST['width'], $_POST['brightness'], $_POST['contrast']);
}