{{--
    Tela: Cadastrar Paciente (route 'patients.create')

    Premissas (confirmar com o backend):
    - View extendida do layout base (layouts.app).
    - Formulário envia POST para route('patients.store').
    - Botão "Cancelar" redireciona para route('patients.index').
--}}
@extends('layouts.app')

@section('title', 'Nova Paciente · Projeto PAP')

@section('content')
    <div class="max-w-5xl mx-auto">
        <div class="bg-[#FFFEFE] border-2 border-[#009A94] rounded-[16px] p-8 md:p-10 shadow-sm">
            <h1 class="font-heading text-3xl md:text-[40px] font-bold text-[#1B1B18] text-center mb-8">
                Nova Paciente
            </h1>

            <form method="POST" action="{{ route('patients.store') }}" class="space-y-8">
                @csrf

                {{-- Seção: Informações Pessoais --}}
                <div class="space-y-4">
                    <h2 class="text-xl font-bold text-[#009A94]">Informações Pessoais</h2>

                    <div>
                        <label for="nome" class="block text-base font-bold text-[#1B1B18] mb-1">
                            Nome Completo<span class="text-red-600">*</span>
                        </label>
                        <input type="text" id="nome" name="nome" value="{{ old('nome') }}" required
                            class="w-full h-12 px-4 rounded-xl border border-[#009A94] bg-[#F2F2F2] text-[#1B1B18] text-base outline-none focus:ring-2 focus:ring-[#009A94]">
                        @error('nome')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="cpf" class="block text-base font-bold text-[#1B1B18] mb-1">
                                CPF<span class="text-red-600">*</span>
                            </label>
                            <input type="text" id="cpf" name="cpf" value="{{ old('cpf') }}" placeholder="000.000.000-00" required
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] placeholder-gray-400 text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                            @error('cpf')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="data_nascimento" class="block text-base font-bold text-[#1B1B18] mb-1">
                                Data de Nascimento<span class="text-red-600">*</span>
                            </label>
                            <input type="date" id="data_nascimento" name="data_nascimento" value="{{ old('data_nascimento') }}" required
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] placeholder-gray-400 text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                            @error('data_nascimento')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="telefone" class="block text-base font-bold text-[#1B1B18] mb-1">Telefone</label>
                            <input type="text" id="telefone" name="telefone" value="{{ old('telefone') }}"
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                        </div>

                        <div>
                            <label for="profissao" class="block text-base font-bold text-[#1B1B18] mb-1">Profissão</label>
                            <input type="text" id="profissao" name="profissao" value="{{ old('profissao') }}"
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="estado_civil" class="block text-base font-bold text-[#1B1B18] mb-1">Estado Civil</label>
                            <select id="estado_civil" name="estado_civil"
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-gray-500 text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                                <option value="" disabled selected>Solteira</option>
                                <option value="solteira">Solteira</option>
                                <option value="casada">Casada</option>
                                <option value="divorciada">Divorciada</option>
                                <option value="viuva">Viúva</option>
                                <option value="uniao_estavel">União Estável</option>
                            </select>
                        </div>

                        <div>
                            <label for="escolaridade" class="block text-base font-bold text-[#1B1B18] mb-1">Escolaridade</label>
                            <select id="escolaridade" name="escolaridade"
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-gray-500 text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                                <option value="" disabled selected>Não escolarizada</option>
                                <option value="nao_escolarizada">Não escolarizada</option>
                                <option value="fundamental_incompleto">Ensino Fundamental Incompleto</option>
                                <option value="fundamental_completo">Ensino Fundamental Completo</option>
                                <option value="medio_incompleto">Ensino Médio Incompleto</option>
                                <option value="medio_completo">Ensino Médio Completo</option>
                                <option value="superior_incompleto">Ensino Superior Incompleto</option>
                                <option value="superior_completo">Ensino Superior Completo</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Seção: Informações Corporais --}}
                <div class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-[#009A94]">Informações Corporais</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="peso" class="block text-base font-bold text-[#1B1B18] mb-1">Peso (kg)</label>
                            <input type="number" step="0.1" id="peso" name="peso" value="{{ old('peso') }}"
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                        </div>

                        <div>
                            <label for="altura" class="block text-base font-bold text-[#1B1B18] mb-1">Altura (m)</label>
                            <input type="number" step="0.01" id="altura" name="altura" value="{{ old('altura') }}"
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="pressao_arterial" class="block text-base font-bold text-[#1B1B18] mb-1">Pressão Arterial</label>
                            <input type="text" id="pressao_arterial" name="pressao_arterial" value="{{ old('pressao_arterial') }}"
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                        </div>

                        <div>
                            <label for="ca" class="block text-base font-bold text-[#1B1B18] mb-1">CA</label>
                            <input type="text" id="ca" name="ca" value="{{ old('ca') }}"
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                        </div>
                    </div>
                </div>

                {{-- Seção: Informações para Login --}}
                <div class="space-y-4 pt-4">
                    <h2 class="text-xl font-bold text-[#009A94]">Informações para Login</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-base font-bold text-[#1B1B18] mb-1">
                                E-mail (login do portal da paciente)<span class="text-red-600">*</span>
                            </label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password" class="block text-base font-bold text-[#1B1B18] mb-1">
                                Senha<span class="text-red-600">*</span>
                            </label>
                            <input type="password" id="password" name="password" placeholder="********" required
                                class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] placeholder-gray-400 text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Botões de Ação --}}
                <div class="flex justify-end items-center gap-4 pt-6">
                    <a href="{{ route('patients.index') }}"
                        class="px-8 h-12 inline-flex items-center justify-center border-2 border-red-600 text-red-600 font-bold text-lg rounded-xl transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-600">
                        Cancelar
                    </a>

                    <button type="submit"
                        class="px-8 h-12 inline-flex items-center justify-center bg-[#009A94] text-white font-bold text-lg rounded-xl transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#009A94]">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
