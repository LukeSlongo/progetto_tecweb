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
2. **ricerca aule**: ricerca testuale di un'aula tramite nome aula, il sistema mostra una lista di aule.
3. **visualizza lista aule**: Il sistema mostra una lista di aule con
    - nome aula
    - edificio aula
    - numero segnalazioni in stato aperto o in lavorazione
4. **visualizzazione dettaglio aula**: il sistema mostra il dettaglio dell'aula con i seguenti dati:
   - nome aula
   - edificio aula
   - indirizzo aula
   - lista segnalazioni aula (titolo, data apertura, stato segnalazione)
5. **creazione segnalazione**: compilazione del form per segnalare un nuovo guasto in un'aula specifica, inserendo:
    - selezione toggle edificio
    - selezione toggle aula
    - scrittura testuale titolo segnalazione
    - scrittura testuale descrizione segnalazione
  [nota: verranno caricati tutti gli edifici e le aule, ci sarà una toggle list che mostrerà tutti gli edifici, se seleziono un edificio la toggle list di aule mi mostrerà solo le aule di quell'edifico. Se invece seleziono un aula senza aver selezionato un edificio, mi verrà inserito automaticamente l'edifico di quell'aula. Questa logica sarà gestita con JS]
6. **eliminazione segnalazione**:
    1. l'utente elimina una segnalazione propria in stato aperto _(studente, tecnico, admin)_
    2. l'utente elimina una qualsiasi segnalazione _(admin)_
7. **visualizza dettaglio segnalazione**: seleziona una segnalazione dalla lista e il sistema mostra tutti i dettagli completi e visualizza azioni disponibili in base al suo stato
    - titolo segnalazione
    - edificio
    - aula
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
9. **Visualizza elemento lista preferiti**: ci si trova nella home; viene visualizzata un aula preferita con le seguenti info:
    - nome aula
    - edificio aula
    - stato aula (ok: nessuna segnalazione aperta/in lavorazione, guasta: esiste almeno una segnalazione attiva/in lavorazione)
10.  **Visualizza elemento lista segnalazioni proprie**: ci si trova nella home; viene visualizzata una segnalazione propria con le seguenti info:
    - titolo segnalazione
    - aula segnalazione
    - stato segnalazione
11. **Visualizzazione lista segnalazioni** _(solo tecnico e admin)_: accesso all'elenco di segnalazioni attive. Per ogni segnalazione mostra:
    - titolo segnalazione
    - aula
    - data apertura
    - stato

## Studente (Utente base)
1. **aggiunta di una aula ai preferiti**
2. **rimozione di una aula dai preferiti**


## Tecnico

1. **Presa in carico segnalazione**: auto assegnazone della segnalazione -> passa dallo stato aperto a in lavorazione
2. **Risoluzione segnalazione**: chiusura della segnalazione -> passa dallo stato in lavorazione a chiuso e viene aggiornato il campo "data chiusura"

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
1. aula - edificio
    - se elimino un edificio elimino le aule
    - se creo un aula deve esserci un id edificio valido
2. segnalazione - (aula, studente, tecnico)
    - una segnalazione non può esserci senza un id studente valido
    - una segnalazione non può esserci senza un id aula valida
    - se elimino un aula si eliminano le segnalazioni con quell'aula
    - se elimino uno studente si eliminano le segnalazioni con quello studente
    - se elimino un tecnico le segnalazioni con quell'id tecnico tornano in stato aperto e il campo tecnico_id di queste segnalazioni torna a NULL -> necessita trigger db
3. preferiti
    - un preferito non può esserci senza un id studente valido
    - un preferito non può esserci senza un id aula valida
    - se elimino un aula si eliminano i preferiti con quell'aula
    - se elimino uno studente si eliminano i preferiti con quello studente

## Vincoli unicità
1. utente
   - non possono esistere due utenti con lo stesso nome
2. aula
   - non possono esistere due aule dello stesso edificio con lo stesso nome
3. preferiti
   - un utente non può insere la stessa aula nei preferiti più di una volta

# Vincoli di controllo
1. data segnalazione
   - una data di segnalazione di chiusura non può mai essere antecedente a quella di apertura