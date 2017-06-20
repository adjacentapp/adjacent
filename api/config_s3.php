<?php

require 'vendor/autoload.php';
use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

// Bucket Name
$bucket="elasticbeanstalk-us-west-2-928475162925";

//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJPXKHZFBD2GJIDEQ');
// if (!defined('awsSecretKey')) define('awsSecretKey', $_SERVER['SAL_SECRET']);
if (!defined('awsSecretKey')) define('awsSecretKey', '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');

    // Set Amazon s3 credentials
      $client = S3Client::factory(
      array(
      'key'    => awsAccessKey,
      'secret' => awsSecretKey
       )
      );
?>