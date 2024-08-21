<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use App\Models\Product;
use Filament\Widgets\ChartWidget;

class ProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Products Chart';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = $this->getProductsPerMonth();
        return [
            'datasets' => [
                [
                    'label' => 'Product',
                    'data' => $data['productPerMonth']
                ]
            ],
            'labels' => $data['months']
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function getProductsPerMonth(): array
    {
        $now = Carbon::now();
        $productsPerMonth = [];

        $months = collect(range(1,12))->map(function($month) use($now,&$productsPerMonth){
            $count = Product::whereMonth('created_at',Carbon::parse($now->month($month)->format('Y-m')))->count();

            $productsPerMonth[] = $count;
            return $now->month($month)->format('m');
        })->toArray();
        return [
            'productPerMonth' => $productsPerMonth,
            'months' => $months
        ];
    }
}
