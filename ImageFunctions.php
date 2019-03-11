<?php

class Lib_ImageFunctions
{

	var $gdavailable;
	
	function __construct()
	{
		$this->gdavailable=(extension_loaded('GD') ? 1 : 0);			
	}
	
	function reduceImage($file,$filewidth,$fileheight,$file_path)
	{
		if($this->gdavailable)
		{
			$image_type=array('gif'=>1,'jpg'=>2,'png'=>3,'bmp'=>4);
			$uploadedfile = $file;
			list($width,$height,$type)=getimagesize($uploadedfile);

				switch($type)
				{
					case 1: $src = imagecreatefromgif($uploadedfile); break;
					case 2: $src = imagecreatefromjpeg($uploadedfile); break;
					case 3: $src = imagecreatefrompng($uploadedfile); break;
					case 4: $src = imagecreatefromwbmp($uploadedfile); break;					
				}

				// For our purposes, I have resized the image to be  600 pixels wide, and maintain the original aspect
				// ratio. This prevents the image from being "stretched"  or "squashed". If you prefer some max width other than
				// 600, simply change the $newwidth variable

				$newwidth=$filewidth;
				$newheight=$fileheight;//($height/$width)*$size;
				$tmp=imagecreatetruecolor($newwidth,$newheight)or die("Cannot Initialize new GD image stream");

				// this line actually does the image resizing, copying from the original image into the $tmp image
				imagecopyresampled($tmp,$src,0,0,0,0,$newwidth,$newheight,$width,$height);

				// now write the resized image to disk. I have assumed that you want the
				// resized, uploaded image file to reside in the ./images subdirectory.
				//$file_path = "upload_images/".date('Ymdhs').$_FILES[$file]['name'];
				switch($type)
				{
					case $image_type['gif']: $ret=imagegif($tmp,$file_path,100); break;
					case $image_type['jpg']: $ret=imagejpeg($tmp,$file_path,100); break;
					case $image_type['png']: $ret=imagepng($tmp,$file_path); break;
					case $image_type['bmp']: $ret = imagewbmp($tmp,$file_path); break;
				}
				imagedestroy($src);
				imagedestroy($tmp);
			}
				return true;
		}	

}



?>