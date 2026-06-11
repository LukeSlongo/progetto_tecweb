document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById('issue-form');
    if (!form) return;

    const buildingSelect = document.getElementById('building_id');
    const roomSelect = document.getElementById('room_id');

    const buildings = JSON.parse(form.dataset.buildings || '[]');
    const rooms = JSON.parse(form.dataset.rooms || '[]');

    // Funzione di supporto per riempire la tendina degli edifici dinamicamente
    function renderBuildings() {
        buildingSelect.innerHTML = '<option value="">Seleziona</option>';
        buildings.forEach(building => {
            const opt = document.createElement('option');
            opt.value = building.id;
            opt.textContent = building.name;
            buildingSelect.appendChild(opt);
        });
    }
    // Funzione di supporto per riempire la tendina delle aule dinamicamente
    function renderRooms(roomsToRender) {
        roomSelect.innerHTML = '<option value="">Seleziona</option>';
        roomsToRender.forEach(room => {
            const opt = document.createElement('option');
            opt.value = room.id;
            opt.textContent = room.name;
            roomSelect.appendChild(opt);
        });
    }

    renderBuildings();
    renderRooms(rooms);

    // CASO A: l'utente seleziona prima un edificio
    buildingSelect.addEventListener('change', (e) => {
        const selectedBuildingId = e.target.value;

        if (selectedBuildingId === "") {
            // Se rimette "Seleziona" mostra di nuovo tutte le aule
            renderRooms(rooms);
        } else {
            // filtra le aule tenendo solo quelle dell'edificio scelto
            const filteredRooms = rooms.filter(room => room.building_id == selectedBuildingId);
            renderRooms(filteredRooms);
        }
    });

    // CASO B: L'utente seleziona direttamente un aula
    roomSelect.addEventListener('change', (e) => {
        const selectedRoomId = e.target.value;
        if (selectedRoomId === "") return;

        // Cerca l'oggetto aula corrispondente per scoprire a che edificio appartiene
        const selectedRoom = rooms.find(room => room.id == selectedRoomId);

        if (selectedRoom) {
            // autocpmpleta la prima tendina con l'edificio corretto
            buildingSelect.value = selectedRoom.building_id;
            // riduce la lista delle aule a quelle di questo edificio
            const filteredRooms = rooms.filter(room => room.building_id == selectedRoom.building_id);
            renderRooms(filteredRooms);

            // rimette l'aula selezionata visto che renderRooms ha azzerato le option
            roomSelect.value = selectedRoom.id;
        }
    });
});