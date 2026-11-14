import { protegerPagina, rotuloCargo } from '../api';

const usuario = await protegerPagina();

if (usuario) {
    document.querySelectorAll('[data-cargo-rotulo]').forEach((el) => {
        el.textContent = rotuloCargo(usuario.cargo);
    });
}
