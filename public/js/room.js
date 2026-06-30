document.addEventListener("DOMContentLoaded", () => {
    const favoriteForms = document.querySelectorAll('.favorite-form');

    function apiUrl(path) {
        return `${window.UNIFIX_BASE_PATH || ''}${path}`;
    }

    // Funzione di supporto per aggiornare visivamente e semanticamente il bottone
    function setButtonState(form, button, isFavorite, roomId, roomName) {
        // Aggiorna lo stato nel DOM
        form.dataset.isFavorite = isFavorite ? 'true' : 'false';
        form.setAttribute('action', isFavorite ? apiUrl(`/api/favorites/${roomId}/remove`) : apiUrl(`/api/favorites/${roomId}/add`));

        // Aggiorna interfaccia e accessibilità
        if (isFavorite) {
            button.textContent = "Rimuovi dai preferiti";
            button.title = "Rimuovi dai preferiti";
            button.setAttribute('aria-label', `Rimuovi preferiti aula: ${roomName}`);
            button.classList.add('is-favorite'); // Applica la classe rossa del tuo CSS
        } else {
            button.textContent = "Aggiungi ai preferiti";
            button.title = "Aggiungi ai preferiti";
            button.setAttribute('aria-label', `Aggiungi preferiti aula: ${roomName}`);
            button.classList.remove('is-favorite');
        }
    }

    favoriteForms.forEach(form => {
        const roomId = form.dataset.roomId;
        const roomName = form.dataset.roomName;
        const button = form.querySelector('.btn-favorite');

        fetch(apiUrl(`/api/favorites/${roomId}/check`))
            .then(response => response.json())
            .then(data => {
                // Se riceviamo una risposta, aggiorniamo il bottone
                if (data.isFavorite !== undefined) {
                    setButtonState(form, button, data.isFavorite, roomId, roomName);
                }
            })
            .catch(err => console.error(`Errore nel check preferiti per aula ${roomId}:`, err));

        form.addEventListener('submit', async function(event) {
            event.preventDefault();
            
            const currentAction = this.getAttribute('action');
            const isCurrentlyFavorite = this.dataset.isFavorite === 'true';

            try {
                const response = await fetch(currentAction, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (response.ok) {
                    const data = await response.json();
                    
                    if (data.success) {
                        setButtonState(this, button, !isCurrentlyFavorite, roomId, roomName);
                    } else {
                        alert(data.error || "Impossibile aggiornare i preferiti.");
                    }
                } else {
                    alert("Si è verificato un errore sul server. Riprova più tardi.");
                }
            } catch (error) {
                alert("Errore di rete. Controlla la tua connessione.");
                console.error("Errore AJAX:", error);
            }
        });
    });
});
