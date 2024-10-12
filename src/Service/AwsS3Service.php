<?php
namespace App\Service;

use Aws\Exception\AwsException;
use Aws\S3\S3Client;

class AwsS3Service
{
    private S3Client $s3Client;

    public function __construct(string $awsKey, string $awsSecret, string $awsEndpoint)
    {
        $this->s3Client = new S3Client([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'credentials' => [
                'key'    => $awsKey,
                'secret' => $awsSecret,
            ],
            'endpoint' => $awsEndpoint,
            'use_path_style_endpoint' => true,
        ]);
    }

    public function createBucket(string $bucketName)
    {

        try {
            $result = $this->s3Client->createBucket(['Bucket' => $bucketName]);
            return "Bucket created: " . $result['Location'];
        } catch (AwsException $e) {
            return $e->getMessage();
        }
    }

    public function checkOrCreateBucket(string $bucketName)
    {
        $buckets = $this->s3Client->listBuckets();

        foreach ($buckets['Buckets'] as $bucket) {
            if ($bucket['Name'] === $bucketName) {
                return "Bucket '$bucketName' exists.";
            }
        }

        try {
            $result = $this->s3Client->createBucket(['Bucket' => $bucketName]);
            return "Bucket created: " . $result['Location'];
        } catch (AwsException $e) {
            return "Error creating bucket: " . $e->getMessage();
        }
    }

    public function deleteBucket(string $bucketName)
    {
        $objects = $this->s3Client->listObjects(['Bucket' => $bucketName]);

        if (isset($objects['Contents']) && count($objects['Contents']) > 0) {
            foreach ($objects['Contents'] as $object) {
                $this->s3Client->deleteObject([
                    'Bucket' => $bucketName,
                    'Key'    => $object['Key'],
                ]);
            }
        }

        try {
            $this->s3Client->deleteBucket(['Bucket' => $bucketName]);
            return "Bucket '$bucketName' deleted successfully.";
        } catch (AwsException $e) {
            return "Error deleting bucket: " . $e->getMessage();
        }
    }

    public function uploadFile(string $bucketName, string $key, string $filePath)
    {
        
    
        try {
            /** @var \AWS\Result $result */
            $result = $this->s3Client->putObject([
                'Bucket' => $bucketName,
                'Key'    => $key,
                'SourceFile' => $filePath,
            ]);

            return  $result['ObjectURL'];
        } catch (AwsException $e) {
            return $e->getMessage();
        }
        
    }

    public function downloadFile(string $bucketName, string $key, string $savePath)
    {
        try {
            $result = $this->s3Client->getObject([
                'Bucket' => $bucketName,
                'Key'    => $key,
            ]);

            file_put_contents($savePath, $result['Body']);
            return "Datei erfolgreich heruntergeladen: $key";
        } catch (AwsException $e) {
            return $e->getMessage();
        }
    }

    public function listFiles(string $bucketName)
    {
        try {
            $result = $this->s3Client->listObjects([
                'Bucket' => $bucketName,
            ]);
    
            $files = [];
            if (isset($result['Contents'])) {
                foreach ($result['Contents'] as $object) {
                    $files[] = [
                        'key' => $object['Key'],
                        'url' => $this->s3Client->getObjectUrl($bucketName, $object['Key']),
                        'lastModified' => $object['LastModified'],
                    ];
                }
            }

            return $files;
        } catch (AwsException $e) {
            return $e->getMessage();
        }
    }

    public function deleteFile(string $bucketName, string $key)
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $bucketName,
                'Key'    => $key,
            ]);
            return "Datei erfolgreich gelöscht: $key";
        } catch (AwsException $e) {
            return "Fehler beim Löschen der Datei: " . $e->getMessage();
        }
    }

    public function listBuckets()
    {
        try {
            $result = $this->s3Client->listBuckets();
            return $result['Buckets'];
        } catch (AwsException $e) {
            throw new \RuntimeException('Error listing buckets: ' . $e->getMessage());
        }
    }
}