<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashClosure extends Model
{
    use HasFactory;

    // Définir les champs autorisés pour le remplissage en masse
    protected $fillable = [
        'date',
         'shift',
        'total_sales',
        'total_received',
        'total_change',
        'real_cash',
        'difference',
        'user_id',
    ];
// App/Models/CashClosure.php
public function user()
{
    return $this->belongsTo(User::class);
}
public const SHIFTS = [
    'matin', // 8h–14h
    'soir',  // 14h–22h
    'nuit',  // 22h–6h
];


    // (Optionnel) Si vous voulez que 'date' soit traité comme un objet Carbon
    protected $dates = ['date'];
}
