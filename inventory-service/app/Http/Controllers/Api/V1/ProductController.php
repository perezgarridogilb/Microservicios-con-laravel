<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use MongoDB\Client;
use Symfony\Component\HttpFoundation\Response;
use MongoDB\BSON\ObjectId;

class ProductController extends Controller
{
    protected $db;
    protected $collection;

    public function __construct() {
        $this->db = new Client(env('MONGO_URI'));
        $this->collection = $this->db->inventory->products;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $products = $this->collection->find()->toArray();
            return response()->json($products, Response::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $valData = $request->validate([
            'name'=>'required|string|max:100',
            'description'=>'required|string|max:1000',
            'price'=>'required|numeric|min:0',
            'category'=>'required|string|max:100',
            'available'=>'required|boolean',
            'ingredients'=>'required|array',
            'quantity'=>'required|integer',
        ]);
        try{
            // sin unique en validate
            $exists = $this->collection->findOne(['name'=>$valData['name']]);

            if($exists){
                return response()->json(['error'=>'Ya existe el producto con ese nombre'],Response::HTTP_CONFLICT);
            }

            $data = [
                'name'=> $valData['name'],
                'description'=> $valData['description'],
                'price'=> $valData['price'],
                'category'=> $valData['category'],
                'available'=> $valData['available'],
                'ingredients'=> $valData['ingredients'],
                'quantity'=> $valData['quantity']
            ];
            $product = $this->collection->insertOne($data);
            /** Devuelve el último id insertado */
            $data['_id'] = $product->getInsertedId();
            return response()->json([
                'message' => 'Guardado con éxito',
                'product' => $data
            ],Response::HTTP_CREATED);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try{
            $product = $this->collection->findOne(['_id'=> new ObjectId($id)]);
            if(!$product){
                return response()->json(['error' => 'No encontrado'],Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'message' => 'Producto encontrado',
                'producto'=> $product
            ],Response::HTTP_OK);
        }catch(\Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $valData = $request->validate([
            'name'=>'required|string|max:100',
            'description'=>'required|string|max:1000',
            'price'=>'required|numeric|min:0',
            'category'=>'required|string|max:100',
            'available'=>'required|boolean',
            'ingredients'=>'required|array',
            'quantity'=>'required|integer',
        ]);
        try{
            
            $product = $this->collection->updateOne(
                ['_id' => new ObjectId($id)],
                /** set es de MongoDB */
                ['$set' => $valData]
            );

            if($product->getMatchedCount() === 0){
                return response()->json(['error' => 'No encontrado'],Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'message' => 'Actualizado con éxito'
            ],Response::HTTP_OK);
        }catch(\Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
