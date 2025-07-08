<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use Faker\Factory as Faker;

class AddBarcodeToProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();
        
        // Get all products that don't have barcodes
        $products = Product::whereNull('barcode')->orWhere('barcode', '')->get();
        
        $this->command->info("Adding barcodes to {$products->count()} products...");
        
        $progressBar = $this->command->getOutput()->createProgressBar($products->count());
        $progressBar->start();
        
        foreach ($products as $product) {
            try {
                // Generate unique barcode
                do {
                    $barcode = $faker->unique()->ean13();
                } while (Product::where('barcode', $barcode)->exists());
                
                $product->update(['barcode' => $barcode]);
                
                $progressBar->advance();
                
            } catch (\Exception $e) {
                $this->command->error("Failed to update product {$product->id}: " . $e->getMessage());
            }
        }
        
        $progressBar->finish();
        $this->command->newLine();
        $this->command->info("Successfully added barcodes to {$products->count()} products!");
        
        // Show some sample barcodes
        $sampleProducts = Product::whereNotNull('barcode')->limit(5)->get();
        $this->command->info("Sample products with barcodes:");
        
        foreach ($sampleProducts as $product) {
            $this->command->line("- {$product->product_name} (SKU: {$product->sku}) - Barcode: {$product->barcode}");
        }
    }
}
