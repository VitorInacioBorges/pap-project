
<?php

/*o model Anamnese é a classe que representa a tabela anamnese do banco de dados
e permite que o Laravel consulte, crie, altere e exclua logicamente
seus registros.*/

//o namespace como o endereço de uma classe dentro do projeto.
namespace App\Models;

/*O use permite referenciar uma classe pelo seu nome curto,
sem precisar escrever seu endereço completo toda vez.*/

/*Nesse caso, estamos importando Model, a classe-base do Eloquent, o ORM do Laravel.

ORM significa Object-Relational Mapping. É uma ferramenta que permite trabalhar
com registros do banco de dados por meio de objetos PHP.

Por exemplo, em vez de escrever manualmente uma consulta SQL para buscar uma anamnese, 
você poderá utilizar o model para fazer essa consulta.

Quando nossa classe herdar de Model, ela ganhará os recursos do Eloquent.*/
use Illuminate\Database\Eloquent\Model;

/*Importa o recurso SoftDeletes, que permite realizar a chamada exclusão lógica.

No seu banco, existe a coluna deleted_at. 
Ela registra quando um registro foi marcado como excluído.

Em vez de remover permanentemente uma anamnese, o Laravel pode preencher essa coluna 
com a data e hora da exclusão.*/
use Illuminate\Database\Eloquent\SoftDeletes;

/*Importa a classe BelongsTo, utilizada para indicar 
o tipo de relacionamento entre dois models.

No nosso caso, uma anamnese pertence a um paciente 
e também está associada a um usuário.

Esse import permite que os métodos que representam esses relacionamentos 
declarem explicitamente o tipo de retorno BelongsTo.

Uma distinção importante: use importa uma classe para que possamos referenciá-la
pelo nome curto. Ele não cria uma instância dessa classe 
nem executa o relacionamento.*/
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Anamnese extends Model
{
    /*Ela utiliza um recurso do PHP chamado trait.
    Uma trait permite reutilizar funcionalidades em diferentes classes 
    sem precisar herdá-las de outra classe-base.*/
    use SoftDeletes;

    //Define 'anamnese' como o nome da tabela no banco de dados.
    protected $table = 'anamnese';

    /*Essa propriedade define os campos que podem ser preenchidos por meio 
    de operações de atribuição em massa, chamadas mass assignment.
    Nesse array, cada item é o nome de uma coluna*/
    protected $fillable = [
        'paciente_id',
        'usuario_id',
        'data_atendimento',
    ];

    /*data_atendimento é o atributo que queremos configurar.
    datetime indica que seu valor será tratado como uma data e hora pelo Eloquent.*/
    protected function casts(): array
    {
        return [
            'data_atendimento' => 'datetime',
        ];
    }

    //conecta a anamnese ao paciente correspondente.
    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    //conecta a anamnese ao usuário que cadastrou a ficha.
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
