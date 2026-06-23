document.addEventListener("DOMContentLoaded", () => {
    const deleteUserForms = document.querySelectorAll('.delete-user-form');

    deleteUserForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            
            const username = this.dataset.username;
            
            const isConfirmed = confirm(`Sei sicuro di voler eliminare l'utente ${username}?`);
            
            if (!isConfirmed) {
                event.preventDefault();
            }
        });
    });

});