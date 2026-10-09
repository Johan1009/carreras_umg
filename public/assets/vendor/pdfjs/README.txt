PDF.js (https://mozilla.github.io/pdf.js/) — renderizador de PDF de Mozilla, vendorizado localmente (sin CDN externo).

Version: 6.4.299 (pdfjs-dist, npm)
Archivos incluidos: pdf.min.mjs (API principal), pdf.worker.min.mjs (worker de decodificacion).
No se incluyen cmaps/ ni standard_fonts/ (fuentes CJK y de respaldo) para mantener el peso bajo; los PDF
de este proyecto son documentos en español con fuentes embebidas. Si algún PDF usa fuentes no embebidas
y se ve con glifos incorrectos, copiar la carpeta standard_fonts/ del paquete pdfjs-dist aquí y configurar
la opción `standardFontDataUrl` en public/assets/js/visor-pdf.js.

Licencia: Apache-2.0 (ver LICENSE).
