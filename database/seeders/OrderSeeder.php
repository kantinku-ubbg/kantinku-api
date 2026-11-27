<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', User::ROLE_CUSTOMER)->orderBy('id')->get();

        if ($customers->isEmpty() || Menu::count() === 0) {
            return;
        }

        $statuses = ['pending', 'paid', 'preparing', 'ready', 'completed', 'cancelled', 'completed', 'paid'];

        foreach ($statuses as $index => $status) {
            $customer = $customers[$index % $customers->count()];
            $order = Order::updateOrCreate(
                ['order_code' => sprintf('ORD-%s-%03d', now()->format('Ymd'), $index + 1)],
                [
                    'user_id' => $customer->id,
                    'status' => $status,
                    'order_type' => $index % 2 === 0 ? 'dine_in' : 'take_away',
                    'note' => $index % 3 === 0 ? 'Tanpa sambal' : null,
                ],
            );

            $menus = Menu::inRandomOrder()->limit(2)->get();
            $total = 0;

            foreach ($menus as $offset => $menu) {
                $quantity = ($index % 3) + 1;
                $subtotal = $menu->price * $quantity;
                $total += $subtotal;

                $order->items()->updateOrCreate(
                    ['menu_id' => $menu->id],
                    [
                        'quantity' => $quantity + $offset,
                        'price' => $menu->price,
                        'subtotal' => $menu->price * ($quantity + $offset),
                    ],
                );
            }

            $total = (int) $order->items()->sum('subtotal');
            $order->update(['total_price' => $total]);
        }
    }
}
