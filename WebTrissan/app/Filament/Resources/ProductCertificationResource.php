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

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'License & Certification';

    protected static ?string $navigationLabel = 'Product Certifications';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Certification Details')
                    ->schema([
                        Forms\Components\Select::make('work_id')
                            ->relationship('work', 'work_title')
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('product_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('subtitle')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('certificate_number')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('issuing_body')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('validity_date'),
                        Forms\Components\TextInput::make('model_spec_identifier')
                            ->maxLength(255),
                    ])->columns(2),

                Forms\Components\Section::make('Categorization & Visibility')
                    ->schema([
                        Forms\Components\TextInput::make('category_tag')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('category_icon')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('card_icon')
                            ->maxLength(255),
                        Forms\Components\Toggle::make('is_featured')
                            ->required(),
                        Forms\Components\TextInput::make('sort_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Specifications & Files')
                    ->schema([
                        Forms\Components\Textarea::make('scope_description')
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('spec_rows')
                            ->schema([
                                Forms\Components\TextInput::make('label')->required(),
                                Forms\Components\TextInput::make('value')->required(),
                            ])
                            ->columns(2)
                            ->maxItems(4)
                            ->columnSpanFull(),
                        Forms\Components\FileUpload::make('certificate_image')
                            ->image()
                            ->directory('certificates/images'),
                        Forms\Components\FileUpload::make('certificate_pdf')
                            ->acceptedFileTypes(['application/pdf'])
                            ->directory('certificates/pdf'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('certificate_image'),
                Tables\Columns\TextColumn::make('product_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('work.work_title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('certificate_number')
                    ->searchable(),
                Tables\Columns\TextColumn::make('issuing_body')
                    ->searchable(),
                Tables\Columns\TextColumn::make('validity_date')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\SelectFilter::make('work_id')
                    ->relationship('work', 'work_title')
                    ->label('Work'),
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
