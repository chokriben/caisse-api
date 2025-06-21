<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\CashClosure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashClosureController extends Controller
{
    /**
     * Clôturer la caisse pour la journée en cours
     */
    public function closeCashRegister(Request $request)
    {
        // Validation des données entrantes
        $request->validate([
            'real_cash' => 'required|numeric',
        ]);

        $today = now()->toDateString();

        // Vérifier si une clôture a déjà été faite aujourd'hui
        if (CashClosure::where('date', $today)->exists()) {
            return response()->json([
                'message' => 'La caisse a déjà été clôturée aujourd\'hui.'
            ], 409);
        }

        try {
            // Récupération des ventes du jour
            $sales = Sale::whereDate('created_at', $today)->get();

            // Calculs
            $totalSales = $sales->sum('total');
            $totalReceived = $sales->sum('received');
            $totalChange = $sales->sum('change');

            $realCash = $request->input('real_cash');
            $expectedCash = $totalReceived - $totalChange;
            $difference = $realCash - $expectedCash;

            // Vérification qu'un utilisateur est bien connecté
            $userId = Auth::id();
            if (!$userId) {
                return response()->json([
                    'message' => 'Utilisateur non authentifié.'
                ], 401);
            }

            // Enregistrement de la clôture
            $closure = CashClosure::create([
                'date' => $today,
                'total_sales' => $totalSales,
                'total_received' => $totalReceived,
                'total_change' => $totalChange,
                'real_cash' => $realCash,
                'difference' => $difference,
                'user_id' => $userId,
            ]);

            return response()->json([
                'message' => 'Caisse clôturée avec succès.',
                'closure' => $closure,
            ], 201); // 201 = Created
        } catch (\Exception $e) {
            // Pour debug, surtout en dev
            return response()->json([
                'message' => 'Erreur lors de la clôture de caisse.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
public function getClosures(Request $request)
{
    // Récupérer les 30 dernières clôtures avec l'utilisateur lié
    $closures = CashClosure::with('user')
        ->orderBy('date', 'desc')
        ->take(30)
        ->get();

    return response()->json([
        'closures' => $closures
    ]);
}

}
