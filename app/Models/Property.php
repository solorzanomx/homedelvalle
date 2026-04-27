<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Property extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = ['title', 'description', 'price', 'city', 'colony', 'market_colonia_id', 'address', 'zipcode', 'area', 'construction_area', 'lot_area', 'parking', 'status', 'is_featured', 'bedrooms', 'bathrooms', 'half_bathrooms', 'floors', 'year_built', 'maintenance_fee', 'furnished', 'amenities', 'photo', 'property_type', 'operation_type', 'currency', 'broker_id', 'client_id', 'easybroker_id', 'easybroker_status', 'easybroker_published_at', 'easybroker_public_url', 'youtube_url'];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('gallery')
            ->useDisk('public');
    }

    public function marketColonia(): BelongsTo
    {
        return $this->belongsTo(MarketColonia::class, 'market_colonia_id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PropertyPhoto::class)->orderBy('sort_order');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class);
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    public function qrCode(): HasOne
    {
        return $this->hasOne(PropertyQrCode::class);
    }

    public function valuations(): HasMany
    {
        return $this->hasMany(PropertyValuation::class)->latest();
    }

    public function latestValuation(): HasOne
    {
        return $this->hasOne(PropertyValuation::class)->latestOfMany();
    }

    /**
     * Emails that included this property (stored as JSON array).
     */
    public function getEmailsAttribute(): Collection
    {
        return ClientEmail::whereJsonContains('property_ids', $this->id)->latest()->get();
    }

    public function primaryPhoto(): ?PropertyPhoto
    {
        return $this->photos->firstWhere('is_primary', true) ?? $this->photos->first();
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'area' => 'decimal:2',
            'construction_area' => 'decimal:2',
            'lot_area' => 'decimal:2',
            'maintenance_fee' => 'decimal:2',
            'bedrooms' => 'integer',
            'bathrooms' => 'integer',
            'half_bathrooms' => 'integer',
            'parking' => 'integer',
            'floors' => 'integer',
            'year_built' => 'integer',
            'amenities' => 'array',
            'easybroker_published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function getFormattedPriceAttribute(): string
    {
        $symbol = $this->currency === 'USD' ? 'USD $' : '$';
        return $symbol . number_format($this->price, 0, '.', ',');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? Storage::url($this->photo) : null;
    }

    public function getOperationLabelAttribute(): string
    {
        return match ($this->operation_type) {
            'sale' => 'Venta',
            'rental' => 'Renta',
            'temporary_rental' => 'Renta Temporal',
            default => $this->operation_type ?? '',
        };
    }

    public function getPropertyTypeLabelAttribute(): string
    {
        return match ($this->property_type) {
            'House' => 'Casa',
            'Apartment' => 'Departamento',
            'Land' => 'Terreno',
            'Office' => 'Oficina',
            'Commercial' => 'Comercial',
            'Warehouse' => 'Bodega',
            'Building' => 'Edificio',
            default => $this->property_type ?? '',
        };
    }

    public function getSlugAttribute(): string
    {
        return \Illuminate\Support\Str::slug($this->title);
    }

    public function isPublishedToEasyBroker(): bool
    {
        return $this->easybroker_id !== null && $this->easybroker_status === 'published';
    }

    public function hasEasyBrokerId(): bool
    {
        return $this->easybroker_id !== null;
    }

    /**
     * Import legacy PropertyPhoto rows into Media Library once.
     */
    public function importLegacyPhotosToMediaCollection(): int
    {
        if ($this->getMedia('gallery')->isNotEmpty()) {
            return 0;
        }

        $imported = 0;

        foreach ($this->photos()->orderBy('sort_order')->get() as $legacyPhoto) {
            if (! Storage::disk('public')->exists($legacyPhoto->path)) {
                continue;
            }

            $this->addMediaFromDisk($legacyPhoto->path, 'public')
                ->withCustomProperties([
                    'alt' => $legacyPhoto->description,
                    'legacy_photo_id' => $legacyPhoto->id,
                ])
                ->toMediaCollection('gallery', 'public');

            $imported++;
        }

        return $imported;
    }

    /**
     * Keep old property_photos/photo fields synced for existing Blade flows.
     */
    public function syncMediaGalleryToLegacyPhotos(): void
    {
        $mediaItems = $this->getMedia('gallery')->sortBy('order_column')->values();

        if ($mediaItems->isEmpty()) {
            $this->photos()->delete();
            if ($this->photo !== null) {
                $this->updateQuietly(['photo' => null]);
            }
            return;
        }

        $existingByPath = $this->photos()->get()->keyBy('path');
        $usedPaths = [];
        $order = 1;

        /** @var Media $mediaItem */
        foreach ($mediaItems as $mediaItem) {
            $path = $mediaItem->getPathRelativeToRoot();
            $usedPaths[] = $path;

            $payload = [
                'description' => (string) ($mediaItem->getCustomProperty('alt') ?? ''),
                'sort_order' => $order,
                'is_primary' => $order === 1,
            ];

            $legacyPhoto = $existingByPath->get($path);

            if ($legacyPhoto) {
                $legacyPhoto->update($payload);
            } else {
                $this->photos()->create(array_merge($payload, ['path' => $path]));
            }

            $order++;
        }

        $this->photos()->whereNotIn('path', $usedPaths)->delete();

        $firstPath = $usedPaths[0] ?? null;
        if ($this->photo !== $firstPath) {
            $this->updateQuietly(['photo' => $firstPath]);
        }
    }
}
