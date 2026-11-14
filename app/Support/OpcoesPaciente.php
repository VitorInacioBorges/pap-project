<?php

namespace App\Support;

class OpcoesPaciente
{
    // DECISAO: opções de estado civil e escolaridade — escolhido: lista fixa validada com in: na API e repetida no Blade; alternativas: lista só no frontend
    public const ESTADOS_CIVIS = ['Solteira', 'Casada', 'União estável', 'Divorciada', 'Viúva'];

    public const ESCOLARIDADES = [
        'Não escolarizada',
        'Fundamental incompleto',
        'Fundamental completo',
        'Médio incompleto',
        'Médio completo',
        'Superior incompleto',
        'Superior completo',
        'Pós-graduação',
    ];

    // DECISAO: formato do CPF gravado — escolhido: sempre com máscara, aceitando 11 dígitos ou mascarado; alternativas: gravar só dígitos
    public const FORMATO_CPF = '/^\d{3}\.?\d{3}\.?\d{3}-?\d{2}$/';

    public static function formatarCpf(string $cpf): string
    {
        if (preg_match(self::FORMATO_CPF, $cpf) !== 1) {
            return $cpf;
        }

        $digitos = preg_replace('/\D/', '', $cpf);

        return substr($digitos, 0, 3).'.'.substr($digitos, 3, 3).'.'.substr($digitos, 6, 3).'-'.substr($digitos, 9, 2);
    }
}
