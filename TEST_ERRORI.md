# Guida Test Gestione Errori

## 🚀 Avvio Rapido

1. **Avvia lo script di test:**
   ```
   http://localhost/php/test_errori.php
   ```

2. **Visualizza i log in tempo reale:**
   ```bash
   docker-compose logs -f web
   ```

---

## 📝 Scenari di Test Dettagliati

### 1. ERRORI DI CONNESSIONE DATABASE

#### Test: Database non disponibile
```bash
# Ferma il database
docker-compose stop db

# Prova ad accedere a qualsiasi pagina
# Risultato atteso: Redirect a /500.php con messaggio generico
# Log atteso: Dettagli completi dell'errore PDO

# Riavvia
docker-compose start db
```

**Cosa verificare:**
- [X] Messaggio utente generico (no dettagli SQL)
- [ ] Log contiene errore completo con file e riga
- [X] Redirect automatico a 500.php

---

### 2. ERRORI DI AUTENTICAZIONE

#### Test 2.1: Login con credenziali errate
```
URL: /php/pages/login.php
1. Username: "utente_inesistente" / Password: "qualsiasi"
2. Username corretto / Password errata
```
**Risultato atteso:** "Errore: Credenziali non corrette."

#### Test 2.2: Accesso non autorizzato
```
1. Logout (se loggato)
2. Prova ad accedere a: /php/pages/profilo.php
```
**Risultato atteso:** Redirect automatico a login.php

#### Test 2.3: Utente normale tenta accesso admin
```
1. Login come utente normale
2. Accedi a: /php/pages/profilo_admin.php
```
**Risultato atteso:** Redirect a profilo.php

---

### 3. ERRORI DI REGISTRAZIONE

#### Test 3.1: Username duplicato
```sql
-- Prima crea un utente di test
INSERT INTO utenti (username, password, ruolo) 
VALUES ('test_duplicato', '$2y$10$hashedpassword', 'user');
```
```
URL: /php/pages/registrazione.php
Username: "test_duplicato"
Password: "Password123"
Conferma: "Password123"
```
**Risultato atteso:** "Errore: Username già esistente!"
**Verifica:** Username rimane compilato nel form

#### Test 3.2: Password non coincidono
```
Username: "nuovo_utente"
Password: "Password123"
Conferma: "Password456"
```
**Risultato atteso:** "Errore: Le password non coincidono."

---

### 4. ERRORI REGISTRAZIONE DONATORE

#### Test 4.1: Età non valida (< 18 anni)
```
Data nascita: 2020-01-01 (meno di 18 anni fa)
```
**Risultato atteso:** "Errore: Devi avere almeno 18 anni..."
**Verifica:** Dati del form preservati in sessione

#### Test 4.2: Età non valida (> 60 anni)
```
Data nascita: 1960-01-01 (più di 60 anni fa)
```
**Risultato atteso:** "Errore: ...non più di 60 anni..."

#### Test 4.3: Peso insufficiente
```
Peso: 45 kg
```
**Risultato atteso:** "Errore: Il peso minimo per donare è 50 Kg."

#### Test 4.4: Codice Fiscale errato - Cognome
```
CF: XYZXYZ00A01H501Z
Nome: Mario
Cognome: Rossi
Data: 2000-01-01
Sesso: Maschio
```
**Risultato atteso:** Messaggio specifico sul cognome non corrispondente

#### Test 4.5: CF duplicato
```sql
-- Prima inserisci un donatore
INSERT INTO donatori (user_id, codice_fiscale, ...) 
VALUES (1, 'RSSMRA80A01H501Z', ...);
```
Poi tenta registrazione con stesso CF.
**Risultato atteso:** "Errore: Il codice fiscale è già registrato."

---

### 5. ERRORI PRENOTAZIONE

#### Test 5.1: Data nel passato
```
URL: /php/pages/dona_ora.php (dopo login)
Data: 2020-01-01
```
**Risultato atteso:** "Errore: Non puoi prenotare in una data passata!"

