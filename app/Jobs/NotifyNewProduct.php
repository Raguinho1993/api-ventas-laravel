<?php

namespace App\Jobs;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueabble; // Permite que este Job sea puesto en una cola (Redis).
use Illuminate\Contracts\Queue\ShouldQueue;// Esta es la "interfaz". Le dice a Laravel: "Oye, no ejecutes esto ahora, ponlo en la cola".
use Illuminate\Foundation\Bus\Dispatchable; // Nos da el método ::dispatch() que usaste en el controlador.
use Illuminate\Queue\InteractsWithQueue; // Permite que el Job pueda eliminarse o reintentarse desde la cola.
use Illuminate\Queue\SerializesModels;// ¡ESTA ES CLAVE! Convierte tus modelos en texto para que quepan en Redis y luego los restaura.
use Illuminate\Support\Facades\Log; // Para escribir en el archivo de registro (laravel.log).
use Illuminate\Foundation\Queue\Queueable;
use Psr\Log\LoggerAwareTrait;

class NotifyNewProduct implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $product;
    public $user;

    /**
     * Create a new job instance.
     */
    // El constructor se ejecuta en el MOMENTO que haces ::dispatch() en el controlador.
    public function __construct(Product $product, User $user)
    {
       $this->product = $product;
       $this->user = $user;

    }

    /**
     * Execute the job.
     */
    // En este punto, Laravel ya fue a la DB y volvió a buscar el Producto y Usuario usando sus IDs automáticamente.
    public function handle(): void
    {
        Log::info('¡Tarea en segundo plano completada!');
        Log::info('Producto: ' . $this->product->name);
        Log::info('Usuario: ' . $this->user->name);         

    }
}
