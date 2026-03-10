<?php

namespace App\Http\Controllers;


use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::all();
        return response()->json([
            'message' => 'Lista de categorias',
            'data' => $categories,
        ],200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
       $data = $request->validated();
       

        $category = DB::transaction(function () use ($data){
            return Category::create($data);
        });
            
        return response()->json([
            'message' => 'Categoria creada',
            'data' => $category,
            ], 201);
        
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $category = Category::with('products')->findOrFail($id);
        return response()->json([
            'message' => 'Categoria encontrada',
            'data' => $category,
        ],200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::with('products')->findOrFail($id);

        $data = $request->validate();

        $category = DB::transaction(function () use ($data, $category){
            $category->update($data);

            return $category;
        });
        return response()->json([
            'message' => 'Categoria actualizada',
            'data' => $category,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
        public function destroy($id)
        {
            $category = Category::with('products')->findOrFail($id);
            if($category->products()->exists()){
                return response()->json([
                'message' => 'Error: No se puede eliminar una categoría que tiene productos asociados.'
            ], 400);
            }

            $category = DB::transaction(function () use($category){

                $category->delete();
            });
            return response()->json([
                'message' => 'Categoria eliminada con exito',
                
            ],200);
        }
    }
