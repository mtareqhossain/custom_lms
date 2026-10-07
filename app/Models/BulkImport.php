<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkImport extends Model
{
    protected $fillable = [
        'user_id', 'organization_id', 'type', 'file_path', 'status',
        'total_rows', 'processed_rows', 'failed_rows', 'errors',
    ];

    protected $casts = ['errors' => 'array'];
}
