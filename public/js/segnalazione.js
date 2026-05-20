// Funzione per caricare i dipartimenti all'apertura della pagina
function caricaDipartimenti() {
    // 1. Fai la richiesta alla rotta del tuo router
    fetch('/api/segnalazione/dipartimenti') 
        .then(response => {
            // Verifica che la risposta sia OK (status 200)
            if (!response.ok) {
                throw new Error('Errore nella rete');
            }
            return response.json(); // Trasforma il JSON ricevuto in un array Javascript
        })
        .then(dipartimenti => {
            // 2. Qui hai l'array di dipartimenti! Puoi usarlo per popolare il select
            const selectDip = document.getElementById('dipartimento');
            if (!selectDip) {
                console.error('Elemento #dipartimento non trovato in caricaDipartimenti');
                return;
            }

            // Reset delle opzioni e aggiunta di un placeholder
            selectDip.innerHTML = '<option value="">Seleziona un dipartimento</option>';

            dipartimenti.forEach(dip => {
                const option = document.createElement('option');
                option.value = dip.id;
                option.textContent = dip.nome;
                selectDip.appendChild(option);
            });
        })
        .catch(error => console.error('Errore nel fetch:', error));
}

function caricaEdifici() {
    const selectDip = document.getElementById('dipartimento');
    if (!selectDip) {
        console.error('Elemento #dipartimento non trovato quando si richiedono edifici');
        return;
    }

    const dipartimentoId = selectDip.value; // Ottieni l'ID del dipartimento selezionato
    if (!dipartimentoId) {
        // Se non c'è un dipartimento selezionato, resetta il select degli edifici e non fare fetch
        const selectEdificio = document.getElementById('edificio');
        if (selectEdificio) selectEdificio.innerHTML = '<option value="">Seleziona un edificio</option>';
        return;
    }

    // Fai la richiesta per ottenere gli edifici associati al dipartimento selezionato
    fetch(`/api/segnalazione/edifici?dipartimentoId=${dipartimentoId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore nella rete');
            }
            return response.json();
        })
        .then(edifici => {
            const selectEdificio = document.getElementById('edificio');
            selectEdificio.innerHTML = '<option value="">Seleziona un edificio</option>'; // Resetta le opzioni

            edifici.forEach(edificio => {
                const option = document.createElement('option');
                option.value = edificio.id;
                option.textContent = edificio.nome;
                selectEdificio.appendChild(option);
            });
        })
        .catch(error => console.error('Errore nel fetch:', error));
}

// Avvia la funzione quando la pagina è pronta
document.addEventListener('DOMContentLoaded', () => {
    caricaDipartimenti();

    const selectDip = document.getElementById('dipartimento');
    if (selectDip) {
        selectDip.addEventListener('change', caricaEdifici);
    } else {
        console.error('Elemento #dipartimento non trovato al DOMContentLoaded');
    }
});