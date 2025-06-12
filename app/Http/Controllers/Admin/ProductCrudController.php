<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\ProductRequest;
use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Models\Category; // Add this use statement

/**
 * Class ProductCrudController
 * @package App\Http\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class ProductCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\App\Models\Product::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/product');
        CRUD::setEntityNameStrings('product', 'products');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        // CRUD::setFromDb(); // We'll define columns manually for better control
        CRUD::column('id');
        CRUD::column('name');
        CRUD::column('slug');
        CRUD::column('category_id')->type('select')->entity('category')->model(Category::class)->attribute('name'); // Use the imported Category
        CRUD::column('price')->type('number')->decimals(2)->prefix('$');
        CRUD::column('stock')->type('number');
        CRUD::column('image')->type('image')->prefix(asset('storage/'))->height('100px')->width('100px');
        CRUD::column('created_at');
        CRUD::column('is_active')->type('boolean');

        /**
         * Columns can be defined using the fluent syntax:
         * - CRUD::column('price')->type('number');
         */


    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        CRUD::setValidation(ProductRequest::class);
        // CRUD::setFromDb(); // We'll define fields manually for better control

        CRUD::field('name')->type('text')->label('Product Name');
        CRUD::field('slug')->type('text')->label('Slug (auto-generated if empty)');
        CRUD::field('description')->type('textarea');
        CRUD::field('price')->type('number')->attributes(['step' => '0.01']);
        CRUD::field('stock')->type('number')->default(0);
        CRUD::field('category_id')->type('select')->entity('category')->model(Category::class)->attribute('name'); // Use the imported Category
        CRUD::field('is_active')->type('boolean')->default(true);

        CRUD::field('image')
            ->type('upload')
            ->withFiles([
                'disk' => 'public', // Define your disk in config/filesystems.php
                'path' => 'products', // Path within the disk
            ]);

        CRUD::field('image_url_input')
            ->type('url')
            ->label('Or Paste Image URL')
            ->hint('If an image URL is provided here, it will be downloaded and used if no file is uploaded directly above.')
            ->after('image'); // Places this field after the 'image' upload field
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->setupCreateOperation();
    }

    /**
     * Define what happens when the Show operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-show
     * @return void
     */
    protected function setupShowOperation()
    {
        $this->setupListOperation(); // Use the same columns as list for show view by default
        // You can customize this further if needed, for example, to show a larger image:
        // CRUD::column('image')->type('image')->prefix(asset('storage/'))->height('300px');
    }
}
