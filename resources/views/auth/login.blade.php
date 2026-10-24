<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · Projeto PAP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#E6F4F4] min-h-screen flex items-center justify-center p-4 md:py-16">

    <div class="w-full max-w-[680px] bg-white rounded-[24px] p-6 md:p-12 shadow-sm flex flex-col items-center">
        
        {{-- Logos ProjetoPAP e UEPG --}}
        <div class="flex items-center justify-center gap-6 mb-6">
            <img src="{{ asset('images/pap-fullogo.svg') }}" alt="Projeto PAP" class="h-10 w-auto">

            <div class="h-8 w-[2px] bg-gray-200"></div>

            <img src="{{ asset('images/uepg.svg') }}" alt="UEPG" class="h-10 w-auto">
        </div>

        {{-- Título e Subtítulo --}}
        <h1 class="text-2xl md:text-3xl font-bold text-[#1B1B18] text-center mb-2">
            Faça seu Login
        </h1>
        <p class="text-sm md:text-base text-gray-600 text-center mb-8 max-w-md">
            Ambulatório de Saúde UEPG — Prevenção e Educação na Atenção à Saúde da Mulher
        </p>

        {{-- Container com Borda e Abas --}}
        <div class="w-full border-2 border-[#009A94] rounded-[16px] overflow-hidden bg-white">
            
            {{-- Abas de Seleção de Perfil --}}
            <div class="grid grid-cols-2 text-center text-sm md:text-base font-bold cursor-pointer" id="tabs-header">
                <button type="button" id="tab-paciente-btn" onclick="switchTab('paciente')"
                    class="py-3.5 px-2 flex items-center justify-center gap-2 bg-[#E6F4F4] text-[#009A94] border-r border-[#009A94] transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Sou Paciente
                </button>

                <button type="button" id="tab-projeto-btn" onclick="switchTab('projeto')"
                    class="py-3.5 px-2 flex items-center justify-center gap-2 bg-[#009A94] text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                    Sou do ProjetoPAP
                </button>
            </div>

            {{-- Formulário --}}
            <form method="POST" action="{{ route('login') }}" class="p-6 md:p-8 space-y-5">
                @csrf
                <input type="hidden" name="user_type" id="user_type" value="projeto">

                <div>
                    <label id="login-label" for="login" class="block text-sm font-bold text-[#1B1B18] mb-1">
                        E-mail ou CPF<span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="login" name="login" required placeholder="000.000.000-00"
                        class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] placeholder-gray-400 text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                </div>

                <div>
                    <label for="password" class="block text-sm font-bold text-[#1B1B18] mb-1">
                        Senha<span class="text-red-600">*</span>
                    </label>
                    <input type="password" id="password" name="password" required placeholder="********"
                        class="w-full h-12 px-4 rounded-xl border border-gray-300 bg-[#F2F2F2] text-[#1B1B18] placeholder-gray-400 text-base outline-none focus:border-[#009A94] focus:ring-2 focus:ring-[#009A94]">
                </div>

                <button type="submit"
                    class="w-full h-12 bg-[#009A94] text-white font-bold text-lg rounded-xl transition hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-[#009A94] mt-2">
                    Entrar
                </button>

                <div class="text-center pt-2 text-sm text-gray-500 font-medium">
                    Usuário novo(a) no nosso sistema? 
                    <a href="{{ route('patients.create') }}" class="text-[#009A94] font-bold underline hover:brightness-90">
                        Cadastre-se
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        function switchTab(type) {
            const btnPaciente = document.getElementById('tab-paciente-btn');
            const btnProjeto = document.getElementById('tab-projeto-btn');
            const userTypeInput = document.getElementById('user_type');
            const loginLabel = document.getElementById('login-label');
            const loginInput = document.getElementById('login');

            userTypeInput.value = type;

            if (type === 'paciente') {
                btnPaciente.className = "py-3.5 px-2 flex items-center justify-center gap-2 bg-[#009A94] text-white transition border-r border-[#009A94]";
                btnProjeto.className = "py-3.5 px-2 flex items-center justify-center gap-2 bg-[#E6F4F4] text-[#009A94] transition";
                loginLabel.innerHTML = 'CPF<span class="text-red-600">*</span>';
                loginInput.placeholder = '000.000.000-00';
            } else {
                btnProjeto.className = "py-3.5 px-2 flex items-center justify-center gap-2 bg-[#009A94] text-white transition";
                btnPaciente.className = "py-3.5 px-2 flex items-center justify-center gap-2 bg-[#E6F4F4] text-[#009A94] border-r border-[#009A94] transition";
                loginLabel.innerHTML = 'CPF ou E-mail<span class="text-red-600">*</span>';
                loginInput.placeholder = '000.000.000-00 ou email@exemplo.com';
            }
        }
    </script>
</body>
</html>
