/* Interacciones de la interfaz. Sin dependencias externas. */
(function () {
    'use strict';

    function normalizar(texto) {
        return (texto || '')
            .toLowerCase()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '');
    }

    // Buscador del catálogo: filtra las baldosas sin recargar la página.
    var buscador = document.getElementById('buscador');
    if (buscador) {
        var sinResultados = document.getElementById('sin-resultados');
        buscador.addEventListener('input', function () {
            var termino = normalizar(buscador.value.trim());
            var visibles = 0;
            document.querySelectorAll('[data-carrera]').forEach(function (baldosa) {
                var coincide = normalizar(baldosa.getAttribute('data-carrera')).indexOf(termino) !== -1;
                baldosa.hidden = !coincide;
                if (coincide) {
                    visibles++;
                }
            });
            if (sinResultados) {
                sinResultados.hidden = visibles > 0;
            }
        });
    }

    // Visor de PDF: el cambio de documento y el dibujado de páginas lo maneja
    // visor-pdf.js (PDF.js embebido). Aquí solo queda la mecánica genérica de
    // "pantalla completa", que es UI, no específica del visor.
    var visor = document.getElementById('visor');
    var btnAmpliar = document.getElementById('btn-ampliar');
    var btnCerrarVisor = document.getElementById('btn-cerrar-visor');

    function alternarAmpliado(activar) {
        if (!visor) {
            return;
        }
        visor.classList.toggle('is-full', activar);
        document.body.style.overflow = activar ? 'hidden' : '';
        // Avisa a visor-pdf.js para que vuelva a dibujar la página al nuevo tamaño.
        visor.dispatchEvent(new CustomEvent('visor:redimensionado'));
    }

    if (btnAmpliar) {
        btnAmpliar.addEventListener('click', function () { alternarAmpliado(true); });
    }
    if (btnCerrarVisor) {
        btnCerrarVisor.addEventListener('click', function () { alternarAmpliado(false); });
    }

    // Menú hamburguesa (barra superior en pantallas angostas).
    var btnMenu = document.getElementById('btn-menu');
    var menuPrincipal = document.getElementById('menu-principal');

    function alternarMenu(abrir) {
        if (!btnMenu || !menuPrincipal) {
            return;
        }
        menuPrincipal.classList.toggle('abierto', abrir);
        btnMenu.setAttribute('aria-expanded', abrir ? 'true' : 'false');
    }

    if (btnMenu && menuPrincipal) {
        btnMenu.addEventListener('click', function () {
            alternarMenu(!menuPrincipal.classList.contains('abierto'));
        });
        // Cierra el menú al tocar fuera de él.
        document.addEventListener('click', function (evento) {
            if (!menuPrincipal.classList.contains('abierto')) {
                return;
            }
            if (!menuPrincipal.contains(evento.target) && evento.target !== btnMenu && !btnMenu.contains(evento.target)) {
                alternarMenu(false);
            }
        });
    }

    // Hoja inferior para enviar por correo.
    var hoja = document.getElementById('hoja-correo');
    function abrirHoja() {
        if (hoja) {
            hoja.hidden = false;
            var primerCampo = hoja.querySelector('input[type="email"]');
            if (primerCampo) {
                primerCampo.focus();
            }
        }
    }
    function cerrarHoja() {
        if (hoja) {
            hoja.hidden = true;
        }
    }

    document.querySelectorAll('[data-abrir-hoja]').forEach(function (b) {
        b.addEventListener('click', abrirHoja);
    });
    document.querySelectorAll('[data-cerrar-hoja]').forEach(function (b) {
        b.addEventListener('click', cerrarHoja);
    });
    if (hoja) {
        hoja.addEventListener('click', function (evento) {
            if (evento.target === hoja) {
                cerrarHoja();
            }
        });
    }

    document.addEventListener('keydown', function (evento) {
        if (evento.key === 'Escape') {
            alternarAmpliado(false);
            cerrarHoja();
            alternarMenu(false);
        }
    });

    // Confirmación antes de acciones destructivas (data-confirm en el formulario o botón).
    document.querySelectorAll('form[data-confirm]').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!window.confirm(formulario.getAttribute('data-confirm'))) {
                evento.preventDefault();
            }
        });
    });

    // Cambio de tema claro / oscuro. La elección se guarda en una cookie (no localStorage)
    // para que funcione igual en todas las páginas y no dependa del almacenamiento local,
    // que algunos navegadores bloquean. El servidor ya aplica esta misma cookie al renderizar
    // <html data-theme="...">, así que aquí solo hace falta mantenerla sincronizada.
    var raiz = document.documentElement;

    function temaActual() {
        var forzado = raiz.getAttribute('data-theme');
        if (forzado === 'dark' || forzado === 'light') {
            return forzado;
        }
        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function actualizarBotonesTema() {
        var esOscuro = temaActual() === 'dark';
        document.querySelectorAll('[data-tema]').forEach(function (boton) {
            // Muestra el tema al que se cambiará: sol para pasar a claro, luna para pasar a oscuro.
            boton.textContent = esOscuro ? '☀' : '☾';
            boton.title = esOscuro ? 'Cambiar a tema claro' : 'Cambiar a tema oscuro';
        });
    }

    document.querySelectorAll('[data-tema]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var nuevo = temaActual() === 'dark' ? 'light' : 'dark';
            raiz.setAttribute('data-theme', nuevo);
            try {
                var valor = nuevo === 'dark' ? 'oscuro' : 'claro';
                document.cookie = 'tema=' + valor + '; path=/; max-age=31536000; samesite=lax';
            } catch (e) { /* el cambio dura solo esta página */ }
            actualizarBotonesTema();
        });
    });
    actualizarBotonesTema();

    // Preajuste de Gmail en la configuración SMTP.
    var preajusteGmail = document.querySelector('[data-preset-gmail]');
    if (preajusteGmail) {
        preajusteGmail.addEventListener('click', function () {
            var campos = {
                host: 'smtp.gmail.com',
                puerto: '587',
                cifrado: 'tls'
            };
            Object.keys(campos).forEach(function (nombre) {
                var campo = document.querySelector('[name="' + nombre + '"]');
                if (campo) {
                    campo.value = campos[nombre];
                }
            });
        });
    }
})();
