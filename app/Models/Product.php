<?php

namespace App\Models;

use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use App\Models\Category;

class Product extends Model
{
    use CrudTrait;
    use HasFactory;

    protected $table = 'products';
    protected $guarded = ['id'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSORS
    |--------------------------------------------------------------------------
    */
    public function getImageUrlAttribute()
    {
        if ($this->image && \Storage::disk('public')->exists($this->image)) {
            return \Storage::disk('public')->url($this->image);
        }
        // Optionally, return a default placeholder image URL
        // return asset('images/default-product.png');
        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT METHOD
    |--------------------------------------------------------------------------
    */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });
        static::saving(function ($product) {
            // Check if image_url_input was provided in the form
            if (!empty($product->image_url_input) && filter_var($product->image_url_input, FILTER_VALIDATE_URL)) {

                $request = request();
                // Check if a file was NOT uploaded to the 'image' field directly.
                // Direct file upload takes precedence.
                $isDirectImageUploaded = $request->hasFile('image') && $request->file('image')->isValid();

                if (!$isDirectImageUploaded) {
                    try {
                        $client = new Client(['timeout' => 10, 'connect_timeout' => 5]);
                        $response = $client->get($product->image_url_input);

                        if ($response->getStatusCode() == 200) {
                            $contents = $response->getBody()->getContents();
                            $contentType = $response->getHeaderLine('Content-Type');
                            $extension = 'jpg'; // Default

                            if (strpos($contentType, 'image/jpeg') !== false) $extension = 'jpg';
                            elseif (strpos($contentType, 'image/png') !== false) $extension = 'png';
                            elseif (strpos($contentType, 'image/gif') !== false) $extension = 'gif';
                            elseif (strpos($contentType, 'image/webp') !== false) $extension = 'webp';
                            else {
                                $pathInfoExtension = pathinfo(parse_url($product->image_url_input, PHP_URL_PATH), PATHINFO_EXTENSION);
                                if (!empty($pathInfoExtension) && strlen($pathInfoExtension) <= 4 && ctype_alnum($pathInfoExtension)) {
                                    $extension = strtolower($pathInfoExtension);
                                }
                            }

                            $filename = Str::random(32) . '.' . $extension;
                            $disk = 'public'; // Should match your ProductCrudController disk for 'image'
                            $destination_path = 'products'; // Should match your ProductCrudController path for 'image'
                            $new_path = $destination_path . '/' . $filename;

                            // If there's an old image and it's different, delete it
                            if ($product->getOriginal('image') && $product->getOriginal('image') !== $new_path) {
                                Storage::disk($disk)->delete($product->getOriginal('image'));
                            }
                            Storage::disk($disk)->put($new_path, $contents);
                            $product->image = $new_path; // Set the 'image' attribute to the new path
                        }
                    } catch (\Exception $e) {
                        \Log::error("Failed to download image from URL: {$product->image_url_input}. Error: " . $e->getMessage());
                    }
                }
            }
        });
    }
}
