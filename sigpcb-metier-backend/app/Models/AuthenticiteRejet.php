<?php

namespace App\Models;

use App\Models\Authenticite;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AuthenticiteRejet extends Model
{
    use HasFactory;
    protected $fillable = [
        'authenticite_id',
        'motif',
        'date_validation',
        'date_correction',
        'state',
    ];
    public function demandeAuthenticite()
    {
        return $this->belongsTo(Authenticite::class);
    }
}
