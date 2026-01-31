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
    const searchStatus = document.getElementById('searchStatus');
    let searchTimeout;

    function announceToScreenReader(message) {
        if (!searchStatus) return;
        searchStatus.textContent = "";

        setTimeout(() => {
            searchStatus.textContent = message;
        }, 50);
    }

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimeout);

            searchTimeout = setTimeout(() => {
                const searchTerm = this.value.toLowerCase().trim();
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

                // Screen reader
                if (searchTerm === "") {
                    announceToScreenReader("Ricerca azzerata. Mostrate tutte le sedi.");
                } else if (visibileCount > 0) {
                    announceToScreenReader("Trovate " + visibileCount + " sedi corrispondenti alla ricerca.");
                } else {
                    announceToScreenReader("Nessuna sede trovata per la ricerca inserita.");
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

    // 7. GESTIONE DIALOG ANNULLA PRENOTAZIONE
    const dialogAnnullaPrenotazione = document.getElementById('dialog-annulla-prenotazione');
    const btnAnnullaDialogPrenotazione = document.getElementById('btn-annulla-dialog-prenotazione');
    const hiddenIdPrenotazione = document.getElementById('hidden-id-prenotazione');
    const prenotazioneData = document.getElementById('prenotazione-data');
    const prenotazioneOra = document.getElementById('prenotazione-ora');

    if (dialogAnnullaPrenotazione) {
        // Gestione click su tutti i bottoni "Annulla" della tabella
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-annulla-prenotazione')) {
                const idPrenotazione = e.target.dataset.idPrenotazione;
                const data = e.target.dataset.data;
                const ora = e.target.dataset.ora;

                // Popola il dialog con i dati
                if (hiddenIdPrenotazione) hiddenIdPrenotazione.value = idPrenotazione;
                if (prenotazioneData) prenotazioneData.textContent = data;
                if (prenotazioneOra) prenotazioneOra.textContent = ora;

                // Apri il dialog
                dialogAnnullaPrenotazione.showModal();
            }
        });

        // Chiudi dialog con bottone Chiudi
        if (btnAnnullaDialogPrenotazione) {
            btnAnnullaDialogPrenotazione.addEventListener('click', () => {
                dialogAnnullaPrenotazione.close();
            });
        }

        // Chiudi dialog cliccando fuori (backdrop)
        dialogAnnullaPrenotazione.addEventListener('click', (e) => {
            if (e.target === dialogAnnullaPrenotazione) {
                dialogAnnullaPrenotazione.close();
            }
        });
    }

    // 8. GESTIONE DIALOG ELIMINA PRENOTAZIONE ADMIN
    const dialogEliminaPrenotazioneAdmin = document.getElementById('dialog-elimina-prenotazione-admin');
    const btnAnnullaEliminaAdmin = document.getElementById('btn-annulla-elimina-admin');
    const hiddenIdPrenotazioneAdmin = document.getElementById('hidden-id-prenotazione-admin');
    const eliminaUsername = document.getElementById('elimina-username');
    const eliminaData = document.getElementById('elimina-data');
    const eliminaOra = document.getElementById('elimina-ora');

    if (dialogEliminaPrenotazioneAdmin) {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-elimina-prenotazione-admin')) {
                const idPrenotazione = e.target.dataset.idPrenotazione;
                const username = e.target.dataset.username;
                const data = e.target.dataset.data;
                const ora = e.target.dataset.ora;

                if (hiddenIdPrenotazioneAdmin) hiddenIdPrenotazioneAdmin.value = idPrenotazione;
                if (eliminaUsername) eliminaUsername.textContent = username;
                if (eliminaData) eliminaData.textContent = data;
                if (eliminaOra) eliminaOra.textContent = ora;

                dialogEliminaPrenotazioneAdmin.showModal();
            }
        });

        if (btnAnnullaEliminaAdmin) {
            btnAnnullaEliminaAdmin.addEventListener('click', () => {
                dialogEliminaPrenotazioneAdmin.close();
            });
        }

        dialogEliminaPrenotazioneAdmin.addEventListener('click', (e) => {
            if (e.target === dialogEliminaPrenotazioneAdmin) {
                dialogEliminaPrenotazioneAdmin.close();
            }
        });
    }

    // 9. GESTIONE VALIDAZIONE MESI E POPUP CONFERMA (ADMIN E USER)
    const prenotaForm = document.getElementById('prenotaForm');
    const dialogMesi = document.getElementById('dialog-conferma-mesi');
    const descMesi = document.getElementById('dialog-mesi-desc');
    const btnProcedi = document.getElementById('btn-procedi-mesi');
    const btnAnnulla = document.getElementById('btn-annulla-mesi');

    if (prenotaForm && dialogMesi) {
        prenotaForm.addEventListener('submit', function(e) {
            const ultimaDataStr = this.dataset.ultima;
            const sesso = this.dataset.sesso || 'Maschio';
            const isAdmin = this.dataset.isAdmin === 'true';
            const inputData = document.getElementById('data');

            if (ultimaDataStr && inputData && inputData.value) {
                const dataScelta = new Date(inputData.value);
                const ultimaDonazione = new Date(ultimaDataStr);
                
                let diffMesi = (dataScelta.getFullYear() - ultimaDonazione.getFullYear()) * 12;
                diffMesi += dataScelta.getMonth() - ultimaDonazione.getMonth();
                
                const soglia = (sesso === 'Femmina') ? 6 : 3;

                // Se l'intervallo non è rispettato
                if (diffMesi < soglia) {
                    if (isAdmin) {
                        // LOGICA ADMIN: Mostra il dialog se non ancora confermato
                        if (!prenotaForm.dataset.confermaForzata) {
                            e.preventDefault();
                            const dataFormattata = ultimaDataStr.split('-').reverse().join('/');
                            
                            descMesi.innerHTML = `Il donatore ha già una prenotazione il <strong>${dataFormattata}</strong>. 
                                                  Non ci sono i <strong>${soglia} mesi</strong> di distanza previsti per un profilo <strong>${sesso}</strong>. 
                                                  <br><br>Vuoi forzare comunque il salvataggio?`;
                            
                            dialogMesi.showModal();
                        }
                   } else {
   
                        e.preventDefault();
                        const dataFormattata = ultimaDataStr.split('-').reverse().join('/');
                        descMesi.innerHTML = `Errore: non sono ancora passati i ${soglia} mesi richiesti dalla tua ultima donazione (${dataFormattata}). <br>Per favore, scegli una data successiva.`;
                        if (btnProcedi) btnProcedi.style.display = 'none';
                        if (btnAnnulla) btnAnnulla.textContent = 'HO CAPITO';
                        dialogMesi.showModal();
                    }
                }
            }
        });

        // Listener per i bottoni
        btnProcedi.addEventListener('click', () => {
            prenotaForm.dataset.confermaForzata = "true";
            dialogMesi.close();
            prenotaForm.requestSubmit();
        });

        btnAnnulla.addEventListener('click', () => {
            delete prenotaForm.dataset.confermaForzata;
            dialogMesi.close();
        });

        dialogMesi.addEventListener('click', (e) => {
            if (e.target === dialogMesi) dialogMesi.close();
        });
    }

