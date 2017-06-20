<?php

	$target_path = 'uploads/profile_photos/';
	$public_url = "https://s3-us-west-2.amazonaws.com/elasticbeanstalk-us-west-2-928475162925/";

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Headers: *');

	// Include the SDK using the Composer autoloader
	require 'vendor/autoload.php';
	use Aws\S3\S3Client;
	use Aws\S3\Exception\S3Exception;

	include('image_validation.php'); // getExtension Method

	$message='';
	if($_SERVER['REQUEST_METHOD'] == "POST")
	{
		$name = $_FILES['file']['name'];
		$size = $_FILES['file']['size'];
		$tmp = $_FILES['file']['tmp_name'];
		$ext = getExtension($name);

		if(strlen($name) > 0)
		{
			// File format validation
			if(in_array($ext,$valid_formats))
			{
				// File size validation
				if($size<(1024*1024))
				{
					// correct orientation
					$exif = exif_read_data($tmp, 'IFDO', true);
					$orientation = $exif['IFD0']['Orientation'];;
					if($orientation != 0 || $ext == "") {
						$image = imagecreatefromstring(file_get_contents($tmp));
						switch($orientation) {
						case 8:
							$image = imagerotate($image,90,0);
							break;
						case 3:
							$image = imagerotate($image,180,0);
							break;
						case 6:
							$image = imagerotate($image,-90,0);
							break;
						}
						imagejpeg($image, $tmp);
						$ext = 'jpg';
					}

					include('config_s3.php');
					//Rename image name.
					$image_name_actual = time().".".$ext;

					try {
						$client->putObject(array(
						'Bucket'=>$bucket,
						'Key' =>  $target_path . $image_name_actual,
						'SourceFile' => $tmp,
						'StorageClass' => 'REDUCED_REDUNDANCY'
						));
						$message = "S3 Upload Successful.";
						// $s3file='http://'.$bucket.'.s3.amazonaws.com/'.$actual_image_name;
						$s3file = $public_url . $target_path . $image_name_actual;

						$res = (object) ['success' => true, 'photo_url' => $s3file];
 						exit(json_encode($res, JSON_UNESCAPED_SLASHES));

					} catch (S3Exception $e) {
						// Catch an S3 specific exception.
						echo $e->getMessage();
					}
				} 
				else
					$message = "Image size Max 1 MB";

			}
			else
				$message = "Invalid file, please upload image file. " . $ext . " not supported from: " . $name;
		}
		else
			$message = "Please select image file.";
		 
	}

	exit($message);

?>