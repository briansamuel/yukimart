<?php

namespace App\Events;

use App\Models\Invoice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $invoice;
    public $oldStatus;
    public $newStatus;
    public $isNewInvoice;
    public $statusChanged;

    /**
     * Create a new event instance.
     */
    public function __construct(Invoice $invoice, string $oldStatus = null, string $newStatus = null)
    {
        $this->invoice = $invoice;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus ?? $invoice->status;
        $this->isNewInvoice = false;
        $this->statusChanged = true;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
