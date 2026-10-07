Add to app/Models/User.php (inside the class), alongside your other accessors:

    public function avatarUrl(): ?string
    {
        return $this->avatar ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->avatar) : null;
    }

Add 'avatar' to $fillable if it's not already covered by a wildcard.
