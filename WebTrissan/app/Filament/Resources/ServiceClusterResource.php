<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceClusterResource\Pages;
use App\Models\ServiceCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ServiceClusterResource extends Resource
{
    protected static ?string $model = ServiceCluster::class;

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Works Page';

    protected static ?string $navigationLabel = 'Service Clusters';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cluster_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('cluster_tag')
                    ->maxLength(255),
                Forms\Components\TextInput::make('cluster_icon')
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
                Tables\Columns\TextColumn::make('cluster_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cluster_tag')
                    ->searchable(),
                Tables\Columns\TextColumn::make('cluster_icon')
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
            'index' => Pages\ListServiceClusters::route('/'),
            'create' => Pages\CreateServiceCluster::route('/create'),
            'edit' => Pages\EditServiceCluster::route('/{record}/edit'),
        ];
    }
}
