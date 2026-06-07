# Guida ai Test (PHPUnit) per il Backend

##  1. Setup Rapido (Da fare solo la prima volta)
Apri il terminale nella root del progetto (`progetto_tecweb/`) e lancia:
    composer install

Scaricare strumento per verificare code coverage:
    sudo apt update
    sudo apt install php8.3-pcov

Vedere la coverage:
    ./vendor/bin/phpunit --coverage-html coverage-report

## 2. Lanciare i test
Dalla root del progetto, lancia il comando:
    ./vendor/bin/phpunit

