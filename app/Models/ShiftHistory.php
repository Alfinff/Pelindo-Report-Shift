<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftHistory extends Model
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
    protected $table      = 'ms_shift_history';
    protected $guarded    = [];

    public function jadwal()
    {
        return $this->hasOne(Jadwal::class, 'uuid', 'jadwal_shift_id');
    }

    public function editoruser()
    {
        return $this->hasOne(User::class, 'uuid', 'editor');
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
