/* =========================================
   GESTIONE PRENOTAZIONI LATO ADMIN
========================================= */

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


document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM caricato');
    
    // Gestione ricerca sedi (pagina dove_trovarci.html)
    const searchInput = document.getElementById('searchInput');
    const noResultsMessage = document.getElementById('noResults');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const sedi = document.querySelectorAll('.sede');
            let visibileCount = 0; 
            
            sedi.forEach(sede => {
                const nome = sede.querySelector('h3').textContent.toLowerCase();
                const indirizzo = sede.querySelector('p:nth-of-type(2)').textContent.toLowerCase();
                
                if (nome.includes(searchTerm) || indirizzo.includes(searchTerm)) {
                    sede.classList.remove('hidden');
                    visibileCount++;
                } else {
                    sede.classList.add('hidden');
                }
            });

            if (noResultsMessage) {
                if (visibileCount === 0) {
                    noResultsMessage.style.display = 'block';
                } else {
                    noResultsMessage.style.display = 'none';
                }
            }
        });
    }

    // Scroll effect per sticky header
    const header = document.querySelector('.sticky-header');
    let lastScrollTop = 0; // Variabile per ricordare la posizione precedente

    if (header) {
        window.addEventListener('scroll', function() {
            let scrollTop = window.pageYOffset || document.documentElement.scrollTop;

            // 1. Logica Colore Sfondo (.scrolled)
            if (scrollTop > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }

            // 2. Logica Nascondi/Mostra (Direzione Scroll)
            // Se scrollo GIÙ e sono oltre i 100px dall'inizio...
            if (scrollTop > lastScrollTop && scrollTop > 100) {
                // ...AGGIUNGO la classe che la nasconde
                header.classList.add('header-hidden');
            } else {
                // Se scrollo SU (o clicco "torna su"), RIMUOVO la classe e la mostro
                header.classList.remove('header-hidden');
            }
            
            // Aggiorno la posizione per il prossimo controllo
            lastScrollTop = scrollTop <= 0 ? 0 : scrollTop; 
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

/* =========================================
   GESTIONE FOTO PROFILO (Upload / Rimuovi)
========================================= */
document.addEventListener('DOMContentLoaded', () => {
    const profilePicture = document.querySelector('.profile-picture');
    const photoUpload = document.getElementById('photo-upload');
    const removeBtn = document.getElementById('remove-photo-btn');
    const profileImg = document.getElementById('profile-img');
    const navImg = document.getElementById('imgProfilo'); // L'immagine nella navbar

    if (!profilePicture || !photoUpload || !profileImg) return;

    let isOpeningFilePicker = false;

    const openFilePicker = () => {
        if (isOpeningFilePicker) return;
        isOpeningFilePicker = true;
        photoUpload.click();
        setTimeout(() => { isOpeningFilePicker = false; }, 300);
    };

    profilePicture.addEventListener('click', (e) => {
        if (e.target.closest('#remove-photo-btn')) return;
        if (e.target === photoUpload) return;
        openFilePicker();
    });

    profilePicture.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            // Se il focus è sul tasto X, non aprire il selettore file
            if (document.activeElement === removeBtn) return;
            openFilePicker();
        }
    });

    /* ===============================
       UPLOAD FOTO (Profilo + Navbar)
    =============================== */
    photoUpload.addEventListener('change', () => {
        if (!photoUpload.files || !photoUpload.files[0]) return;

        const formData = new FormData();
        formData.append('foto_profilo', photoUpload.files[0]);

        fetch('../uploadFoto.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (!data.success) {
                alert('Errore: ' + data.message);
                return;
            }

            // Aggiungiamo il timestamp per evitare la cache del browser
            const timestamp = new Date().getTime();
            const newSrc = '../../images/profili/' + data.filename + '?t=' + timestamp;

            // Aggiorna foto grande
            profileImg.src = newSrc;
            
            // AGGIUNTA: Aggiorna icona Navbar
            if (navImg) {
                navImg.src = newSrc;
            }

            profilePicture.classList.remove('is-default');
            photoUpload.value = ''; // Reset per consentire ri-selezione
        })
        .catch(err => console.error('Errore upload:', err));
    });

    /* ===============================
       RIMOZIONE FOTO (Profilo + Navbar)
    =============================== */
    if (removeBtn) {
        const handleRemoval = (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (!confirm('Vuoi rimuovere la tua foto profilo?')) return;

            fetch('../rimuoviFoto.php', { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        alert('Errore: ' + data.message);
                        return;
                    }

                    const defaultSrc = '../../images/profilo.jpg';
                    
                    // Reset foto grande
                    profileImg.src = defaultSrc;
                    
                    // AGGIUNTA: Reset icona Navbar
                    if (navImg) {
                        navImg.src = defaultSrc;
                    }

                    profilePicture.classList.add('is-default');
                })
                .catch(err => console.error('Errore rimozione:', err));
        };

        removeBtn.addEventListener('click', handleRemoval);
        removeBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                handleRemoval(e);
            }
        });
    }
});

let resizeTimer;
window.addEventListener("resize", () => {
  // Aggiunge la classe che blocca le animazioni
  document.body.classList.add("resize-animation-stopper");
  
  // Resetta il timer se stiamo ancora ridimensionando
  clearTimeout(resizeTimer);
  
  // Dopo 400ms che hai finito di ridimensionare, riattiva le animazioni
  resizeTimer = setTimeout(() => {
    document.body.classList.remove("resize-animation-stopper");
  }, 400);
});