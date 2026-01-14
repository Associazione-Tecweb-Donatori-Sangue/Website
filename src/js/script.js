// Carica prenotazioni admin
function caricaPrenotazioniAdmin(sede = 'tutte') {
    console.log('Chiamata caricaPrenotazioniAdmin con sede:', sede);
    fetch(`/php/get_prenotazioni_admin.php?sede=${sede}`) 
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) throw new Error('Errore nel caricamento');
            return response.text();
        })
        .then(html => {
            console.log('Risposta ricevuta:', html);
            const tbody = document.querySelector('.tabella_dati tbody');
            if (tbody) {
                tbody.innerHTML = html;
                console.log('Tabella aggiornata');
            } else {
                console.error('tbody non trovato');
            }
        })
        .catch(error => {
            console.error('Errore:', error);
            const tbody = document.querySelector('.tabella_dati tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Errore nel caricamento dei dati</td></tr>';
        });
}

// Carica prenotazioni user
function caricaPrenotazioniUser(sede = 'tutte') {
    console.log('Chiamata caricaPrenotazioniUser con sede:', sede);
    fetch(`/php/get_prenotazioni_user.php?sede=${sede}`)  
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) throw new Error('Errore nel caricamento');
            return response.text();
        })
        .then(html => {
            console.log('Risposta ricevuta:', html);
            const tbody = document.querySelector('.tabella_dati tbody');
            if (tbody) {
                tbody.innerHTML = html;
                console.log('Tabella aggiornata');
            } else {
                console.error('tbody non trovato');
            }
        })
        .catch(error => {
            console.error('Errore:', error);
            const tbody = document.querySelector('.tabella_dati tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="4" style="text-align: center;">Errore nel caricamento dei dati</td></tr>';
        });
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM caricato');
    
    // Gestione ricerca sedi
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const sedi = document.querySelectorAll('.sede');
            
            sedi.forEach(sede => {
                const nome = sede.querySelector('h3').textContent.toLowerCase();
                const indirizzo = sede.querySelector('p:nth-of-type(2)').textContent.toLowerCase();
                
                if (nome.includes(searchTerm) || indirizzo.includes(searchTerm)) {
                    sede.classList.remove('hidden');
                } else {
                    sede.classList.add('hidden');
                }
            });
        });
    }

    // Scroll effect per sticky header
    const header = document.querySelector('.sticky-header');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }
    
    // Gestione prenotazioni
    const selectSede = document.getElementById('sede-donazioni');
    const isAdminPage = document.body.classList.contains('profilo-admin');
    const isUserPage = document.body.classList.contains('profilo-user');
    
    console.log('Admin page:', isAdminPage);
    console.log('User page:', isUserPage);
    console.log('Select trovato:', selectSede !== null);
    
    // Carica dati iniziali
    if (isAdminPage) {
        console.log('Avvio caricamento prenotazioni admin...');
        caricaPrenotazioniAdmin();
    } else if (isUserPage) {
        console.log('Avvio caricamento prenotazioni user...');
        caricaPrenotazioniUser();
    }
    
    // Listener per filtro sede
    if (selectSede) {
        selectSede.addEventListener('change', function() {
            const sede = this.value;
            console.log('Filtro sede cambiato:', sede);
            if (isAdminPage) {
                caricaPrenotazioniAdmin(sede);
            } else if (isUserPage) {
                caricaPrenotazioniUser(sede);
            }
        });
    }
});