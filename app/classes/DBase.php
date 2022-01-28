<?php

 class DBase {
     
     public static function dbh() {
         
         try {
             $dbh = new PDO('mysql:dbname=dijpeg;host=localhost', 'dijpeg', 'dijpeg');
         } catch (PDOException $e) {
             die($e->getMessage());
         }
         return $dbh;
     }
 }

?>