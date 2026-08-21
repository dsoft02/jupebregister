<?php

use App\Actions\Logs\LogActivity;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.app')] class extends Component {
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = '';

    public bool $showCreate = false;

    public ?int $editingId = null;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$this->editingId],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::enum(UserRole::class)],
        ];
    }

    public function startCreate(): void
    {
        $this->reset(['name', 'email', 'password', 'role', 'editingId']);
        $this->showCreate = true;
    }

    public function startEdit(User $user): void
    {
        $this->authorize('update', $user);

        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->getRoleNames()->first() ?? '';
        $this->showCreate = true;
    }

    public function save(LogActivity $log): void
    {
        if ($this->editingId) {
            $this->authorize('update', User::findOrFail($this->editingId));
        } else {
            $this->authorize('create', User::class);
        }

        $this->validate();

        $data = [
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->password !== '') {
            $data['password'] = $this->password;
        }

        if ($this->editingId) {
            $user = tap(User::findOrFail($this->editingId))->update($data);
            $user->syncRoles([$this->role]);

            $log->run(
                action: 'user.updated',
                description: 'Updated account for '.$user->email.' with role '.$this->role,
                modelType: User::class,
                modelId: $user->id,
            );

            $message = 'User account updated.';
        } else {
            $user = User::create($data);
            $user->assignRole($this->role);

            $log->run(
                action: 'user.created',
                description: 'Created account for '.$user->email.' with role '.$this->role,
                modelType: User::class,
                modelId: $user->id,
            );

            $message = 'User account created.';
        }

        $this->reset(['name', 'email', 'password', 'role', 'showCreate', 'editingId']);
        $this->dispatch('flash-message', message: $message);
    }

    public function deleteUser(User $user, LogActivity $log): void
    {
        $this->authorize('delete', $user);

        $log->run(
            action: 'user.deleted',
            description: 'Deleted account for '.$user->email,
            modelType: User::class,
            modelId: $user->id,
        );

        $user->delete();

        $this->dispatch('flash-message', message: 'User deleted.');
    }

    #[Computed]
    public function users()
    {
        return User::with('roles')->latest()->get();
    }

    #[Computed]
    public function roles(): array
    {
        return UserRole::cases();
    }
};

?>

<div class="space-y-6">
    <x-admin.page-header
        title="Users"
        eyebrow="Access Control"
        description="Create and manage staff and student accounts.">
        <button wire:click="startCreate" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New User
        </button>
    </x-admin.page-header>

    @if ($showCreate)
        <form wire:submit="save" class="card p-6">
            <h3 class="mb-5 text-sm font-bold uppercase tracking-wider text-slate-500">
                {{ $editingId ? 'Edit User Account' : 'Create User Account' }}
            </h3>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="name" class="label">Name <span class="text-red-500">*</span></label>
                    <input type="text" id="name" wire:model="name" class="input" placeholder="Full name">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <div>
                    <label for="email" class="label">Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" wire:model="email" class="input" placeholder="user@example.com">
                    <x-input-error :messages="$errors->get('email')" class="mt-1" />
                </div>
                <div>
                    <label for="password" class="label">Password @if (! $editingId)<span class="text-red-500">*</span>@endif</label>
                    <input type="password" id="password" wire:model="password" class="input" placeholder="{{ $editingId ? 'Leave blank to keep current password' : 'Min 8 characters' }}">
                    <x-input-error :messages="$errors->get('password')" class="mt-1" />
                </div>
                <div>
                    <label for="role" class="label">Role <span class="text-red-500">*</span></label>
                    <select id="role" wire:model="role" class="input">
                        <option value="">Select role</option>
                        @foreach ($this->roles as $role)
                            <option value="{{ $role->value }}">{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('role')" class="mt-1" />
                </div>
            </div>
            <div class="mt-5 flex justify-end gap-3">
                <button type="button" wire:click="$set('showCreate', false)" class="btn-outline">Cancel</button>
                <button type="submit" class="btn-primary">{{ $editingId ? 'Update User' : 'Create User' }}</button>
            </div>
        </form>
    @endif

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="border-b border-slate-100">
                    <tr>
                        <th class="th">User</th>
                        <th class="th">Role</th>
                        <th class="th">Created</th>
                        <th class="th text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($this->users as $user)
                        <tr class="hover:bg-slate-50">
                            <td class="td">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-100 text-sm font-bold text-primary-800">
                                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800">{{ $user->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="td">
                                @foreach ($user->getRoleNames() as $roleName)
                                    <span class="badge {{ $roleName === 'super_admin' ? 'bg-accent-100 text-accent-800' : 'bg-secondary-50 text-secondary-700' }}">
                                        {{ str_replace('_', ' ', $roleName) }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="td">{{ $user->created_at?->format('d M Y') }}</td>
                            <td class="td text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="startEdit({{ $user->id }})" title="Edit"
                                        class="rounded-lg p-2 text-slate-500 transition hover:bg-primary-50 hover:text-primary-700">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/></svg>
                                    </button>
                                    @if (auth()->id() !== $user->id && ! $user->isSuperAdmin())
                                        <button wire:click="deleteUser({{ $user->id }})" wire:confirm="Delete account for {{ $user->email }}?"
                                            class="rounded-lg p-2 text-slate-500 transition hover:bg-red-50 hover:text-red-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <x-admin.empty-state title="No users yet" icon="users" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
