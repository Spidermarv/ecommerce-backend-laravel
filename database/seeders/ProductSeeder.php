<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    private function downloadAndSaveImage(string $imageUrlSeed, string $productName): ?string
    {
        try {
            // Using a seed for picsum.photos to get somewhat consistent images for the same product name/seed
            $imageUrl = "https://picsum.photos/seed/" . Str::slug($imageUrlSeed) . "/640/480";

            // Initialize Guzzle client with a timeout and connection timeout
            // 'verify' => false can be used for local dev if SSL issues with picsum, but not recommended for production.
            $client = new Client(['timeout' => 15, 'connect_timeout' => 7, 'verify' => false]);
            $response = $client->get($imageUrl);

            if ($response->getStatusCode() == 200) {
                $contents = $response->getBody()->getContents();
                $extension = 'jpg'; // Picsum usually returns jpeg

                $filename = Str::slug($productName) . '_' . Str::random(10) . '.' . $extension;
                $disk = 'public';
                $destination_path = 'products';
                $path = $destination_path . '/' . $filename;

                Storage::disk($disk)->put($path, $contents);
                $this->command->info("Downloaded image for {$productName} to {$path}");
                return $path;
            } else {
                $this->command->error("ProductSeeder: Failed to download image for {$productName} from {$imageUrl}. Status: " . $response->getStatusCode());
            }
        } catch (\Exception $e) {
            Log::error("ProductSeeder: Exception while downloading image for {$productName} from seed {$imageUrlSeed}. Error: " . $e->getMessage());
            $this->command->error("ProductSeeder: Exception for {$productName}. Error: " . $e->getMessage());
        }
        return null;
    }

    public function run()
    {
        // Ensure there's at least one category
        $category = Category::first();
        if (!$category) {
            $category = Category::create(['name' => 'General']);
            $this->command->info("Created 'General' category.");
        }

        // Define products data
        $productsData = [ // Added stock and is_active
            ['name' => 'Sample Product 1', 'description' => 'This is a sample product.', 'price' => 49.99, 'stock' => 10, 'is_active' => true, 'image_seed' => 'sample-1-seed'],
            ['name' => 'Sample Product 2', 'description' => 'Another sample product.', 'price' => 89.99, 'stock' => 5, 'is_active' => true, 'image_seed' => 'sample-2-seed'],
            ['name' => 'Laptop Bag', 'description' => 'Stylish and durable laptop bag.', 'price' => 49.00, 'stock' => 15, 'is_active' => true, 'image_seed' => 'laptop-bag-seed'],
            ['name' => 'Bluetooth Speaker', 'description' => 'Portable speaker with deep bass.', 'price' => 89.00, 'stock' => 20, 'is_active' => true, 'image_seed' => 'bt-speaker-seed'],
            ['name' => 'iPad Air', 'description' => 'Lightweight and powerful Apple tablet.', 'price' => 699.00, 'stock' => 8, 'is_active' => true, 'image_seed' => 'ipad-air-seed'],
            ['name' => 'Canon EOS M50', 'description' => 'Compact mirrorless camera.', 'price' => 599.00, 'stock' => 3, 'is_active' => false, 'image_seed' => 'canon-m50-seed'], // Example inactive
            ['name' => 'Gaming Chair', 'description' => 'Ergonomic chair for gamers.', 'price' => 199.00, 'stock' => 12, 'is_active' => true, 'image_seed' => 'gaming-chair-seed'],
            // ... add stock and is_active for other products ...
            ['name' => 'USB-C Hub', 'description' => 'Multiport adapter with HDMI and USB.', 'price' => 29.00, 'stock' => 50, 'is_active' => true, 'image_seed' => 'usbc-hub-seed'],
            ['name' => 'Mechanical Keyboard', 'description' => 'RGB backlit gaming keyboard.', 'price' => 129.00, 'stock' => 25, 'is_active' => true, 'image_seed' => 'mech-keyboard-seed'],
            ['name' => 'Wireless Mouse', 'description' => 'Smooth and fast Bluetooth mouse.', 'price' => 39.00, 'stock' => 30, 'is_active' => true, 'image_seed' => 'wireless-mouse-seed'],
            ['name' => 'Apple Watch Series 9', 'description' => 'Smartwatch with health tracking.', 'price' => 399.00, 'stock' => 10, 'is_active' => true, 'image_seed' => 'apple-watch-9-seed'],
            ['name' => 'Kindle Paperwhite', 'description' => 'E-reader with glare-free display.', 'price' => 129.00, 'stock' => 18, 'is_active' => true, 'image_seed' => 'kindle-pw-seed'],
            ['name' => 'Sony PlayStation 5', 'description' => 'Next-gen gaming console.', 'price' => 499.00, 'stock' => 7, 'is_active' => true, 'image_seed' => 'ps5-seed'],
            ['name' => 'Xbox Series X', 'description' => 'High-performance Microsoft console.', 'price' => 499.00, 'stock' => 6, 'is_active' => true, 'image_seed' => 'xbox-x-seed'],
            ['name' => 'Raspberry Pi 4', 'description' => 'Mini computer for projects.', 'price' => 75.00, 'stock' => 22, 'is_active' => true, 'image_seed' => 'rpi4-seed'],
            ['name' => 'External SSD 1TB', 'description' => 'Fast storage for backups.', 'price' => 149.00, 'stock' => 13, 'is_active' => true, 'image_seed' => 'ssd-1tb-seed'],
            ['name' => 'Ring Light', 'description' => 'LED light for video creators.', 'price' => 59.00, 'stock' => 30, 'is_active' => true, 'image_seed' => 'ring-light-seed'],
            ['name' => 'Smart Thermostat', 'description' => 'Wi-Fi enabled climate control.', 'price' => 199.00, 'stock' => 9, 'is_active' => true, 'image_seed' => 'smart-thermo-seed'],
            ['name' => 'Drone DJI Mini 3', 'description' => 'Compact drone with 4K camera.', 'price' => 599.00, 'stock' => 4, 'is_active' => true, 'image_seed' => 'dji-mini3-seed'],
            ['name' => 'Noise-Cancelling Earbuds', 'description' => 'In-ear ANC headphones.', 'price' => 179.00, 'stock' => 17, 'is_active' => true, 'image_seed' => 'anc-earbuds-seed'],
            ['name' => 'Fitness Tracker', 'description' => 'Track steps, sleep, and workouts.', 'price' => 79.00, 'stock' => 23, 'is_active' => true, 'image_seed' => 'fitness-tracker-seed'],
            ['name' => 'Electric Scooter', 'description' => 'Eco-friendly urban transport.', 'price' => 749.00, 'stock' => 2, 'is_active' => true, 'image_seed' => 'escooter-seed'],
            ['name' => 'Projector 1080p', 'description' => 'Home theater projector.', 'price' => 229.00, 'stock' => 11, 'is_active' => true, 'image_seed' => 'projector-1080p-seed'],
            ['name' => 'Smart LED Bulbs (4-pack)', 'description' => 'Voice-controlled color bulbs.', 'price' => 49.00, 'stock' => 40, 'is_active' => true, 'image_seed' => 'smart-bulbs-seed'],
            ['name' => 'VR Headset', 'description' => 'Immersive virtual reality gear.', 'price' => 399.00, 'stock' => 0, 'is_active' => false, 'image_seed' => 'vr-headset-seed'], // Example out of stock and inactive
        ];

        foreach ($productsData as $productData) {
            Product::firstOrCreate(
                ['name' => $productData['name']],
                [
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'category_id' => $category->id,
                    'slug' => Str::slug($productData['name']),
                    'image' => $this->downloadAndSaveImage($productData['image_seed'], $productData['name']),
                    'stock' => $productData['stock'],
                    'is_active' => $productData['is_active'],
                ]
            );
        }
        $this->command->info('Product seeding completed.');
    }
}
