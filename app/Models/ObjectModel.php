<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class ObjectModel extends Model
{
    protected $fillable = [
        'id',
        'user_id',
        'type',
        'info',
        'price',
        'unit',
        'stage',
    ];
    use HasFactory;
}
