<?php

namespace App\Http\Livewire;

use App\Models\Business;
use App\Models\BusinessLocation;
use Livewire\Component;
use Livewire\WithPagination;

class BusinessLocations extends Component
{
    use WithPagination;

    public $business;
    public $location = [];
    public $isOpen = false;
    public $confirmingLocationDeletion = false;
    public $locationIdBeingDeleted;

    protected $rules = [
        'location.name' => 'required|string|max:255',
        'location.address' => 'nullable|string|max:255',
        'location.city' => 'nullable|string|max:255',
        'location.state' => 'nullable|string|max:255',
        'location.zip_code' => 'nullable|string|max:255',
        'location.country' => 'nullable|string|max:255',
        'location.phone' => 'nullable|string|max:255',
        'location.email' => 'nullable|email|max:255',
        'location.is_active' => 'boolean',
    ];


    public function render()
    {
        return view('livewire.business-locations', [
            'locations' => $this->business->locations()->paginate(10),
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
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



    public function edit($id)
    {
        $location = BusinessLocation::findOrFail($id);
        $this->location = $location->toArray();
        $this->openModal();
    }



    public function confirmLocationDeletion($locationId)
    {
        $this->confirmingLocationDeletion = true;
        $this->locationIdBeingDeleted = $locationId;
    }


    private function resetInputFields()
    {
        $this->location = [
            'is_active' => true
        ];
    }

    public function mount(Business $business)
    {
        if (request()->user()->cannot('view', $business)) {
            abort(403);
        }

        $this->business = $business;
        $this->location['is_active'] = true;
    }

    public function store()
    {
        if (request()->user()->cannot('update', $this->business)) {
            abort(403);
        }

        $this->validate();

        $this->business->locations()->create($this->location);

        session()->flash('message', 'Location created successfully.');

        $this->closeModal();
        $this->resetInputFields();
    }

    public function update()
    {
        $this->validate();

        if (isset($this->location['id'])) {
            $location = BusinessLocation::find($this->location['id']);

            if (request()->user()->cannot('update', $location)) {
                abort(403);
            }

            $location->update($this->location);
            session()->flash('message', 'Location updated successfully.');
        }

        $this->closeModal();
        $this->resetInputFields();
    }

    public function deleteLocation()
    {
        $location = BusinessLocation::find($this->locationIdBeingDeleted);

        if (!$location || request()->user()->cannot('delete', $location)) {
            abort(403);
        }

        $location->delete();
        session()->flash('message', 'Location deleted successfully.');

        $this->confirmingLocationDeletion = false;
        $this->locationIdBeingDeleted = null;
    }
}