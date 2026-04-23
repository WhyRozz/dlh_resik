<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Masyarakat extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'masyarakat';
    protected $primaryKey = 'id_masyarakat';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['nama', 'email', 'password', 'otp', 'otp_expires', 'google_id'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = ['otp_expires' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function laporan()
    {
        return $this->hasMany(Laporan::class, 'id_masyarakat', 'id_masyarakat');
    }
}
