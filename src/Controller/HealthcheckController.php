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

            return new Response('Verbindung zur Datenbank erfolgreich hergestellt!');
        } catch (ConnectionException $e) {
            // Datenbankverbindung fehlgeschlagen
            return new Response('Fehler bei der Verbindung zur Datenbank: ' . $e->getMessage());
        } catch (\Exception $e) {
            // Andere Fehler abfangen
            return new Response('Ein Fehler ist aufgetreten: ' . $e->getMessage());
        }

        return new Response('Die Verbindung zur Datenbank konnte nicht hergestellt werden.');
    }
}