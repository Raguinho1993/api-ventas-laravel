<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;


class ProductController extends Controller
{
    public function index(Request $request)
   {
        $products = $request->user()->products() #ha este usuario validado consulta sus productos
                           ->where('stock', '>', 0  )#filtra 
                           ->with('category') #Trae sus categorias
                           ->get(); # has la consulta
        return ProductResource::collection($products)->additional([
            'message' => 'Listado de productos disponibles',
        ], 200);
   }

   public function store(StoreProductRequest $request)
   {    
       $data = $request->validated(); # vFiltramos y validamos la petición; solo permitimos los campos definidos en el StoreProductRequest, bloqueando datos maliciosos
   
        $product = $request->user()->products()->create($data);# Creamos el producto asignándolo automáticamente al usuario autenticado (dueño del token) con los datos ya limpios
        return (new ProductResource($product->load('category'))) # pasomo nuestro objeto por el resource y le pedimos que cargue la categora
            ->additional(['message' => 'Guardado con exito']) # me temos un datoextrao el mensaje
            ->response() # manupulamos la respuesta http
            ->setStatusCode(201); # Establecemos el código HTTP 201, indicando que el recurso se creó exitosamente en el servidor
    }

    public function show(Request $request, $id)
    {
        $product = $request->user()->products() # Mira el usuario de la request y dejalo ir a el producto que eligio si le pestenece
         ->with('category')                     #   que traiga de una vez la categoria
         ->where('id', $id)                     # filtra que el id del producto solicitado, sea el que esta en base de datos
         ->firstOrFail();                       # si no es correcto arroja error
        return new ProductResource($product);    # retorna el product limpio
    }
      
    
    public function update(UpdateProductRequest $request, $id)
    {
          $product = $request->user()->products()->findOrFail($id); # Mira mi user_id que productos tiene y ahora findOrFail($id) verifica si alguno el el que 
          #eligio el usuario, si no arroja error 
                
          $data = $request->validated(); # Validamos que la request conseve datos limpios gracias a nuestro fromrequest- UpdateProductRequest;
        
            $product->update($data); # Actualizamos en base a qui se tienen en cuenta los mutators y por ultimo $fillable

            return (new ProductResource($product->load('category')))->additional(['message' => 'Producto Actualizado']);
            #retornamos el $product limpio con la categoria y le agregamos el mensaje del proceso.
    }
       
    public function destroy(Request $request, $id){
        $product = $request->user()->products()->findOrFail($id); # Mira el usuario gracias a el user_id en la tabla products y de los id "productos que tiene"
        # findOrFail($id) mira si alguno es el seleccionado por el usuario si no es arroja error

        if($product->stock > 0){                # si el producto es mayor a 0 no se puede eliminar por que tiene stock
            return response()->json([
                'message' => 'No se puede eliminar un producto con existencias',
                'stock_actual' => $product->stock,

            ], 400);
        }
            $product->delete(); # si no entra en el if se elimina el prodcto
        
    return response()->json([
        'message' => 'Producto eliminado correctamente' # se da la respuesta exitosa

    ], 200);
}
}