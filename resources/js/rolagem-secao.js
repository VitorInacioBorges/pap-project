export function revelarSecao(secao, rodape = null) {
    const margem = 16;
    const viewport = window.visualViewport;
    const topoVisivel = (viewport?.offsetTop ?? 0) + margem;
    const fimVisivel = (viewport?.offsetTop ?? 0) + (viewport?.height ?? window.innerHeight) - margem - (rodape?.offsetHeight ?? 0);
    const caixa = secao.getBoundingClientRect();
    if (caixa.top >= topoVisivel && caixa.bottom <= fimVisivel) return;

    const cabeNaTela = caixa.height <= fimVisivel - topoVisivel;
    const deslocamento = cabeNaTela && caixa.top >= topoVisivel ? caixa.bottom - fimVisivel : caixa.top - topoVisivel;
    if (deslocamento === 0) return;

    window.scrollBy({
        top: deslocamento,
        behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth',
    });
}
