0. Project OverviewL:
This project leverages containerized services orchestrated via Docker to build a robust and scalable development environment for a Symfony web application. It includes various services for log management, email testing, AWS simulation, and data visualization, all interconnected within a Docker network.

Services
1. Symfony Application
Purpose: The core web application built with the Symfony framework. It processes user activities such as registrations, logins, and file uploads.
Logging: The application generates logs for events like successful user registrations and logins, which are forwarded to Logstash for further processing.
Email Handling: Email-based actions (e.g., confirmation emails) are simulated using Mailpit.
AWS Integration: The application interacts with LocalStack to simulate AWS S3 for file uploads.
2. Logstash
Purpose: Centralized log processing and forwarding.
Input: Logstash is configured to receive HTTP input on port 5044 with the JSON codec from the Symfony application.
Processing: It parses and processes logs, which are then forwarded to Elasticsearch for storage and indexing.
3. Elasticsearch
Purpose: Full-text search and analytics engine used to store and index logs.
Data Flow: Receives logs from Logstash, allowing for the fast retrieval and search of data.
Integration: Data stored in Elasticsearch is visualized through Kibana.
4. Kibana
Purpose: Visualization layer for Elasticsearch data.
Features: Allows real-time visualization and monitoring of logs such as user activity, login attempts, and registration success in interactive dashboards.
5. Mailpit
Purpose: A lightweight SMTP server used for email testing in development environments.
Usage: Catches and displays emails sent by the Symfony application, including registration and confirmation emails, without sending them to real addresses.
6. LocalStack
Purpose: A fully functional local AWS cloud stack used to simulate AWS services.
Integration: Simulates AWS S3, allowing the Symfony application to upload and manage files as if interacting with the real AWS infrastructure.
7. Symfony Application:
Sends logs of user activities (e.g., registrations, logins) to Logstash over HTTP.
Sends emails for testing purposes to Mailpit.
Uploads files to LocalStack simulating AWS S3.
8. Logstash:
Receives logs from Symfony via HTTP input on port 5044 and processes them.
Forwards the processed logs to Elasticsearch for storage.
9. Elasticsearch:
Stores all logs received from Logstash in an indexed format, making them searchable and easy to analyze.
10. Kibana:
Visualizes data from Elasticsearch, providing an interface to explore log data, such as login attempts and registration successes.
11. Mailpit:
Receives and displays email messages for testing purposes, capturing emails sent by Symfony to avoid real email dispatch during development.
12. LocalStack:
Provides an emulated AWS environment, enabling local file uploads to a simulated S3 bucket without interacting with live AWS services.
13. Workflow
A user registers or logs in via the Symfony application.
The application sends a log entry to Logstash.
Logstash processes the log entry and forwards it to Elasticsearch for indexing.
Elasticsearch stores the log entry, making it searchable.
Kibana visualizes the stored logs, enabling the monitoring of user activity and system events.
The Symfony application sends a registration confirmation email, which is captured by Mailpit for testing purposes.
When a file upload is initiated, the file is uploaded to a simulated S3 bucket in LocalStack, emulating AWS behavior.
14. Key Advantages
15. Modular Architecture: Each service runs in its own Docker container, ensuring flexibility and scalability.
16. Centralized Logging: Logs from the Symfony application are collected, processed, and visualized in real-time using Logstash, Elasticsearch, and Kibana.
17. Email Testing: Using Mailpit ensures that no real emails are sent, facilitating safe email testing in a development environment.
18. AWS Service Simulation: LocalStack enables the simulation of AWS services like S3, allowing for local development without incurring AWS costs.

19. Essential Commands

wsl -d Ubuntu

sudo docker-compose -f compose.yaml --env-file .env.dev build --no-cache

sudo docker-compose -f compose.yaml --env-file .env.dev up -d

sudo docker exec -it portfolio-app-1 /bin/bash

php bin/console make:migration

php bin/console doctrine:migrations:migrate

php bin/console doctrine:fixtures:load

sudo docker-compose down

curl -X POST http://logstash:5044 -H "Content-Type: application/json" -d '{"message": "Log message", "level": "info", "context": {"user": "test_user"}}'

20. Important Commands

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