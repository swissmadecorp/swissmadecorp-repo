<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailMass extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * The legacy table stores category IDs as a serialized PHP array.
     */
    public function categoryIds(): array
    {
        if (blank($this->category)) {
            return [];
        }

        $categories = @unserialize($this->category, ['allowed_classes' => false]);

        if (! is_array($categories)) {
            return [];
        }

        return collect($categories)
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
