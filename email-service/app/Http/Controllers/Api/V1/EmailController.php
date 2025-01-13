<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\SendOrderShippedEmail;
use App\Mail\OrderShipped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;

class EmailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function sendOrderShippedEmail(Request $request)
    {
        try {
        $order = $request->input('order');

        $from = $request->input('from');

        $to = $request->input('to');

        $subject = $request->input('subject');

        $content = $request->input('content');

        SendOrderShippedEmail::dispatch(
            $order,
            $from,
            $to,
            $subject,
            $content
        );
        return response()->json(['message' => 'Email sent successfully'], Response::HTTP_OK);
    }catch(\Exception $e){
        return response()->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
