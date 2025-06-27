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
        'shift' => 'required|in:matin,soir,nuit', // shift requis et validé
    ]);

    $today = now()->toDateString();
    $shift = $request->input('shift');

    // Vérifier si une clôture existe déjà pour ce jour et ce shift
    if (CashClosure::where('date', $today)->where('shift', $shift)->exists()) {
        return response()->json([
            'message' => "La clôture pour le shift '$shift' a déjà été faite aujourd'hui."
        ], 409);
    }

    try {
        // Déterminer la plage horaire du shift
        [$start, $end] = $this->getShiftTimeRange($shift);

        // Récupération des ventes pendant le shift
        $sales = Sale::whereBetween('created_at', [$start, $end])->get();

        // Calculs
        $totalSales = $sales->sum('total');
        $totalReceived = $sales->sum('received');
        $totalChange = $sales->sum('change');

        $realCash = $request->input('real_cash');
        $expectedCash = $totalReceived - $totalChange;
        $difference = $realCash - $expectedCash;

        // Vérification qu'un utilisateur est connecté
        $userId = Auth::id();
        if (!$userId) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.'
            ], 401);
        }

        // Enregistrement de la clôture
        $closure = CashClosure::create([
            'date' => $today,
            'shift' => $shift,
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
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Erreur lors de la clôture de caisse.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    private function getShiftTimeRange(string $shift): array
{
    $today = now()->startOfDay();
    switch ($shift) {
        case 'matin':
            return [$today->copy()->setTime(8, 0), $today->copy()->setTime(14, 0)];
        case 'soir':
            return [$today->copy()->setTime(14, 0), $today->copy()->setTime(22, 0)];
        case 'nuit':
            return [
                $today->copy()->setTime(22, 0),
                $today->copy()->addDay()->setTime(6, 0),
            ];
        default:
            throw new \InvalidArgumentException("Shift invalide : $shift");
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
