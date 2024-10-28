<?php

namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'GenerateSbomCommand',
    description: 'Generates SBOM and uploads it to Dependency-Track',
)]
class GenerateSbomCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Generating SBOM...');
        exec('composer cyclonedx:make-sbom --output-format xml --output-file sbom.xml');

        // SBOM in Base64 kodieren
        $base64Bom = base64_encode(file_get_contents('sbom.xml'));

        // API-Client vorbereiten
        $client = new Client();

        $projectId = $_ENV['DEPENDENY_PROJECT_ID']; // Setze hier die Projekt-ID
        $apiKey = $_ENV['DEPENDENY_TRACK_API_KEY']; // Setze hier deinen API-Schlüssel

        // Hochladen der SBOM
        try {
            $response = $client->request('PUT', 'http://host.docker.internal:8081/api/v1/bom', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'X-API-Key' => $apiKey,
                ],
                'json' => [
                    'project' => $projectId,
                    'bom' => $base64Bom,
                ],
            ]);

            if ($response->getStatusCode() === 200) {
                $output->writeln('SBOM uploaded successfully.');
            } else {
                $output->writeln('Failed to upload SBOM: ' . $response->getBody()->getContents());
            }
        } catch (\Exception $e) {
            $output->writeln('Failed to upload SBOM: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}
