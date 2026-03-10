<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Category;
use App\Models\User;

class Product extends Model
{
    use HasFactory;
    protected $table = 'products';

    protected $fillable = [
            'nombre',
            'descripcion',
            'codigo_de_barras',
            'precio',
            'stock',
            'category_id',
            'user_id',

    ];

    protected $casts = [
        'precio' => 'float',
        'stock' => 'integer',
#permite que los datos que vienen en string de la DB pasen al controlador en float e ireger
    ];

    public function category(){
        return $this->belongsTo(Category::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    // --- MUTATORS (Tu lógica de negocio centralizada) ---

    /**
     * Cada vez que se asigne un código de barras, se guarda en MAYÚSCULAS
     */
    public function setCodigoDeBarrasAttribute($value)
    {
        $this->attributes['codigo_de_barras'] = strtoupper($value);
    }

    public function setDescripcionAttribute($value)
    {
        $stock = request('stock');
        $category_id = request('category_id');

        if ($category_id == 10 && $stock > 50) {
            $this->attributes['descripcion'] = 'REQUIERE EMBALAJE ESPECIAL -' . $value;
            }
        else{
            $this->attributes['descripcion'] = $value;
        }
    }
}

