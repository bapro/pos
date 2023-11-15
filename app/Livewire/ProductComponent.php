<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\Attributes\Rule;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
class ProductComponent extends Component
{

use WithFileUploads;
use WithPagination;
    public $isOpen = 0;
    #[Rule('required|min:2')]
    public $title;
     
    #[Rule('required|min:6')]
    public $codigo;

    #[Rule('image|max:2048')] // 2MB Max
    public $imagen;


    #[Rule('required|min:1')]
    public $stock;


    #[Rule('required')]
    public $category_id;

    public $categories;

    public $productId;
    public $oldImage;
 
    public function create()
    {
        $this->productId='';
        $this->reset('title','codigo', 'imagen', 'stock', 'category_id');
        $this->openModal();
    }
    public function openModal()
    {
        $this->isOpen = true;
    }
    public function closeModal()
    {
        $this->isOpen = false;
    }

    public function mount()
    {
        $this->categories = Category::all();
        
    }

    public function render()
    {
        return view('livewire.product-component', [
            // 'products' => Product::paginate(5)
            'products' => Product::with('category')->latest()->paginate(5)
            
        ]);
    }


    public function store()
    {
        $this->validate();
        Product::create([
            'title' => $this->title,
            'codigo' => $this->codigo,
            'imagen' => $this->imagen->store('public/products'),
            'stock' => $this->stock,
            'category_id' =>$this->category_id
        ]);
        session()->flash('success', 'Productos creados exitosamente.');
        
        $this->reset('title','codigo', 'imagen', 'stock', 'category_id');
        $this->closeModal();
    }

    public function edit($id){

        $product = Product::findOrFail($id);
        $this->productId=$id;
        $this->title=$product->title;
        $this->codigo=$product->codigo;
        $this->oldImage=$product->imagen;
        $this->stock=$product->stock;
        $this->category_id=$product->category_id;
        $this->openModal();
    }


    public function update()
    {  
        $this->validate([
            'title' => 'required|min:2', 
            'codigo' =>'required|min:6', 
            'stock' =>'required|min:1', 
            'category_id' =>'required', 
        ]);
     
     $product = Product::findOrFail($this->productId);
         $image = $product->imagen;
            if($this->imagen)
            {
                Storage::delete($product->imagen);
                $image = $this->imagen->store('public/products');
            }else{
                $image = $product->imagen;
            }
    //dd($image);
            $product->update([
                'title' => $this->title,
                'codigo' => $this->codigo,
                'imagen' => $image,
                'stock' => $this->stock,
                'category_id' =>$this->category_id
            ]);
            $this->productId='';
 
            session()->flash('success', 'Image updated successfully.');
            $this->closeModal();
            $this->reset('title','codigo', 'imagen', 'stock', 'category_id');
    }



    public function delete($id)
    {
          $singleImage = Product::findOrFail($id);
          Storage::delete($singleImage->imagen);
             $singleImage->delete();
          session()->flash('success','Image deleted Successfully!!');
          $this->reset('title','codigo', 'imagen', 'stock', 'category_id');
    }



}
