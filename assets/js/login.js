(function () {
    document.querySelectorAll('.toggle-password').forEach(function (btn) {
        var input = btn.parentElement && btn.parentElement.querySelector('input[type="password"], input[type="text"]');
        var icon  = btn.querySelector('i');
        if (!input || !icon) return;

        btn.addEventListener('click', function () {
            var showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            icon.classList.toggle('bi-eye-fill', showing);
            icon.classList.toggle('bi-eye-slash-fill', !showing);
            btn.setAttribute('aria-label', showing ? 'Mostrar contrasena' : 'Ocultar contrasena');
            input.focus();
        });
    });

    var slides = document.querySelectorAll('.carrusel-slide');
    var dots   = document.querySelectorAll('.carrusel-dots .dot');
    var left   = document.querySelector('.login-left');

    if (slides.length === 0) return;

    var current = 0;
    var timer   = null;
    var DELAY   = 5000;

    function show(i) {
        current = (i + slides.length) % slides.length;
        slides.forEach(function (s, idx) { s.classList.toggle('active', idx === current); });
        dots.forEach(function   (d, idx) { d.classList.toggle('active', idx === current); });
    }

    function start() {
        stop();
        timer = setInterval(function () { show(current + 1); }, DELAY);
    }

    function stop() {
        if (timer !== null) {
            clearInterval(timer);
            timer = null;
        }
    }

    dots.forEach(function (d) {
        d.addEventListener('click', function () {
            var idx = parseInt(d.getAttribute('data-index'), 10);
            if (!isNaN(idx)) {
                show(idx);
                start();
            }
        });
    });

    if (left && window.matchMedia && window.matchMedia('(hover: hover)').matches) {
        left.addEventListener('mouseenter', stop);
        left.addEventListener('mouseleave', start);
    }

    start();
})();