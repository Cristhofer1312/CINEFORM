<?php

namespace Modules\Taller\Entities;

use App\Helpers\Encryptor;
use Illuminate\Database\Eloquent\Model;

class CursoBorrador extends Model
{
    protected $table = 'taller.curso_borradores';
    protected $primaryKey = 'id_borrador';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'token',
        'paso_actual',
        'payload',
        'foto_path',
    ];

    protected $casts = [
        'payload' => 'array',
        'paso_actual' => 'integer',
    ];

    public function getCryptIdAttribute()
    {
        return Encryptor::encrypt($this->id_borrador);
    }

    protected $appends = ['crypt_id'];

    /**
     * Slices del wizard con defaults seguros.
     */
    public function wizardData(): array
    {
        $payload = is_array($this->payload) ? $this->payload : [];

        return [
            'token' => $this->token,
            'paso1' => $payload['paso1'] ?? [],
            'paso2' => $payload['paso2'] ?? [],
            'contenidos' => $payload['contenidos'] ?? [],
            'requisitos' => $payload['requisitos'] ?? [],
            'paso_actual' => (int) $this->paso_actual,
        ];
    }
}
