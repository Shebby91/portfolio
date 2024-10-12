Essential Commands to Start after cloning the projekt:

wsl -d Ubuntu

sudo docker-compose -f compose.yaml --env-file .env.dev build --no-cache

sudo docker-compose -f compose.yaml --env-file .env.dev up -d

sudo docker exec -it portfolio-app-1 /bin/bash

php bin/console make:migration

php bin/console doctrine:migrations:migrate

php bin/console doctrine:fixtures:load

sudo docker-compose down

curl -X POST http://logstash:5044 -H "Content-Type: application/json" -d '{"message": "Log message", "level": "info", "context": {"user": "test_user"}}'

'Important Commands'

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