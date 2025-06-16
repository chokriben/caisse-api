<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Liste tous les produits
    public function index()
    {
        return Product::all();
    }

    // Ajouter un produit
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        return Product::create($validated);
    }

    // Modifier un produit
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name'  => 'required|string',
            'price' => 'required|numeric|min:0',
        ]);

        $product->update($validated);

        return $product;
    }

    // Supprimer un produit
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json(['message' => 'Produit supprimé']);
    }
}
