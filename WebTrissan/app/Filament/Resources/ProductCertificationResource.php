<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCertificationResource\Pages;
use App\Models\ProductCertification;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductCertificationResource extends Resource
{
    protected static ?string $model = ProductCertification::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('work_id')
                    ->relationship('work', 'id'),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('category_tag')
                    ->maxLength(255),
                Forms\Components\TextInput::make('category_icon')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_featured')
                    ->required(),
                Forms\Components\DatePicker::make('validity_date'),
                Forms\Components\TextInput::make('product_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('subtitle')
                    ->maxLength(255),
                Forms\Components\TextInput::make('model_spec_identifier')
                    ->maxLength(255),
                Forms\Components\TextInput::make('card_icon')
                    ->maxLength(255),
                Forms\Components\TextInput::make('certificate_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('issuing_body')
                    ->maxLength(255),
                Forms\Components\TextInput::make('spec_rows'),
                Forms\Components\Textarea::make('scope_description')
                    ->columnSpanFull(),
                Forms\Components\FileUpload::make('certificate_image')
                    ->image(),
                Forms\Components\TextInput::make('certificate_pdf')
                    ->maxLength(255),
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
                Tables\Columns\TextColumn::make('work.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category_tag')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category_icon')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean(),
                Tables\Columns\TextColumn::make('validity_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('subtitle')
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_spec_identifier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('card_icon')
                    ->searchable(),
                Tables\Columns\TextColumn::make('certificate_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('issuing_body')
                    ->searchable(),
                Tables\Columns\ImageColumn::make('certificate_image'),
                Tables\Columns\TextColumn::make('certificate_pdf')
                    ->searchable(),
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
            'index' => Pages\ListProductCertifications::route('/'),
            'create' => Pages\CreateProductCertification::route('/create'),
            'edit' => Pages\EditProductCertification::route('/{record}/edit'),
        ];
    }
}
