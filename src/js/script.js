/* =========================================
   GESTIONE MENU MOBILE
========================================= */
document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger-menu');
    const navMenu = document.querySelector('.nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
        });

        document.querySelectorAll('.nav-link').forEach(n => n.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navMenu.classList.remove('active');
        }));
    }
});

/* =========================================
   GESTIONE PRENOTAZIONI (AJAX)
========================================= */

// Funzione per caricare le prenotazioni ADMIN
function caricaPrenotazioniAdmin(sede = 'tutte') {
    // Nota il percorso: ../ajax/... perché lo script è chiamato da pages/profilo_admin.php
    fetch(`../ajax/get_prenotazioni_admin.php?sede=${sede}`) 
        .then(response => {
            if (!response.ok) throw new Error('Errore nel caricamento');
            return response.text();
        })
        .then(html => {
            const tbody = document.querySelector('.tabella_dati tbody');
            if (tbody) {
                tbody.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Errore:', error);
            const tbody = document.querySelector('.tabella_dati tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Errore nel caricamento dei dati</td></tr>';
        });
}

// Funzione per caricare le prenotazioni USER
function caricaPrenotazioniUser(sede = 'tutte') {
    fetch(`../ajax/get_prenotazioni_user.php?sede=${sede}`)
        .then(response => {
            if (!response.ok) throw new Error('Errore nel caricamento');
            return response.text();
        })
        .then(html => {
            const tbody = document.querySelector('.tabella_dati tbody');
            if (tbody) {
                tbody.innerHTML = html;
            }
        })
        .catch(error => {
            console.error('Errore:', error);
            const tbody = document.querySelector('.tabella_dati tbody');
            if (tbody) tbody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Errore nel caricamento dei dati</td></tr>';
        });
}

/* =========================================
   INIZIALIZZAZIONE PAGINE
========================================= */
document.addEventListener('DOMContentLoaded', function() {
    
    // Se siamo nella pagina ADMIN
    if (document.body.classList.contains('profilo-admin')) {
        caricaPrenotazioniAdmin();

        const selectSede = document.getElementById('sede-donazioni');
        if (selectSede) {
            selectSede.addEventListener('change', function() {
                caricaPrenotazioniAdmin(this.value);
            });
        }
    }

    // Se siamo nella pagina UTENTE con tabelle
    // (Verifichiamo se esiste la select per il filtro sede user, se prevista)
    /* Se in futuro aggiungerai un filtro sede anche per l'utente, 
       qui andrà la logica simile a quella admin.
       Per ora carica solo al load se necessario o se gestito lato PHP.
    */
    
    
    /* =========================================
       GESTIONE FOTO PROFILO (UPLOAD & REMOVE)
    ========================================= */
    const photoUpload = document.getElementById('photo-upload');
    const profileImg = document.getElementById('profile-img');
    const removeBtn = document.getElementById('remove-photo-btn');

    // Funzione helper per gestire la risposta
    const handleResponse = (response) => {
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error("ERRORE CRITICO: Il server non ha restituito un JSON valido.");
                console.log("Risposta grezza dal server:", text);
                alert("Errore tecnico. Controlla la console (F12) per i dettagli.");
                throw new Error("Risposta server non valida");
            }
        });
    };

    // --- UPLOAD ---
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
                        // FIX: Ritardo minimo per evitare l'errore "message channel closed"
                        setTimeout(() => {
                            window.location.reload(); 
                        }, 100);
                    } else {
                        alert('Errore dal server: ' + data.message);
                    }
                })
                .catch(error => console.error('Errore Fetch:', error));
            }
        });
    }

    // --- RIMOZIONE ---
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
                    // FIX: Ritardo minimo per evitare l'errore "message channel closed"
                    setTimeout(() => {
                        window.location.reload(); 
                    }, 100);
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