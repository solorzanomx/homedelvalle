<?php

namespace App\Filament\Resources;

use App\Models\FormSubmission;
use Filament\Forms\Components\{FileUpload, Grid, Section, Select, Textarea, TextInput, Toggle, Badge, Placeholder};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\{BulkActionGroup, DeleteBulkAction, EditAction, ViewAction};
use Filament\Tables\Columns\{BadgeColumn, TextColumn};
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\{SelectFilter, TernaryFilter};
use Filament\Tables\Table;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;
    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = 'Leads / Form Submissions';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'CRM';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Lead Information')
                    ->columns(['sm' => 2])
                    ->schema([
                        TextInput::make('full_name')->label('Nombre completo')->required()->disabled(),
                        TextInput::make('email')->email()->required()->disabled(),
                        TextInput::make('phone')->label('Teléfono / WhatsApp')->required()->disabled(),
                        Select::make('form_type')
                            ->label('Tipo de formulario')
                            ->options([
                                'vendedor' => 'Vendedor',
                                'comprador' => 'Comprador',
                                'b2b' => 'Desarrollador / Inversionista',
                                'contacto' => 'Contacto',
                                'propiedad' => 'Consulta de propiedad',
                            ])
                            ->disabled(),
                        Select::make('lead_tag')
                            ->label('Tag / Categoría')
                            ->options([
                                'LEAD_VENDEDOR' => 'Vendedor',
                                'LEAD_COMPRADOR' => 'Comprador',
                                'LEAD_B2B' => 'B2B',
                                'LEAD_ADMIN' => 'Administración',
                                'LEAD_LEGAL' => 'Legal',
                                'LEAD_OTRO' => 'Otro',
                            ])
                            ->disabled(),
                        TextInput::make('source_page')->label('Origen (URL)')->disabled(),
                    ]),

                Section::make('Lead Qualification')
                    ->columns(['sm' => 2])
                    ->schema([
                        Placeholder::make('client_type')
                            ->label('Tipo de Lead')
                            ->content(fn (FormSubmission $record) => match($record->client_type) {
                                'buyer' => 'Comprador',
                                'owner' => 'Propietario',
                                'investor' => 'Inversionista',
                                default => $record->client_type ?? '—'
                            }),
                        Placeholder::make('lead_temperature')
                            ->label('Lead Temperature')
                            ->content(fn (FormSubmission $record) => match ($record->lead_temperature) {
                                'hot' => '🔥 Hot',
                                'warm' => '🔆 Warm',
                                'cold' => '❄️ Cold',
                                default => $record->lead_temperature ?? '—',
                            }),
                        Placeholder::make('budget_range')
                            ->label('Presupuesto')
                            ->content(fn (FormSubmission $record) => $record->budget_min && $record->budget_max
                                ? "$ " . number_format($record->budget_min, 0, ',', '.') . " - $ " . number_format($record->budget_max, 0, ',', '.')
                                : '—'),
                        Placeholder::make('property_type')
                            ->label('Tipo de Propiedad')
                            ->content(fn (FormSubmission $record) => $record->property_type ?? '—'),
                    ]),

                Section::make('Status & Assignment')
                    ->columns(['sm' => 2])
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'Nuevo',
                                'contacted' => 'Contactado',
                                'qualified' => 'Calificado',
                                'won' => 'Ganado',
                                'lost' => 'Perdido',
                            ]),
                        Select::make('assigned_to')
                            ->label('Asignado a')
                            ->relationship('assignedUser', 'name')
                            ->searchable()
                            ->preload(),
                    ]),

                Section::make('Contact Log')
                    ->columns(['sm' => 1])
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notas / Seguimiento')
                            ->rows(4),
                    ]),

                Section::make('Technical Data')
                    ->columns(['sm' => 2])
                    ->collapsed()
                    ->schema([
                        TextInput::make('ip')->label('IP Address')->disabled(),
                        TextInput::make('utm_source')->label('UTM Source')->disabled(),
                        TextInput::make('utm_medium')->label('UTM Medium')->disabled(),
                        TextInput::make('utm_campaign')->label('UTM Campaign')->disabled(),
                        TextInput::make('referrer')->label('Referrer URL')->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                BadgeColumn::make('form_type')
                    ->label('Tipo')
                    ->colors([
                        'danger' => 'vendedor',
                        'info' => 'comprador',
                        'warning' => 'b2b',
                        'gray' => 'contacto',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'vendedor' => 'Vender',
                        'comprador' => 'Comprar',
                        'b2b' => 'B2B',
                        'contacto' => 'Contacto',
                        default => $state,
                    }),
                TextColumn::make('full_name')->label('Nombre')->searchable()->sortable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('phone')->label('Teléfono')->searchable(),
                BadgeColumn::make('lead_tag')->label('Tag'),
                BadgeColumn::make('lead_temperature')
                    ->label('Temperatura')
                    ->colors([
                        'danger' => 'hot',
                        'warning' => 'warm',
                        'gray' => 'cold',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'hot' => '🔥 Hot',
                        'warm' => '🔆 Warm',
                        'cold' => '❄️ Cold',
                        default => $state ?? '—',
                    }),
                BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'new',
                        'warning' => 'contacted',
                        'info' => 'qualified',
                        'success' => 'won',
                        'gray' => 'lost',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'new' => 'Nuevo',
                        'contacted' => 'Contactado',
                        'qualified' => 'Calificado',
                        'won' => 'Ganado',
                        'lost' => 'Perdido',
                        default => $state,
                    }),
                TextColumn::make('assignedUser.name')->label('Asignado a')->searchable(),
                TextColumn::make('created_at')->label('Creado')->dateTime('M d, Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('form_type')
                    ->options([
                        'vendedor' => 'Vendedor',
                        'comprador' => 'Comprador',
                        'b2b' => 'Desarrollador/Inversionista',
                        'contacto' => 'Contacto',
                    ]),
                SelectFilter::make('lead_tag'),
                SelectFilter::make('status')
                    ->options([
                        'new' => 'Nuevo',
                        'contacted' => 'Contactado',
                        'qualified' => 'Calificado',
                        'won' => 'Ganado',
                        'lost' => 'Perdido',
                    ]),
                TernaryFilter::make('assigned_to')->label('Sin asignar'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ], position: ActionsPosition::BeforeCells)
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\FormSubmissionResource\Pages\ListFormSubmissions::class,
            'view' => \App\Filament\Resources\FormSubmissionResource\Pages\ViewFormSubmission::class,
            'edit' => \App\Filament\Resources\FormSubmissionResource\Pages\EditFormSubmission::class,
        ];
    }
}
