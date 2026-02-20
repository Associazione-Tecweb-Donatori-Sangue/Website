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
        // Inizializza gestione tab
        initAdminTabs();
        
        // Carica prenotazioni solo quando si apre la tab corrispondente
        // Non più caricamento automatico all'apertura pagina
        
        if (selectSede) {
            selectSede.addEventListener('change', function() {
                caricaPrenotazioniAdmin(this.value);
            });
        }
        document.getElementById('filtro-utenti')?.addEventListener('change', (e) => {
        caricaUtentiAdmin(e.target.value);
    });
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

    // GESTIONE DIALOG ELIMINA UTENTE
    const hiddenIdUtente = document.getElementById('hidden-id-utente');
    const eliminaUtenteUsername = document.getElementById('elimina-utente-username');

    setupDialog({
        dialogId: 'dialog-elimina-utente',
        openTriggers: [{
            selector: 'btn-elimina-utente',
            event: 'click',
            dataHandler: (target) => {
                if (hiddenIdUtente) hiddenIdUtente.value = target.dataset.idUtente;
                if (eliminaUtenteUsername) eliminaUtenteUsername.textContent = target.dataset.username;
            }
        }],
        closeBtnId: 'btn-annulla-elimina-utente'
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
   GESTIONE TAB ADMIN
========================================= */

function initAdminTabs() {
    const tabs = document.querySelectorAll('.admin-tab');
    const panels = document.querySelectorAll('.admin-tabpanel');
    
    if (tabs.length === 0) return;

        // Gestione click sulle tab
        tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            selectTab(tab);
        });

        // Gestione navigazione da tastiera (frecce)
        tab.addEventListener('keydown', (e) => {
        const tabsArray = Array.from(tabs);
        const index = tabsArray.indexOf(tab);
        let nuovoIndex = null;

        if (e.key === 'ArrowRight') {
            e.preventDefault();
            nuovoIndex = (index + 1) % tabsArray.length;
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            nuovoIndex = (index - 1 + tabsArray.length) % tabsArray.length;
        } else if (e.key === 'Home') {
            e.preventDefault();
            nuovoIndex = 0;
        } else if (e.key === 'End') {
            e.preventDefault();
            nuovoIndex = tabsArray.length - 1;
        }

        if (nuovoIndex !== null) {
            selectTab(tabsArray[nuovoIndex]);
            tabsArray[nuovoIndex].focus();
        }
    });
    });

    function selectTab(selectedTab) {
        const targetPanelId = selectedTab.getAttribute('aria-controls');
        const targetPanel = document.getElementById(targetPanelId);
        
        if (!targetPanel) return;

        // Se la tab è già selezionata, chiudi tutto (toggle)
        const isAlreadySelected = selectedTab.getAttribute('aria-selected') === 'true';
        
        if (isAlreadySelected) {
            // Deseleziona la tab
            selectedTab.setAttribute('aria-selected', 'false');
            
            // Nascondi il panel
            targetPanel.hidden = true;
            
            return;
        }

        // Deseleziona tutte le tab
        tabs.forEach(tab => {
            tab.setAttribute('aria-selected', 'false');
        });

        // Nascondi tutti i panel
        panels.forEach(panel => {
            panel.hidden = true;
        });

        // Seleziona la tab corrente
        selectedTab.setAttribute('aria-selected', 'true');

        // Mostra il panel corrispondente
        targetPanel.hidden = false;

        // Carica i dati in base alla tab selezionata
        if (targetPanelId === 'panel-prenotazioni') {
            const selectSede = document.getElementById('sede-donazioni');
            const sedeSelezionata = selectSede ? selectSede.value : 'tutte';
            caricaPrenotazioniAdmin(sedeSelezionata);
        } else if (targetPanelId === 'panel-utenti') {
            caricaUtentiAdmin();
        }
    }

    // Apri automaticamente una tab se specificata nell'URL
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');
    
    if (tabParam) {
        const tabToOpen = document.getElementById(`tab-${tabParam}`);
        if (tabToOpen) {
            selectTab(tabToOpen);
            tabToOpen.focus();
        }
    }
}

/* =========================================
   GESTIONE UTENTI ADMIN (AJAX)
========================================= */

function caricaUtentiAdmin(filtro = 'tutti', ordine = 'cognome', direzione = 'ASC') {
    const wrapper = document.getElementById('utenti-wrapper');
    if (!wrapper) return;

    wrapper.innerHTML = '<p class="text-standard">Caricamento...</p>';

    fetch(`/ggiora/src/php/ajax/get_utenti_admin.php?filtro=${encodeURIComponent(filtro)}&ordine=${encodeURIComponent(ordine)}&direzione=${encodeURIComponent(direzione)}`)
        .then(response => {
            if (!response.ok) throw new Error('Errore nel caricamento');
            return response.text();
        })
        .then(html => {
            wrapper.innerHTML = html;
            wrapper.querySelectorAll('.th-sort-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const filtroAttivo = document.getElementById('filtro-utenti')?.value ?? 'tutti';
                    caricaUtentiAdmin(filtroAttivo, btn.dataset.ordine, btn.dataset.direzione);
                });
            });
        })
        .catch(() => {
            wrapper.innerHTML = '<p class="text-standard msg-error">Errore nel caricamento dei dati.</p>';
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


/* =========================================
   LOGICA MOSTRA NASCONDI PW
========================================= */

document.addEventListener('DOMContentLoaded', function() {
    const eyeOpen = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="icon-password"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;
    const eyeClosed = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="icon-password"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

    const togglePassword = document.querySelector('#togglePassword');
    const passwordInput = document.querySelector('#password');
    const iconContainer = document.querySelector('#eye-icon-container');

    if (togglePassword && passwordInput && iconContainer) {
        iconContainer.innerHTML = eyeOpen;
        togglePassword.addEventListener('click', function() {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            iconContainer.innerHTML = isPassword ? eyeClosed : eyeOpen;
            this.setAttribute('aria-label', isPassword ? 'Nascondi password' : 'Mostra password');
        });
    }

    const toggleConfirm = document.querySelector('#togglePasswordConfirm');
    const confirmInput = document.querySelector('#password_confirm');
    const iconContainerConfirm = document.querySelector('#eye-icon-container-confirm');

    if (toggleConfirm && confirmInput && iconContainerConfirm) {
        iconContainerConfirm.innerHTML = eyeOpen;
        toggleConfirm.addEventListener('click', function() {
            const isPassword = confirmInput.getAttribute('type') === 'password';
            confirmInput.setAttribute('type', isPassword ? 'text' : 'password');
            iconContainerConfirm.innerHTML = isPassword ? eyeClosed : eyeOpen;
            this.setAttribute('aria-label', isPassword ? 'Nascondi password' : 'Mostra password');
        });
    }
});