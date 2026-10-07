<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Certificate extends Model
{
    protected $fillable = ['enrollment_id', 'code', 'issued_at'];
    protected $casts = ['issued_at' => 'datetime'];
    public function enrollment(): BelongsTo { return $this->belongsTo(Enrollment::class); }
}
