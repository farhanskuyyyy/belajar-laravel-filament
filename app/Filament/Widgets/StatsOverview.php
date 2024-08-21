<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\Customer;
use App\Enums\OrderStatusEnum;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers',Customer::count())
                ->description("Increase in customers")
                ->descriptionIcon("heroicon-m-arrow-trending-up")
                ->color("success")
                ->chart([2,3,1,2,3,2,1,2]),

            Stat::make('Total Products',Product::count())
                ->description("Total products in app")
                ->descriptionIcon("heroicon-m-arrow-trending-down")
                ->color("danger")
                ->chart([2,3,4,2,1,0,1,2]),

            Stat::make('Pending Orders',Order::where('status',OrderStatusEnum::PENDING->value)->count())
                ->description("Total orders in app")
                ->descriptionIcon("heroicon-m-arrow-trending-down")
                ->color("danger")
                ->chart([2,3,4,2,5,0,3,2]),


        ];
    }
}
