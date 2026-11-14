import { sair } from './api';

document.querySelectorAll('[data-acao="sair"]').forEach((botao) => {
    botao.addEventListener('click', () => {
        botao.disabled = true;
        sair();
    });
});

const toggle = document.getElementById('menu-toggle');
const menu = document.getElementById('menu-lateral');
const overlay = document.getElementById('menu-overlay');

if (toggle && menu && overlay) {
    const abrir = (aberto) => {
        menu.classList.toggle('-translate-x-full', !aberto);
        overlay.hidden = !aberto;
        menu.toggleAttribute('inert', !aberto);
        toggle.setAttribute('aria-expanded', aberto ? 'true' : 'false');

        if (aberto) {
            menu.querySelector('a:not([hidden]), button')?.focus();
        } else {
            toggle.focus();
        }
    };

    toggle.addEventListener('click', () => abrir(menu.hasAttribute('inert')));
    overlay.addEventListener('click', () => abrir(false));
    document.getElementById('menu-fechar')?.addEventListener('click', () => abrir(false));
    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape' && !menu.hasAttribute('inert')) abrir(false);
    });
}
