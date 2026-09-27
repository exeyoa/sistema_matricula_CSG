(function () {
    function setup() {
        document.querySelectorAll('.campo-cedula').forEach(function (container) {
            var selectProv = container.querySelector('.cedula-prov');
            var selectTipo = container.querySelector('.cedula-tipo');
            var inputLibro = container.querySelector('.cedula-libro');
            var inputTomo  = container.querySelector('.cedula-tomo');
            var hidden     = container.querySelector('input[type="hidden"]');
            var form       = container.closest('form');

            if (!selectProv || !selectTipo || !inputLibro || !inputTomo || !hidden || !form) return;

            function setInvalid(invalid) {
                container.classList.toggle('invalid', invalid);
            }

            function armar() {
                var prov  = selectProv.value;
                var tipo  = selectTipo.value;
                var libro = inputLibro.value.trim();
                var tomo  = inputTomo.value.trim();

                if ((prov === '00' && tipo === '00') || libro === '' || tomo === '') {
                    hidden.value = '';
                    return false;
                }
                var prefix;
                if (tipo !== '00') {
                    prefix = tipo;
                } else {
                    prefix = (prov.length === 2 && prov.charAt(0) === '0') ? prov.charAt(1) : prov;
                }
                hidden.value = prefix + '-' + libro + '-' + tomo;
                return true;
            }

            function update() {
                var ok = armar();
                setInvalid(!ok);
            }

            [selectProv, selectTipo].forEach(function (el) {
                el.addEventListener('change', update);
            });
            [inputLibro, inputTomo].forEach(function (el) {
                el.addEventListener('input', function () {
                    el.value = el.value.replace(/\D/g, '').slice(0, 5);
                    update();
                });
            });

            form.addEventListener('submit', function (e) {
                if (!armar()) {
                    e.preventDefault();
                    setInvalid(true);
                }
            });

            if (hidden.value && hidden.value !== '') {
                var parts = hidden.value.split('-');
                if (parts.length === 3) {
                    var prefix = parts[0];
                    if (/^\d+$/.test(prefix)) {
                        var provNorm = (prefix.length === 1) ? '0' + prefix : prefix;
                        var found = false;
                        for (var i = 0; i < selectProv.options.length; i++) {
                            if (selectProv.options[i].value === provNorm) { selectProv.selectedIndex = i; found = true; break; }
                        }
                        if (!found) selectProv.value = provNorm;
                    } else {
                        selectTipo.value = prefix;
                    }
                    inputLibro.value = parts[1];
                    inputTomo.value  = parts[2];
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setup);
    } else {
        setup();
    }
})();