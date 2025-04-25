<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DataResetController extends AbstractController
{
    #[Route('/truncate-data', name: 'truncate_data')]
    public function truncateData(EntityManagerInterface $entityManager): Response
    {
        $connection = $entityManager->getConnection();

        // Liste der Tabellen, die geleert werden sollen
        $tables = ['user', 'session', 'estimate', 'product_backlog_item', 'session_card'];

        // Deaktiviere referenzielle Integrität, um verknüpfte Tabellen zu leeren
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            $connection->executeQuery("TRUNCATE TABLE `$table`");
        }

        // Aktiviere referenzielle Integrität wieder
        $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1');

        return new Response('Daten wurden erfolgreich geleert.');
    }
}
