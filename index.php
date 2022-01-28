<?php
    error_reporting(-1);
	include_once "app/classes/Dijpeg.php";

	define('UPLOAD','public/uploads/');
	define('GALERY','public/galery/');
	define('MAXWIDTH',4000);
	define('MAXHEIGHT',3000);
    define('WIDTH',800);
    define('IMAGE', 'image');

    if (isset($_FILES[IMAGE])) {
        $obj = new Dijpeg();
	}
?>

<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>DiJPeg - digitals your picture</title>
<link href="public/css/style.css" rel="stylesheet" type="text/css">
<script src="public/js/jquery-3.6.0.min.js" type="text/javascript"></script>
<script src="public/js/myscript.js" type="text/javascript"></script>
</head>
<body>
    <div class="body">
	<div class="header">
		<div class="headerwrapper">
			<div class="logo">DiJPeg - <em>digitals your picture</em></div>
		</div>
	</div>
	<div class="conteiner">
		<div class="center">
			<div class="left">
				<div class="lblock acenter upload">
                    <div class="head">
                        Upload picture
                    </div>
                    <div class="wrapper">
                        <form action="" method="post" enctype="multipart/form-data" name="submit">
                            <input type="file" name="image" class="w120"><br />
                            <input type="submit" name="sub" disabled>
                        </form>
                    </div>
                </div>
                <div class="lblock acenter convert">
                    <div class="head">
						Сonvert picture
                    </div>
					<div class="wrapper">
							<form action='' method='post' name='convert' id='<? echo (is_object($obj) ? $obj->getTempId() : '') ?>'>
                            <div class="set">
                                <div class="settop"><div class="setname">Width symbols :&nbsp;&nbsp;</div><div class="val">200</div></div>
                                <div class="setvolmin">1</div><div class="setrange"><input type="range" min="1" max="200" id="size" oninput="" value="200"></div><div class="setvolmax">200</div>
                            </div>
							<div class="set">
                                <div class="settop"><div class="setname">Contrast :&nbsp;&nbsp;</div><div class="val">0</div></div>
                                <div class="setvolmin">-255</div><div class="setrange"><input type="range" min="1" max="511" id="contrast" oninput="" value="256"></div><div class="setvolmax">255</div>
                            </div>
							<div class="set">
                                <div class="settop"><div class="setname">Brightness :&nbsp;&nbsp;</div><div class="val">0</div></div>
                                <div class="setvolmin">-255</div><div class="setrange"><input type="range" min="1" max="511" id="brightness" oninput="" value="256"></div><div class="setvolmax">255</div>
                            </div>
							<button type="submit">Сonvert</button>
						</form>
					</div>
                </div>
                <div class="lblock acenter photoalbum">
                    <div class="head">
                        Galery
                    </div>
                </div>
                 <div class="wp">
                    <div class="galery">
                        <?php
                        $list = Dijpeg::getListGalleryId();
                        if (!empty($list)) {
                            foreach ($list as $k=>$v) {?>
                                <a href=# class='a_thumb' id='<?=$v?>'><img src='<?=GALERY.Dijpeg::getGalleryThombImgName($v)?>' width='200px' class='thumb' style='border: #C4CEDA 1px solid'></a>
                            <?}
                        }
                        ?>
                    </div>
                </div>
            </div>
			<div class="data">
				<div class="imgfield">
					<?php
						if (isset($obj)) {?>
							<img src='<?=UPLOAD.$obj->getTempFileNameOnId($obj->getTempId())?>' class='img' id='<?=$obj->getTempId()?>'><br />
						<?}
					?>
				</div>
			</div>
		</div>
	</div>
    </div>
</body>
</html>