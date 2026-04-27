<?php

namespace App\Filament\Resources\Properties;

use App\Filament\Resources\Properties\Pages\CreateProperty;
use App\Filament\Resources\Properties\Pages\EditProperty;
use App\Filament\Resources\Properties\Pages\ListProperties;
use App\Models\Property;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PropertyResource extends Resource
{
    protected static ?string $model = Property::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Titulo')
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->label('Precio')
                    ->required()
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0),
                TextInput::make('city')
                    ->label('Ciudad')
                    ->required()
                    ->maxLength(255),
                TextInput::make('colony')
                    ->label('Colonia')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'available' => 'Disponible',
                        'sold' => 'Vendida',
                        'rented' => 'Rentada',
                    ])
                    ->default('available'),
                Select::make('operation_type')
                    ->label('Operacion')
                    ->options([
                        'sale' => 'Venta',
                        'rental' => 'Renta',
                        'temporary_rental' => 'Renta temporal',
                    ])
                    ->default('sale'),
                Select::make('currency')
                    ->label('Moneda')
                    ->options([
                        'MXN' => 'MXN',
                        'USD' => 'USD',
                    ])
                    ->default('MXN'),
                SpatieMediaLibraryFileUpload::make('gallery')
                    ->label('Galeria de fotos')
                    ->collection('gallery')
                    ->disk('public')
                    ->multiple()
                    ->maxFiles(20)
                    ->reorderable()
                    ->appendFiles()
                    ->image()
                    ->imageEditor()
                    ->openable()
                    ->downloadable()
                    ->helperText('Puedes recortar/editar cada imagen desde el editor integrado.')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('gallery_preview')
                    ->label('Foto')
                    ->circular()
                    ->getStateUsing(fn (Property $record): ?string => $record->getFirstMediaUrl('gallery') ?: $record->photo_url),
                TextColumn::make('title')
                    ->label('Titulo')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Precio')
                    ->money('MXN')
                    ->sortable(),
                TextColumn::make('city')
                    ->label('Ciudad')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('updated_at')
                    ->label('Actualizada')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'available' => 'Disponible',
                        'sold' => 'Vendida',
                        'rented' => 'Rentada',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProperties::route('/'),
            'create' => CreateProperty::route('/create'),
            'edit' => EditProperty::route('/{record}/edit'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Propiedades (Filament)';
    }

    public static function getModelLabel(): string
    {
        return 'Propiedad';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Propiedades';
    }
}
