<?php

namespace App\Http\Controllers;

use App\Events\StoreDataChanged;
use App\Models\Role;
use App\Support\RolePermissions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::where('store_id', $request->user()->store_id)
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        $groups = RolePermissions::groups();
        $profiles = RolePermissions::profiles();

        return view('roles.create', [
            'groups' => $groups,
            'profiles' => $profiles,
            'selectedProfile' => 'custom',
            'role' => null,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        Role::create([
            'store_id' => $request->user()->store_id,
            'name' => $data['name'],
            'permissions' => $data['permissions'] ?? [],
        ]);
        StoreDataChanged::dispatch($request->user()->store_id, ['employees']);

        return redirect()->route('roles.index')->with('success', 'Role berhasil dibuat.');
    }

    public function edit(Role $role)
    {
        $groups = RolePermissions::groups();
        $profiles = RolePermissions::profiles();
        $selectedProfile = RolePermissions::profileFor($role->permissions);

        return view('roles.edit', compact('role', 'groups', 'profiles', 'selectedProfile'));
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validatedData($request, $role);
        $role->update([
            'name' => $data['name'],
            'permissions' => $data['permissions'] ?? [],
        ]);
        StoreDataChanged::dispatch($request->user()->store_id, ['employees']);

        return redirect()->route('roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with('error', 'Role bawaan tidak dapat dihapus. Ubah izinnya sesuai kebutuhan toko.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Role masih digunakan. Pindahkan pegawai ke role lain sebelum menghapusnya.');
        }

        $storeId = $role->store_id;
        $role->delete();
        StoreDataChanged::dispatch($storeId, ['employees']);

        return redirect()->route('roles.index')->with('success', 'Role berhasil dihapus.');
    }

    private function validatedData(Request $request, ?Role $role = null): array
    {
        $uniqueName = Rule::unique('roles', 'name')->where('store_id', $request->user()->store_id);
        if ($role) {
            $uniqueName->ignore($role->id);
        }

        $data = $request->validate([
            'name' => [
                'required', 'string', 'max:80',
                $uniqueName,
            ],
            'permission_profile' => ['nullable', Rule::in(array_keys(RolePermissions::profiles()))],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(RolePermissions::all())],
        ]);

        if (!empty($data['permission_profile']) && $data['permission_profile'] !== 'custom') {
            $data['permissions'] = RolePermissions::profiles()[$data['permission_profile']]['permissions'];
        }

        return $data;
    }
}
