{{--
    Tela: Pacientes (route 'patients.index')

    Premissas (confirmar com o backend):
    - A view recebe $patients (coleção ou paginação) com os campos
      nome, cpf, idade e ativo (booleano).
    - Rotas usadas: patients.create (GET), patients.edit (GET, recebe o paciente)
      e patients.toggle (PATCH, recebe o paciente; alterna ativo/inativo).
    - Menu de ações: "Editar" e "Ocultar" (para paciente inativo, "Reativar").
    - Estado vazio não existe no Figma: linha simples "Nenhum paciente cadastrado.".
    - O texto do botão ("Nova Paciente") segue o Figma.
--}}
@extends('layouts.app')

@section('title', 'Pacientes · Projeto PAP')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="font-heading text-2xl font-bold leading-tight text-[#1B1B18] md:text-[40px]">Pacientes</h1>
            <p class="mt-1 text-lg text-[#1B1B18] md:text-xl">Cadastro, edição e ocultação de pacientes.</p>
        </div>

        <a href="{{ route('patients.create') }}"
           class="font-heading inline-flex h-[62px] items-center gap-3 rounded-[20px] bg-[#009A94] px-5 text-[22px] font-bold text-[#FFFEFE] transition hover:brightness-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#009A94] focus-visible:ring-offset-2">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" aria-hidden="true">
                <path d="M12 4v16M4 12h16" />
            </svg>
            Nova Paciente
        </a>
    </div>

    <div class="mt-10 overflow-hidden rounded-[20px] bg-white md:mt-[72px]">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] table-fixed border-collapse">
                <caption class="sr-only">Lista de pacientes</caption>

                <thead class="bg-[#009A94]">
                    <tr>
                        <th scope="col" class="font-heading h-[60px] w-[31%] px-4 text-center text-[22px] font-bold text-[#FFFEFE]">Nome</th>
                        <th scope="col" class="font-heading h-[60px] w-[31%] px-4 text-center text-[22px] font-bold text-[#FFFEFE]">CPF</th>
                        <th scope="col" class="font-heading h-[60px] w-[12%] px-4 text-center text-[22px] font-bold text-[#FFFEFE]">Idade</th>
                        <th scope="col" class="font-heading h-[60px] w-[13%] px-4 text-center text-[22px] font-bold text-[#FFFEFE]">Status</th>
                        <th scope="col" class="font-heading h-[60px] w-[13%] px-4 text-center text-[22px] font-bold text-[#FFFEFE]">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($patients as $patient)
                        <tr class="h-[66px] border-b-2 border-[#F2F2F2] last:border-b-0">
                            <td class="px-4 text-center text-xl text-[#1B1B18]">{{ $patient->nome }}</td>
                            <td class="px-4 text-center text-xl text-[#1B1B18]">{{ $patient->cpf }}</td>
                            <td class="px-4 text-center text-xl text-[#1B1B18]">{{ $patient->idade }}</td>
                            <td class="px-4 text-center">
                                <span class="inline-flex items-center rounded-full border px-3.5 py-1 text-xl leading-6
                                             {{ $patient->ativo ? 'border-[#0F9D58] text-[#0F9D58]' : 'border-[#E00000] text-[#E00000]' }}">
                                    {{ $patient->ativo ? 'Ativo' : 'Inativo' }}
                                </span>
                            </td>
                            <td class="px-4 text-center">
                                <details class="inline-block text-left" data-acoes>
                                    <summary class="inline-flex h-[34px] w-[49px] cursor-pointer list-none items-center justify-center rounded-full border border-[#009A94] text-[#009A94] transition hover:bg-[#E6F4F4] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#009A94] [&::-webkit-details-marker]:hidden"
                                             aria-label="Ações para {{ $patient->nome }}">
                                        <svg class="h-4 w-6" viewBox="0 0 24 16" fill="currentColor" aria-hidden="true">
                                            <circle cx="4" cy="8" r="2.2" />
                                            <circle cx="12" cy="8" r="2.2" />
                                            <circle cx="20" cy="8" r="2.2" />
                                        </svg>
                                    </summary>

                                    <div data-painel class="fixed z-20 w-44 rounded-2xl border border-[#009A94] bg-white p-1 shadow-lg">
                                        <a href="{{ route('patients.edit', $patient) }}"
                                           class="block rounded-xl px-4 py-2 text-lg text-[#1B1B18] hover:bg-[#E6F4F4] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#009A94]">
                                            Editar
                                        </a>

                                        <form method="POST" action="{{ route('patients.toggle', $patient) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="block w-full rounded-xl px-4 py-2 text-left text-lg text-[#1B1B18] hover:bg-[#E6F4F4] focus:outline-none focus-visible:ring-2 focus-visible:ring-[#009A94]">
                                                {{ $patient->ativo ? 'Ocultar' : 'Reativar' }}
                                            </button>
                                        </form>
                                    </div>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-xl text-[#1B1B18]">Nenhum paciente cadastrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($patients instanceof \Illuminate\Contracts\Pagination\Paginator && $patients->hasPages())
        <div class="mt-6">
            {{ $patients->links() }}
        </div>
    @endif

    <script>
        (function () {
            var menus = document.querySelectorAll('[data-acoes]');

            function fechar(exceto) {
                menus.forEach(function (m) {
                    if (m !== exceto) { m.removeAttribute('open'); }
                });
            }

            menus.forEach(function (m) {
                m.addEventListener('toggle', function () {
                    if (!m.open) { return; }
                    fechar(m);

                    var botao = m.querySelector('summary');
                    var painel = m.querySelector('[data-painel]');
                    var r = botao.getBoundingClientRect();

                    painel.style.top = (r.bottom + 8) + 'px';
                    painel.style.left = Math.max(8, r.right - painel.offsetWidth) + 'px';
                });
            });

            document.addEventListener('click', function (e) {
                menus.forEach(function (m) {
                    if (m.open && !m.contains(e.target)) { m.removeAttribute('open'); }
                });
            });

            document.addEventListener('keydown', function (e) {
                if (e.key !== 'Escape') { return; }
                menus.forEach(function (m) {
                    if (m.open) {
                        m.removeAttribute('open');
                        m.querySelector('summary').focus();
                    }
                });
            });

            window.addEventListener('scroll', function () { fechar(null); }, true);
            window.addEventListener('resize', function () { fechar(null); });
        })();
    </script>
@endsection
