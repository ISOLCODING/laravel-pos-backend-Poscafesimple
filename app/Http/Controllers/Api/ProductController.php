<?php

namespace App\Http\Controllers\Api; // Sebaiknya letakkan di dalam folder Api untuk membedakan dengan controller web

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::when($request->input('name'), function ($query, $name) {
                return $query->where('name', 'like', '%' . $name . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'message' => 'List Data Product',
            'data' => $products
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:products',
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|image|mimes:png,jpg,jpeg|max:2048', // max 2MB
            'is_favorite' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'data' => $validator->errors()
            ], 422);
        }

        $filename = time() . '.' . $request->image->extension();
        $request->image->storeAs('public/products', $filename);
        $category = Category::find($request->category_id);

        $product = Product::create([
            'name' => $request->name,
            'price' => (int) $request->price,
            'stock' => (int) $request->stock,
            'category_id' => $request->category_id,
            'category' => $category->name,
            'image' => $filename,
            'is_favorite' => $request->is_favorite ?? false,
        ]);

        if ($product) {
            return response()->json([
                'success' => true,
                'message' => 'Product Created',
                'data' => $product
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product Failed to Save',
            ], 409);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Data Product',
            'data' => $product
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|unique:products,name,' . $id,
            'price' => 'required|integer|min:0',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_favorite' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'data' => $validator->errors()
            ], 422);
        }

        $category = Category::find($request->category_id);

        $product->fill($request->only(['name', 'price', 'stock', 'is_favorite']));
        $product->category_id = $request->category_id;
        $product->category = $category->name;

        // Cek jika ada file gambar baru yang di-upload
        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:png,jpg,jpeg|max:2048' // Validasi file gambar
            ]);

            // Hapus gambar lama jika ada
            if ($product->image && Storage::exists('public/products/' . $product->image)) {
                Storage::delete('public/products/' . $product->image);
            }

            // Simpan gambar baru
            $filename = time() . '.' . $request->image->extension();
            $request->image->storeAs('public/products', $filename);
            $product->image = $filename;
        }

        if ($product->save()) {
             return response()->json([
                'success' => true,
                'message' => 'Product Updated',
                'data' => $product
            ], 200);
        } else {
             return response()->json([
                'success' => false,
                'message' => 'Product Failed to Update',
            ], 409);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product Not Found',
            ], 404);
        }

        // Hapus gambar jika ada
        if ($product->image && Storage::exists('public/products/' . $product->image)) {
            Storage::delete('public/products/' . $product->image);
        }

        if ($product->delete()) {
            return response()->json([
                'success' => true,
                'message' => 'Product Deleted',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Product Failed to Delete',
            ], 500);
        }
    }
}
