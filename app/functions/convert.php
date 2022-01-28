<?php

include_once "../classes/Dijpeg.php";

if (isset($_POST['id'])) {

	$map = Dijpeg::DijpegGDodj($_POST['id'], $_POST['width'], $_POST['brightness'], $_POST['contrast']);
	echo "<div class='dijpeg'>";
	foreach ($map as $k => $v) {
		foreach ($v as $kk => $vv) {
            $vv = 20 - intdiv($vv,13);
			
			switch ($vv) {
				case 0:
					echo " ";
					break;
				case 1:
					echo "·";
					break;
				case 2:
					echo ":";
					break;
				case 3:
					echo "″";
					break;
				case 4:
					echo "^";
					break;
				case 5:
					echo "*";
					break;
				case 6:
					echo "¡";
					break;
				case 7:
					echo "¦";
					break;
				case 8:
					echo "|";
					break;
				case 9:
					echo ")";
					break;
				case 10:
					echo "«";
					break;
				case 11:
					echo "‴";
					break;
				case 12:
					echo "‼";
					break;
				case 13:
					echo "○";
					break;
				case 14:
					echo "§";
					break;
				case 15:
					echo "#";
					break;
				case 16:
					echo "%";
					break;
				case 17:
					echo "₡";
					break;
				case 18:
					echo "¶";
					break;
				case 19:
					echo "♣";
					break;
				case 20:
					echo "☻";
					break;
				
			}
		}
        echo '<br />';
	}
	echo '</div><br /><br /><form action="" method="post" name="save"><button type="submit" id="save">Add to Galery</button></form>';
}