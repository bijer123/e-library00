<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Fine extends Model
{
    protected $fillable = ['loan_detail_id', 'amount', 'paid'];
    protected $casts = ['paid' => 'boolean'];

    public function loanDetail(): BelongsTo
    {
        return $this->belongsTo(LoanDetail::class);
    }
}
