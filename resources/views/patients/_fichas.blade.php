<div class="mt-1 flex flex-wrap items-center justify-center gap-x-3 text-sm">
    <a data-fichas data-url="{{ route('fichas.index', ['paciente' => '__PACIENTE__'], false) }}"
       class="inline-flex min-h-11 items-center rounded-lg px-1 font-semibold text-pap-escuro underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-pap"></a>
    <a data-criar-ficha data-url="{{ route('fichas.create', ['paciente' => '__PACIENTE__'], false) }}"
       class="inline-flex min-h-11 items-center rounded-lg px-1 font-semibold text-pap-escuro underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-pap">+ Criar ficha</a>
</div>
