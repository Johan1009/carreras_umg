/* Visor de PDF embebido con PDF.js (Mozilla), vendorizado en assets/vendor/pdfjs/.
 * Reemplaza el <iframe> nativo porque Chrome para Android y, en varios casos, Safari de iOS
 * no renderizan PDF dentro de un iframe: aquí se dibuja cada página en un <canvas> con JavaScript,
 * así que funciona igual en PC, tableta y celular. Solo se usa en consulta/ver.php. */
import * as pdfjsLib from '../vendor/pdfjs/pdf.min.mjs';

pdfjsLib.GlobalWorkerOptions.workerSrc = new URL('../vendor/pdfjs/pdf.worker.min.mjs', import.meta.url).href;

(function () {
    'use strict';

    var lienzo = document.getElementById('visor-lienzo');
    if (!lienzo) {
        return; // Esta carrera no tiene documentos: no hay visor en la página.
    }

    var envoltorio = document.getElementById('visor-lienzo-envoltorio');
    var visor = document.getElementById('visor');
    var estado = document.getElementById('visor-estado');
    var paginacion = document.getElementById('visor-paginacion');
    var btnAnterior = document.getElementById('btn-pagina-anterior');
    var btnSiguiente = document.getElementById('btn-pagina-siguiente');
    var indicadorPagina = document.getElementById('visor-indicador-pagina');
    var enlaceAbrir = document.getElementById('btn-abrir-pestana');
    var ctx = lienzo.getContext('2d');

    var documentoActual = null;
    var paginaActual = 1;
    var renderizando = false;
    var pendiente = null;

    function mostrarEstado(texto) {
        if (!estado) {
            return;
        }
        estado.textContent = texto;
        estado.hidden = texto === '';
    }

    function actualizarControles() {
        var total = documentoActual ? documentoActual.numPages : 0;
        if (indicadorPagina) {
            indicadorPagina.textContent = total ? ('Página ' + paginaActual + ' de ' + total) : '';
        }
        if (btnAnterior) {
            btnAnterior.disabled = paginaActual <= 1;
        }
        if (btnSiguiente) {
            btnSiguiente.disabled = paginaActual >= total;
        }
        if (paginacion) {
            paginacion.hidden = total <= 1;
        }
    }

    function renderizarPagina(num) {
        if (!documentoActual) {
            return;
        }
        if (renderizando) {
            pendiente = num;
            return;
        }
        renderizando = true;

        documentoActual.getPage(num).then(function (pagina) {
            var anchoDisponible = (envoltorio ? envoltorio.clientWidth : lienzo.parentElement.clientWidth) - 24 || 600;
            var escalaDispositivo = window.devicePixelRatio || 1;
            var vistaBase = pagina.getViewport({ scale: 1 });
            var escala = Math.max(anchoDisponible, 280) / vistaBase.width;
            var vista = pagina.getViewport({ scale: escala * escalaDispositivo });

            lienzo.width = vista.width;
            lienzo.height = vista.height;
            lienzo.style.width = (vista.width / escalaDispositivo) + 'px';
            lienzo.style.height = (vista.height / escalaDispositivo) + 'px';

            return pagina.render({ canvasContext: ctx, viewport: vista }).promise;
        }).then(function () {
            renderizando = false;
            mostrarEstado('');
            if (pendiente !== null) {
                var siguiente = pendiente;
                pendiente = null;
                renderizarPagina(siguiente);
            }
        }).catch(function (error) {
            renderizando = false;
            mostrarEstado('No se pudo mostrar la página. Use "Abrir en pestaña nueva".');
            window.console && console.error('Error al renderizar PDF:', error);
        });
    }

    function cargarDocumento(url) {
        documentoActual = null;
        paginaActual = 1;
        actualizarControles();
        mostrarEstado('Cargando documento…');

        pdfjsLib.getDocument({ url: url }).promise.then(function (pdf) {
            documentoActual = pdf;
            paginaActual = 1;
            actualizarControles();
            renderizarPagina(paginaActual);
        }).catch(function (error) {
            mostrarEstado('No se pudo cargar el documento. Use "Abrir en pestaña nueva".');
            window.console && console.error('Error al abrir PDF:', error);
        });
    }

    if (btnAnterior) {
        btnAnterior.addEventListener('click', function () {
            if (documentoActual && paginaActual > 1) {
                paginaActual -= 1;
                actualizarControles();
                renderizarPagina(paginaActual);
            }
        });
    }
    if (btnSiguiente) {
        btnSiguiente.addEventListener('click', function () {
            if (documentoActual && paginaActual < documentoActual.numPages) {
                paginaActual += 1;
                actualizarControles();
                renderizarPagina(paginaActual);
            }
        });
    }

    // Cambiar de documento (pestañas: trifoliar / administrativo 1 / administrativo 2).
    document.querySelectorAll('[data-pdf-src]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            document.querySelectorAll('[data-pdf-src]').forEach(function (otro) {
                otro.setAttribute('aria-selected', otro === boton ? 'true' : 'false');
            });
            var url = boton.getAttribute('data-pdf-src');
            cargarDocumento(url);
            if (enlaceAbrir) {
                enlaceAbrir.href = url;
            }
        });
    });

    // Volver a dibujar al girar la tableta/celular o al ampliar/cerrar pantalla completa
    // (app.js dispara "visor:redimensionado" al alternar la clase is-full).
    var reintento = null;
    function redibujarDespuesDeUnMomento() {
        clearTimeout(reintento);
        reintento = setTimeout(function () {
            if (documentoActual) {
                renderizarPagina(paginaActual);
            }
        }, 150);
    }
    window.addEventListener('resize', redibujarDespuesDeUnMomento);
    if (visor) {
        visor.addEventListener('visor:redimensionado', redibujarDespuesDeUnMomento);
    }

    var inicial = document.querySelector('[data-pdf-src][aria-selected="true"]');
    if (inicial) {
        cargarDocumento(inicial.getAttribute('data-pdf-src'));
    }
})();
