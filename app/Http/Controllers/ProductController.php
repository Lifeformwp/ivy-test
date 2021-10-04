<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use \Illuminate\Http\JsonResponse;
use \Illuminate\Contracts\Foundation\Application;
use \Illuminate\Contracts\Routing\ResponseFactory;
use \Illuminate\Http\Response;

/**
 * Class ProductController
 *
 * @package App\Http\Controllers
 */
class ProductController extends Controller
{
    /**
     * Add product.
     *
     * @return JsonResponse
     */
    public function add(Request $request)
    {
        $request->validate([
            'code' => [
                'required',
                'string'
            ],
            'name' => [
                'required',
                'string'
            ],
            'description' => [
                'required',
                'string'
            ]
        ]);

        $product = Product::create([
            'code' => $request->post('code'),
            'name' => $request->post('name'),
            'description' => $request->post('description'),
            'created_at' => (new \DateTimeImmutable('now'))->getTimestamp()
        ]);

        return response()->json($product);
    }

    /**
     * Update product.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $request->validate([
            'code' => [
                'string'
            ],
            'name' => [
                'string'
            ],
            'description' => [
                'string'
            ]
        ]);

        $product = Product::find($id);

        $product->code = $request->post('code');
        $product->name = $request->post('name');
        $product->description = $request->post('description');

        $product->save();

        return response()->json($product);
    }

    /**
     * Delete product.
     *
     * @param  int  $id
     * @return Application|Response|ResponseFactory
     */
    public function delete(int $id)
    {
        DB::table('products')
            ->delete($id);

        return response([], 204);
    }

    /**
     * List products.
     *
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $request->validate([
            'order' => [
                'string',
                Rule::in(['ASC', 'DESC']),
                'nullable'
            ],
            'isStockAvailable' => [
                'bool',
                'nullable'
            ]
        ]);

        $productsQuery = DB::table('products', 'p')
            ->select('p.id', 'p.name', 'p.code')
            ->join('stocks', 'p.id', '=', 'stocks.product_id');

        if (!empty($request->query('isStockAvailable'))) {
            $productsQuery->where('stocks.taken', '=', '0');
        }

        if (!empty($request->query('order'))) {
            $productsQuery->orderBy('stocks.on_hand', $request->query('order'));
        }

        $products = $productsQuery->get();

        return response()->json($products);
    }

    /**
     * Show product details.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id)
    {
        $request->validate([
            'id' => [
                'required',
                'integer'
            ]
        ]);

        $productDetails = DB::table('products', 'p')
            ->select('p.id', 'p.name', 'p.code', 'p.description', 'p.created_at', 'p.updated_at')
            ->where('p.id', '=', $id)
            ->get();

        return response()->json($productDetails);
    }

    /**
     * Add stock on hand.
     *
     * @return Application|Response|ResponseFactory
     */
    public function addStock(Request $request, int $id)
    {
        $request->validate([
            'onHand' => [
                'required',
                'integer'
            ]
        ]);

        $stocks = Product::find($id)->stocks;

        foreach ($stocks as $stock) {
            $stock->on_hand = $request->post('on_hand');

            $stock->save();
        }

        return response('saved');
    }
}
