<?php

namespace App\Http\Controllers\Api;

use App\Models\Type;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class TypeController extends Controller
{
    public function index()
    {
        return response()->json(Type::with('category')->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:types,name',
            'category_id' => 'required|exists:categories,id'
        ]);

        $type = Type::create($request->all());

        return response()->json($type, 201);
    }

    public function show($id)
    {
        $type = Type::with('category')->find($id);
        if ($type) {
            return response()->json($type, 200);
        }
        return response()->json(['message' => 'Type not found'], 404);
    }

    public function update(Request $request, $id)
    {
        $type = Type::find($id);

        if (is_null($type)) {
            return response()->json(['message' => 'Type not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255|unique:types,name,' . $type->id,
            'category_id' => 'sometimes|required|exists:categories,id'
        ]);

        $type->update($request->all());

        return response()->json($type, 200);
    }

    public function destroy($id)
    {
        $type = Type::find($id);
        if ($type) {
            $type->delete();
            return response()->json(['message' => 'Type deleted'], 200);
        }
        return response()->json(['message' => 'Type not found'], 404);
    }

    public function getTypesByCategory($categoryId)
    {
        try {
            $types = Type::where('category_id', $categoryId)->get();
            if ($types->isEmpty()) {
                return response()->json(['message' => 'No types found for this category'], 404);
            }
            return response()->json($types, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch types', 'error' => $e->getMessage()], 500);
        }
    }

    public function getRawMaterialsAndPackagesTypes()
    {
        try {
            $categories = Category::whereIn('name', ['Raw Materials', 'Packages'])->pluck('id');

            $types = Type::whereIn('category_id', $categories)
                        ->with('category')
                        ->get();

            if ($types->isEmpty()) {
                return response()->json(['message' => 'No types found for Raw Materials and Packages'], 404);
            }

            return response()->json($types, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to fetch types', 'error' => $e->getMessage()], 500);
        }
    }
}
