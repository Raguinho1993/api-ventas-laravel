<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Support\Facades\Cache;
use App\Jobs\NotifyNewProduct;
use App\Jobs\NotifyLowStock;




class ProductController extends Controller
{
    public function index(Request $request)
   {
    // 1. Creamos una llave única para ESTE usuario
    // Ejemplo: "products_user_5"
        $userId = $request->user()->id;
        $cacheKey = 'products_user_' . $userId;
    // 2. Envolvemos la lógica en el remember
        $products = Cache::remember($cacheKey, 3600, function () use ($request){
        return $request->user()->products() #ha este usuario validado consulta sus productos
                           ->where('stock', '>', 0  )#filtra 
                           ->with('category') #Trae sus categorias
                           ->get(); # has la consulta
        
        });
        // 3. Devolvemos a través del Resource (Redis guarda el modelo, el Resource lo formatea)
        return ProductResource::collection($products)->additional([
            'message' => 'Listado de productos disponibles',
        ], 200);
        
   }

   public function store(StoreProductRequest $request)
   {    // 1. Validamos
       $data = $request->validated(); # vFiltramos y validamos la petición; solo permitimos los campos definidos en el StoreProductRequest, bloqueando datos maliciosos
        // 2. Guardamos en MySQL
        $product = $request->user()->products()->create($data);# Creamos el producto asignándolo automáticamente al usuario autenticado (dueño del token) con los datos ya limpios
        // --- AQUI ENTRA REDIS (Limpieza) ---
    // Borramos solo la lista de este usuario específico
    $userId =$request->user()->id;
    Cache::forget('products_user_'. $userId);
    // --- AQUI ENTRA EL JOB (Segundo Plano) ---
    // Supongamos que creamos un Job llamado 'NotifyNewProduct'
    // Se dispara y el usuario no tiene que esperar el envío del correo
    NotifyNewProduct::dispatch($product, $request->user());

        return (new ProductResource($product->load('category'))) # pasomo nuestro objeto por el resource y le pedimos que cargue la categora
            ->additional(['message' => 'Guardado con exito']) # me temos un datoextrao el mensaje
            ->response() # manupulamos la respuesta http
            ->setStatusCode(201); # Establecemos el código HTTP 201, indicando que el recurso se creó exitosamente en el servidor
    }

    public function show(Request $request, $id)
    {
        $cacheKey = 'product_detail_' . $id ;// Aquí la llave puede ser por ID de producto
        $product = Cache::remember($cacheKey, 3600, function () use ($request, $id){
            return $request->user()->products() # Mira el usuario de la request y dejalo ir a el producto que eligio si le pestenece
         ->with('category')                     #   que traiga de una vez la categoria
         ->where('id', $id)                     # filtra que el id del producto solicitado, sea el que esta en base de datos
         ->firstOrFail(); 
         });                      # si no es correcto arroja error
        return new ProductResource($product);    # retorna el product limpio
    }
      
    
    public function update(UpdateProductRequest $request, $id)
    {
          $product = $request->user()->products()->findOrFail($id); # Mira mi user_id que productos tiene y ahora findOrFail($id) verifica si alguno el el que 
          #eligio el usuario, si no arroja error 
                
          $data = $request->validated(); # Validamos que la request conseve datos limpios gracias a nuestro fromrequest- UpdateProductRequest;
          $product->update($data); # Actualizamos en base a qui se tienen en cuenta los mutators y por ultimo $fillable
        // --- LIMPIEZA DE REDIS ---
          $userId = $request->user()->id;
        // Borramos la lista del index del usuario
          Cache::forget('products_user_'. $userId);
          Cache::forget('product_detail_'. $id); // Limpia el detalle individual

          $msg = 'Producto Actualizado';
    
    // Si el stock quedó en 0, le avisamos al usuario
    if ($product->stock == 0) {
        $msg = 'Producto actualizado. Nota: Al tener stock 0, ya no será visible en el listado general.';
    }

          // DISPARAMOS EL JOB SI EL STOCK ES BAJO
          if($product->stock > 0 && $product->stock <= 5){
            NotifyLowStock::dispatch($product, $request->user());
          }
            return (new ProductResource($product->load('category')))->additional(['message' => $msg]);
            #retornamos el $product limpio con la categoria y le agregamos el mensaje del proceso.
    }
       
    public function destroy(Request $request, $id){
        $product = $request->user()->products()->findOrFail($id); # Mira el usuario gracias a el user_id en la tabla products y de los id "productos que tiene"
        # findOrFail($id) mira si alguno es el seleccionado por el usuario si no es arroja error

        // 2. Guardamos el nombre o datos necesarios para el Job antes de borrarlo
        $productName =$product->name;
        $userId = $request->user()->id;
        if($product->stock > 0){   
                         # si el producto es mayor a 0 no se puede eliminar por que tiene stock
            return response()->json([
                'message' => 'No se puede eliminar un producto con existencias',
                'stock_actual' => $product->stock,

            ], 400);
        }
            $product->delete(); # si no entra en el if se elimina el prodcto

            Cache::forget('products_user_' . $userId);
            Cache::forget('product_detail_' . $id);
        
    return response()->json([
        'message' => "Producto '{$productName}' eliminado correctamente" # se da la respuesta exitosa

    ], 200);
}
}