#### Test 5.2: Campi vuoti (validazione server-side)
```javascript
// Disabilita JavaScript nella console
document.querySelector('form').noValidate = true;
// Invia form vuoto
```
**Risultato atteso:** "Errore: Compila tutti i campi obbligatori."

#### Test 5.3: Doppia prenotazione stesso giorno
```
1. Prenota per: 2026-03-15 ore 09:00
2. Tenta di prenotare per: 2026-03-15 ore 10:00
```
**Risultato atteso:** "Errore: Esiste già una prenotazione per l'utente in questa data!"

#### Test 5.4: Intervallo minimo non rispettato
```
1. Prenota per: 2026-03-01 (utente maschio)
2. Tenta di prenotare per: 2026-05-01 (solo 2 mesi dopo)
```
**Risultato atteso:** "Errore: La data scelta non rispetta l'intervallo di 3 mesi..."

#### Test 5.5: Fascia oraria piena
```sql
-- Riempi una fascia oraria
INSERT INTO lista_prenotazioni (user_id, sede_id, data_prenotazione, ora_prenotazione, tipo_donazione)
VALUES 
(1, 1, '2026-03-15', '09:00:00', 'Sangue intero'),
(2, 1, '2026-03-15', '09:00:00', 'Sangue intero');
```
Poi tenta prenotazione stessa fascia.
**Risultato atteso:** "Errore: La fascia oraria selezionata è già completa."

#### Test 5.6: ID non validi (SQL injection test)
```
POST /php/actions/prenota.php
luogo: "abc' OR '1'='1"
```
**Risultato atteso:** "Errore: Sede non valida."

---

### 6. ERRORI MODIFICA ACCOUNT

#### Test 6.1: Password attuale errata
```
URL: /php/pages/modifica_account.php
Password attuale: "password_sbagliata"
```
**Risultato atteso:** "Errore: La password attuale inserita non è corretta."

#### Test 6.2: Nuove password non coincidono
```
Password attuale: [corretta]
Nuova password: "NewPass123"
Conferma: "NewPass456"
```
**Risultato atteso:** "Errore: Le nuove password non coincidono."

#### Test 6.3: Username già in uso
```
Password attuale: [corretta]
Nuovo username: [username di un altro utente esistente]
```
**Risultato atteso:** "Errore: Lo username scelto è già in uso."

---

### 7. ERRORI UPLOAD FILE

#### Test 7.1: File troppo grande
```bash
# Crea un file > 5MB
dd if=/dev/zero of=test_large.jpg bs=1M count=6

# Prova ad uploadare da /php/pages/profilo.php
```
**Risultato atteso:** JSON `{"success": false, "message": "File troppo grande (Max 5MB)"}`

#### Test 7.2: Formato non valido
```bash
# Crea un PDF e rinominalo .jpg
echo "fake image" > test.pdf
mv test.pdf test.jpg

# Prova ad uploadare
```
**Risultato atteso:** `{"success": false, "message": "Formato non valido (solo JPG/PNG)"}`

#### Test 7.3: Permessi cartella negati
```bash
# Nel container Docker
docker exec -it [container_name] bash
chmod 000 /var/www/html/images/profili
```
Prova ad uploadare.
**Risultato atteso:** "Errore spostamento file. Permessi cartella?"

**Ripristina:**
```bash
chmod 755 /var/www/html/images/profili
```

---

### 8. ERRORI AJAX

#### Test 8.1: Parametri mancanti
```javascript
// Console browser
fetch('/php/ajax/get_orari_disponibili.php?sede_id=1')
  .then(r => r.json())
  .then(console.log);
```
**Risultato atteso:** `{"error": "Parametri mancanti"}`

#### Test 8.2: ID non valido
```javascript
fetch('/php/ajax/get_orari_disponibili.php?sede_id=abc&data=2024-01-01')
  .then(r => r.json())
  .then(console.log);
```
**Risultato atteso:** `{"error": "Sede non valida"}`

