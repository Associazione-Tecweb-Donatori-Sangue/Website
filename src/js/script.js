/* =========================================
   GESTIONE DOM (Menu, Ricerca, Header)
========================================= */

document.addEventListener('DOMContentLoaded', () => {

    // 1. Menu mobile - Accessibilità hamburger menu
    const burgerInput = document.getElementById('burger-input');
    
    if (burgerInput) {
        burgerInput.addEventListener('change', function() {
            const isChecked = this.checked;
            
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
                    const indirizzo = sede.querySelectorAll('p')[1]?.textContent.toLowerCase() || '';

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

    let isAutoScrolling = false; 

    // 3. Header sticky
    const header = document.querySelector('.sticky-header');
    let lastScrollTop = 0;

    if (header) {
        window.addEventListener('scroll', function () {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            if (scrollTop < 50) {
                header.classList.remove('header-hidden');
                header.classList.remove('scrolled');
                isAutoScrolling = false;
                lastScrollTop = scrollTop;
                return; 
            }

            header.classList.toggle('scrolled', scrollTop > 50);
            if (!isAutoScrolling) {
                if (scrollTop > lastScrollTop && scrollTop > 100) {
                    header.classList.add('header-hidden');
                } else {
                    header.classList.remove('header-hidden');
                }
            }

            lastScrollTop = Math.max(scrollTop, 0);
        });
    }

    // 3b. Gestione bottone "Torna all'inizio"
    const backToTopBtn = document.querySelector('.footer-buttons a[href="#content"]');
    
    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', (e) => {
            e.preventDefault();
            isAutoScrolling = true;

            if (header) {
                header.classList.add('header-hidden');
            }
         
            const contentElement = document.getElementById('content');
            if (contentElement) {
                contentElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            } else {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
            
    
            setTimeout(() => {
                isAutoScrolling = false;
            }, 1000);
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

    // 6. GESTIONE DIALOG ELIMINA PROFILO
    const btnEliminaProfilo = document.getElementById('btn-elimina-profilo');
    const dialogEliminaProfilo = document.getElementById('dialog-elimina-profilo');
    const btnAnnullaElimina = document.getElementById('btn-annulla-elimina');

    if (btnEliminaProfilo && dialogEliminaProfilo) {
        // Apri dialog
        btnEliminaProfilo.addEventListener('click', () => {
            dialogEliminaProfilo.showModal();
        });

        // Chiudi dialog con bottone Annulla
        if (btnAnnullaElimina) {
            btnAnnullaElimina.addEventListener('click', () => {
                dialogEliminaProfilo.close();
            });
        }

        // Chiudi dialog premendo ESC (già gestito dal browser con showModal)
        // Chiudi dialog cliccando fuori (backdrop)
        dialogEliminaProfilo.addEventListener('click', (e) => {
            if (e.target === dialogEliminaProfilo) {
                dialogEliminaProfilo.close();
            }
        });
    }

    // 5. GESTIONE FOTO PROFILO
    const photoUpload = document.getElementById('photo-upload');
    const profileImg = document.getElementById('profile-img');
    const removeBtn = document.getElementById('remove-photo-btn');
    const profileContainer = document.querySelector('.profile-picture');
    const navImg = document.getElementById('imgProfilo');

    const showMessage = (message, isError = false) => {
        console.log(isError ? 'Errore:' : 'Successo:', message);
        if (isError) alert(message);
    };

    const handleResponse = (response) => {
        return response.text().then(text => {
            try {
                return JSON.parse(text.trim());
            } catch (e) {
                console.error("Errore parsing JSON:", text);
                throw new Error("Risposta server non valida");
            }
        });
    };

    const triggerUpload = () => {
        if (photoUpload) photoUpload.click();
    };

    if (profileContainer && photoUpload) {    
        profileContainer.addEventListener('click', (e) => {
            if (e.target.closest('#remove-photo-btn')) return;
            triggerUpload();
        });

        profileContainer.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                if (document.activeElement === removeBtn) return;
                
                e.preventDefault();
                triggerUpload();
            }
        });

        /* --- GESTIONE UPLOAD FILE --- */
        
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

        /* --- RIMOZIONE foto profilo ---*/

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