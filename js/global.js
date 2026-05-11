document.addEventListener("DOMContentLoaded", () => {
    // 1. Configuration de l'observateur
    const obserOptions = {
        threshold: 0.2 // Se déclenche quand 20% de l'élément est visible
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            //Si l'élément est dans la zone de vue
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                //une fois visible, on arrête de surveiller cet élément pour économiser les ressources
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);
    
    //On sélectionne tous les éléments avec la classe reveal-item'
    const items = document.querySelectorAll('.reveal-item');
    items.forEach(item => {
        observer.observe(item);
    })
})