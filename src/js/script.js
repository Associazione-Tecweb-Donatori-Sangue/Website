/* =========================================
   GESTIONE DOM (Menu, Ricerca, Header)
========================================= */
document.addEventListener('DOMContentLoaded', () => {

    // 1. Menu mobile - Accessibilità hamburger menu
    const burgerInput = document.getElementById('burger-input');
    
    if (burgerInput) {
        burgerInput.addEventListener('change', function() {
            const isChecked = this.checked;
            
            // Aggiorna ARIA per accessibilità
            this.setAttribute('aria-expanded', isChecked);
            this.setAttribute('aria-label', isChecked ? 'Chiudi menu' : 'Apri menu');
        });
    }

    // 2. Ricerca sedi
    const searchInput = document.getElementById('searchInput');
    const noResultsMessage = document.getElementById('noResults');

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();
            const sedi = document.querySelectorAll('.location');
            let visibileCount = 0;

            sedi.forEach(sede => {
                const nome = sede.querySelector('h3')?.textContent.toLowerCase() || '';
                const indirizzo = sede.querySelector('p:nth-of-type(2)')?.textContent.toLowerCase() || '';

                if (nome.includes(searchTerm) || indirizzo.includes(searchTerm)) {
                    sede.classList.remove('hidden');
                    visibileCount++;
                } else {
                    sede.classList.add('hidden');
                }
            });

            if (noResultsMessage) {
                if (visibileCount === 0) {
                    noResultsMessage.classList.remove('no-results-message');
                    noResultsMessage.classList.add('no-results-message-visible');
                } else {
                    noResultsMessage.classList.remove('no-results-message-visible');
                    noResultsMessage.classList.add('no-results-message');
                }
            }
        });
    }

    // 3. Header sticky
    const header = document.querySelector('.sticky-header');
    let lastScrollTop = 0;

    if (header) {
        window.addEventListener('scroll', function () {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            header.classList.toggle('scrolled', scrollTop > 50);

            if (scrollTop > lastScrollTop && scrollTop > 100) {
                header.classList.add('header-hidden');
            } else {
                header.classList.remove('header-hidden');
            }

            lastScrollTop = Math.max(scrollTop, 0);
        });
    }

});

/* =========================================
   GESTIONE PRENOTAZIONI (SOLO ADMIN)
========================================= */
// Nota: L'utente carica i dati direttamente tramite PHP in profilo.php
// L'Admin invece usa AJAX per poter filtrare le sedi dinamicamente.

function caricaPrenotazioniAdmin(sede = 'tutte') {
    fetch(`../ajax/get_prenotazioni_admin.php?sede=${sede}`) 
        .then(response => {
            if (!response.ok) throw new Error('Errore nel caricamento');
            return response.text();
        })
        .then(html => {
            const tbody = document.querySelector('.data-table tbody');
            if (tbody) tbody.innerHTML = html;
        })
        .catch(error => {
            console.error('Errore Admin:', error);
            const tbody = document.querySelector('.data-table tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" class="table-cell-centered">Errore nel caricamento dei dati</td></tr>';
        });
}


/* =========================================
   INIZIALIZZAZIONE PAGINE & FOTO PROFILO
========================================= */
document.addEventListener('DOMContentLoaded', function() {
    
    // --- GESTIONE LOGICA PAGINE ---
    const isAdminPage = document.body.classList.contains('profile-admin');
    const selectSede = document.getElementById('sede-donazioni');

    // 1. Caricamento Iniziale (SOLO ADMIN)
    if (isAdminPage) {
        caricaPrenotazioniAdmin();
    } 
    // SE È USER: Non facciamo nulla. PHP ha già stampato le tabelle corrette.

    // 2. Event Listener Filtro Sede (SOLO ADMIN)
    if (selectSede && isAdminPage) {
        selectSede.addEventListener('change', function() {
            caricaPrenotazioniAdmin(this.value);
        });
    }

    // --- GESTIONE FOTO PROFILO (UPLOAD & REMOVE) ---
    const photoUpload = document.getElementById('photo-upload');
    const profileImg = document.getElementById('profile-img');
    const removeBtn = document.getElementById('remove-photo-btn');

    // Funzione helper per gestire la risposta JSON
    const handleResponse = (response) => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("ERRORE CRITICO: Il server non ha restituito un JSON valido.", text);
                alert("Errore tecnico. Controlla la console per i dettagli.");
                throw new Error("Risposta server non valida");
            }
        });
    };

    // UPLOAD
    if (photoUpload && profileImg) {
        profileImg.addEventListener('click', () => photoUpload.click());

        photoUpload.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const formData = new FormData();
                formData.append('foto_profilo', this.files[0]);
                formData.append('azione', 'upload');

                fetch('../actions/gestioneFotoProfilo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(handleResponse)
                .then(data => {
                    if (data.success) {
                        setTimeout(() => { window.location.reload(); }, 100);
                    } else {
                        alert('Errore dal server: ' + data.message);
                    }
                })
                .catch(error => console.error('Errore Fetch:', error));
            }
        });
    }

    // RIMOZIONE
    if (removeBtn) {
        const handleRemoval = (e) => {
            e.stopPropagation(); 
            if (!confirm('Sei sicuro di voler rimuovere la foto profilo?')) return;

            const formData = new FormData();
            formData.append('azione', 'rimuovi');

            fetch('../actions/gestioneFotoProfilo.php', {
                method: 'POST',
                body: formData
            })
            .then(handleResponse)
            .then(data => {
                if (data.success) {
                    setTimeout(() => { window.location.reload(); }, 100);
                } else {
                    alert('Errore dal server: ' + data.message);
                }
            })
            .catch(error => console.error('Errore Fetch:', error));
        };

        removeBtn.addEventListener('click', handleRemoval);
        removeBtn.addEventListener('keydown', (e) => { if(e.key==='Enter') handleRemoval(e); });
    }
});

/* =========================================
   PREVENZIONE ANIMAZIONI AL RESIZE
========================================= */
let resizeTimer;
window.addEventListener("resize", () => {
  document.body.classList.add("resize-animation-stopper");
  clearTimeout(resizeTimer);
  resizeTimer = setTimeout(() => {
    document.body.classList.remove("resize-animation-stopper");
  }, 400);
});