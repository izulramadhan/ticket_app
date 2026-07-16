<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventStatusHistory extends Model
{
    use HasFactory;

    protected $table = 'event_status_histories';

    protected $fillable = [
        'event_id',
        'status_lama',
        'status_baru',
        'changed_by',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
