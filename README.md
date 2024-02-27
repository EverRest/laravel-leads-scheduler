
## Stack

- PHP ^8.2
- MYSQL 8
- Laravel 10
- NodeJS 18
- Express ^4
- Pupeteer ^22 

## Local Set UP

- cp .env.example .env
- docker-compose up -d
- docker-compose run app php artisan migrate:fresh
- docker-compose run app php artisan db:seed
- docker-compose run app php artisan schedule:work

## Server Set Up

- cp .env.example .env
- docker-compose up -d
- docker-compose run app php artisan migrate:fresh
- docker-compose run app php artisan db:seed
- crontab -e
- `* * * * * cd /path/to/your/project && docker-compose run app php artisan schedule:run >> /dev/null 2>&1`
- or
- `* * * * * docker-compose -f=/path/to/your/project/compose.yml run app php artisan schedule:run >> /dev/null 2>&1`
