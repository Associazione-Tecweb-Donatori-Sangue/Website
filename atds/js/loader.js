document.addEventListener("DOMContentLoaded", () => {
    console.log("Loader.js caricato!");
    function afterHeaderLoaded() {
        const path = window.location.pathname.split('/').pop();
        const currentPage = document.title || 'Pagina Corrente'; 
        const breadcrumbContainer = document.getElementById('breadcrumb');
        if (breadcrumbContainer) {
            breadcrumbContainer.innerHTML = 
                '<p>Ti trovi in: <a href="index.html" lang="en">Home</a>; <span>' + currentPage + '</span></p>';
        }
        const navLinks = document.querySelectorAll('#menu a');
        navLinks.forEach(link => {
            if (path && link.getAttribute('href').endsWith(path.replace('.html.html', '.html'))) {
                const linkText = link.textContent;
                const listItem = link.parentNode;
                link.remove(); 
                const span = document.createElement('span');
                span.textContent = linkText;
                listItem.appendChild(span);
                listItem.classList.add('active'); 
            }
        });
        
    }

    fetch("../html/header.html")
        .then(response => {
            console.log("Response header:", response.status);
            if (!response.ok) throw new Error('Header non trovato');
            return response.text();
        })
        .then(data => {
            console.log("Header caricato con successo!");
            document.getElementById("header-placeholder").innerHTML = data;
            afterHeaderLoaded(); 

        })
        .catch(err => {
            console.error('Errore caricamento header:', err);
            document.getElementById("header-placeholder").innerHTML = '<p>Errore nel caricamento dell\'header</p>';
        });
    fetch("../html/footer.html")
        .then(response => {
            console.log("Response footer:", response.status);
            if (!response.ok) throw new Error('Footer non trovato');
            return response.text();
        })
        .then(data => {
            console.log("Footer caricato con successo!");
            document.getElementById("footer-placeholder").innerHTML = data;
        })
        .catch(err => {
            console.error('Errore caricamento footer:', err);
            document.getElementById("footer-placeholder").innerHTML = '<p>Errore nel caricamento del footer</p>';
        });
});