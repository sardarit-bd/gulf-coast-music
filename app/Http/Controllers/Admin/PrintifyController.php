<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PrintifyController extends Controller
{
    protected $apiToken;
    protected $shopId;

    public function __construct()
    {
        $this->apiToken = env('PRINTIFY_API_TOKEN');
        $this->shopId = 24164961;
    }

    // 1. Fetch Shop Info
    public function getShop()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->get("https://api.printify.com/v1/shops.json");

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch shop', 'details' => $response->json()], 400);
        }

        return response()->json($response->json());
    }

    // 2. Fetch Products
    public function getProducts()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->get("https://api.printify.com/v1/shops/{$this->shopId}/products.json");

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch products', 'details' => $response->json()], 400);
        }

        return response()->json($response->json());
    }

    // 3. Add Product
    public function addProduct(Request $request)
    {
        $payload = $request->all();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ])->post("https://api.printify.com/v1/shops/{$this->shopId}/products.json", $payload);

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to add product', 'details' => $response->json()], 400);
        }

        return response()->json($response->json());
    }

    // 4. Fetch Orders
    public function getOrders()
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->get("https://api.printify.com/v1/shops/{$this->shopId}/orders.json");

        if ($response->failed()) {
            return response()->json(['error' => 'Failed to fetch orders', 'details' => $response->json()], 400);
        }

        return response()->json($response->json());
    }
}