#### Test 8.3: Data non valida
```javascript
fetch('/php/ajax/get_giorni_pieni.php?sede_id=1')
  .then(r => r.json())
  .then(console.log);

fetch('/php/ajax/get_orari_disponibili.php?sede_id=1&data=invalid')
  .then(r => r.json())
  .then(console.log);
```
**Risultato atteso:** `{"error": "Data non valida"}`

---

### 9. ERRORI CANCELLAZIONE

#### Test 9.1: Cancella prenotazione non propria
```sql
-- Trova ID di prenotazione di altro utente
SELECT id FROM lista_prenotazioni WHERE user_id != [tuo_user_id] LIMIT 1;
```
```javascript
// Console browser (loggato come utente normale)
let formData = new FormData();
formData.append('id_prenotazione', '[id_altro_utente]');
fetch('/php/actions/cancellaPrenotazione.php', {
  method: 'POST',
  body: formData
}).then(() => location.reload());
```
**Risultato atteso:** "Errore: Impossibile trovare la prenotazione."

#### Test 9.2: Cancellazione profilo completa
```
1. Login
2. Vai a /php/pages/profilo.php
3. Click "Elimina account"
```
**Verifica:**
- [ ] Redirect a login.php
- [ ] Messaggio "Account eliminato con successo"
- [ ] Record eliminato da `utenti` e `donatori`
- [ ] Sessione distrutta

---

### 10. ERRORI TEMPLATE/FILESYSTEM

#### Test 10.1: Template mancante
```php
// Modifica temporaneamente un file PHP
caricaTemplate('file_inesistente_xyz.html');
```
**Risultato atteso:** 
- Redirect a 500.php
- Log: "Template non trovato: file_inesistente_xyz.html"

---

## 🔍 Comandi Utili per Debugging

### Visualizza log in tempo reale
```bash
# Tutti i log
docker-compose logs -f

# Solo web server
docker-compose logs -f web

# Ultimi 100 righe
docker-compose logs --tail=100 web
```

### Controlla log errori PHP nel container
```bash
docker exec -it [container_name] tail -f /var/log/apache2/error.log
```

### Resetta database di test
```bash
docker-compose down -v
docker-compose up -d
```

### Verifica permessi cartelle
```bash
docker exec -it [container_name] ls -la /var/www/html/images/profili
```

---

## ✅ Checklist Finale

Dopo aver eseguito tutti i test, verifica:

### Sicurezza
- [ ] Nessun errore SQL mostrato all'utente
- [ ] Nessun path di sistema rivelato
- [ ] Tutti gli errori loggati con dettagli completi
- [ ] Validazioni server-side funzionanti anche senza JS

### User Experience
- [ ] Messaggi di errore chiari e in italiano
- [ ] Colori appropriati (rosso errori, verde successi)
- [ ] Campi form preservati dopo errori (dove previsto)
- [ ] Redirect corretti dopo ogni operazione

### Funzionalità
- [ ] Tutti i try-catch presenti e funzionanti
- [ ] Validazioni input (date, orari, ID) operative
- [ ] Gestione errori AJAX con JSON corretto
- [ ] Pagine 404.php e 500.php visualizzate correttamente

### Logging
- [ ] Tutti gli errori critici loggati
- [ ] Log include timestamp, messaggio e contesto
- [ ] File di log accessibile e leggibile

---

## 🐛 Problemi Comuni

### "Call to undefined function logError()"
**Causa:** utility.php non incluso  
**Soluzione:** Aggiungi `require_once '../utility.php';` all'inizio del file

### Log non viene scritto
**Causa:** Permessi insufficienti  
**Soluzione:** 
```bash
docker exec -it [container] chmod 777 /var/log/apache2
```

### Redirect infinito
**Causa:** Errore in db.php crea loop  
**Soluzione:** Controlla che il database sia online e accessibile

---

## 📞 Supporto

Se un test fallisce inaspettatamente:
1. Controlla i log del server
2. Verifica che il database sia accessibile
3. Controlla i permessi delle cartelle
4. Usa `var_dump()` per debug temporaneo (rimuovi dopo!)
