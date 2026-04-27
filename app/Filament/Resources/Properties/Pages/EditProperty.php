<?php

namespace App\Filament\Resources\Properties\Pages;

use App\Filament\Resources\Properties\PropertyResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditProperty extends EditRecord
{
    protected static string $resource = PropertyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('importLegacyPhotos')
                ->label('Importar fotos legacy')
                ->icon('heroicon-o-arrow-up-tray')
                ->visible(fn (): bool => $this->record->getMedia('gallery')->isEmpty() && $this->record->photos()->exists())
                ->requiresConfirmation()
                ->action(function (): void {
                    $count = $this->record->importLegacyPhotosToMediaCollection();
                    $this->record->syncMediaGalleryToLegacyPhotos();

                    Notification::make()
                        ->title("{$count} foto(s) importada(s) a Media Library")
                        ->success()
                        ->send();

                    $this->redirect(static::getResource()::getUrl('edit', ['record' => $this->record]));
                }),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $this->record->syncMediaGalleryToLegacyPhotos();
    }
}
