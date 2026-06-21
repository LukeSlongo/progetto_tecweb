document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('issue-form');
    if (!form) return;

    const buildingSelect = document.getElementById('building_id');
    const roomSelect = document.getElementById('room_id');

    const allOptGroups = Array.from(roomSelect.querySelectorAll('optgroup'));

    // CASO A: l'utente seleziona prima un edificio
    buildingSelect.addEventListener('change', (e) => {
        const selectedBuildingId = e.target.value;
        const currentSelectedRoom = roomSelect.value; // Salva la stanza attualmente selezionata

        // Svuota la tendina mantenendo solo l'opzione "Seleziona" di base
        roomSelect.innerHTML = '<option value="">Seleziona</option>';

        if (selectedBuildingId === "") {
            // Se rimette "Seleziona" mostra di nuovo tutte le aule clonando gli optgroup originali
            allOptGroups.forEach(optgroup => {
                roomSelect.appendChild(optgroup.cloneNode(true));
            });
        } else {
            // filtra le aule aggiungendo solo l'optgroup dell'edificio scelto
            const matchingGroup = allOptGroups.find(group => group.dataset.buildingId === selectedBuildingId);
            if (matchingGroup) {
                roomSelect.appendChild(matchingGroup.cloneNode(true));
            }
        }

        // Tenta di mantenere la stanza selezionata se è ancora valida dopo il filtro
        if (currentSelectedRoom) {
            roomSelect.value = currentSelectedRoom;
            if (roomSelect.value === "") { // Se non esiste più tra le opzioni filtrate, resetta
                roomSelect.value = "";
            }
        }
    });

    // CASO B: L'utente seleziona direttamente un aula
    roomSelect.addEventListener('change', (e) => {
        const selectedRoomId = e.target.value;
        if (selectedRoomId === "") return;

        // Cerca l'optgroup corrispondente per scoprire a che edificio appartiene
        let targetBuildingId = null;
        for (const optgroup of allOptGroups) {
            const option = optgroup.querySelector(`option[value="${selectedRoomId}"]`);
            if (option) {
                targetBuildingId = optgroup.dataset.buildingId;
                break;
            }
        }

        if (targetBuildingId) {
            // autocpmpleta la prima tendina con l'edificio corretto
            buildingSelect.value = targetBuildingId;

            // riduce la lista delle aule a quelle di questo edificio scatenando l'evento 'change'
            const event = new Event('change');
            buildingSelect.dispatchEvent(event);

            // rimette l'aula selezionata visto che il reset precedente ha azzerato le option
            roomSelect.value = selectedRoomId;
        }
    });
});