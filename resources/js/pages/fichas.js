import { api, desabilitarEnquanto, ErroValidacao, limparErros, mostrarErros, protegerPagina } from '../api';

const usuario = await protegerPagina();

if (usuario) {
    const $ = (id) => document.getElementById(id);
    const pagina = $('pagina-fichas');
    const cadastro = pagina.dataset.cadastro === 'true';
    const caminho = `/pacientes/${encodeURIComponent(pagina.dataset.paciente)}`;
    const formulario = $('form-ficha');
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
        const agora = new Date();
        const local = new Date(agora.getTime() - agora.getTimezoneOffset() * 60000);
        $('data-atendimento').value = local.toISOString().slice(0, 16);
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
                        },
                    });
                    window.location.assign(`${pagina.dataset.lista}?salva=1`);
                } catch (erro) {
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
