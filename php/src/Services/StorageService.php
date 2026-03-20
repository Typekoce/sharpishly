<?php
use Aws\S3\S3Client;

public function uploadToCloud($localFilePath, $fileName) {
    $s3 = new S3Client([
        'version' => 'latest',
        'region'  => 'us-west-004', // Backblaze Region
        'endpoint' => 'https://s3.us-west-004.backblazeb2.com',
        'credentials' => [
            'key'    => 'YOUR_B2_KEY_ID',
            'secret' => 'YOUR_B2_APPLICATION_KEY',
        ],
    ]);

    $s3->putObject([
        'Bucket' => 'sharpishly-assets',
        'Key'    => "uploads/$fileName",
        'SourceFile' => $localFilePath,
    ]);
}