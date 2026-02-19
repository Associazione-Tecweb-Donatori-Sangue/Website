/* =========================================
   GESTIONE DOM (Menu, Ricerca, Header)
========================================= */

/* =========================================
   HELPER FUNZIONE PER DIALOG
========================================= */
function setupDialog(config) {
    const {
        dialogId,
        openTriggers = [],
        closeBtnId,
        onOpen = null,
        closeOnBackdrop = true
    } = config;

    const dialog = document.getElementById(dialogId);
    if (!dialog) return;
    openTriggers.forEach(trigger => {
        if (typeof trigger === 'string') {
            const btn = document.getElementById(trigger);
            if (btn) {
                btn.addEventListener('click', () => {
                    if (onOpen) onOpen();
                    dialog.showModal();
                });
            }
        } else if (trigger.selector && trigger.event === 'click') {
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains(trigger.selector)) {
                    if (trigger.dataHandler) {
                        trigger.dataHandler(e.target);
                    }
                    if (onOpen) onOpen();
                    dialog.showModal();
                }
            });
        }
    });

    if (closeBtnId) {
        const closeBtn = document.getElementById(closeBtnId);
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                dialog.close();
            });
        }
    }

    if (closeOnBackdrop) {
        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) {
                dialog.close();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {

    // 1. Menu mobile - Accessibilità hamburger menu
    const burgerInput = document.getElementById('burger-input');
    const burgerMenu = document.querySelector('.burger-menu');
    const menuUl = document.querySelector('#menu ul');
    const logoImg = document.getElementById('logo');
    
    if (burgerInput && burgerMenu && menuUl) {
        // Funzione per gestire il focus trap quando il menu è aperto
        function trapFocus(e) {
            if (!burgerInput.checked) return;
            
            if (e.key === 'Tab') {
                e.preventDefault();

                const menuLinks = Array.from(menuUl.querySelectorAll('a:not(#currentLink)'));
                const focusableElements = [...menuLinks, burgerInput];
                
                let currentIndex = focusableElements.indexOf(document.activeElement);
                
                if (currentIndex === -1) {
                    currentIndex = 0;
                }
                
                let nextIndex;
                
                if (e.shiftKey) {
                    nextIndex = currentIndex === 0 ? focusableElements.length - 1 : currentIndex - 1;
                } else {
                    nextIndex = currentIndex === focusableElements.length - 1 ? 0 : currentIndex + 1;
                }
                
                const nextElement = focusableElements[nextIndex];
                if (nextElement) {
                    nextElement.focus();
                }
            }
        }
        
        burgerInput.addEventListener('change', function() {
            const isChecked = this.checked;
            
            this.setAttribute('aria-expanded', isChecked);
            this.setAttribute('aria-label', isChecked ? 'Chiudi menu' : 'Apri menu');
            
            if (isChecked) {
                burgerMenu.classList.add('menu-open');
                menuUl.classList.add('menu-open');
                
                document.addEventListener('keydown', trapFocus);
                
                setTimeout(() => {
                    const firstLink = menuUl.querySelector('a:not(#currentLink)');
                    if (firstLink) {
                        firstLink.focus();
                    } else {
                        const fallbackLink = menuUl.querySelector('a');
                        if (fallbackLink) fallbackLink.focus();
                    }
                }, 100);
            } else {
                burgerMenu.classList.remove('menu-open');
                menuUl.classList.remove('menu-open');
                
                document.removeEventListener('keydown', trapFocus);
            }
        });

        burgerInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.checked = !this.checked;
                this.dispatchEvent(new Event('change'));
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && burgerInput.checked) {
                burgerInput.checked = false;
                burgerInput.dispatchEvent(new Event('change'));
                burgerInput.focus();
            }
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

                if (searchTerm === "") {
                    announceToScreenReader("Ricerca azzerata. Mostrate tutte le sedi.");
                } else if (visibileCount > 0) {
                    announceToScreenReader("Trovate " + visibileCount + " sedi corrispondenti alla ricerca.");
                } else {
                    announceToScreenReader("Nessuna sede trovata per la ricerca inserita.");
                }

            }, 300);
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

    // GESTIONE PAGINE ADMIN
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

    // GESTIONE DIALOG ELIMINA PROFILO
    setupDialog({
        dialogId: 'dialog-elimina-profilo',
        openTriggers: ['btn-elimina-profilo'],
        closeBtnId: 'btn-annulla-elimina'
    });

    // GESTIONE DIALOG ANNULLA PRENOTAZIONE
    const hiddenIdPrenotazione = document.getElementById('hidden-id-prenotazione');
    const prenotazioneData = document.getElementById('prenotazione-data');
    const prenotazioneOra = document.getElementById('prenotazione-ora');

    setupDialog({
        dialogId: 'dialog-annulla-prenotazione',
        openTriggers: [{
            selector: 'btn-annulla-prenotazione',
            event: 'click',
            dataHandler: (target) => {
                if (hiddenIdPrenotazione) hiddenIdPrenotazione.value = target.dataset.idPrenotazione;
                if (prenotazioneData) prenotazioneData.textContent = target.dataset.data;
                if (prenotazioneOra) prenotazioneOra.textContent = target.dataset.ora;
            }
        }],
        closeBtnId: 'btn-annulla-dialog-prenotazione'
    });

    // GESTIONE DIALOG ELIMINA PRENOTAZIONE ADMIN
    const hiddenIdPrenotazioneAdmin = document.getElementById('hidden-id-prenotazione-admin');
    const eliminaUsername = document.getElementById('elimina-username');
    const eliminaData = document.getElementById('elimina-data');
    const eliminaOra = document.getElementById('elimina-ora');

    setupDialog({
        dialogId: 'dialog-elimina-prenotazione-admin',
        openTriggers: [{
            selector: 'btn-elimina-prenotazione-admin',
            event: 'click',
            dataHandler: (target) => {
                if (hiddenIdPrenotazioneAdmin) hiddenIdPrenotazioneAdmin.value = target.dataset.idPrenotazione;
                if (eliminaUsername) eliminaUsername.textContent = target.dataset.username;
                if (eliminaData) eliminaData.textContent = target.dataset.data;
                if (eliminaOra) eliminaOra.textContent = target.dataset.ora;
            }
        }],
        closeBtnId: 'btn-annulla-elimina-admin'
    });

    //VALIDAZIONE: Campi vuoti 
    const prenotaForm = document.getElementById('prenotaForm');
    const dialogCampiVuoti = document.getElementById('dialog-campi-vuoti');
    const btnChiudiVuoti = document.getElementById('btn-chiudi-vuoti');

    if (prenotaForm) {
        const nuovoForm = prenotaForm.cloneNode(true);
        prenotaForm.parentNode.replaceChild(nuovoForm, prenotaForm);
        
        nuovoForm.addEventListener('submit', function(e) {
            const campiObbligatori = this.querySelectorAll('[required]');
            let campiVuoti = [];
            
            campiObbligatori.forEach(campo => {
                if (!campo.value || campo.value.trim() === '') {
                    campiVuoti.push(campo);
                }
            });
            
            if (campiVuoti.length > 0 && dialogCampiVuoti) {
                e.preventDefault();
                e.stopImmediatePropagation();
                dialogCampiVuoti.showModal();
                return false;
            }
        });

        if (btnChiudiVuoti && dialogCampiVuoti) {
            btnChiudiVuoti.addEventListener('click', () => {
                dialogCampiVuoti.close();
                const primoVuoto = Array.from(nuovoForm.querySelectorAll('[required]')).find(c => !c.value || c.value.trim() === '');
                if (primoVuoto) primoVuoto.focus();
            });

            dialogCampiVuoti.addEventListener('click', (e) => {
                if (e.target === dialogCampiVuoti) dialogCampiVuoti.close();
            });
        }
    }

    // GESTIONE FOTO PROFILO
    const profileContainer = document.getElementById('profile-picture-btn');
    const photoUpload = document.getElementById('photo-upload');
    const removePhotoBtn = document.getElementById('remove-photo-btn');
    const deletePhotoDialog = document.getElementById('deletePhotoDialog');
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

    if (profileContainer && photoUpload) {
        // Click sul container per aprire file picker
        profileContainer.addEventListener('click', (e) => {
            if (e.target.id === 'remove-photo-btn' || e.target.closest('#remove-photo-btn')) return;
            photoUpload.click();
        });

        // Tastiera sul container
        profileContainer.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                if (e.target.id === 'remove-photo-btn' || e.target.closest('#remove-photo-btn')) return;
                e.preventDefault();
                photoUpload.click();
            }
        });

        // Quando viene selezionato un file
        photoUpload.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const file = this.files[0];
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                
                if (!validTypes.includes(file.type)) {
                    const invalidDialog = document.getElementById('invalidFormatDialog');
                    if (invalidDialog) invalidDialog.showModal();
                    this.value = '';
                    return;
                }

                const maxSize = 5 * 1024 * 1024;
                if (file.size > maxSize) {
                    const tooLargeDialog = document.getElementById('fileTooLargeDialog');
                    if (tooLargeDialog) tooLargeDialog.showModal();
                    this.value = '';
                    return;
                }

                const formData = new FormData();
                formData.append('foto_profilo', file);
                formData.append('azione', 'upload');

                fetch('/ggiora/src/php/actions/gestioneFotoProfilo.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    if (!response.ok) throw new Error('size_error');
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        if (data.message && (data.message.includes('1') || data.message.toLowerCase().includes('grande'))) {
                            const d = document.getElementById('fileTooLargeDialog');
                            if (d) d.showModal();
                        } else {
                            showUploadError(data.message);
                        }
                        photoUpload.value = '';
                    }
                })
                .catch(() => {
                    const d = document.getElementById('fileTooLargeDialog');
                    if (d) d.showModal();
                    photoUpload.value = '';
                });
            }
        });
    }

    // GESTIONE RIMOZIONE FOTO (Click e Tastiera)
    if (removePhotoBtn && deletePhotoDialog) {
        const openRemoveDialog = (e) => {
            e.stopPropagation();
            deletePhotoDialog.showModal();
        };
        removePhotoBtn.addEventListener('click', openRemoveDialog);
        removePhotoBtn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openRemoveDialog(e);
            }
        });

        if (confirmDeleteBtn) {
            confirmDeleteBtn.addEventListener('click', () => {
                const formData = new FormData();
                formData.append('azione', 'rimuovi');
                fetch('/ggiora/src/php/actions/gestioneFotoProfilo.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.success) window.location.reload();
                    else showUploadError("Errore durante la rimozione.");
                });
            });
        }

        if (cancelDeleteBtn) {
            cancelDeleteBtn.addEventListener('click', () => deletePhotoDialog.close());
        }
    }

    // CHIUSURA DIALOG ERRORI
    const setupCloseBtn = (btnId, dialogId) => {
        const btn = document.getElementById(btnId);
        const dialog = document.getElementById(dialogId);
        if (btn && dialog) {
            btn.addEventListener('click', () => dialog.close());
        }
    };

    setupCloseBtn('closeInvalidFormatBtn', 'invalidFormatDialog');
    setupCloseBtn('closeFileTooLargeBtn', 'fileTooLargeDialog');
    setupCloseBtn('closeUploadErrorBtn', 'uploadErrorDialog');

    function showUploadError(message) {
        const errorDialog = document.getElementById('uploadErrorDialog');
        const msgEl = document.getElementById('uploadErrorDesc');
        if (errorDialog && msgEl) {
            msgEl.textContent = message;
            errorDialog.showModal();
        }
    }

    // GESTIONE PAUSE/PLAY CAROSELLO
    const carouselToggle = document.getElementById('carousel-toggle');
    const carouselTrack = document.querySelector('.carousel-track');
    
    if (carouselToggle && carouselTrack) {
        let isPaused = false;
        
        carouselToggle.addEventListener('click', function() {
            isPaused = !isPaused;
            
            if (isPaused) {
                carouselTrack.classList.add('manually-paused');
                this.setAttribute('aria-label', 'Riprendi il carosello');
                this.setAttribute('aria-pressed', 'true');
                this.querySelector('.pause-icon').classList.add('hidden');
                this.querySelector('.play-icon').classList.remove('hidden');
            } else {
                carouselTrack.classList.remove('manually-paused');
                this.setAttribute('aria-label', 'Metti in pausa il carosello');
                this.setAttribute('aria-pressed', 'false');
                this.querySelector('.pause-icon').classList.remove('hidden');
                this.querySelector('.play-icon').classList.add('hidden');
            }
        });
    }

    // VALIDAZIONE USERNAME - Registrazione
    const usernameInput = document.getElementById('username');
    const formRegistrazione = document.querySelector('form[action="registrazione.php"]');

    if (usernameInput && formRegistrazione) {
        // Funzione di validazione username
        function validaUsernameClient(username) {
            username = username.trim();
            
            if (username === '') {
                return { valido: false, errore: 'L\'username non può essere vuoto' };
            }
            
            if (username.length < 4 || username.length > 50) {
                return { valido: false, errore: 'L\'username deve essere tra 4 e 50 caratteri' };
            }
            
            if (!/^[a-zA-Z0-9]/.test(username)) {
                return { valido: false, errore: 'L\'username deve iniziare con una lettera o un numero' };
            }
            
            if (!/[a-zA-Z0-9]$/.test(username)) {
                return { valido: false, errore: 'L\'username deve finire con una lettera o un numero' };
            }
            
            const alfanumericiCount = (username.match(/[a-zA-Z0-9]/g) || []).length;
            if (alfanumericiCount < 2) {
                return { valido: false, errore: 'L\'username deve contenere almeno 2 caratteri alfanumerici' };
            }
            
            if (!/^[a-zA-Z0-9._-]+$/.test(username)) {
                return { valido: false, errore: 'L\'username può contenere solo lettere, numeri, underscore, trattino e punto' };
            }
            
            return { valido: true, errore: null };
        }

        usernameInput.addEventListener('input', function() {
            this.setCustomValidity('');
        });

        // Validazione al submit
        formRegistrazione.addEventListener('submit', function(e) {
            const risultato = validaUsernameClient(usernameInput.value);
            
            if (!risultato.valido) {
                e.preventDefault();
                usernameInput.setCustomValidity(risultato.errore);
                usernameInput.reportValidity();
                usernameInput.focus();
                return false;
            }
        });
    }

    // VALIDAZIONE MODIFICA ACCOUNT
    const formModificaAccount = document.querySelector('form[action="modifica_account.php"]');
    
    if (formModificaAccount) {
        const usernameModifica = document.getElementById('username');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        // Funzione di validazione username (riutilizzata)
        function validaUsernameClient(username) {
            username = username.trim();
            
            if (username === '') {
                return { valido: false, errore: 'L\'username non può essere vuoto' };
            }
            
            if (username.length < 4 || username.length > 50) {
                return { valido: false, errore: 'L\'username deve essere tra 4 e 50 caratteri' };
            }
            
            if (!/^[a-zA-Z0-9]/.test(username)) {
                return { valido: false, errore: 'L\'username deve iniziare con una lettera o un numero' };
            }
            
            if (!/[a-zA-Z0-9]$/.test(username)) {
                return { valido: false, errore: 'L\'username deve finire con una lettera o un numero' };
            }
            
            const alfanumericiCount = (username.match(/[a-zA-Z0-9]/g) || []).length;
            if (alfanumericiCount < 2) {
                return { valido: false, errore: 'L\'username deve contenere almeno 2 caratteri alfanumerici' };
            }
            
            if (!/^[a-zA-Z0-9._-]+$/.test(username)) {
                return { valido: false, errore: 'L\'username può contenere solo lettere, numeri, underscore, trattino e punto' };
            }
            
            return { valido: true, errore: null };
        }

        // Reset validità quando l'utente digita
        if (usernameModifica) {
            usernameModifica.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        }

        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        }

        if (confirmPasswordInput) {
            confirmPasswordInput.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        }

        // Validazione al submit
        formModificaAccount.addEventListener('submit', function(e) {
            // Validazione username
            if (usernameModifica) {
                const risultatoUsername = validaUsernameClient(usernameModifica.value);
                if (!risultatoUsername.valido) {
                    e.preventDefault();
                    usernameModifica.setCustomValidity(risultatoUsername.errore);
                    usernameModifica.reportValidity();
                    usernameModifica.focus();
                    return false;
                }
            }

            // Validazione password solo se è stata inserita
            if (newPasswordInput && newPasswordInput.value.trim() !== '') {
                const newPass = newPasswordInput.value;
                const confirmPass = confirmPasswordInput ? confirmPasswordInput.value : '';

                // Controllo lunghezza password
                if (newPass.length < 4 || newPass.length > 50) {
                    e.preventDefault();
                    newPasswordInput.setCustomValidity('La password deve essere tra 4 e 50 caratteri');
                    newPasswordInput.reportValidity();
                    newPasswordInput.focus();
                    return false;
                }

                // Controllo che le password coincidano
                if (newPass !== confirmPass) {
                    e.preventDefault();
                    confirmPasswordInput.setCustomValidity('Le password non coincidono');
                    confirmPasswordInput.reportValidity();
                    confirmPasswordInput.focus();
                    return false;
                }
            }
        });
    }

    // VALIDAZIONE REGISTRAZIONE DONATORE
    const formDonatore = document.getElementById('form-registrazione-donatore');
    
    if (formDonatore) {
        const telefonoInput = document.getElementById('telefono');
        
        // Funzione di validazione telefono
        function validaTelefonoClient(telefono) {
            telefono = telefono.trim();
            
            if (telefono === '') {
                return { valido: false, errore: 'Il numero di telefono non può essere vuoto' };
            }
            
            if (telefono.length > 20) {
                return { valido: false, errore: 'Il numero di telefono è troppo lungo' };
            }
            
            if (!/^[\d\s+\-()]+$/.test(telefono)) {
                return { valido: false, errore: 'Il numero di telefono può contenere solo numeri, spazi, +, - e parentesi' };
            }
            
            const soloCifre = telefono.replace(/\D/g, '');
            
            if (soloCifre.length < 9) {
                return { valido: false, errore: 'Il numero di telefono deve contenere almeno 9 cifre' };
            }
            
            if (soloCifre.length > 13) {
                return { valido: false, errore: 'Il numero di telefono contiene troppe cifre' };
            }
            
            if (/^\+39/.test(telefono)) {
                const cifreSenzaPrefisso = telefono.replace(/^\+39\D*/, '').replace(/\D/g, '');
                if (cifreSenzaPrefisso.length !== 10) {
                    return { valido: false, errore: 'I numeri italiani con +39 devono avere 10 cifre' };
                }
            }
            
            return { valido: true, errore: null };
        }

        // Reset validità quando l'utente digita
        if (telefonoInput) {
            telefonoInput.addEventListener('input', function() {
                this.setCustomValidity('');
            });
        }

        // Validazione al submit
        formDonatore.addEventListener('submit', function(e) {
            if (telefonoInput && telefonoInput.value.trim() !== '') {
                const risultatoTelefono = validaTelefonoClient(telefonoInput.value);
                
                if (!risultatoTelefono.valido) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    telefonoInput.setCustomValidity(risultatoTelefono.errore);
                    telefonoInput.reportValidity();
                    telefonoInput.focus();
                    
                    return false;
                }
                
                // Resetta validità se è ok
                telefonoInput.setCustomValidity('');
            }
        });
    }
});

/* =========================================
   GESTIONE PRENOTAZIONI ADMIN (AJAX)
========================================= */

function caricaPrenotazioniAdmin(sede = 'tutte') {
    fetch(`/ggiora/src/php/ajax/get_prenotazioni_admin.php?sede=${sede}`) 
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
