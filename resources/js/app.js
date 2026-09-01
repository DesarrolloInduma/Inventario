import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

/* ---------- Config global de gráficos ---------- */
Chart.defaults.font.family = "'Manrope', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = '#64748b';
Chart.defaults.borderColor = 'rgba(148, 163, 184, .15)';
Chart.defaults.plugins.legend.labels.usePointStyle = true;
Chart.defaults.plugins.legend.labels.boxWidth = 8;
Chart.defaults.plugins.tooltip.backgroundColor = '#0f172a';
Chart.defaults.plugins.tooltip.padding = 10;
Chart.defaults.plugins.tooltip.cornerRadius = 8;

window.PALETTE = ['#b3072d', '#0891b2', '#f97316', '#10b981', '#475569', '#e11d48', '#0f766e', '#f59e0b'];

window.makeChart = function (ctx, config) {
    if (!ctx) return null;
    const chart = new Chart(ctx, config);
    // Oculta el esqueleto en cuanto el gráfico ya tiene pintado su primer frame.
    requestAnimationFrame(() => ctx.closest('[data-chart]')?.classList.add('is-ready'));
    return chart;
};

/* ---------- Toasts ---------- */
window.toast = function (message, type = 'success') {
    window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
};

/* ---------- Confirmación global para formularios destructivos ---------- */
document.addEventListener('submit', (e) => {
    const form = e.target;
    if (form instanceof HTMLFormElement && form.matches('form[data-confirm]') && !form.dataset.confirmed) {
        e.preventDefault();
        e.stopPropagation();
        window.dispatchEvent(new CustomEvent('confirm-open', {
            detail: {
                message: form.dataset.confirm || '¿Estás seguro de realizar esta acción?',
                action: () => {
                    form.dataset.confirmed = '1';
                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        HTMLFormElement.prototype.submit.call(form);
                    }
                },
            },
        }));
    }
}, true);

/* ---------- Indicador de carga entre páginas ---------- */
const loader = (() => {
    let barra, velo, titulo, subtitulo, icono;
    let avance = 0;
    let temporizadorBarra = null;
    let temporizadorVelo = null;
    let temporizadorSeguridad = null;
    let activo = false;

    const refs = () => {
        barra = barra || document.getElementById('app-progress');
        velo = velo || document.getElementById('app-loader');
        titulo = titulo || document.getElementById('app-loader-title');
        subtitulo = subtitulo || document.getElementById('app-loader-sub');
        icono = icono || document.getElementById('app-loader-icon');
        return barra && velo;
    };

    const pintar = (pct) => {
        avance = pct;
        if (barra) barra.firstElementChild.style.width = pct + '%';
    };

    const limpiar = () => {
        [temporizadorBarra, temporizadorVelo, temporizadorSeguridad].forEach(clearTimeout);
        temporizadorBarra = temporizadorVelo = temporizadorSeguridad = null;
    };

    const start = (opciones = {}) => {
        if (!refs() || activo) return;
        activo = true;
        limpiar();

        barra.classList.add('is-active');
        pintar(8);
        const empujar = () => {
            // Se acerca al 90% sin llegar nunca: la carga real la termina el navegador.
            pintar(avance + Math.max(1, (90 - avance) * 0.18));
            temporizadorBarra = setTimeout(empujar, 260);
        };
        temporizadorBarra = setTimeout(empujar, 180);

        // El velo solo aparece si la respuesta tarda: evita el parpadeo en páginas rápidas.
        temporizadorVelo = setTimeout(() => {
            if (titulo) titulo.textContent = opciones.titulo || 'Cargando…';
            if (subtitulo) subtitulo.textContent = opciones.subtitulo || 'Preparando la información del módulo';
            if (icono) icono.className = 'bi ' + (opciones.icono || 'bi-hdd-network');
            velo.classList.add('is-active');
        }, 260);

        // Red de seguridad: descargas o respuestas que nunca navegan.
        temporizadorSeguridad = setTimeout(done, 15000);
    };

    const done = () => {
        if (!refs()) return;
        limpiar();
        activo = false;
        velo.classList.remove('is-active');
        document.querySelectorAll('.is-navigating').forEach((el) => el.classList.remove('is-navigating'));
        if (avance === 0) return;
        pintar(100);
        setTimeout(() => {
            barra.classList.remove('is-active');
            setTimeout(() => pintar(0), 250);
        }, 200);
    };

    return { start, done };
})();

window.pageLoader = loader;

const etiquetaDeEnlace = (a) => {
    const texto = (a.dataset.loading || a.querySelector('.flex-1')?.textContent || a.textContent || '').trim();
    return texto.length > 48 ? texto.slice(0, 45) + '…' : texto;
};

const navegable = (a, e) => {
    if (!a || !a.href || a.target === '_blank' || a.hasAttribute('download') || a.dataset.noLoader !== undefined) return false;
    if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return false;
    const href = a.getAttribute('href') || '';
    if (href.startsWith('#') || /^(mailto|tel|javascript):/i.test(href)) return false;
    const url = new URL(a.href, location.href);
    if (url.origin !== location.origin) return false;
    // Mismo documento (solo cambia el ancla): no hay navegación real.
    return !(url.pathname === location.pathname && url.search === location.search && url.hash);
};

document.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (!navegable(a, e)) return;
    a.classList.add('is-navigating');
    loader.start({
        titulo: etiquetaDeEnlace(a) || 'Cargando…',
        subtitulo: a.dataset.loadingSub || 'Consultando el inventario…',
        icono: a.querySelector('i.bi')?.classList.item(1),
    });
});

document.addEventListener('submit', (e) => {
    const form = e.target;
    if (e.defaultPrevented || !(form instanceof HTMLFormElement)) return;
    if (form.target === '_blank' || form.dataset.noLoader !== undefined) return;
    const esBusqueda = (form.method || 'get').toLowerCase() === 'get';
    loader.start({
        titulo: form.dataset.loading || (esBusqueda ? 'Buscando…' : 'Procesando…'),
        subtitulo: form.dataset.loadingSub || (esBusqueda ? 'Filtrando el inventario…' : 'Guardando los cambios…'),
        icono: esBusqueda ? 'bi-search' : 'bi-arrow-repeat',
    });
});

// Al volver con el botón «atrás» la página sale del bfcache ya cargada.
window.addEventListener('pageshow', (e) => { if (e.persisted) loader.done(); });

/* ---------- Contadores animados (KPIs del panel) ---------- */
const animarContador = (el) => {
    const destino = Number(el.dataset.count || 0);
    const duracion = 900;
    const inicio = performance.now();
    const paso = (ahora) => {
        const t = Math.min(1, (ahora - inicio) / duracion);
        const eased = 1 - Math.pow(1 - t, 3);
        // 'en-US' para coincidir con el number_format() que imprime el servidor.
        el.textContent = Math.round(destino * eased).toLocaleString('en-US');
        if (t < 1) requestAnimationFrame(paso);
    };
    requestAnimationFrame(paso);
};

document.addEventListener('DOMContentLoaded', () => {
    loader.done();

    const archivoInput = document.getElementById('acta-archivo');
    const archivoNombre = document.getElementById('acta-archivo-nombre');

    if (archivoInput && archivoNombre) {
        archivoInput.addEventListener('change', () => {
            const archivo = archivoInput.files && archivoInput.files[0];
            archivoNombre.textContent = archivo ? archivo.name : 'Ningún archivo seleccionado';
        });
    }

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    document.querySelectorAll('[data-count]').forEach(animarContador);
});

Alpine.start();
