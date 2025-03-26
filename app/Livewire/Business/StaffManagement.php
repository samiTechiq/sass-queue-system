<?php

namespace App\Http\Livewire\Business;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class StaffManagement extends Component
{
    use WithPagination;

    public $name;
    public $email;
    public $phone;
    public $role = 'staff';
    public $password;
    public $staffId;
    public $isEditing = false;

    // Modal states
    public $showAddModal = false;
    public $showEditModal = false;
    public $showDeleteModal = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'nullable|string|max:20',
        'role' => 'required|in:business_admin,staff',
        'password' => 'required|min:8',
    ];

    public function render()
    {
        $business = auth()->user()->business;
        $staff = User::where('business_id', $business->id)
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.business.staff-management', [
            'staff' => $staff
        ]);
    }

    public function openAddModal()
    {
        $this->resetInputFields();
        $this->showAddModal = true;
    }

    public function openEditModal($id)
    {
        $this->resetInputFields();
        $this->staffId = $id;
        $this->isEditing = true;

        $staff = User::findOrFail($id);
        $this->name = $staff->name;
        $this->email = $staff->email;
        $this->phone = $staff->phone;
        $this->role = $staff->role;

        // Don't fill password - will require new password if changing
        $this->rules['password'] = 'nullable|min:8';

        $this->showEditModal = true;
    }

    public function openDeleteModal($id)
    {
        $this->staffId = $id;
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showAddModal = false;
        $this->showEditModal = false;
        $this->showDeleteModal = false;
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->phone = '';
        $this->role = 'staff';
        $this->password = '';
        $this->staffId = null;
        $this->isEditing = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function create()
    {
        // Update validation rules for unique email
        $this->rules['email'] = [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')
        ];

        $this->validate();

        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'password' => Hash::make($this->password),
            'business_id' => auth()->user()->business_id,
        ]);

        $this->closeModal();
        $this->resetInputFields();

        session()->flash('message', 'Staff member created successfully.');
    }

    public function update()
    {
        // Update validation rules for unique email except current user
        $this->rules['email'] = [
            'required',
            'email',
            'max:255',
            Rule::unique('users', 'email')->ignore($this->staffId)
        ];

        $this->validate();

        $user = User::findOrFail($this->staffId);

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
        ];

        // Only update password if provided
        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        $user->update($userData);

        $this->closeModal();
        $this->resetInputFields();

        session()->flash('message', 'Staff member updated successfully.');
    }

    public function delete()
    {
        $user = User::findOrFail($this->staffId);

        // Prevent deleting self
        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot delete your own account.');
            $this->closeModal();
            return;
        }

        // Instead of actually deleting, we might want to deactivate
        // This keeps history intact but prevents login
        $user->update(['active' => false]);

        $this->closeModal();
        session()->flash('message', 'Staff member deactivated successfully.');
    }
}