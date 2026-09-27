(function () {
    var burger  = document.getElementById('hamburger');
    var sidebar = document.getElementById('sidebar');
    var backdrop = document.getElementById('sidebar-backdrop');

    function open() {
        sidebar.classList.add('open');
        backdrop.classList.add('show');
    }
    function close() {
        sidebar.classList.remove('open');
        backdrop.classList.remove('show');
    }
    function toggle() {
        sidebar.classList.contains('open') ? close() : open();
    }

    if (burger)  burger.addEventListener('click', toggle);
    if (backdrop) backdrop.addEventListener('click', close);

    document.querySelectorAll('.sidebar-link').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 900) close();
        });
    });
})();