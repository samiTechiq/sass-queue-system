<?php

namespace App\Http\Livewire;

use App\Models\Customer;
use App\Models\QueueEntry;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class CustomerManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $perPage = 10;
    public $showDeleteModal = false;
    public $showCustomerModal = false;
    public $selectedCustomer = null;
    public $customerName = '';
    public $customerPhone = '';
    public $customerEmail = '';
    public $customerNotes = '';
    public $isEditing = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'name'],
        'sortDirection' => ['except' => 'asc'],
        'perPage' => ['except' => 10],
    ];

    protected $rules = [
        'customerName' => 'required|string|max:255',
        'customerPhone' => 'nullable|string|max:20',
        'customerEmail' => 'nullable|email|max:255',
        'customerNotes' => 'nullable|string',
    ];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function render()
    {
        $customers = Customer::where('business_id', request()->user()->business_id)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->withCount([
                'queueEntries as total_visits',
                'queueEntries as served_count' => function ($query) {
                    $query->where('status', 'served');
                },
                'queueEntries as no_show_count' => function ($query) {
                    $query->where('status', 'no_show');
                },
            ])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.customer-management', [
            'customers' => $customers,
        ]);
    }

    public function openCustomerModal(?Customer $customer = null)
    {
        $this->resetValidation();

        if ($customer->id) {
            $this->selectedCustomer = $customer;
            $this->customerName = $customer->name;
            $this->customerPhone = $customer->phone;
            $this->customerEmail = $customer->email;
            $this->customerNotes = $customer->notes;
            $this->isEditing = true;
        } else {
            $this->selectedCustomer = null;
            $this->customerName = '';
            $this->customerPhone = '';
            $this->customerEmail = '';
            $this->customerNotes = '';
            $this->isEditing = false;
        }

        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        $this->validate();

        $data = [
            'name' => $this->customerName,
            'phone' => $this->customerPhone,
            'email' => $this->customerEmail,
            'notes' => $this->customerNotes,
        ];

        if ($this->isEditing) {
            $this->selectedCustomer->update($data);
            session()->flash('message', 'Customer updated successfully.');
        } else {
            $data['business_id'] = request()->user()->business_id;
            Customer::create($data);
            session()->flash('message', 'Customer created successfully.');
        }

        $this->showCustomerModal = false;
        $this->resetPage();
    }

    public function confirmCustomerDeletion(Customer $customer)
    {
        $this->selectedCustomer = $customer;
        $this->showDeleteModal = true;
    }

    public function deleteCustomer()
    {
        if ($this->selectedCustomer) {
            $this->selectedCustomer->delete();
            session()->flash('message', 'Customer deleted successfully.');
        }

        $this->showDeleteModal = false;
        $this->resetPage();
    }

    public function closeModals()
    {
        $this->showDeleteModal = false;
        $this->showCustomerModal = false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }
}