document.addEventListener('DOMContentLoaded', function() {
    // Animation au défilement
    const animatedElements = document.querySelectorAll('.hero, .books, .browse, .help, .book');
    
    animatedElements.forEach(element => {
        element.classList.add('animated');
    });
    
    // Fonction pour vérifier si un élément est visible
    function checkIfInView() {
        const windowHeight = window.innerHeight;
        const windowTopPosition = window.scrollY;
        const windowBottomPosition = windowTopPosition + windowHeight;
        
        animatedElements.forEach(element => {
            const elementHeight = element.offsetHeight;
            const elementTopPosition = element.offsetTop;
            const elementBottomPosition = elementTopPosition + elementHeight;
            
            // Vérifier si l'élément est visible
            if (
                (elementBottomPosition >= windowTopPosition) &&
                (elementTopPosition <= windowBottomPosition)
            ) {
                element.classList.add('in-view');
            }
        });
    }
    
    // Vérifier au chargement
    checkIfInView();
    
    // Vérifier lors du défilement
    window.addEventListener('scroll', checkIfInView);
    
    // Animation pour les messages d'alerte
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.style.display = 'none';
            }, 1000);
        }, 5000);
    });
    
    // Amélioration de l'expérience utilisateur
    const formInputs = document.querySelectorAll('input, textarea, select');
    formInputs.forEach(input => {
        // Ajouter une classe lorsque le champ est en focus
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('input-focus');
        });
        
        // Enlever la classe lorsque le champ perd le focus
        input.addEventListener('blur', () => {
            input.parentElement.classList.remove('input-focus');
        });
    });
});