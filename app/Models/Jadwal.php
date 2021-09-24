<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
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

    protected $fillable = [
        'uuid',
        'user_id',
        'tanggal',
        'kode_shift',
    ];

    protected $connection = 'pelindo_repport';
    protected $table      = 'ms_shift_jadwal';
    protected $guarded    = [];

    public function user()
    {
        return $this->hasOne(User::class, 'uuid', 'user_id');
    }

    public function shift()
    {
        return $this->hasOne(Shift::class, 'kode', 'kode_shift');
    }

    public function history()
    {
        return $this->hasMany(ShiftHistory::class, 'jadwal_shift_id', 'uuid');
    }

    public function getCreatedAtAttribute($value)
    {
        return formatTanggal($value);
    }

    public function getUpdatedAtAttribute($value)
    {
        return formatTanggal($value);
    }

}
