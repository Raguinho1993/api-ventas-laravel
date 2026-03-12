<?php

namespace App\Jobs;
use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;


class NotifyLowStock implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;

    public $product;
    public $user;

    /**
     * Create a new job instance.
     */
    // Recibimos el producto y el usuario desde el controlador
    public function __construct(Product $product, User $user)
    {
        
        $this->product = $product;
        $this->user = $user;
    }

    /**
     * Execute the job.
     */
    
    public function handle(): void
    {
        Log::warning("ALERTA DE STOCK: Al producto '{$this->product->name}'le quedan solo {$this->product->stock} unidades.");
        Log::info("Notificando al dueño: {$this->user->email}");
    }
}
