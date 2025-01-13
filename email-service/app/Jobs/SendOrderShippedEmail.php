<?php

namespace App\Jobs;

use App\Mail\OrderShipped;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderShippedEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $order;

    public $fromAddress;

    public $toAddress;

    public $subject;

    public $contentBody;

    /**
     * Create a new message instance.
     *
     * @param mixed $order
     * @param string $fromAddress
     * @param string $toAddress
     * @param string $subject
     * @param string $contentBody
     */
    public function __construct($order, $fromAddress, $toAddress, $subject, $contentBody)
    {
        $this->order = $order;
        $this->fromAddress = $fromAddress;
        $this->toAddress = $toAddress;
        $this->subject = $subject;
        $this->contentBody = $contentBody;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {    
        Mail::send(new OrderShipped($this->order, $this->fromAddress, $this->toAddress, $this->subject, $this->contentBody));
    }
}
