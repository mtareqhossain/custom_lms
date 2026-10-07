<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class CertificateFont extends Model
{
    protected $fillable = ['name', 'file_path'];

    /**
     * Unique CSS font-family name so each uploaded font gets its own
     * @font-face without colliding with another font of the same name.
     */
    public function cssFamily(): string
    {
        return 'cert-font-' . $this->id;
    }

    public function absolutePath(): ?string
    {
        $path = Storage::disk('public')->path($this->file_path);

        return file_exists($path) ? $path : null;
    }

    public function fontFormat(): string
    {
        return str_ends_with(strtolower($this->file_path), '.otf') ? 'opentype' : 'truetype';
    }
}
