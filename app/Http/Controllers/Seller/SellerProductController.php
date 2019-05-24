<?php

namespace App\Http\Controllers\Seller;

use App\Product;
use App\Seller;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class SellerProductController extends Controller
{

    public function index(Seller $seller)
    {
        $prduct = $seller->products;
        return response()->json(['data'=>$prduct],200);
    }

    public function store(Request $request,User $seller)
    {
        $this->validate($request,[
            'name'=>'required',
            'description'=>'required',
            'quantity'=>'required',
            'image'=>'required|image'


        ]);

        $data = $request->all();
        $data['status'] = Product::UNAVAILABLE_PRODUCT;
        $data['image'] = $request->image->store('');
        $data['seller_id'] = $seller->id;

       $product =  Product::create($data);
        return response()->json(['data'=>$product],200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function show(Seller $seller)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function edit(Seller $seller)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $this->validate($request,[
           'quantity'=>'integer|min:3',
            'status'=>'in:'.Product::UNAVAILABLE_PRODUCT.','.Product::AVAILABLE_PRODUCT,
            'image'=>'image'
        ]);
        $this->checkSeller($seller,$product);

        $product->fill($request->only([
            'name',
            'description',
            'quantity'
        ]));
        if($request->hasFile('image')){
            Storage::delete($product->id);
            $product->image = $request->image->store('');
        }


        if($product->isClean()){
            return response()->json(['error','message error']);
        }
        $product->save();
        return response()->json(['data',$product]);


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller,Product $product)
    {
        $this->checkSeller($seller,$product);
        Storage::delete($product->image);
        $product->delete();
        return response()->json(['data'=>$product]);
    }

    private function checkSeller(Seller $seller, Product $product)
    {
        if ($seller->id != $product->seller_id){
            throw new \HttpException('not allow');
        }
    }
}
