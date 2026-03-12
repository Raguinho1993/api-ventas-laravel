<?php

namespace App\Http\Controllers;


use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use Illuminate\Support\Facades\Cache; # para usar redis

class CategoryController extends Controller
{
    private $cacheKey = 'categories_all'; # definimos el key que llevara el dato
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Cache::remember hace 3 cosas:
        // 1. Busca en Redis la llave 'categories_all'
        // 2. Si NO existe, ejecuta el Category::all() y lo guarda por 1 hora (3600s)
        // 3. Si SÍ existe, te lo entrega de inmediato sin tocar MySQL
        $categories = Cache::remember($this->cacheKey, 3600, function(){
            return Category::all();
        });
        return response()->json($categories, 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCategoryRequest $request)
    {
       $data = $request->validated(); # Valida en el StoreCategoryRequest
        
       $category = Category::create($data); # Guarda el dato
        Cache::forget('categories_all'); # Limpia redis, lo que obliga al get a una nueva consulta   

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
    // 1. Creamos una llave ÚNICA para cada ID
    // Por ejemplo: "category_show_1", "category_show_2", etc.
        $key = 'category_show_' . $id; 

        $category =  Cache::remember($key, 3600, function() use ($id){
            return Category::with('products')->findOrFail($id);
        });

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
        $category = Category::findOrFail($id);

        $data = $request->validated();

        $category->update($data);

        Cache::forget('categories_all'); // Borramos la lista general
        Cache::forget('category_show_'. $id);

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
            // 1. Buscamos la categoría y sus productos
            $category = Category::with('products')->findOrFail($id);
            // 2. Verificamos si tiene productos asociados
            if($category->products()->exists()){
                return response()->json([
                'message' => 'Error: No se puede eliminar una categoría que tiene productos asociados.'
            ], 400);
            }
                
            $category->delete();
        
            Cache::forget('categories_all');
            Cache::forget('category_show_' . $id);

            return response()->json([
                'message' => 'Categoria eliminada con exito',
                
            ],200);
        }
    }
