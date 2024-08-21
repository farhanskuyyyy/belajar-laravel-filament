<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use App\Enums\ProductTypeEnum;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Tabs::make('Products')->tabs([
                    Tab::make('Information')->schema([
                        TextInput::make('name')->required()->live(onBlur: false)->unique()->afterStateUpdated(function (string $operation, $state, Set $set) {
                            if ($operation !== 'create') {
                                return;
                            }

                            $set('slug', Str::slug($state));
                        }),
                        TextInput::make('slug')->disabled()->dehydrated()->required()->unique(Product::class, 'slug', ignoreRecord: true),
                        MarkdownEditor::make('description')->columnSpan('full'),
                    ])->columns(2),
                    Tab::make('Pricing & Inventory')->schema([
                        TextInput::make('sku')->label('SKU (Stock Keeping Unit)')->unique()->required(),
                        TextInput::make('price')->numeric()->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')->required(),
                        TextInput::make('quantity')->numeric()->minValue(0)->required(),
                        Select::make('type')->options([
                            'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                            'deliverable' => ProductTypeEnum::DELIVERATBLE->value,
                        ]),
                    ])->columns(2),
                    Tab::make('Additional Information')->schema([
                        Toggle::make('is_visible')->label("Visibility")->helperText('Enable or disable product visibility')->default(true),
                        Toggle::make('is_featured')->label("Featured")->helperText('Enable or disable featured status'),
                        DatePicker::make('published_at')->label('Availability')->default(now()),
                        Select::make('brand_id')->relationship('brand', 'name')->required(),
                        // Select::make('categories')->relationship('categories','name')->multiple()->required(),
                        FileUpload::make('image')->directory('form-attachment')->preserveFilenames()->image()->imageEditor()->required()->columnSpanFull()
                    ])->columns(2),
                ])->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('brand.name')->searchable()->sortable()->toggleable(),
                Tables\Columns\IconColumn::make('is_visible')->boolean()->sortable()->toggleable()->label('Visibility'),
                Tables\Columns\TextColumn::make('price')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('quantity')->sortable()->toggleable(),
                Tables\Columns\TextColumn::make('published_at')->date()->sortable(),
                Tables\Columns\TextColumn::make('type'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                ActionGroup::make([
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
}
