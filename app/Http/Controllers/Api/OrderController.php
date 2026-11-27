<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderItemRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $orders = Order::query()
            ->with(['user', 'items.menu'])
            ->when(! $user->isAdmin(), fn ($q) => $q->ownedBy($user->id))
            ->status($request->query('status'))
            ->search($request->query('search'))
            ->latest()
            ->paginate((int) min($request->integer('per_page', 10), 50));

        return ApiResponse::paginated($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $items = collect($request->input('items'));

        $menuIds = $items->pluck('menu_id')->all();
        $menus = Menu::query()->whereIn('id', $menuIds)->get()->keyBy('id');

        foreach ($items as $item) {
            $menu = $menus->get($item['menu_id']);

            if (! $menu->is_available) {
                return ApiResponse::error('Menu tidak tersedia.', 422, [
                    'items' => ["Menu {$menu->name} sedang tidak tersedia."],
                ]);
            }

            if ($menu->stock < $item['quantity']) {
                return ApiResponse::error('Stok tidak mencukupi.', 422, [
                    'items' => ["Stok {$menu->name} tersisa {$menu->stock}."],
                ]);
            }
        }

        $order = DB::transaction(function () use ($request, $items, $menus) {
            $order = Order::create([
                'order_code' => $this->generateOrderCode(),
                'user_id' => $request->user()->id,
                'status' => 'pending',
                'order_type' => $request->string('order_type')->toString(),
                'note' => $request->input('note'),
                'total_price' => 0,
            ]);

            foreach ($items as $item) {
                $menu = $menus->get($item['menu_id']);
                $subtotal = $menu->price * $item['quantity'];

                $order->items()->create([
                    'menu_id' => $menu->id,
                    'quantity' => $item['quantity'],
                    'price' => $menu->price,
                    'subtotal' => $subtotal,
                ]);

                $menu->decrement('stock', $item['quantity']);
            }

            $order->recalculateTotal();

            return $order;
        });

        return ApiResponse::created(
            new OrderResource($order->load(['user', 'items.menu'])),
            'Pesanan berhasil dibuat.'
        );
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorizeAccess($request, $order);

        return ApiResponse::success(new OrderResource($order->load(['user', 'items.menu'])));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order->update($request->only(['status', 'note']));

        return ApiResponse::success(
            new OrderResource($order->fresh()->load(['user', 'items.menu'])),
            'Status pesanan berhasil diperbarui.'
        );
    }

    public function addItem(StoreOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorizeAccess($request, $order);

        if ($order->status !== 'pending') {
            return ApiResponse::error('Item hanya bisa ditambah saat pesanan masih pending.', 422, [
                'order' => ['Status pesanan saat ini: '.$order->status],
            ]);
        }

        $items = collect($request->input('items'));
        $menus = Menu::query()->whereIn('id', $items->pluck('menu_id'))->get()->keyBy('id');

        DB::transaction(function () use ($order, $items, $menus) {
            foreach ($items as $item) {
                $menu = $menus->get($item['menu_id']);
                $subtotal = $menu->price * $item['quantity'];

                $order->items()->updateOrCreate(
                    ['menu_id' => $menu->id],
                    [
                        'quantity' => $item['quantity'],
                        'price' => $menu->price,
                        'subtotal' => $subtotal,
                    ],
                );
            }

            $order->recalculateTotal();
        });

        return ApiResponse::success(
            new OrderResource($order->fresh()->load(['user', 'items.menu'])),
            'Item pesanan berhasil ditambahkan.'
        );
    }

    public function updateItem(UpdateOrderItemRequest $request, Order $order, OrderItem $item): JsonResponse
    {
        $this->authorizeAccess($request, $order);

        if ($item->order_id !== $order->id) {
            return ApiResponse::error('Item tidak termasuk dalam pesanan ini.', 422);
        }

        if ($order->status !== 'pending') {
            return ApiResponse::error('Item hanya bisa diubah saat pesanan masih pending.', 422);
        }

        $quantity = $request->integer('quantity');
        $item->update([
            'quantity' => $quantity,
            'subtotal' => $item->price * $quantity,
        ]);

        $order->recalculateTotal();

        return ApiResponse::success(
            new OrderResource($order->fresh()->load(['user', 'items.menu'])),
            'Item pesanan berhasil diperbarui.'
        );
    }

    public function removeItem(Request $request, Order $order, OrderItem $item): JsonResponse
    {
        $this->authorizeAccess($request, $order);

        if ($item->order_id !== $order->id) {
            return ApiResponse::error('Item tidak termasuk dalam pesanan ini.', 422);
        }

        if ($order->items()->count() === 1) {
            return ApiResponse::error('Pesanan minimal memiliki satu item.', 422);
        }

        $item->delete();
        $order->recalculateTotal();

        return ApiResponse::success(
            new OrderResource($order->fresh()->load(['user', 'items.menu'])),
            'Item pesanan berhasil dihapus.'
        );
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $this->authorizeAccess($request, $order);

        if (! in_array($order->status, ['pending', 'paid'], true)) {
            return ApiResponse::error('Pesanan dengan status ini tidak bisa dibatalkan.', 422);
        }

        $order->update(['status' => 'cancelled']);

        return ApiResponse::success(
            new OrderResource($order->fresh()->load(['user', 'items.menu'])),
            'Pesanan berhasil dibatalkan.'
        );
    }

    public function destroy(Request $request, Order $order): JsonResponse
    {
        $this->authorizeAccess($request, $order);

        if ($order->status !== 'cancelled') {
            return ApiResponse::error('Hanya pesanan berstatus cancelled yang bisa dihapus.', 422);
        }

        $order->delete();

        return ApiResponse::success(null, 'Pesanan berhasil dihapus.');
    }

    public function receipt(Request $request, Order $order): JsonResponse
    {
        $this->authorizeAccess($request, $order);
        $order->load(['user', 'items.menu']);

        return ApiResponse::success([
            'order_code' => $order->order_code,
            'status' => $order->status,
            'order_type' => $order->order_type,
            'created_at' => $order->created_at?->toIso8601String(),
            'customer' => $order->user->name,
            'lines' => $order->items->map(fn (OrderItem $item) => [
                'menu' => $item->menu->name,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
            ]),
            'total_price' => $order->total_price,
            'total_price_formatted' => 'Rp '.number_format($order->total_price, 0, ',', '.'),
        ]);
    }

    /**
     * Ringkasan pesanan untuk dashboard admin.
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $base = Order::query()->when(! $user->isAdmin(), fn ($q) => $q->ownedBy($user->id));

        $byStatus = (clone $base)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return ApiResponse::success([
            'total_orders' => (clone $base)->count(),
            'total_spending' => (int) (clone $base)->where('status', 'completed')->sum('total_price'),
            'by_status' => $byStatus,
        ]);
    }

    private function authorizeAccess(Request $request, Order $order): void
    {
        $user = $request->user();

        abort_if(! $user->isAdmin() && $order->user_id !== $user->id, 404, 'Resource tidak ditemukan.');
    }

    private function generateOrderCode(): string
    {
        $prefix = 'ORD-'.now()->format('Ymd');

        $lastId = (int) Order::query()->max('id') + 1;

        return sprintf('%s-%05d', $prefix, $lastId);
    }
}
