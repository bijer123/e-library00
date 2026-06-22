<?php

namespace App\Livewire\Forms;

use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Form;

class UserForm extends Form
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $role = 'mahasiswa';
    public ?User $user = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($this->user?->id),
            ],
            'password' => [$this->user ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,petugas,mahasiswa'],
        ];
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
    }

    public function store(): void
    {
        $this->validate();
        User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => bcrypt($this->password),
            'role' => $this->role,
        ]);
        $this->reset();
    }

    public function update(): void
    {
        $this->validate();
        $data = ['name' => $this->name, 'email' => $this->email, 'role' => $this->role];
        if ($this->password) {
            $data['password'] = bcrypt($this->password);
        }
        $this->user->update($data);
    }
}