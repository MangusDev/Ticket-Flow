<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'area_id',
        'title',
        'description',
        'status',
        'priority',
        'created_at',
        'updated_at',
    ];


    public function requester ()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function operator ()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function area ()
    {
        return $this->belongsTo(Area::class);
    }
    

}
