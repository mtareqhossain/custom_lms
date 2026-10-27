<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = ['disk', 'path', 'filename', 'mime_type', 'size', 'uploaded_by'];

    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    /**
     * Store an uploaded file under $directory on $disk and register it in the
     * shared library in one step. Call this from any FileUpload's
     * ->saveUploadedFileUsing() so every upload anywhere in the app
     * automatically becomes reusable from the library afterwards.
     */
    public static function storeUpload(UploadedFile $file, string $directory, string $disk = 'public'): self
    {
        $path = $file->store($directory, $disk);

        return static::create([
            'disk' => $disk,
            'path' => $path,
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);
    }

    /** Delete the library entry and the underlying file together — never let them drift out of sync. */
    public function deleteWithFile(): void
    {
        Storage::disk($this->disk)->delete($this->path);
        $this->delete();
    }
}
