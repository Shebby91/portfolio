_________________________________________
EN
_________________________________________
0. Project Overview:
This project leverages containerized services orchestrated via Docker to build a robust and scalable development environment for a Symfony web application. It includes various services for log management, email testing, AWS simulation, and data visualization, all interconnected within a Docker network.
_________________________________________
Services
1. Symfony Application
Purpose: The core web application built with the Symfony framework. It processes user activities such as registrations, logins, and file uploads.
Logging: The application generates logs for events like successful user registrations and logins, which are forwarded to Logstash for further processing.
Email Handling: Email-based actions (e.g., confirmation emails) are simulated using Mailpit.
AWS Integration: The application interacts with LocalStack to simulate AWS S3 for file uploads.
_________________________________________
2. Logstash
Purpose: Centralized log processing and forwarding.
Input: Logstash is configured to receive HTTP input on port 5044 with the JSON codec from the Symfony application.
Processing: It parses and processes logs, which are then forwarded to Elasticsearch for storage and indexing.
_________________________________________
3. Elasticsearch
Purpose: Full-text search and analytics engine used to store and index logs.
Data Flow: Receives logs from Logstash, allowing for the fast retrieval and search of data.
Integration: Data stored in Elasticsearch is visualized through Kibana.
_________________________________________
4. Kibana
Purpose: Visualization layer for Elasticsearch data.
Features: Allows real-time visualization and monitoring of logs such as user activity, login attempts, and registration success in interactive dashboards.
_________________________________________
5. Mailpit
Purpose: A lightweight SMTP server used for email testing in development environments.
Usage: Catches and displays emails sent by the Symfony application, including registration and confirmation emails, without sending them to real addresses.
_________________________________________
6. LocalStack
Purpose: A fully functional local AWS cloud stack used to simulate AWS services.
Integration: Simulates AWS S3, allowing the Symfony application to upload and manage files as if interacting with the real AWS infrastructure.
_________________________________________
DE
_________________________________________
0. Projektübersicht:
Dieses Projekt nutzt containerisierte Dienste, die über Docker orchestriert werden, um eine robuste und skalierbare Entwicklungsumgebung für eine Symfony-Webanwendung zu schaffen. Es umfasst verschiedene Dienste für das Log-Management, das Testen von E-Mails, die Simulation von AWS-Diensten und die Datenvisualisierung, die alle innerhalb eines Docker-Netzwerks miteinander verbunden sind.
_________________________________________
1. Symfony-Anwendung
Zweck: Die zentrale Webanwendung, die mit dem Symfony-Framework erstellt wurde. Sie verarbeitet Benutzeraktivitäten wie Registrierungen, Logins und Dateiuploads.
Logging: Die Anwendung generiert Protokolle für Ereignisse wie erfolgreiche Benutzerregistrierungen und Logins, die an Logstash zur weiteren Verarbeitung weitergeleitet werden.
E-Mail-Verarbeitung: E-Mail-basierte Aktionen (z. B. Bestätigungs-E-Mails) werden mit Mailpit simuliert.
AWS-Integration: Die Anwendung interagiert mit LocalStack, um AWS S3 für Dateiuploads zu simulieren.
_________________________________________
2. Logstash
Zweck: Zentrale Logverarbeitung und -weiterleitung.
Eingang: Logstash ist so konfiguriert, dass es HTTP-Eingaben über Port 5044 mit dem JSON-Codec von der Symfony-Anwendung empfängt.
Verarbeitung: Es analysiert und verarbeitet Protokolle, die dann an Elasticsearch zur Speicherung und Indizierung weitergeleitet werden.
_________________________________________
3. Elasticsearch
Zweck: Volltext-Such- und Analyse-Engine zur Speicherung und Indizierung von Protokollen.
Datenfluss: Empfängt Protokolle von Logstash, was eine schnelle Abfrage und Suche von Daten ermöglicht.
Integration: Die in Elasticsearch gespeicherten Daten werden durch Kibana visualisiert.
_________________________________________
4. Kibana
Zweck: Visualisierungsschicht für Elasticsearch-Daten.
Funktionen: Ermöglicht die Echtzeitvisualisierung und Überwachung von Protokollen wie Benutzeraktivität, Anmeldeversuche und Registrierungserfolge in interaktiven Dashboards.
_________________________________________
5. Mailpit
Zweck: Ein leichter SMTP-Server, der zum Testen von E-Mails in Entwicklungsumgebungen verwendet wird.
Nutzung: Fängt E-Mails ab und zeigt sie an, die von der Symfony-Anwendung gesendet werden, einschließlich Registrierungs- und Bestätigungs-E-Mails, ohne sie an echte Adressen zu senden.
_________________________________________
6. LocalStack
Zweck: Ein voll funktionsfähiger lokaler AWS-Cloud-Stack, der zur Simulation von AWS-Diensten verwendet wird.
Integration: Simuliert AWS S3, sodass die Symfony-Anwendung Dateien hochladen und verwalten kann, als ob sie mit der echten AWS-Infrastruktur interagieren würde.
_________________________________________
7. Essential Commands

    wsl -d Ubuntu

    sudo docker-compose -f compose.yaml --env-file .env.dev build --no-cache

    sudo docker-compose -f compose.yaml --env-file .env.dev up -d

    sudo docker exec -it portfolio-app-1 /bin/bash

    php bin/console make:migration

    php bin/console doctrine:migrations:migrate

    php bin/console doctrine:fixtures:load

    sudo docker-compose down
_________________________________________
8. Important Commands

    curl -X POST "your_url" -H "Content-Type: application/json" -d '{"message": "Log message", "level": "info", "context": {"user": "test_user"}}'

    sudo chown -R $(whoami):$(whoami) var/cache var/logs

    #php bin/console make:migration

    #php bin/console make:entity

    #php bin/console make:controller    

    #php bin/console make:form

    #php bin/console asset-map:compile

    #php bin/console make:fixtures

    #php bin/console make:factory

    php bin/console messenger:consume async

    php bin/console messenger:failed:show