import { api, desabilitarEnquanto, ErroValidacao, limparErros, mostrarErros, protegerPagina } from '../api';
import { revelarSecao } from '../rolagem-secao';
import { fecharOutrasSecoes, lerSecaoUnica, salvarSecaoUnica } from '../preferencias-ficha';

const usuario = await protegerPagina();

if (usuario) {
    const $ = (id) => document.getElementById(id);
    const pagina = $('pagina-fichas');
    const cadastro = pagina.dataset.cadastro === 'true';
    const caminho = `/pacientes/${encodeURIComponent(pagina.dataset.paciente)}`;
    const formulario = $('form-ficha');
    const secoes = formulario?.querySelectorAll('[data-secao-ficha]') ?? [];
    const chavePreferencia = `pap.ficha.secao-unica.${usuario.alias || usuario.email}`;
    let secaoUnica = lerSecaoUnica(chavePreferencia);
    let ultimaSecao = [...secoes].find((secao) => secao.open);
    pagina.addEventListener('click', (evento) => {
        const resumo = evento.target.closest('summary');
        if (!resumo) return;
        const secao = resumo.parentElement;
        requestAnimationFrame(() => {
            if (!evento.defaultPrevented && secao.open) {
                if (secao.matches('[data-secao-ficha]')) {
                    ultimaSecao = secao;
                    fecharOutrasSecoes(secao, secoes, secaoUnica);
                }
                revelarSecao(secao, formulario?.contains(secao) ? $('acoes-ficha') : null);
            }
        });
    });
    let proximaPagina = 1;

    const mostrarErro = (erro) => {
        $('erro-fichas-texto').textContent = erro.message;
        $('erro-fichas').hidden = false;
    };

    const carregarFichas = async (numero = 1) => {
        $('erro-fichas').hidden = true;
        const resposta = await api(`${caminho}/fichas?page=${numero}`);
        if (numero === 1) $('lista-fichas').replaceChildren();
        for (const ficha of resposta.data) {
            const item = $('modelo-ficha').content.firstElementChild.cloneNode(true);
            item.querySelector('[data-numero]').textContent = `Ficha #${ficha.id}`;
            const data = item.querySelector('[data-data]');
            data.dateTime = ficha.data_atendimento;
            data.textContent = new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short', timeStyle: 'short' }).format(new Date(ficha.data_atendimento));
            item.querySelector('[data-responsavel]').textContent = ficha.usuario?.nome || ficha.usuario?.alias || 'Responsável indisponível';
            item.querySelector('[data-observacoes]').textContent = ficha.observacoes || 'Sem observações.';
            const dados = ficha.dados_clinicos || {};
            item.querySelectorAll('[data-item-clinico]').forEach((linha) => {
                const valor = dados[linha.dataset.itemClinico];
                linha.hidden = valor === null || valor === undefined || valor === '';
                linha.querySelector('[data-valor-clinico]').textContent = valor ?? '';
            });
            item.querySelector('[data-legado]').hidden = Boolean(ficha.dados_clinicos);
            $('lista-fichas').append(item);
        }
        proximaPagina = resposta.current_page + 1;
        $('mais-fichas').hidden = resposta.current_page >= resposta.last_page;
        $('fichas-vazias').hidden = resposta.total > 0;
    };

    const carregar = async () => {
        $('erro-fichas').hidden = true;
        try {
            const paciente = await api(caminho);
            $('identificacao-paciente').textContent = `${paciente.dados_pessoais?.nome || paciente.email} · CPF ${paciente.cpf}`;
            const dados = paciente.dados_pessoais || {};
            const nascimento = dados.data_nascimento?.slice(0, 10).split('-').reverse().join('/');
            const identificacao = { ...dados, nascimento, email: paciente.email };
            document.querySelectorAll('[data-identificacao]').forEach((campo) => {
                campo.textContent = identificacao[campo.dataset.identificacao] || 'Não informado';
            });
            $('identificacao-adicional').hidden = false;
            if (cadastro) {
                if (!paciente.ativo) {
                    throw new Error('Reative a paciente antes de cadastrar uma ficha.');
                }
                formulario.hidden = false;
            } else {
                $('criar-ficha').hidden = !paciente.ativo;
                await carregarFichas();
            }
        } catch (erro) {
            $('identificacao-paciente').textContent = 'Não foi possível carregar a paciente.';
            mostrarErro(erro);
        }
    };

    $('tentar-fichas').addEventListener('click', (evento) => desabilitarEnquanto(evento.currentTarget, carregar));

    if (cadastro) {
        const preferencias = $('preferencias-ficha');
        const opcaoSecaoUnica = $('preferencia-secao-unica');
        opcaoSecaoUnica.checked = secaoUnica;
        $('abrir-preferencias-ficha').addEventListener('click', () => preferencias.showModal());
        $('fechar-preferencias-ficha').addEventListener('click', () => preferencias.close());
        opcaoSecaoUnica.addEventListener('change', () => {
            secaoUnica = opcaoSecaoUnica.checked;
            salvarSecaoUnica(chavePreferencia, secaoUnica);
            const aberta = ultimaSecao?.open ? ultimaSecao : [...secoes].find((secao) => secao.open);
            fecharOutrasSecoes(aberta, secoes, secaoUnica);
        });
        formulario.addEventListener('invalid', (evento) => {
            const secao = evento.target.closest('details');
            if (secao) secao.open = true;
        }, true);
        formulario.addEventListener('submit', (evento) => {
            evento.preventDefault();
            if (!formulario.reportValidity()) return;
            desabilitarEnquanto($('salvar-ficha'), async () => {
                limparErros(formulario);
                $('erro-salvar-ficha').hidden = true;
                try {
                    await api(`${caminho}/fichas`, {
                        method: 'POST',
                        body: {
                            data_atendimento: new Date($('data-atendimento').value).toISOString(),
                            observacoes: $('observacoes').value.trim() || null,
                            dados_clinicos: Object.fromEntries([...formulario.querySelectorAll('[data-clinico]')].map((campo) => [
                                campo.dataset.clinico,
                                campo.value.trim() === '' ? null : campo.type === 'number' ? Number(campo.value) : campo.value.trim(),
                            ])),
                        },
                    });
                    window.location.assign(`${pagina.dataset.lista}?salva=1`);
                } catch (erro) {
                    if (erro instanceof ErroValidacao) {
                        for (const campo of Object.keys(erro.erros)) {
                            const secao = formulario.querySelector(`[data-erro="${CSS.escape(campo)}"]`)?.closest('details');
                            if (secao) secao.open = true;
                        }
                    }
                    if (!(erro instanceof ErroValidacao) || !mostrarErros(formulario, erro.erros)) {
                        $('erro-salvar-ficha').textContent = erro.message;
                        $('erro-salvar-ficha').hidden = false;
                    }
                }
            });
        });
    } else {
        $('ficha-salva').hidden = new URLSearchParams(window.location.search).get('salva') !== '1';
        $('mais-fichas').addEventListener('click', (evento) => desabilitarEnquanto(evento.currentTarget, async () => {
            try {
                await carregarFichas(proximaPagina);
            } catch (erro) {
                mostrarErro(erro);
            }
        }));
    }

    carregar();
}
