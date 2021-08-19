<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;

class Informasi extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $hidden = [
        'id'
    ];
    protected $connection = 'pelindo_repport';
    protected $table      = 'ms_informasi';
    protected $guarded    = [];

    public function informasi()
    {
        return $this->belongsToMany(User::class, 'informasi_user', 'user_id', 'uuid', 'info_id', 'uuid');
    }

    public function aktivitas()
    {
        return $this->hasOne(Aktivitas::class, 'uuid', 'aktivitas_id');
    }

    public function getGambarUnformatedAttribute()
    {
        return $this->attributes['gambar'];
    }

    public function getGambarAttribute($value)
    {
        if ($value) {
            return Storage::disk('s3')->temporaryUrl($value, Carbon::now()->addMinutes(5));
        }

        return $value;
    }

    public function getIkonAttribute($ikon)
    {
        if ($ikon) {
            return Storage::disk('s3')->temporaryUrl($ikon, Carbon::now()->addMinutes(5));
        }

        return $ikon;
    }

    public function getCreatedAtAttribute()
    {
        return date('Y-m-d H:i:s', strtotime($this->attributes['created_at']));
    }

    public function getCreatedAtYmdAttribute()
    {
        return date('Y-m-d', strtotime($this->attributes['created_at']));
    }

    public function getUpdatedAtAttribute()
    {
        return date('Y-m-d H:i:s', strtotime($this->attributes['updated_at']));
    }
}
