Essential Commands to Start after cloning the projekt
wsl -d Ubuntu
sudo docker-compose -f compose.yaml --env-file .env.dev build --no-cache
sudo docker-compose -f compose.yaml --env-file .env.dev up -d
sudo docker exec -it portfolio-app-1 /bin/bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
sudo docker-compose down

#'Important Commands'


#winpty docker exec -it portfolio-app-1 sh
#winpty docker exec -it portfolio-app-1 bash
#php bin/console doctrine:migrations:diff
#php bin/console doctrine:migrations:migrate
#php bin/console make:migration
#php bin/console make:entity
#php bin/console make:controller    
#php bin/console make:form
#php bin/console asset-map:compile
#php bin/console doctrine:fixtures:load
#php bin/console make:fixtures
#symfony console make:factory
#composer require symfony/apache-pack
#composer require orm-fixtures --dev
#composer require zenstruck/foundry --dev