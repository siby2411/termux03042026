// Optionnel : gestion de la hauteur du marquee dynamique
document.addEventListener('DOMContentLoaded', function() {
    const marquee = document.querySelector('.marquee');
    if (marquee) {
        const images = marquee.children.length;
        const heightPerImage = 150; // environ hauteur image + margin
        const totalHeight = images * heightPerImage;
        marquee.style.height = totalHeight + 'px';
    }
});
