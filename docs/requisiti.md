# Attori del sistema:
- studente: Naviga il sistema, cerca aule, salva le aule tra i preferiti e apre nuove segnalazioni di guasto.
- tecnico: prende in carico le segnalazioni e le risolve.
- amministratore: supervisiona gli utenti del sistema.
# Casi d'uso

## Utente non autenticato (Ospite)
1. **registrazione**: creazione di un nuovo account studente con *nome utente* e *password*
2. **login**: accesso al sistema tramite *nome utente* e *password*

## Utente autenticato (azioni comuni)
1. **logout**: uscire dal sistema tramite tasto logout/esci
2. **ricerca aule**: ricerca testuale di un'room tramite nome room, il sistema mostra una lista di aule.
3. **visualizza lista aule**: Il sistema mostra una lista di aule con
    - nome room
    - building room
    - numero segnalazioni in stato aperto o in lavorazione
4. **visualizzazione dettaglio room**: il sistema mostra il dettaglio dell'room con i seguenti dati:
   - nome room
   - building room
   - indirizzo room
   - lista segnalazioni room (titolo, data apertura, stato issue)
5. **creazione issue**: compilazione del form per segnalare un nuovo guasto in un'room specifica, inserendo:
    - selezione toggle building
    - selezione toggle room
    - scrittura testuale titolo issue
    - scrittura testuale descrizione issue
  [nota: verranno caricati tutti gli edifici e le aule, ci sarà una toggle list che mostrerà tutti gli edifici, se seleziono un building la toggle list di aule mi mostrerà solo le aule di quell'edifico. Se invece seleziono un room senza aver selezionato un building, mi verrà inserito automaticamente l'edifico di quell'room. Questa logica sarà gestita con JS. Ecco come funzionerà:
    1. Il Server (PHP) prepara il pacchetto: Il tuo Controller interroga il database, prende tutti gli edifici e tutte le aule, li trasforma in stringhe JSON e li "appiccica" come attributi invisibili (es. data-buildings="...") sul tag <form> nel tuo HTML.

    2. Il Browser (JS) legge il pacchetto: Quando la pagina si carica, il tuo file JavaScript cerca il form, legge quegli attributi data- e li ritrasforma in veri e propri array di oggetti JS.

    3. L'Interazione (Niente più server): Quando l'utente seleziona un building, JS filtra istantaneamente il suo array di aule (usando l'building_id) e aggiorna la seconda tendina in un millisecondo. Il server PHP ha già finito il suo lavoro da un pezzo e non viene più disturbato.
  
  ]
6. **eliminazione issue**:
    1. l'utente elimina una issue propria in stato aperto _(studente, tecnico, admin)_
    2. l'utente elimina una qualsiasi issue _(admin)_
7. **visualizza dettaglio issue**: seleziona una issue dalla lista e il sistema mostra tutti i dettagli completi e visualizza azioni disponibili in base al suo stato
    - titolo issue
    - building
    - room
    - data apertura
    - data chiusura (se presente)
    - stato
    - tecnico in carica (se presente)
    - _(solo per admin e tecnico)_ utente segnalatore
    - _(solo per tecnico)_ [azione disponibile]
8. **Visualizza HomePage**:
    - banner di ricerca aule _(studente, tecnico, admin)_
    - banner di genera segnalazioni _(studente, tecnico, admin)_
    - lista preferiti _(studente)_
    - lista segnalazioni proprie _(studente)_
9. **Visualizza elemento lista preferiti**: ci si trova nella home; viene visualizzata un room preferita con le seguenti info:
    - nome room
    - building room
    - stato room (ok: nessuna issue aperta/in lavorazione, guasta: esiste almeno una issue attiva/in lavorazione)
10.  **Visualizza elemento lista segnalazioni proprie**: ci si trova nella home; viene visualizzata una issue propria con le seguenti info:
    - titolo issue
    - room issue
    - stato issue
11. **Visualizzazione lista segnalazioni** _(solo tecnico e admin)_: accesso all'elenco di segnalazioni attive. Per ogni issue mostra:
    - titolo issue
    - room
    - data apertura
    - stato

## Studente (Utente base)
1. **aggiunta di una room ai preferiti**
2. **rimozione di una room dai preferiti**


## Tecnico

1. **Presa in carico issue**: auto assegnazone della issue -> passa dallo stato aperto a in lavorazione
2. **Risoluzione issue**: chiusura della issue -> passa dallo stato in lavorazione a chiuso e viene aggiornato il campo "data chiusura"

## Amministratore

1. **Visualizzazione lista utenti**: visualizzazione di una lista di tutti gli account del sistema con le seguenti informazioni:
    - nome utente
    - ruolo utente
    - azione utente [rimuovi utente]
2. **Rimozione utente**: rimozione di un utente selezionato


# Edge Cases
1. le entità (aule ed edifici) sono hardcoddate, non sono previste interfacce che gestiscano operazioni crud
2. i profili admin e tecnico sono creati a priori hardcoddati, non sono previste interfacce di modifica ruolo





# Informazioni generali

## Vincoli di integrità
1. room - building
    - se elimino un building elimino le aule
    - se creo un room deve esserci un id building valido
2. issue - (room, studente, tecnico)
    - una issue non può esserci senza un id studente valido
    - una issue non può esserci senza un id room valida
    - se elimino un room si eliminano le segnalazioni con quell'room
    - se elimino uno studente il campo studente_id delle segnalazioni va a NULL
    - se elimino un tecnico le segnalazioni in lavorazione con quell'id tecnico tornano in stato aperto e il campo tecnico_id di queste segnalazioni torna a NULL (quelle chiuse restano chiuse) -> necessita trigger db
3. preferiti
    - un preferito non può esserci senza un id studente valido
    - un preferito non può esserci senza un id room valida
    - se elimino un room si eliminano i preferiti con quell'room
    - se elimino uno studente si eliminano i preferiti con quello studente

## Vincoli unicità
1. utente
   - non possono esistere due utenti con lo stesso nome
2. room
   - non possono esistere due aule dello stesso building con lo stesso nome
3. preferiti
   - un utente non può insere la stessa room nei preferiti più di una volta

# Vincoli di controllo
1. data issue
   - una data di issue di chiusura non può mai essere antecedente a quella di apertura