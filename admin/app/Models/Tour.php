<?php
namespace App\Models;

use App\Core\Model;

class Tour extends Model
{
    protected string $table = 'tours';
    protected array $fillable = [
        'slug', 'title', 'category', 'duration_days', 'duration_nights',
        'pickup_city', 'destination_city', 'base_price', 'description',
        'itinerary_json', 'inclusions', 'exclusions', 'max_passengers', 'image_url', 'status'
    ];

    public function findBySlug(string $slug): ?array
    {
        return $this->findBy('slug', $slug);
    }

    public function getPublished(): array
    {
        return $this->where("`status` = 'published'", [], 'title ASC');
    }
}
