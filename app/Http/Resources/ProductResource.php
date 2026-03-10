<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
                'id'               => $this->id,
                'nombre'           => $this->nombre,
                'descripcion'      => $this->descripcion,
                'codigo_barras'    => $this->codigo_de_barras,

                // Formateo internacional: asegura que el cliente reciba un número decimal exacto
                'precio'           => (float) $this->precio, 
                'precio_formateado'=> '$' . number_format($this->precio, 2), 

                'stock'            => (int) $this->stock,
                'categoria'      => new CategoryResource($this->whenLoaded('category')), 
                // ^ Solo carga la relación si ya fue llamada con with() en el controlador
              ];
    }
}
