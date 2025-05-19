<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PushSubscription extends Model
{
    use HasFactory;

    /**
     * Атрибуты, которые можно массово назначать.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'endpoint',
        'keys',
        'expiration_time'
    ];

    /**
     * Атрибуты, которые нужно приводить к определённым типам.
     *
     * @var array
     */
    protected $casts = [
        'expiration_time' => 'datetime',
    ];

    /**
     * Получить пользователя, которому принадлежит подписка.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить ключи подписки в виде массива.
     *
     * @return array
     */
    public function getKeysArrayAttribute()
    {
        return json_decode($this->keys, true);
    }
}
