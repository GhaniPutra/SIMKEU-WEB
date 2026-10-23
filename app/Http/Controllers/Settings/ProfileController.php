<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): View
    {
        return view('settings.profile', [
            'user' => $request->user(),
        ]);
    }

    public function editStore(Request $request): View
    {
        abort_unless($request->user()->isOwner() && $request->user()->store_id, 403);

        return view('settings.store', ['store' => $request->user()->store]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        unset($validated['profile_photo'], $validated['remove_profile_photo'], $validated['password_confirmation']);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        $oldPhotoPath = $user->profile_photo_path;
        $newPhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $newPhotoPath = $request->file('profile_photo')->store("profile-photos/{$user->id}", 'public');

            if (! $newPhotoPath) {
                return back()->withErrors(['profile_photo' => 'Foto profil gagal disimpan.'])->withInput();
            }

            $user->profile_photo_path = $newPhotoPath;
        } elseif ($request->boolean('remove_profile_photo')) {
            $user->profile_photo_path = null;
        }

        $user->fill($validated);

        try {
            $user->save();
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        if ($oldPhotoPath && $oldPhotoPath !== $user->profile_photo_path) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return to_route('profile.edit')->with('success', 'Pengaturan akun berhasil disimpan.');
    }

    public function updateStore(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user->isOwner() && $user->store_id, 403);

        $validated = $request->validate([
            'store_name' => ['required', 'string', 'max:100'],
            'store_address' => ['nullable', 'string', 'max:2000'],
            'store_phone' => ['nullable', 'string', 'max:20'],
            'stock_closing_time' => ['sometimes', 'required', 'date_format:H:i'],
            'store_logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_store_logo' => ['nullable', 'boolean'],
        ]);

        $store = $user->store;
        abort_unless($store, 404);

        $oldLogoPath = $store->logo;
        $newLogoPath = null;

        if ($request->hasFile('store_logo')) {
            $newLogoPath = $request->file('store_logo')->store("store-logos/{$store->id}", 'public');

            if (! $newLogoPath) {
                return back()->withErrors(['store_logo' => 'Logo toko gagal disimpan.'])->withInput();
            }

            $store->logo = $newLogoPath;
        } elseif ($request->boolean('remove_store_logo')) {
            $store->logo = null;
        }

        $storeData = [
            'name' => $validated['store_name'],
            'address' => $validated['store_address'] ?? null,
            'phone' => $validated['store_phone'] ?? null,
        ];

        if (array_key_exists('stock_closing_time', $validated)) {
            $storeData['stock_closing_time'] = $validated['stock_closing_time'];
        }

        $store->fill($storeData);

        try {
            $store->save();
        } catch (Throwable $exception) {
            if ($newLogoPath) {
                Storage::disk('public')->delete($newLogoPath);
            }

            throw $exception;
        }

        if ($oldLogoPath && $oldLogoPath !== $store->logo) {
            Storage::disk('public')->delete($oldLogoPath);
        }

        return to_route('profile.store.edit')->with('success', 'Data usaha berhasil diperbarui.');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();
        $photoPath = $user->profile_photo_path;

        Auth::logout();

        $user->delete();

        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
