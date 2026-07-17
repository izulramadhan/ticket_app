<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kategori_id',
        'judul',
        'deskripsi',
        'lokasi',
        'gambar',
        'tanggal_waktu',
    ];

    protected $casts = [
        'tanggal_waktu' => 'datetime',
    ];

    protected static function booted()
    {
        static::created(function ($event) {
            $event->statusHistories()->create([
                'status_lama' => null,
                'status_baru' => $event->status,
                'changed_by' => auth()->id(),
            ]);
        });

        static::updating(function ($event) {
            if ($event->isDirty('tanggal_waktu')) {
                $oldTime = $event->getOriginal('tanggal_waktu');
                $oldStatus = $event->calculateStatusForTime($oldTime);
                $newStatus = $event->status;

                if ($oldStatus !== $newStatus) {
                    $event->statusHistories()->create([
                        'status_lama' => $oldStatus,
                        'status_baru' => $newStatus,
                        'changed_by' => auth()->id(),
                    ]);
                }
            }
        });
    }

    public function calculateStatusForTime($time)
    {
        if (!$time) {
            return 'Completed';
        }
        $time = \Carbon\Carbon::parse($time);
        $now = now();

        if ($time > $now) {
            return 'Upcoming';
        }

        if ($time >= $now->copy()->subHours(3)) {
            return 'Ongoing';
        }

        return 'Completed';
    }

    public function getStatusAttribute()
    {
        return $this->calculateStatusForTime($this->tanggal_waktu);
    }

    public function statusHistories()
    {
        return $this->hasMany(EventStatusHistory::class);
    }

    public function getImageUrlAttribute()
    {
        if ($this->gambar && filter_var($this->gambar, FILTER_VALIDATE_URL)) {
            return $this->gambar;
        }

        if (!empty($this->gambar) && \Illuminate\Support\Facades\Storage::disk('public')->exists($this->gambar)) {
            return asset('storage/' . $this->gambar);
        }

        return asset('storage/konser.jpg');
    }

    public function hasSales(): bool
    {
        return $this->orders()->exists();
    }

    public function scopeUpcoming($query)
    {
        return $query->where('tanggal_waktu', '>', now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('tanggal_waktu', '<=', now())
                     ->where('tanggal_waktu', '>=', now()->subHours(3));
    }

    public function scopeCompleted($query)
    {
        return $query->where('tanggal_waktu', '<', now()->subHours(3));
    }

    public function tikets()
    {
        return $this->hasMany(Tiket::class);
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
