document.addEventListener("DOMContentLoaded", () => {
    
    // logica dettaglio form
    const form = document.getElementById('issue-form');
    
    if (form) {
        const buildingSelect = document.getElementById('building_id');
        const roomSelect = document.getElementById('room_id');

        const allOptGroups = Array.from(roomSelect.querySelectorAll('optgroup'));

        if (buildingSelect.value !== "") {
            buildingSelect.dispatchEvent(new Event('change'));
        }

        buildingSelect.addEventListener('change', (e) => {
            const selectedBuildingId = e.target.value;
            const currentSelectedRoom = roomSelect.value; 

            roomSelect.innerHTML = '<option value="">Seleziona</option>';

            if (selectedBuildingId === "") {
                allOptGroups.forEach(optgroup => {
                    roomSelect.appendChild(optgroup.cloneNode(true));
                });
            } else {
                const matchingGroup = allOptGroups.find(group => group.dataset.buildingId === selectedBuildingId);
                if (matchingGroup) {
                    roomSelect.appendChild(matchingGroup.cloneNode(true));
                }
            }

            if (currentSelectedRoom) {
                roomSelect.value = currentSelectedRoom;
                if (roomSelect.value === "") { 
                    roomSelect.value = "";
                }
            }
        });

        roomSelect.addEventListener('change', (e) => {
            const selectedRoomId = e.target.value;
            if (selectedRoomId === "") return;

            let targetBuildingId = null;
            for (const optgroup of allOptGroups) {
                const option = optgroup.querySelector(`option[value="${selectedRoomId}"]`);
                if (option) {
                    targetBuildingId = optgroup.dataset.buildingId;
                    break;
                }
            }

            if (targetBuildingId) {
                buildingSelect.value = targetBuildingId;
                const event = new Event('change');
                buildingSelect.dispatchEvent(event);
                roomSelect.value = selectedRoomId;
            }
        });
    }

   // logica dettaglio segnalazione

    const deleteForms = document.querySelectorAll('.delete-issue-form');
    deleteForms.forEach(function (deleteForm) {
        deleteForm.addEventListener('submit', function (event) {
            if (!confirm('Vuoi eliminare definitivamente questa segnalazione?')) {
                event.preventDefault(); // Ferma l'invio
            }
        });
    });

    const closeForms = document.querySelectorAll('.close-issue-form');
    closeForms.forEach(function (closeForm) {
        closeForm.addEventListener('submit', function (event) {
            if (!confirm('Confermi di aver risolto il problema e voler chiudere la segnalazione?')) {
                event.preventDefault();
            }
        });
    });
});