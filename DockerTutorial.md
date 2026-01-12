## 📋 Prerequisiti

Prima di iniziare, assicurarsi di avere installato:

- [Docker](https://docs.docker.com/get-docker/) (versione 20.10 o superiore)
- [Docker Compose](https://docs.docker.com/compose/install/) (versione 2.0 o superiore)

### Verifica installazione

```bash
docker --version
docker-compose --version
```

## 🛠️ Configurazione Iniziale

### 1. Struttura del Progetto

La struttura delle cartelle cartelle deve essere la seguente:

```
progetto/
├── docker-compose.yaml
└── src/
    ├──html
    ├──css
    ├──php
    ├──ecc ecc 
    ├──index.php
    └── db/
        └── init.sql (opzionale)
```

### 2. Configurazione Database

Per modificare il database, aprire il file `docker-compose.yaml` e modificare i parametri del database nella sezione `db`:

```yaml
environment:
  MARIADB_ROOT_PASSWORD: root      # Password amministratore
  MARIADB_DATABASE: miodb          # Nome del database
  MARIADB_USER: studente           # Tuo username
  MARIADB_PASSWORD: pass           # Tua password
```

## 🚀 Avvio del Server

### Prima volta

```bash
# Posizionarsi nella cartella del progetto
cd /percorso/del/progetto

# Avviare i container (scarica in automatico le immagini se necessario)
docker-compose up -d
```

Il primo avvio potrebbe richiedere alcuni minuti per scaricare le immagini Docker.

### Avvii successivi

```bash
docker-compose up -d
```

Oppure, basta schiacciare il tasto play accanto al container in Docker Desktop

### Verificare lo stato

```bash
# Controllare che i container siano attivi
docker-compose ps

# Visualizzare i log
docker-compose logs

# Visualizzare i log in tempo reale
docker-compose logs -f
```

## 🌐 Accesso ai Servizi

Dopo l'avvio, si può accedere a:

- **Sito Web**: [http://localhost](http://localhost) oppure [http://localhost/index.php](http://localhost/index.php) 
- **phpMyAdmin**: [http://localhost:8080](http://localhost:8080)

### Credenziali phpMyAdmin

- **Server**: `db`
- **Username**: quello configurato in `MARIADB_USER`
- **Password**: quella configurata in `MARIADB_PASSWORD`

## 💻 Sviluppo

### Modificare i file PHP

Tutti i file nella cartella `./src/` sono automaticamente sincronizzati con il server Apache. Basta modificarli e ricaricare la pagina nel browser.

### Connessione al Database da PHP

Esempio di connessione:

```php
<?php
$host = 'db';              // Nome del container (NON 'localhost')
$dbname = 'miodb';         // Nome del database
$username = 'studente';    // Username
$password = 'pass';        // Password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connessione riuscita!";
} catch(PDOException $e) {
    echo "Errore: " . $e->getMessage();
}
?>
```

## 🛑 Gestione del Server

### Fermare i container

```bash
docker-compose stop
```
Oppure, schiacciare il tasto stop accanto al container in Docker Desktop

### Riavviare i container

```bash
docker-compose restart
```

### Fermare e rimuovere i container

```bash
docker-compose down
```

### Fermare e rimuovere tutto (inclusi i dati del database)

```bash
# ⚠️ ATTENZIONE: Elimina tutti i dati del database!
docker-compose down -v
```

### Ricostruire i container (dopo modifiche al docker-compose.yaml)

```bash
docker-compose up -d --build
```

## 🐛 Risoluzione Problemi

### Porta già in uso

Se ricevi un errore come "port is already allocated":

```bash
# Ferma tutti i container
docker-compose down

# Cambia la porta nel docker-compose.yaml
# Ad esempio, cambia "80:80" in "8000:80"
```

### Container non si avvia

```bash
# Visualizza i log dettagliati
docker-compose logs db
docker-compose logs web

# Rimuovi tutto e ricomincia
docker-compose down -v
docker-compose up -d
```

### Errore di connessione al database

- Verificare che il nome host sia `db` e non `localhost`
- Controllare le credenziali nel `docker-compose.yaml`
- Aspettare qualche secondo dopo l'avvio (il database impiega tempo ad inizializzarsi)

## 📦 Caratteristiche Tecniche

- **PHP**: 8.4.11
- **Apache**: 2.4
- **MariaDB**: 11.8.3
- **phpMyAdmin**: Latest

## 📝 Note 

- I dati del database sono persistenti grazie ai Docker volumes
- Le modifiche ai file PHP sono immediate (no riavvio necessario)
- Il database è accessibile solo dall'interno della rete Docker