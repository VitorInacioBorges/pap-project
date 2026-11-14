<?php

namespace App\Support;

class CamposAnamnese
{
    public const SECOES = [
        'Motivo do atendimento' => [
            'queixa_principal' => ['Queixa principal e duração', 'text'],
            'historia_atual' => ['História da doença atual', 'textarea'],
        ],
        'Antecedentes e cuidados de saúde' => [
            'antecedentes_pessoais' => ['Doenças prévias e atuais', 'textarea'],
            'cirurgias_internacoes' => ['Cirurgias, internações, traumas e transfusões', 'textarea'],
            'alergias' => ['Alergias e reações (medicamentos, alimentos, látex e outras)', 'textarea'],
            'medicamentos' => ['Medicamentos, suplementos e fitoterápicos (dose, via e frequência)', 'textarea'],
            'antecedentes_familiares' => ['Antecedentes familiares e grau de parentesco', 'textarea'],
            'vacinacao' => ['Vacinação e cuidados preventivos', 'textarea'],
        ],
        'Hábitos e contexto de vida' => [
            'tabagismo' => ['Tabagismo e exposição ao tabaco', 'textarea'],
            'alcool_substancias' => ['Álcool e outras substâncias', 'textarea'],
            'alimentacao_atividade' => ['Alimentação e atividade física', 'textarea'],
            'sono_saude_mental' => ['Sono, humor e saúde mental', 'textarea'],
            'contexto_social' => ['Trabalho, moradia, rede de apoio e segurança', 'textarea'],
        ],
        'História ginecológica e obstétrica' => [
            'menarca' => ['Idade da menarca', 'number'],
            'ultima_menstruacao' => ['Data da última menstruação', 'date'],
            'ciclo_menstrual' => ['Ciclo, fluxo, duração, dor e alterações menstruais', 'textarea'],
            'menopausa' => ['Climatério, menopausa e terapia hormonal', 'textarea'],
            'historia_sexual' => ['História sexual e saúde sexual (quando pertinente)', 'textarea'],
            'contracepcao' => ['Contracepção e planejamento reprodutivo', 'textarea'],
            'gestacoes' => ['Número de gestações', 'number'],
            'partos' => ['Número de partos', 'number'],
            'abortamentos' => ['Número de abortamentos', 'number'],
            'historia_obstetrica' => ['Gestações, vias de parto, intercorrências e amamentação', 'textarea'],
            'antecedentes_ginecologicos' => ['Antecedentes ginecológicos, ISTs e tratamentos', 'textarea'],
            'preventivo' => ['Último citopatológico / HPV (data, resultado e seguimento)', 'textarea'],
            'saude_mamas' => ['Saúde das mamas e exames anteriores', 'textarea'],
        ],
        'Revisão de sistemas' => [
            'sintomas_gerais' => ['Sintomas gerais e constitucionais', 'textarea'],
            'cardiorrespiratorio' => ['Cardiovascular e respiratório', 'textarea'],
            'digestivo_urinario' => ['Digestivo e urinário', 'textarea'],
            'neurologico_musculoesqueletico' => ['Neurológico e musculoesquelético', 'textarea'],
            'pele_endocrino' => ['Pele, mucosas e endócrino', 'textarea'],
        ],
        'Sinais vitais e exame físico' => [
            'pressao_arterial' => ['Pressão arterial (mmHg)', 'text'],
            'frequencia_cardiaca' => ['Frequência cardíaca (bpm)', 'decimal'],
            'frequencia_respiratoria' => ['Frequência respiratória (irpm)', 'decimal'],
            'temperatura' => ['Temperatura (°C)', 'decimal'],
            'saturacao' => ['Saturação de O₂ (%)', 'decimal'],
            'peso' => ['Peso (kg)', 'decimal'],
            'altura' => ['Altura (m)', 'decimal'],
            'circunferencia_abdominal' => ['Circunferência abdominal (cm)', 'decimal'],
            'exame_geral' => ['Estado geral e exame físico por sistemas', 'textarea'],
            'exame_ginecologico' => ['Exame das mamas e ginecológico, se realizado e consentido', 'textarea'],
        ],
        'Avaliação e plano' => [
            'hipoteses_diagnosticas' => ['Avaliação e hipóteses diagnósticas', 'textarea'],
            'exames' => ['Exames solicitados e resultados avaliados', 'textarea'],
            'conduta' => ['Conduta, orientações e tratamento', 'textarea'],
            'encaminhamentos' => ['Encaminhamentos', 'textarea'],
            'retorno' => ['Retorno e plano de acompanhamento', 'textarea'],
        ],
    ];

