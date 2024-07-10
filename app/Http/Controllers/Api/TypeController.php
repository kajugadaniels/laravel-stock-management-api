<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Type;
use Illuminate\Http\Request;

class TypeController extends Controller
{
    public function index()
    {
        return response()->json(Type::with('category')->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
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
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id'
        ]);
        $type = Type::find($id);
        if ($type) {
            $type->update($request->all());
            return response()->json($type, 200);
        }
        return response()->json(['message' => 'Type not found'], 404);
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
}
