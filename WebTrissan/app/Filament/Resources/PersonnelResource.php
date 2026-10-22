<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PersonnelResource\Pages;
use App\Models\Personnel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PersonnelResource extends Resource
{
    protected static ?string $model = Personnel::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    protected static ?string $navigationGroup = 'Portfolio Page';

    protected static ?string $navigationLabel = 'Personnel Directory';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('specialty_cluster_id')
                    ->relationship('specialtyCluster', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\TextInput::make('registration_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('job_role')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('technical_focus')
                    ->maxLength(255),
                Forms\Components\TextInput::make('qualification_rank')
                    ->numeric(),
                Forms\Components\Toggle::make('status')
                    ->required()
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('skills')
                    ->relationship('skills', 'skill_name')
                    ->multiple()
                    ->preload()
                    ->minItems(1)
                    ->maxItems(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_id')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('specialtyCluster.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('job_role')
                    ->searchable(),
                Tables\Columns\TextColumn::make('skills.skill_name')
                    ->badge()
                    ->separator(', '),
                Tables\Columns\IconColumn::make('status')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('qualification_rank')
                    ->numeric()
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
                Tables\Filters\TernaryFilter::make('status'),
                Tables\Filters\SelectFilter::make('specialty_cluster_id')
                    ->relationship('specialtyCluster', 'name')
                    ->label('Specialty Cluster'),
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
            'index' => Pages\ListPersonnels::route('/'),
            'create' => Pages\CreatePersonnel::route('/create'),
            'edit' => Pages\EditPersonnel::route('/{record}/edit'),
        ];
    }
}
