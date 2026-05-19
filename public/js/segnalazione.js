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
            
            dipartimenti.forEach(dip => {
                const option = document.createElement('option');
                option.value = dip.id;
                option.textContent = dip.nome;
                selectDip.appendChild(option);
            });
        })
        .catch(error => console.error('Errore nel fetch:', error));
}

// Avvia la funzione quando la pagina è pronta
document.addEventListener('DOMContentLoaded', caricaDipartimenti);