<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Brand;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\MarkdownEditor;
use App\Filament\Resources\BrandResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\BrandResource\RelationManagers;
use App\Filament\Resources\BrandResource\RelationManagers\ProductsRelationManager;

class BrandResource extends Resource
{
    protected static ?string $model = Brand::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Brands'; // custom name menu
    protected static ?string $navigationGroup = 'Shop'; // for grouping menu
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make('Information')->schema([
                        TextInput::make('name')->required()->live()->debounce(1000)->unique()->afterStateUpdated(function (string $operation, $state, Set $set) {
                            // if ($operation !== 'create') {
                            //     return;
                            // }

                            $set('slug', Str::slug($state));
                        }),
                        TextInput::make('slug')->disabled()->dehydrated()->required()->unique(Brand::class, 'slug', ignoreRecord: true),
                        TextInput::make('url')->label('Website URL')->required()->unique(Brand::class, 'url', ignoreRecord: true)->columnSpan('full'),
                        MarkdownEditor::make('description')->columnSpan('full'),
                    ])->columns(2)
                    ]),
                Group::make()->schema([
                    Section::make('Status')->schema([
                        Toggle::make('is_visible')->label("Visibility")->helperText('Enable or disable product visibility')->default(true),
                    ]),

                    Group::make()->schema([
                        Section::make('Color')->schema([
                            ColorPicker::make('primary_hex')->label('Primary Color')
                        ]),
                    ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('url')->label("Website URL")->searchable()->sortable(),
                Tables\Columns\ColorColumn::make('primary_hex')->label("Primary Color")->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_visible')->boolean()->sortable()->toggleable()->label('Visibility'),
                Tables\Columns\TextColumn::make('updated_at')->date()->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
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
            ProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBrands::route('/'),
            'create' => Pages\CreateBrand::route('/create'),
            'edit' => Pages\EditBrand::route('/{record}/edit'),
        ];
    }
}
