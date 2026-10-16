<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkResource\Pages;
use App\Models\Work;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkResource extends Resource
{
    protected static ?string $model = Work::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'id')
                    ->required(),
                Forms\Components\TextInput::make('work_title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('work_slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_featured')
                    ->required(),
                Forms\Components\TextInput::make('featured_sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\FileUpload::make('work_image')
                    ->image(),
                Forms\Components\TextInput::make('gallery_images'),
                Forms\Components\TextInput::make('top_badge')
                    ->maxLength(255),
                Forms\Components\TextInput::make('bottom_badge')
                    ->maxLength(255),
                Forms\Components\TextInput::make('serial_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('client_sector')
                    ->maxLength(255),
                Forms\Components\Textarea::make('short_description')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('full_description')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('highlight_tags'),
                Forms\Components\TextInput::make('quality_standards'),
                Forms\Components\TextInput::make('material')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tolerance_specs')
                    ->maxLength(255),
                Forms\Components\TextInput::make('tolerance_value')
                    ->numeric(),
                Forms\Components\TextInput::make('batch_scale')
                    ->maxLength(255),
                Forms\Components\TextInput::make('scale_value')
                    ->numeric(),
                Forms\Components\DatePicker::make('production_date'),
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
                Tables\Columns\TextColumn::make('service.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('work_title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('work_slug')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('featured_sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('work_image'),
                Tables\Columns\TextColumn::make('top_badge')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bottom_badge')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serial_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('client_sector')
                    ->searchable(),
                Tables\Columns\TextColumn::make('material')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tolerance_specs')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tolerance_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('batch_scale')
                    ->searchable(),
                Tables\Columns\TextColumn::make('scale_value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('production_date')
                    ->date()
                    ->sortable(),
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
            'index' => Pages\ListWorks::route('/'),
            'create' => Pages\CreateWork::route('/create'),
            'edit' => Pages\EditWork::route('/{record}/edit'),
        ];
    }
}
