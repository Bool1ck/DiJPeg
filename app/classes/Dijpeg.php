<?php

include_once 'DBase.php';

 class Dijpeg {
     private $GDObj = NULL;
	 private $id = NULL;
     private $errors = NULL;
     
     public function __construct() {
             if ($this->ifImageFromForm()) {
                 $this->newGDObjFromForm();
                 if ((self::getHeightGDObj($this->GDObj) < MAXHEIGHT) || (self::getWidthGDObj($this->GDObj) < MAXWIDTH)) {
                     $this->GDObj = $this->resizeToWidthGDobj($this->GDObj, WIDTH);
                     $this->saveTempJpg();

                 } else {
                     $this->errors[] = 'Oversize width/height';
                 }
              }
         self::clearOldTemp();
     }

     public function __destruct()
     {
         //ERRORS outer to file
         self::clearOldTemp();
     }

     /**
      * checks the upload form file type is image
      * @return bool (true if image)
      *
      */
     private function ifImageFromForm() {
		$file_type = $_FILES[IMAGE]['type']; // тип загружаемого файла
		$expensions = array("image/jpg","image/jpeg","image/png","image/gif","image/bmp");
        if (!in_array($file_type, $expensions)) {
            $this->errors[] = "File type mismatch";
            return FALSE;
        } else {
            return TRUE;
        }
     }

     /**
      * creates a graphic object from a picture as $GDObj
      */
     private function newGDObjFromForm() {
         $file_type = $_FILES[IMAGE]['type'];
         if ($file_type == 'image/png') {
             $this->GDObj = imagecreatefrompng($_FILES[IMAGE]['tmp_name']);
		 } elseif ($file_type == 'image/bmp') {
             $this->GDObj = imagecreatefrombmp($_FILES[IMAGE]['tmp_name']);
         } elseif ($file_type == 'image/gif') {
             $this->GDObj = imagecreatefromgif($_FILES[IMAGE]['tmp_name']);
         } elseif ($file_type == 'image/jpeg') {
             $this->GDObj = imagecreatefromjpeg($_FILES[IMAGE]['tmp_name']);
         }
     }

     /**
      * Get image width
      * @param $gdobj - GD object
      * @return false|int
      */
     private static function getWidthGDObj($gdobj) {
		 return imagesx($gdobj);
	 }

     /**
      * Get image hight
      * @param $gdobj - GD object
      * @return false|int
      */
	 private static function getHeightGDObj($gdobj) {
		 return imagesy($gdobj);
	 }

     /**
      * Resize image width to $width
      * @param $gdobj - GD object
      * @param $width - new width
      * @return false|resource
      */
     private static function resizeToWidthGDobj($gdobj, $width) {
         if (self::getWidthGDObj($gdobj) <> $width) {
			 $new_height = intval(self::getHeightGDObj($gdobj)*$width/self::getWidthGDObj($gdobj));
			 $tmpGdobj = imagecreatetruecolor($width, $new_height);
			 imagecopyresampled($tmpGdobj, $gdobj, 0, 0, 0, 0, $width, $new_height, self::getWidthGDObj($gdobj), self::getHeightGDObj($gdobj));
             return $tmpGdobj;
             } else {
             return $gdobj;
         }
	 }

     /**
      * Save GDobject to file in upload folder and add to tempfile table in DB
      */
     private function saveTempJpg() {
		 $this->addToTempDB();
         imagejpeg($this->GDObj, UPLOAD.$this->id.'_temp.jpg', 75);
         $this->GDObj = NULL;
         $dbh = DBase::dbh();
         $db = $dbh->prepare('UPDATE `tempfile` SET `file` = ? WHERE `id` = ?');
         $db->execute(array($this->id.'_temp.jpg',$this->id));
     }

     /**
      * Insert into tempfile table new line and assign property $id equal new
      * record id
      */
     private function addToTempDB() {
        $dbh = DBase::dbh();
        $db =$dbh->prepare('INSERT INTO `tempfile` SET `timestamp` = ?');
		$db->execute(array(time()));
        $this->id = $dbh->lastInsertId();
     }

     /**
      * @param $id - id tempfile from tempfile table in DB
      * @param $width - new width
      * @param $brightness - brightness for convert GD object
      * @param $contrast - contrast for convert GD object
      * @return pixel map of GD object
      */
	 public static function DijpegGDodj($id, $width, $brightness, $contrast) {
		$name = self::getTempFileNameOnId($id);
		$img = imagecreatefromjpeg('../../public/uploads/'.$name);
		imagefilter($img, IMG_FILTER_GRAYSCALE);
		imagefilter($img, IMG_FILTER_BRIGHTNESS, $brightness);
		imagefilter($img, IMG_FILTER_CONTRAST, $contrast);
        return self::getMapFromGDobj(self::resizeToWidthGDobj($img, $width));
	 }

     /**
      * @param $gdobj - GD object
      * @return pixel map of GD object
      */
	 private static function getMapFromGDobj($gdobj) {
		 $width = self::getWidthGDObj($gdobj);
		 $height = self::getHeightGDObj($gdobj);
		 for ($x = 0; $x < $width; $x++) {
			 for ($y = 0; $y < $height; $y++) {
				 $map[$y][$x] = imagecolorat($gdobj, $x, $y) & 0xFF;
			 }
		 }
		 return $map;
	 }

     /**
      *
      * Get id param
      */
     public function getTempId() {
		 return $this->id;
	 }

     /**
      * Get temp filename from id in tempfile table in DB
      * @param $tempid - id in tempfile table
      * @return filename
      */
     public static function getTempFileNameOnId($tempid) {
        $dbh = DBase::dbh();
        $db = $dbh->prepare('SELECT `file` FROM `tempfile` WHERE `id`=?');
        $db->execute(array($tempid));
        $myrow = $db->fetch();
        return $myrow['file'];
     }

     /**
      * @param $tempId
      * @param $width
      * @param $brightness
      * @param $contrast
      */
     public static function migrateToGallery($tempId, $width, $brightness, $contrast) {
         $tempfilename = self::getTempFileNameOnId($tempId);
		 $galeryId = self::addToGalleryDB();
		 $galeryfilename = $galeryId.'_original.jpg';
		 $galerythumbname = $galeryId.'_thumb.jpg';
		 self::moveFile($tempfilename,$galeryfilename);
		 self::createGalleryThombImg($galeryId);
         self::updateGalleryDB($galeryId, $galeryfilename, $galerythumbname, $width, $brightness, $contrast);
         self::dellTempBDid($tempId);
     }

     /**
      * adds file to gallery table
      * @return string -id of new record
      */
	 private static function addToGalleryDB() {
			$dbh = DBase::dbh();
			$db =$dbh->prepare('INSERT INTO `dijpeg` SET `original` = ""');
			$db->execute();
			return $dbh->lastInsertId();
     }

     /**
      * updates the parameters of a post in the gallery by its id
      * @param $galeryid
      * @param $filename
      * @param $thumbname
      * @param $width
      * @param $brightness
      * @param $contrast
      */
	 private static function updateGalleryDB($galeryid, $filename, $thumbname, $width, $brightness, $contrast) {
		 	$dbh = DBase::dbh();
			$db =$dbh->prepare('UPDATE `dijpeg` SET `original` = ?, `thumbnail` = ?, `width` = ?, `brightness` = ?, `contrast` = ? WHERE `id` = ?');
			$db->execute(array($filename, $thumbname, $width, $brightness, $contrast, $galeryid));
	 }

     /**
      * moves the image file from the temporary folder to the gallery folder
      * @param $fileFrom
      * @param $fileTo
      * @return bool (true is success)
      */
     private static function moveFile($fileFrom, $fileTo) {
         if (rename('../../public/uploads/'.$fileFrom,'../../public/galery/'.$fileTo)) {
			return TRUE; 
		 } else {
			 return FALSE;
		 }
     }

     /**
      * delete record from temporary base by id
      * @param $tempId
      */
     private static function dellTempBDid($tempId) {
		 $dbh = DBase::dbh();
         $db = $dbh->prepare('DELETE FROM `tempfile` WHERE `id` = ?');
		 $db->execute(array($tempId));
     }

     /**
      * creating a preview for a picture
      * @param $galeryid - id in galery DB
      */
     private static function createGalleryThombImg($galeryid) {
         $gd = self::createGDobjFromGalleryFile($galeryid);
		 $gd = self::resizeToWidthGDobj($gd,280); // width size to thumb
		 imagejpeg($gd, '../../public/galery/'.$galeryid.'_thumb.jpg', 75);
     }

     /**
      * Create GD object from galery file by id in DB
      * @param $galeryid - id in galery DB
      * @return false|resource GD object
      */
	 private static function createGDobjFromGalleryFile($galeryid) {
		 return imagecreatefromjpeg('../../public/galery/'.$galeryid.'_original.jpg');
	 }

     /**
      * deletes old files from temporary folder
      */
     private static function clearOldTemp() {
         $idlist = self::getListOldId();
         foreach ($idlist as $k=>$v) {
             if (unlink('../../public/uploads/'.$v['id'].'_temp.jpg')) {
                 self::dellTempBDid($v['id']);
             }
         }
     }

     /**
      * get list of old files from temp folder
      * @return array
      */
     private static function getListOldId() {
        $now = time() - 1800;
        $dbh = DBase::dbh();
        $db = $dbh->prepare('SELECT `id` FROM `tempfile` WHERE `timestamp` < ?');
        $db->execute(array($now));
        return $result = $db->fetchAll();
     }

     /**
      * get list of gallery files
      * @return array
      */
	 public static function getListGalleryId() {
		$dbh = DBase::dbh();
        $db = $dbh->prepare('SELECT `id` FROM `dijpeg` ORDER BY `id` DESC');
        $db->execute(array());
		return $result = $db->fetchAll(PDO::FETCH_COLUMN, 0);
	 }

     /**
      * get preview file name by id
      * @param $id
      * @return mixed
      */
	 public static function getGalleryThombImgName($id) {
		 if (is_numeric($id)) {
			$dbh = DBase::dbh();
			$db = $dbh->prepare('SELECT `thumbnail` FROM `dijpeg` WHERE `id` = ?');
			$db->execute(array($id));
			return $result = $db->fetch(PDO::FETCH_COLUMN, 0);
		 }
	 }

     /**
      * Get image pixmap by id
      * @param $id
      * @return pixel
      */
     public static function getMapFromGalleryFileById($id) {
        $dbh = DBase::dbh();
        $db = $dbh->prepare('SELECT * FROM `dijpeg` WHERE `id`=?');
        $db->execute(array($id));
		$result = $db->fetch();
        $obj = self::createGDobjFromGalleryFile($id);
		imagefilter($obj, IMG_FILTER_GRAYSCALE);
		imagefilter($obj, IMG_FILTER_BRIGHTNESS, $result['brightness']);
		imagefilter($obj, IMG_FILTER_CONTRAST, $result['contrast']);
        return self::getMapFromGDobj(self::resizeToWidthGDobj($obj, $result['width']));
     }

     /**
      * For debug
      * @param $arr
      */
	 static function debug($arr) {
    echo '<pre>'.print_r($arr, true).'</pre>';
	 }
	 
}