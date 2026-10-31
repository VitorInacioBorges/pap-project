
<?php

/*
O model Anamnese é a classe que representa a tabela anamnese do banco de dados
e permite que o Laravel consulte, crie, altere e exclua logicamente seus registros.
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne; // Importado para relacionar com as tabelas filhas

class Anamnese extends Model
{
    use SoftDeletes;

    // Define 'anamnese' como o nome da tabela no banco de dados.
    protected $table = 'anamnese';

    // Campos que podem ser preenchidos via atribuição em massa.
    protected $fillable = [
        'paciente_id',
        'usuario_id',
        'data_atendimento',
    ];

    // Converte data_atendimento para objeto DateTime.
    protected function casts(): array
    {
        return [
            'data_atendimento' => 'datetime',
        ];
    }

    // ==========================================
    // RELACIONAMENTOS (PERTENCE A...)
    // ==========================================

    // Conecta a anamnese ao paciente correspondente.
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    // Conecta a anamnese ao usuário (especificando 'id_usuario' como chave no model User).
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id_usuario');
    }

    // ==========================================
    // RELACIONAMENTOS (POSSUI UM...)
    // ==========================================

    // Relacionamento com os dados de local da coleta
    public function localAnamnese(): HasOne
    {
        return $this->hasOne(LocalAnamnese::class, 'anamnese_id');
    }

    // Relacionamento com os dados de preparo pré-exame
    public function preparoPreExame(): HasOne
    {
        return $this->hasOne(PreparoPreExame::class, 'anamnese_id');
    }

    // Relacionamento com os antecedentes obstétricos
    public function antecedentesObstetricos(): HasOne
    {
        return $this->hasOne(AntecedentesObstetricos::class, 'anamnese_id');
    }

    // Relacionamento com o histórico ginecológico
    public function historicoGinecologico(): HasOne
    {
        return $this->hasOne(HistoricoGinecologico::class, 'anamnese_id');
    }

    // Relacionamento com o histórico oncológico
    public function historicoOncologico(): HasOne
    {
        return $this->hasOne(HistoricoOncologico::class, 'anamnese_id');
    }
}