    public const CATEGORIAS = [
        'Motivo do atendimento' => [
            'Relato do atendimento' => ['queixa_principal', 'historia_atual'],
        ],
        'Antecedentes e cuidados de saúde' => [
            'Histórico de saúde' => ['antecedentes_pessoais', 'cirurgias_internacoes'],
            'Alergias e medicamentos' => ['alergias', 'medicamentos'],
            'Família e prevenção' => ['antecedentes_familiares', 'vacinacao'],
        ],
        'Hábitos e contexto de vida' => [
            'Exposição a substâncias' => ['tabagismo', 'alcool_substancias'],
            'Rotina e bem-estar' => ['alimentacao_atividade', 'sono_saude_mental'],
            'Contexto social' => ['contexto_social'],
        ],
        'História ginecológica e obstétrica' => [
            'Ciclo menstrual e climatério' => ['menarca', 'ultima_menstruacao', 'ciclo_menstrual', 'menopausa'],
            'Saúde sexual e planejamento reprodutivo' => ['historia_sexual', 'contracepcao'],
            'Histórico obstétrico' => ['gestacoes', 'partos', 'abortamentos', 'historia_obstetrica'],
            'Antecedentes e prevenção' => ['antecedentes_ginecologicos', 'preventivo', 'saude_mamas'],
        ],
        'Revisão de sistemas' => [
            'Avaliação geral e cardiorrespiratória' => ['sintomas_gerais', 'cardiorrespiratorio'],
            'Demais sistemas' => ['digestivo_urinario', 'neurologico_musculoesqueletico', 'pele_endocrino'],
        ],
        'Sinais vitais e exame físico' => [
            'Sinais vitais' => ['pressao_arterial', 'frequencia_cardiaca', 'frequencia_respiratoria', 'temperatura', 'saturacao'],
            'Medidas corporais' => ['peso', 'altura', 'circunferencia_abdominal'],
            'Exame físico' => ['exame_geral', 'exame_ginecologico'],
        ],
        'Avaliação e plano' => [
            'Avaliação clínica' => ['hipoteses_diagnosticas', 'exames'],
            'Conduta e acompanhamento' => ['conduta', 'encaminhamentos', 'retorno'],
        ],
    ];

    public static function regras(): array
    {
        $campos = array_merge(...array_values(self::SECOES));
        $regras = ['dados_clinicos' => ['required', 'array:'.implode(',', array_keys($campos))]];
        foreach ($campos as $nome => [$rotulo, $tipo]) {
            $obrigatorio = in_array($nome, ['queixa_principal', 'historia_atual'], true);
            $regras['dados_clinicos.'.$nome] = match ($tipo) {
                'number' => ['nullable', 'integer', 'min:0', 'max:120'],
                'decimal' => ['nullable', 'numeric', 'min:0', 'max:1000'],
                'date' => ['nullable', 'date_format:Y-m-d'],
                default => [$obrigatorio ? 'required' : 'nullable', 'string', 'max:10000'],
            };
        }
        $regras['dados_clinicos.saturacao'] = ['nullable', 'numeric', 'between:0,100'];
        $regras['dados_clinicos.altura'] = ['nullable', 'numeric', 'gt:0', 'max:3'];

        return $regras;
    }
}
