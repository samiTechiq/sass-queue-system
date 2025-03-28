<?php

namespace App\Http\Livewire;

use App\Models\BusinessLocation;
use Livewire\Component;

class BusinessLocationDashboard extends Component
{
    public $location;
    public $activeQueues;
    public $todayServedCustomers;
    public $averageWaitTime;

    public function mount(BusinessLocation $location)
    {
        if (request()->user()->cannot('view', $location)) {
            abort(403);
        }

        $this->location = $location;
        $this->loadDashboardData();
    }

    public function render()
    {
        return view('livewire.business-location-dashboard');
    }

    private function loadDashboardData()
    {
        // Count active queues
        $this->activeQueues = $this->location->queues()->where('is_active', true)->count();

        // Count today's served customers
        $this->todayServedCustomers = $this->location->queues()
            ->withCount(['tickets' => function ($query) {
                $query->whereDate('created_at', today())
                    ->where('status', 'served');
            }])
            ->get()
            ->sum('tickets_count');

        // Calculate average wait time (in minutes)
        $this->averageWaitTime = 0;
        $tickets = $this->location->queues()
            ->with(['tickets' => function ($query) {
                $query->whereDate('created_at', today())
                    ->whereNotNull('called_at')
                    ->whereNotNull('created_at');
            }])
            ->get()
            ->pluck('tickets')
            ->flatten();

        if ($tickets->count() > 0) {
            $totalWaitTime = 0;
            $ticketCount = 0;

            foreach ($tickets as $ticket) {
                if ($ticket->called_at && $ticket->created_at) {
                    $waitTime = $ticket->called_at->diffInMinutes($ticket->created_at);
                    $totalWaitTime += $waitTime;
                    $ticketCount++;
                }
            }

            if ($ticketCount > 0) {
                $this->averageWaitTime = round($totalWaitTime / $ticketCount, 1);
            }
        }
    }
}