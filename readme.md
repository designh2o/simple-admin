#Simple admin page

##Requirements
- PHP >= 7.0.0

##Instalation

- clone repository
- install composer


	composer istall
- restore datebase from dump file


	mysql -u username -p database_name < dump.sql
- copy file .env.example to .env and write local environment values
- configure web server. The /public directory contains the index.php file, which is the entry point for all requests entering

##Structure folder
- /app - main folder application
  - /Contracts - folder with interfaces
  - /Controllers
  - /Models
  - Application.php - main class
  - Pagination.php - class for page navigation
  - QueryBuilder.php - class for build and execute query to datebase
  - Request.php - class for handle requests
  - Router.php - class for build routes
- /public
  - /css
  - /fonts
  - /js
  - index.php - single point of entry
- /resources
  - /views - templates
    - /pages
      - /index.html.twig
    - layout.html.twig 
  - route.php - file with list routes
- .env - environment values