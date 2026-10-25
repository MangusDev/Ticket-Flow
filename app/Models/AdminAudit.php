<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAudit extends Model
{
    protected $fillable = [
        'actor_id',
        'actor_name',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'changes',
    ];

    protected function casts(): array
    {
        return [
            'changes' => 'array',
        ];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function getActionLabelAttribute(): string
    {
        return [
            'user.created' => 'Usuário criado',
            'user.updated' => 'Usuário atualizado',
            'user.deactivated' => 'Usuário desativado',
            'area.created' => 'Área criada',
            'area.updated' => 'Área atualizada',
            'area.activated' => 'Área ativada',
            'area.deactivated' => 'Área desativada',
            'area.deleted' => 'Área excluída',
        ][$this->action] ?? $this->action;
    }
}
