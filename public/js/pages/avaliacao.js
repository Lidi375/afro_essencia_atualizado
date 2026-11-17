// =============================
// AVALIAÇÃO DE ATENDIMENTO
// =============================

document.addEventListener('DOMContentLoaded', function () {
    const labels = document.querySelectorAll('#star-group label');
    if (!labels.length) return;

    labels.forEach(function (label, index) {
        label.addEventListener('click', function () {
            updateStars(index, labels, '#E1A756');
            markSelectedStar(label);
        });

        label.addEventListener('mouseenter', function () {
            updateStars(index, labels, '#f1c27d');
        });

        label.addEventListener('mouseleave', function () {
            const checked = document.querySelector('#star-group input:checked');
            const selectedIndex = checked ? parseInt(checked.value, 10) - 1 : -1;
            updateStars(selectedIndex, labels, '#E1A756');
        });
    });
});

function updateStars(activeIndex, labels, activeColor) {
    labels.forEach(function (label, index) {
        label.style.color = index <= activeIndex ? activeColor : 'rgba(255,255,255,0.2)';
    });
}

function markSelectedStar(label) {
    const input = label.querySelector('input');
    if (input) {
        input.checked = true;
    }
}
