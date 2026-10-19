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

    protected static ?string $navigationIcon = 'heroicon-o-briefcase';

    protected static ?string $navigationGroup = 'Works Page';

    protected static ?string $navigationLabel = 'Works';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('General Information')
                    ->schema([
                        Forms\Components\Select::make('service_id')
                            ->relationship('service', 'service_title')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Forms\Components\TextInput::make('work_title')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('work_slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('serial_number')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('client_sector')
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('production_date')
                            ->required(),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Badges')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')
                            ->required(),
                        Forms\Components\TextInput::make('featured_sort_order')
                            ->numeric()
                            ->default(0),
                        Forms\Components\TextInput::make('top_badge')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('bottom_badge')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('sort_order')
                            ->required()
                            ->numeric()
                            ->default(0),
                    ])->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('work_image')
                            ->disk('public')
                            ->image()
                            ->directory('works'),
                        Forms\Components\FileUpload::make('gallery_images')
                            ->disk('public')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->directory('works/gallery')
                            ->minFiles(1)
                            ->maxFiles(3),
                    ]),

                Forms\Components\Section::make('Descriptions & Highlights')
                    ->schema([
                        Forms\Components\Textarea::make('short_description')
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('full_description')
                            ->columnSpanFull(),
                        Forms\Components\Repeater::make('highlight_tags')
                            ->simple(Forms\Components\TextInput::make('tag')->required())
                            ->minItems(2)
                            ->maxItems(3)
                            ->default(['Precision Machining', 'ISO Compliant']),
                        Forms\Components\Repeater::make('quality_standards')
                            ->simple(Forms\Components\TextInput::make('standard')->required())
                            ->minItems(1)
                            ->maxItems(3)
                            ->default(['ISO 9001:2015']),
                    ])->columns(2),

                Forms\Components\Section::make('Technical Specifications')
                    ->schema([
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
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('work_image'),
                Tables\Columns\TextColumn::make('work_title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('service.service_title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_featured')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('client_sector')
                    ->searchable(),
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
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_featured'),
                Tables\Filters\SelectFilter::make('service_id')
                    ->relationship('service', 'service_title')
                    ->label('Service'),
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
