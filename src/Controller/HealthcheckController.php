<?php

namespace App\Controller;

use App\Service\AwsS3Service;
use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthcheckController extends AbstractController
{
    private AwsS3Service $awsS3Service;

    public function __construct(AwsS3Service $awsS3Service)
    {
        $this->awsS3Service = $awsS3Service;
    }

    #[Route('/healthcheck', name: 'app_healthcheck')]
    public function healthcheck(EntityManagerInterface $entityManager): Response
    {
        $dbConnection = $this->checkDatabaseConnection($entityManager);
        $s3Status = $this->checkS3Connection();
  
        return $this->render('healthcheck/healthcheck.html.twig', [
            'title' => 'Healthcheck',
            'connection' => $dbConnection,
            's3Status' => $s3Status,
        ]);
    }

    private function checkDatabaseConnection(EntityManagerInterface $entityManager): array
    {
        try {
            $connection = $entityManager->getConnection();
            $connection->executeQuery('SELECT 1');
            return ['status' => true, 'message' => 'Verbindung zur Datenbank erfolgreich hergestellt!'];
        } catch (ConnectionException $e) {
            return ['status' => false, 'message' => 'Fehler bei der Verbindung zur Datenbank: ' . $e->getMessage()];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()];
        }
    }

    private function checkS3Connection(): array
    {
        try {
            $buckets = $this->awsS3Service->listBuckets();
        
            return ['status' => true, 'message' => 'AWS S3 erreichbar: ' . implode(', ', array_column($buckets, 'Name'))];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Fehler bei der Verbindung zu AWS S3: ' . $e->getMessage()];
        }
    }

}