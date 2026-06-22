<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\LoanDetail;


class Loan extends Model
{
    protected $fillable = ['user_id', 'processed_by', 'loan_date', 'due_date', 'status'];
    protected $casts = ['loan_date' => 'date', 'due_date' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(LoanDetail::class);
    }
}
