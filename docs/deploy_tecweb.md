# Deploy UniFix su TecWeb

Hosting: `tecweb.studenti.math.unipd.it`, Apache + PHP + MySQL senza Docker.

L'applicazione viene pubblicata sotto il sottopercorso dell'utente:

```sh
http://tecweb.studenti.math.unipd.it/NOME_UTENTE
```

Per l'account `msanguin`, `BASE_PATH` deve quindi essere `/msanguin`.

## Connessione

Aprire prima il tunnel dal jump host:

```sh
ssh msanguin@sshpaolotti.studenti.math.unipd.it -L8080:tecweb:80 -L8022:tecweb:22
```

Poi collegarsi al server web:

```sh
ssh msanguin@tecweb.studenti.math.unipd.it
```

## Configurazione applicativa

Sul server creare `~/unifix.ini`, fuori da `~/public_html`, partendo da `unifix.ini.example`.

Esempio:

```ini
DB_HOST = localhost
DB_USER = msanguin
DB_PASSWORD = "password_dal_file_pwd_db_2024-25.txt"
DB_NAME = msanguin
BASE_PATH = /msanguin
```

`config.php` legge prima le variabili d'ambiente, usate in locale da Docker, e poi `unifix.ini`. Il file reale `unifix.ini` non va versionato e non va messo nella webroot.

## Database

Importare manualmente lo schema nel database assegnato dall'ateneo:

```sh
mysql -h localhost -u msanguin -p msanguin < db_init/init.sql
```

La password e' quella indicata nel file `pwd_db_2024-25.txt` fornito dall'ateneo.

## File pubblici

La webroot TecWeb e' `~/public_html`. Il contenuto servito da Apache deve puntare al front controller `public/index.php` e includere `public/.htaccess`.

La riscrittura Apache attuale e' relativa alla directory in cui si trova `.htaccess`, quindi funziona anche in una sottocartella come `public_html/msanguin`.

## Verifica

Controllare:

```sh
http://tecweb.studenti.math.unipd.it/msanguin/login
http://tecweb.studenti.math.unipd.it/msanguin/rooms
```

Se i link tornano alla root del dominio invece che a `/msanguin`, verificare `BASE_PATH` in `~/unifix.ini`.
