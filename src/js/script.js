document.addEventListener('DOMContentLoaded', function() {
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
});