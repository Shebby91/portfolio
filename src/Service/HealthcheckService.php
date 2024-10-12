<?php

namespace App\Service;

use App\Service\AwsS3Service;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;

class HealthcheckService
{
    private AwsS3Service $awsS3Service;
    private EntityManagerInterface $entityManager;

    public function __construct(AwsS3Service $awsS3Service, EntityManagerInterface $entityManager)
    {
        $this->awsS3Service = $awsS3Service;
        $this->entityManager = $entityManager;
    }



    public function checkDatabaseConnection(): array
    {
        try {
            $connection = $this->entityManager->getConnection();
            $connection->executeQuery('SELECT 1');
            return ['status' => true, 'message' => 'Verbindung zur Datenbank erfolgreich hergestellt!'];
        } catch (ConnectionException $e) {
            return ['status' => false, 'message' => 'Fehler bei der Verbindung zur Datenbank: ' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()];
        }
    }

    public function checkS3Connection(): array
    {
        try {
            $buckets = $this->awsS3Service->listBuckets();
        
            return ['status' => true, 'message' => 'AWS S3 erreichbar: ' . implode(', ', array_column($buckets, 'Name'))];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Fehler bei der Verbindung zu AWS S3: ' . $e->getMessage()];
        }
    }

}