<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client as GuzzleHttpClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use MongoDB\Client;
use Symfony\Component\HttpFoundation\Response;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;

class OrderController extends Controller
{
    protected $db;
    protected $collection;

    public function __construct() {
        $this->db = new Client(env('MONGO_URI'));
        $this->collection = $this->db->orders_services_db->orders;
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
            'customer_name' => 'required|string|max:100',
            'items' => 'required|array',
            'items.*.product_id' => 'required|string',
            'items.*.name' => 'required|string|max:100',
            'items.*.description' => 'required|string|max:1000',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.category' => 'required|string|max:100',
            'items.*.available' => 'required|boolean',
            'items.*.ingredients' => 'required|array',
            'items.*.quantity' => 'required|integer|min:1',
            'total_price' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,completed,canceled'
        ]);

        $updatedProducts = [];
        
        try{

            $tokenr = $request->header('Authorization');
            $token = str_replace("Bearer ", '', $tokenr);

            foreach ($valData['items'] as $pro) {
                
                $inventoryResponse = Http::withHeaders([
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->get(env('INVENTORY_SERVICE_URL') . 'api/v1/products/' . $pro['product_id']);

                if ($inventoryResponse->failed() || !$inventoryResponse->json()) {
                    return response()->json(['error' => 'producto no encontrado', Response::HTTP_NOT_FOUND]);
                }

                $product = $inventoryResponse->json()['producto'];
                if ($product['quantity'] < $pro['quantity']) {
                    return response()->json(['error' => 'No hay suficiente stock', Response::HTTP_NOT_FOUND]);
                }

                $updatedProducts [] = [
                    'product_id' => $pro['product_id'],
                    'new_quantity' => $product['quantity'] - $pro['quantity']
                ];

                foreach ($updatedProducts as $product) {
                    $updatedResponse = HTTP::withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ])->put(env('INVENTORY_SERVICE_URL') . 'api/v1/products/' . $pro['product_id'], [
                        'quantity' => $product['new_quantity']
                    ]);
                }
                
                if ($updatedResponse->failed()) {
                    return response()->json(['error' => 'Error al actualizar', Response::HTTP_INTERNAL_SERVER_ERROR]);
                }
            }

            $data = [
                'customer_name'=> $valData['customer_name'],
                'items'=> $valData['items'],
                'total_price'=> $valData['total_price'],
                'status'=> $valData['status'],
                'created_at'=> new UTCDateTime(),
                'updated_at'=> new UTCDateTime()
            ];
            $orderResult = $this->collection->insertOne($data);
            /** Devuelve el último id insertado */
            $order['_id'] = $orderResult->getInsertedId();
            return response()->json([
                'message' => 'Orden enviada con éxito',
                'order' => $data
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
            $order = $this->collection->findOne(['_id'=> new ObjectId($id)]);
            if(!$order){
                return response()->json(['error' => 'No encontrada'],Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'message' => 'Producto encontrado',
                'orden'=> $order
            ],Response::HTTP_OK);
        }catch(\Exception $ex){
            return response()->json(['error' => $ex->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     * 
     * @var sometimes Para aveces actualizar
     *  
     */
    public function update(Request $request, string $id)
    {
        $valData = $request->validate([
            'customer_name'=>'sometimes|required|string|max:100',
            'items'=>'sometimes|required|array',
            'total_price'=>'sometimes|required|numeric|min:0',
            /** valores estándar */
            'status'=>'sometimes|required|string|in:pending,completed,canceled'
        ]);
        
        try{
            $updateData = array_filter($valData);

            if(empty($updateData)){
                return response()->json(['error' => 'No se encontraron datos para actualizar'],Response::HTTP_NOT_FOUND);
            }
            
            // $this->collection->findOne(['_id'=> new MongoDB\BSON\ObjectId($id)]);
            $product = $this->collection->updateOne(
                ['_id' => new ObjectId($id)],
                /** set es de MongoDB */
                ['$set' => $valData]
            );

            if($product->getMatchedCount() === 0){
                return response()->json(['error' => 'No encontrado'],Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'message' => 'Orden actualizada con éxito'
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
        try{
            $product = $this->collection->deleteOne(['_id'=> new ObjectId($id)]);
            if($product->getDeletedCount() === 0){
                return response()->json(['error' => 'No encontrado'],Response::HTTP_NOT_FOUND);
            }
            return response()->json([
                'message' => 'Orden eliminada con éxito',
                'producto'=> $product
            ],Response::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage()],Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function searchByName(Request $request) {
        
        $request->validate([
            'name' => 'required|string|max:100',
        ]);
    
        try {
            $name = $request->input('name');
            $products = $this->collection->find([
                'name' => ['$regex' => '.*' . preg_quote($name, '/') . '.*', '$options' => 'i'] 
            ])->toArray();
    
            if (empty($products)) {
                return response()->json(['message' => 'No se encontraron productos con ese nombre.'], Response::HTTP_NOT_FOUND);
            }
    
            return response()->json([
                'message' => 'Productos encontrados con éxito',
                'products' => $products
            ], Response::HTTP_OK);
        } catch (\Exception $ex) {
            return response()->json(['error' => $ex->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
