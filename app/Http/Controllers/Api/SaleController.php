<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index()
    {
        $sales = Sale::with('details.product')->orderByDesc('id')->get()
            ->map(fn ($sale) => [
                'id' => $sale->id,
                'total' => $sale->total,
                'total_discount' => $sale->total_discount,
                'created_at' => $sale->created_at->toDateString(),
                'details' => $sale->details->map(fn ($detail) => [
                    'product' => $detail->product?->name,
                    'quantity' => $detail->quantity,
                    'subtotal' => $detail->subtotal,
                    'discount_percent' => $detail->discount_percent,
                    'discount_amount' => $detail->discount_amount,
                    'subtotal_with_discount' => $detail->subtotal_with_discount,
                ]),
            ]);

        return response()->json($sales);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', Rule::in([1])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_percent' => ['sometimes', 'numeric', 'between:0,10'],
        ]);

        $sale = DB::transaction(function () use ($data) {
            $ids = collect($data['items'])->pluck('product_id')->sort()->values();
            $products = Product::whereIn('id', $ids)->orderBy('id')
                ->lockForUpdate()->get()->keyBy('id');

            $details = [];
            $totalCents = 0;
            $discountCentsTotal = 0;

            foreach ($data['items'] as $item) {
                $product = $products->get($item['product_id']);
                $quantity = $item['quantity'];

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock insuficiente para {$product->name}. Disponible: {$product->stock}"]
                    ]);
                }

                // Valores monetarios calculados en centavos para evitar errores de flotantes.
                $priceCents = (int) round(((float) $product->price) * 100);
                $subtotalCents = $priceCents * $quantity;
                $percentHundredths = (int) round(((float) ($item['discount_percent'] ?? 0)) * 100);
                // Descuento: redondear a centavos, mitad hacia arriba.
                $discountCents = intdiv($subtotalCents * $percentHundredths + 5000, 10000);
                $netCents = $subtotalCents - $discountCents;

                $discountCentsTotal += $discountCents;
                $totalCents += $netCents;
                $details[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => number_format($priceCents / 100, 2, '.', ''),
                    'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                    'discount_percent' => number_format($percentHundredths / 100, 2, '.', ''),
                    'discount_amount' => number_format($discountCents / 100, 2, '.', ''),
                    'subtotal_with_discount' => number_format($netCents / 100, 2, '.', ''),
                ];
            }

            $sale = Sale::create([
                'user_id' => 1,
                'total' => number_format($totalCents / 100, 2, '.', ''),
                'total_discount' => number_format($discountCentsTotal / 100, 2, '.', ''),
            ]);

            foreach ($details as $detail) {
                $sale->details()->create($detail);
                $products->get($detail['product_id'])->decrement('stock', $detail['quantity']);
            }

            return $sale;
        }, 3);

        return response()->json([
            'message' => 'Venta creada correctamente',
            'sale_id' => $sale->id,
            'total' => $sale->total,
            'total_discount' => $sale->total_discount,
        ], 201);
    }

    public function destroy($id)
    {
        $sale = Sale::find($id);
        if (!$sale) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }
        $sale->delete(); // Se conserva el comportamiento original: SoftDeletes, sin reintegrar stock.
        return response()->json(['message' => 'Venta eliminada correctamente']);
    }
}
