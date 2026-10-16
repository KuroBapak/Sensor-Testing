<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorporateAccreditationResource\Pages;
use App\Models\CorporateAccreditation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CorporateAccreditationResource extends Resource
{
    protected static ?string $model = CorporateAccreditation::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('certification_category_id')
                    ->relationship('certificationCategory', 'name')
                    ->required(),
                Forms\Components\TextInput::make('standard_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('standard_description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('accreditation_body')
                    ->maxLength(255),
                Forms\Components\TextInput::make('registration_id')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('validity_date'),
                Forms\Components\Textarea::make('scope_detail')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('modal_title')
                    ->maxLength(255),
                Forms\Components\FileUpload::make('certificate_image')
                    ->image(),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('certificationCategory.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('standard_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('accreditation_body')
                    ->searchable(),
                Tables\Columns\TextColumn::make('registration_id')
                    ->searchable(),
                Tables\Columns\TextColumn::make('validity_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('modal_title')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('certificate_image'),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListCorporateAccreditations::route('/'),
            'create' => Pages\CreateCorporateAccreditation::route('/create'),
            'edit' => Pages\EditCorporateAccreditation::route('/{record}/edit'),
        ];
    }
}