// 5. GESTIONE FOTO PROFILO
const photoUpload = document.getElementById('photo-upload');
const profileContainer = document.querySelector('.profile-picture');
const removeBtn = document.getElementById('remove-photo-btn');

const deleteDialog = document.getElementById('deletePhotoDialog');
const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

// Funzione per rimuovere la foto
function deleteProfilePhoto() {
    const formData = new FormData();
    formData.append('azione', 'rimuovi');

    fetch('../actions/gestioneFotoProfilo.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) window.location.reload();
    });
}

// ===== GESTIONE RIMOZIONE FOTO CON DIALOG =====
if (removeBtn && deleteDialog && confirmDeleteBtn && cancelDeleteBtn) {
    
    const openDeleteDialog = (e) => {
        e.preventDefault();
        e.stopPropagation();
        deleteDialog.showModal();
    };

    // Click sulla X
    removeBtn.addEventListener('click', openDeleteDialog);

    // Tastiera sulla X
    removeBtn.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            openDeleteDialog(e);
        }
    });

    // Bottone Annulla nel dialog
    cancelDeleteBtn.addEventListener('click', () => {
        deleteDialog.close();
        removeBtn.focus();
    });

    // Bottone Conferma Rimuovi nel dialog
    confirmDeleteBtn.addEventListener('click', () => {
        deleteDialog.close();
        deleteProfilePhoto();
    });

    // Quando il dialog si chiude, ritorna il focus
    deleteDialog.addEventListener('close', () => {
        removeBtn.focus();
    });
}

