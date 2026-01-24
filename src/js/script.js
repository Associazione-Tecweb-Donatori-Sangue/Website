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

    // 2. Ricerca sedi con debouncing
    const searchInput = document.getElementById('searchInput');
    const noResultsMessage = document.getElementById('noResults');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);
            
            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.toLowerCase();
                const sedi = document.querySelectorAll('.location');
                let visibileCount = 0;

                sedi.forEach(sede => {
                    const nome = sede.querySelector('h3')?.textContent.toLowerCase() || '';
                    const indirizzo = sede.querySelector('p')?.textContent.toLowerCase() || '';

                    if (nome.includes(searchTerm) || indirizzo.includes(searchTerm)) {
                        sede.classList.remove('hidden');
                        visibileCount++;
                    } else {
                        sede.classList.add('hidden');
                    }
                });

                if (noResultsMessage) {
                    noResultsMessage.classList.toggle('no-results-message-visible', visibileCount === 0);
                    noResultsMessage.classList.toggle('no-results-message', visibileCount > 0);
                }
            }, 300); // Debounce di 300ms
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

    // 4. GESTIONE PAGINE ADMIN
    const isAdminPage = document.body.classList.contains('profile-admin');
    const selectSede = document.getElementById('sede-donazioni');

    if (isAdminPage) {
        caricaPrenotazioniAdmin();
        
        if (selectSede) {
            selectSede.addEventListener('change', function() {
                caricaPrenotazioniAdmin(this.value);
            });
        }
    }

    // 5. GESTIONE FOTO PROFILO
    const photoUpload = document.getElementById('photo-upload');
    const profileImg = document.getElementById('profile-img');
    const removeBtn = document.getElementById('remove-photo-btn');

    // Funzione helper per mostrare messaggi
    const showMessage = (message, isError = false) => {
        console.log(isError ? 'Errore:' : 'Successo:', message);
        // Potresti aggiungere qui un sistema di notifiche toast
    };

    // Funzione helper per gestire la risposta JSON
    const handleResponse = (response) => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("Errore parsing JSON:", text);
                throw new Error("Risposta server non valida");
            }
        });
    };

    // UPLOAD foto profilo
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
                        setTimeout(() => window.location.reload(), 100);
                    } else {
                        showMessage(data.message || 'Errore durante upload', true);
                    }
                })
                .catch(error => {
                    console.error('Errore upload:', error);
                    showMessage('Errore di connessione', true);
                });
            }
        });
    }

    // RIMOZIONE foto profilo
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
                    setTimeout(() => window.location.reload(), 100);
                } else {
                    showMessage(data.message || 'Errore durante rimozione', true);
                }
            })
            .catch(error => {
                console.error('Errore rimozione:', error);
                showMessage('Errore di connessione', true);
            });
        };

        removeBtn.addEventListener('click', handleRemoval);
        removeBtn.addEventListener('keydown', (e) => { 
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                handleRemoval(e);
            }
        });
    }

});

/* =========================================
   GESTIONE PRENOTAZIONI ADMIN (AJAX)
========================================= */
function caricaPrenotazioniAdmin(sede = 'tutte') {
    fetch(`../ajax/get_prenotazioni_admin.php?sede=${sede}`) 
        .then(response => {
            if (!response.ok) throw new Error('Errore nel caricamento');
            return response.text();
        })
        .then(html => {
            const wrapper = document.getElementById('prenotazioni-wrapper');
            if (wrapper) wrapper.innerHTML = html;
        })
        .catch(error => {
            console.error('Errore caricamento prenotazioni:', error);
            const wrapper = document.getElementById('prenotazioni-wrapper');
            if (wrapper) {
                wrapper.innerHTML = '<p class="text-standard testo-centered-message">Errore nel caricamento dei dati.</p>';
            }
        });
}

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