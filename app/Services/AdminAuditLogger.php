<?php

namespace App\Services;

use App\Models\AdminAudit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminAuditLogger
{
    public function record(
        User $actor,
        string $action,
        Model $subject,
        string $subjectLabel,
        ?array $changes = null,
    ): AdminAudit {
        return AdminAudit::create([
            'actor_id' => $actor->id,
            'actor_name' => $actor->name,
            'action' => $action,
            'subject_type' => class_basename($subject),
            'subject_id' => $subject->getKey(),
            'subject_label' => $subjectLabel,
            'changes' => $changes,
        ]);
    }
}
