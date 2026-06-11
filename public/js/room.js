document.addEventListener('DOMContentLoaded', () => {
    const favoriteForms = document.querySelectorAll('.favorite-form');

    favoriteForms.forEach((form) => {
        const roomId = form.getAttribute('data-room-id');
        const button = form.querySelector('.btn-favorite');

        if (!roomId || !button) {
            return;
        }

        fetch(`/api/favorites/${roomId}/check`)
            .then((response) => response.json())
            .then((data) => {
                updateButtonState(form, button, data.isFavorite);
            })
            .catch((err) => console.error('Errore nel controllo preferiti:', err));

        form.addEventListener('submit', (event) => {
            event.preventDefault();

            const isFavorite = form.getAttribute('data-is-favorite') === 'true';
            const url = isFavorite ? `/api/favorites/${roomId}/remove` : `/api/favorites/${roomId}/add`;

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
            })
                .then((response) => {
                    if (response.ok) {
                        updateButtonState(form, button, !isFavorite);
                        return;
                    }

                    alert('Si è verificato un errore. Riprova.');
                })
                .catch((err) => console.error("Errore durante l'aggiornamento:", err));
        });
    });

    function updateButtonState(form, button, isFavorite) {
        form.setAttribute('data-is-favorite', String(isFavorite));

        if (isFavorite) {
            button.textContent = 'Rimuovi dai preferiti';
            button.setAttribute('title', 'Rimuovi dai preferiti');
            button.classList.add('is-favorite');
        } else {
            button.textContent = 'Aggiungi ai preferiti';
            button.setAttribute('title', 'Aggiungi ai preferiti');
            button.classList.remove('is-favorite');
        }
    }
});