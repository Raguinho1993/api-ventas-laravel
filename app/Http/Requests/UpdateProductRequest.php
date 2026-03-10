<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                   'nombre' => 'required|string',   
                   'descripcion' => 'nullable|string',   
                   'codigo_de_barras' => 'required|string|unique:products,codigo_de_barras,' . $this->route('id'),   
                   'precio' => 'numeric|min:1',  
                   'stock' => 'integer|min:0',   
                   'category_id' => 'required|exists:categories,id',  
        ];
    }
}
