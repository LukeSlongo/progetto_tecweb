# Deploy automatico con GitHub Actions

Il deploy automatico pubblica l'app UniFix su TecWeb quando viene aggiornato `develop`.

## Workflow

- `CI`: parte sulle pull request verso `develop` e `main`, installa le dipendenze PHP e lancia PHPUnit.
- `Deploy TecWeb`: parte sui push verso `develop` e puo' essere lanciato anche manualmente da GitHub Actions.

## Secrets richiesti

Nel repository GitHub, aggiungere in `Settings -> Secrets and variables -> Actions`:

```text
TECWEB_USER=msanguin
TECWEB_SSH_PRIVATE_KEY=<chiave privata SSH per il deploy>
```

La chiave pubblica corrispondente deve essere presente in `~/.ssh/authorized_keys` sull'account TecWeb usato per il deploy.

## Layout server usato dal deploy

Il workflow mantiene il layout gia' testato manualmente:

```text
~/public_html/        file pubblici da public/
~/src/                codice applicativo
~/config.php          configurazione PHP
~/db_init/            schema SQL copiato per riferimento
~/unifix.ini          config segreta, non toccata dal deploy
```

Il deploy fallisce se `~/unifix.ini` non esiste, per evitare di pubblicare un'app senza configurazione DB/BASE_PATH.

## Backup

Prima di sovrascrivere i file, la action crea un backup:

```text
~/unifix_backup_YYYYmmdd_HHMMSS/
```

Il database non viene importato automaticamente: `db_init/init.sql` resta copiato sul server solo come riferimento.
