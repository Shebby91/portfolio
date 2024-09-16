<?php

namespace App\Controller;

use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HealthcheckController extends AbstractController
{
    #[Route('/healthcheck', name: 'app_healthcheck')]
    public function healthcheck(EntityManagerInterface $entityManager): Response
    {
        try {
            // Führe eine einfache Abfrage aus, um die Verbindung zu testen
            $connection = $entityManager->getConnection();
            $connection->executeQuery('SELECT 1');

      
            return $this->render('healthcheck/healthcheck.html.twig', [
                'title' => 'Healthcheck',
                'connection' => true,
                'message' => 'Verbindung zur Datenbank erfolgreich hergestellt!',
            ]);
        } catch (ConnectionException $e) {
            return $this->render('healthcheck/healthcheck.html.twig', [
                'title' => 'Healthcheck',
                'connection' => false,
                'error' => 'Fehler bei der Verbindung zur Datenbank: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return $this->render('healthcheck/healthcheck.html.twig', [
                'title' => 'Healthcheck',
                'connection' => false,
                'error' => 'Ein Fehler ist aufgetreten: ' . $e->getMessage()
            ]);
        }

        return $this->render('healthcheck/healthcheck.html.twig', [
            'title' => 'Healthcheck',
            'connection' => false,
            'error' => 'Die Verbindung zur Datenbank konnte nicht hergestellt werden.'
        ]);
    }
}