// ===== GESTIONE UPLOAD FOTO =====
if (profileContainer && photoUpload) {
    
    // Click sul container per aprire file picker
    profileContainer.addEventListener('click', (e) => {
        // Se clicco sulla X, non aprire il file picker
        if (e.target.id === 'remove-photo-btn' || e.target.closest('#remove-photo-btn')) {
            return;
        }
        photoUpload.click();
    });

    // Tastiera sul container
    profileContainer.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            // Se il focus è sulla X, non aprire il file picker
            if (e.target.id === 'remove-photo-btn' || e.target.closest('#remove-photo-btn')) {
                return;
            }
            photoUpload.click();
        }
    });

    // Quando viene selezionato un file
    photoUpload.addEventListener('change', function () {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            
            // Validazione formato client-side
            const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            if (!validTypes.includes(file.type)) {
                // Apri dialog formato non valido
                const invalidDialog = document.getElementById('invalidFormatDialog');
                if (invalidDialog) {
                    invalidDialog.showModal();
                }
                this.value = ''; // Reset input
                return;
            }

            // Upload del file
            const formData = new FormData();
            formData.append('foto_profilo', file);
            formData.append('azione', 'upload');

            fetch('../actions/gestioneFotoProfilo.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    // Mostra errore generico
                    showUploadError(data.message);
                }
            })
            .catch(error => {
                console.error('Errore upload:', error);
                showUploadError('Si è verificato un errore durante il caricamento.');
            });
        }
    });
}

// ===== DIALOG FORMATO NON VALIDO =====
const invalidFormatDialog = document.getElementById('invalidFormatDialog');
const closeInvalidFormatBtn = document.getElementById('closeInvalidFormatBtn');

if (invalidFormatDialog && closeInvalidFormatBtn) {
    closeInvalidFormatBtn.addEventListener('click', () => {
        invalidFormatDialog.close();
        if (photoUpload) {
            photoUpload.value = '';
            photoUpload.focus();
        }
    });

    invalidFormatDialog.addEventListener('close', () => {
        if (photoUpload) photoUpload.focus();
    });
}

// ===== DIALOG ERRORE GENERICO UPLOAD =====
const uploadErrorDialog = document.getElementById('uploadErrorDialog');
const closeUploadErrorBtn = document.getElementById('closeUploadErrorBtn');

if (uploadErrorDialog && closeUploadErrorBtn) {
    closeUploadErrorBtn.addEventListener('click', () => {
        uploadErrorDialog.close();
        if (photoUpload) {
            photoUpload.value = '';
            photoUpload.focus();
        }
    });

    uploadErrorDialog.addEventListener('close', () => {
        if (photoUpload) photoUpload.focus();
    });
}

// Funzione helper per aprire il dialog con messaggio personalizzato
function showUploadError(message) {
    if (!uploadErrorDialog) return;

    const msgEl = document.getElementById('uploadErrorDesc');
    if (msgEl) {
        msgEl.textContent = message || "Si è verificato un errore durante il caricamento della foto.";
    }

    uploadErrorDialog.showModal();
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
                wrapper.innerHTML = '<p class="text-standard">Errore nel caricamento dei dati.</p>';
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

