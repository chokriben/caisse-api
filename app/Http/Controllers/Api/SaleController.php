<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    // Enregistrer une vente
    public function store(Request $request)
    {
        $validated = $request->validate([
            'total'     => 'required|numeric',
            'received'  => 'required|numeric',
            'change'    => 'required|numeric',
            'items'     => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.price'      => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $sale = Sale::create([
                'total'    => $validated['total'],
                'received' => $validated['received'],
                'change'   => $validated['change'],
            ]);

            foreach ($validated['items'] as $item) {
                SaleItem::create([
                    'sale_id'    => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Vente enregistrée', 'sale' => $sale], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Erreur lors de la création de la vente', 'details' => $e->getMessage()], 500);
        }
    }

    // Liste toutes les ventes
    public function index()
    {
        return Sale::with('items.product')->latest()->get();
    }

    // Voir un ticket spécifique
    public function show($id)
    {
        $sale = Sale::with('items.product')->findOrFail($id);
        return $sale;
    }

    public function stats()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();

        $salesToday = DB::table('sales')->whereDate('created_at', $today)->count();
        $totalToday = DB::table('sales')->whereDate('created_at', $today)->sum('total');

        $salesThisWeek = DB::table('sales')->whereBetween('created_at', [$weekStart, now()])->count();
        $totalThisWeek = DB::table('sales')->whereBetween('created_at', [$weekStart, now()])->sum('total');

        return response()->json([
            'tickets_today' => $salesToday,
            'total_today' => $totalToday,
            'tickets_week' => $salesThisWeek,
            'total_week' => $totalThisWeek,
        ]);
    }

    public function closeDay()
    {
        $total = Sale::whereDate('created_at', today())->sum('total');
        $count = Sale::whereDate('created_at', today())->count();

        // Logique pour réinitialiser ou archiver ici
        Sale::whereDate('created_at', today())->delete();

        return response()->json([
            'total' => $total,
            'ticket_count' => $count,
        ]);
    }

    public function monthlyStats()
{
    $monthly = Sale::selectRaw('MONTH(created_at) as month, SUM(total) as total')
        ->groupByRaw('MONTH(created_at)')
        ->orderByRaw('MONTH(created_at)')
        ->get()
        ->map(function ($item) {
            return [
                'month' => Carbon::create()->month($item->month)->format('M'), // Jan, Feb, etc.
                'sales' => $item->total,
            ];
        });

    return response()->json($monthly);
}

}
