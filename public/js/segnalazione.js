// Funzione per caricare i dipartimenti all'apertura della pagina
function caricaDipartimenti() {
    // 1. Fai la richiesta alla rotta del tuo router
    fetch('/api/issue/dipartimenti') 
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
        const selectBuilding = document.getElementById('building');
        if (selectBuilding) selectBuilding.innerHTML = '<option value="">Seleziona un building</option>';
        return;
    }

    // Fai la richiesta per ottenere gli edifici associati al dipartimento selezionato
    fetch(`/api/issue/edifici?dipartimentoId=${dipartimentoId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore nella rete');
            }
            return response.json();
        })
        .then(edifici => {
            const selectBuilding = document.getElementById('building');
            selectBuilding.innerHTML = '<option value="">Seleziona un building</option>'; // Resetta le opzioni

            edifici.forEach(building => {
                const option = document.createElement('option');
                option.value = building.id;
                option.textContent = building.nome;
                selectBuilding.appendChild(option);
            });
        })
        .catch(error => console.error('Errore nel fetch:', error));
}

function caricaAule() {

    const selectBuilding = document.getElementById('building');
    if (!selectBuilding) {
        console.error('Elemento #building non trovato quando si richiedono aule');
        return;
    }

    const buildingId = selectBuilding.value;
    if (!buildingId) {
        const selectRoom = document.getElementById('room');
        if (selectRoom) selectRoom.innerHTML = '<option value="">Seleziona un room</option>';
        return;
    }

    fetch(`/api/issue/aule?buildingId=${buildingId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Errore nella rete');
            }
            return response.json();
        })
        .then(aule => {
            const selectRoom = document.getElementById('room');
            selectRoom.innerHTML = '<option value="">Seleziona un room</option>';

            aule.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id;
                option.textContent = room.nome;
                selectRoom.appendChild(option);
            });
        })
        .catch(error => console.error('Errore nel fetch:', error));
}



// Avvia la funzione quando la pagina è pronta
document.addEventListener('DOMContentLoaded', () => {
    caricaDipartimenti();

    const selectDip = document.getElementById('dipartimento');
    const selectBuilding = document.getElementById('building');

    if (selectDip && selectBuilding) {
        selectDip.addEventListener('change', caricaEdifici);
        selectDip.addEventListener('change', caricaAule);
        selectBuilding.addEventListener('change', caricaAule);
    } else {
        console.error('Elemento #dipartimento o #building non trovato al DOMContentLoaded');
    }

});