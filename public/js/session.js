// Session-Daten aktualisieren
async function fetchSessionData() {
    try {
        const response = await fetch(sessionDataUrl);
        if (!response.ok) {
            if (response.status === 401 || response.status === 403) {
                window.location.href = startPageUrl;
            }
            return;
        }

        const data = await response.json();

        // Aktuelles PBI aktualisieren
        document.getElementById('currentPbi').innerHTML = ''; // Reset first
        const currentPbiElement = document.createElement('div');
        currentPbiElement.classList.add('card');
        currentPbiElement.innerHTML = `
    <div class="card-header text-center">
        <h5>${data.currentPbi ? data.currentPbi.title : 'Kein aktives PBI'}</h5>
    </div>
    <div class="card-body">
        <p>${data.currentPbi ? data.currentPbi.description : ''}</p>
    </div>
`;
        document.getElementById('currentPbi').appendChild(currentPbiElement);
        if (data.winner) {
            // Footer Karten: Average und Winner
            const footerElement = document.getElementById('currentPbiFooter');
            footerElement.innerHTML = ''; // Reset Footer

            // Container für Average und Winner Karten
            const cardsContainer = document.createElement('div');
            cardsContainer.classList.add('d-flex', 'justify-content-around', 'mt-3');

            // Average Karte
            const averageCard = document.createElement('div');
            averageCard.classList.add('estimate-card');
            averageCard.classList.add('average');
            averageCard.innerHTML = `${data.averageRevealed !== null ? data.averageRevealed.toFixed(1) : 'Keine Daten'}`;

            // Winner Karte
            const winnerCard = document.createElement('div');
            winnerCard.classList.add('estimate-card');
            winnerCard.classList.add('winner');
            if (data.winner !== null) {
                winnerCard.innerHTML = `${data.winner}`;
            } else {
                winnerCard.innerHTML = `Keine Karte`;
            }

            // Karten zum Container hinzufügen
            cardsContainer.appendChild(averageCard);
            cardsContainer.appendChild(winnerCard);

            // Container zum Footer hinzufügen
            footerElement.appendChild(cardsContainer);
        }

        // Teilnehmerliste aktualisieren
        const participantList = document.getElementById('participantList');
        participantList.innerHTML = '';
        data.estimates.forEach(participant => {
            const li = document.createElement('li');
            li.classList.add('list-group-item', 'd-flex', 'align-items-center', 'justify-content-between');
            li.innerHTML = `
                <span class="username">${participant.username || 'Unbekannt'}</span>
                <div class="estimate-card-sm ml-2 ${(participant.revealed ? '' : 'hidden')} ">
                    ${participant.estimate ? (participant.revealed ? participant.estimate : '???') : '&nbsp;'}
                </div>
                ${window.isHost ? `<form method="post" action="/session/${window.sessionKey}/remove-user/${participant.participantId}" class="d-inline ml-auto"><button type="submit" class="btn btn-danger btn-sm">X</button></form>` : ''}
            `;
            participantList.appendChild(li);
        });

        // Product Backlog Items aktualisieren
        const pbiList = document.getElementById('productBacklogList');
        pbiList.innerHTML = '';  // Reset first
        data.productBacklogItems.forEach(pbi => {
            const li = document.createElement('li');
            li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
            li.innerHTML = `
                ${pbi.title || 'Unbekannt'}
                ${window.isHost ? `<a href="/session/${window.sessionKey}/activate-pbi/${pbi.id}" class="btn btn-info btn-sm">Sch&auml;tzen</a>` : ''}
            `;
            pbiList.appendChild(li);
        });

    } catch (error) {
        console.error('Fehler beim Abrufen der Sitzungsdaten:', error);
    }
}

function chooseCard(value, sessionKey, pbiId) {
    if (!pbiId) {
        return;
    }
    // Unselect all cards
    Array.from(document.getElementsByClassName('estimate-card')).forEach((cardItem) => {
        cardItem.classList.remove('selected');
    });

    // Select the clicked card
    let cardElement = document.getElementById('cardid_' + value);
    cardElement.classList.add('selected');

    // Send an AJAX request to select the card
    fetch(`/session/${sessionKey}/select-card`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            pbiId: pbiId,
            cardValue: value
        })
    })
        .then(response => response.json())
        .then(data => { })
        .catch(error => {
            console.error('Error:', error);
        });
}

document.addEventListener('DOMContentLoaded', function () {
    // CSRF-Token, URLs und andere serverseitige Daten
    const csrfToken = window.csrfToken; // Wird aus Twig eingebunden
    const sessionKey = window.sessionKey; // Session-Key aus Twig
    const sessionDataUrl = window.sessionDataUrl; // Session-Daten-URL
    const startPageUrl = window.startPageUrl; // Startseiten-URL

    // PBI hinzufügen
    const addPbiForm = document.querySelector('#addPbiForm');
    if (addPbiForm) {
        addPbiForm.addEventListener('submit', function (event) {
            event.preventDefault();

            fetch(`/session/${sessionKey}/add-pbi`, {
                method: 'POST',
                body: new URLSearchParams(new FormData(addPbiForm)),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.json())
                .then(data => {
                    // Modal schließen
                    const modal = document.querySelector('#addPbiModal');
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    modalInstance.hide();

                    // Formular zurücksetzen
                    addPbiForm.reset();
                })
                .catch(error => console.error('Error:', error));
        });
    }

    // Alle 5 Sekunden aktualisieren
    setInterval(fetchSessionData, 5000);
    fetchSessionData();
});
