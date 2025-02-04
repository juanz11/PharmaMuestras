<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with(['role', 'approvedBy'])
            ->where('id', '!=', Auth::id())
            ->latest()
            ->paginate(10);

        return view('admin.users.index', compact('users'));
    }

    public function approve(User $user)
    {
        $user->update([
            'is_active' => true,
            'approved_at' => now(),
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Usuario activado correctamente.');
    }

    public function deactivate(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'No puedes desactivar a un Super Admin.');
        }

        $user->update([
            'is_active' => false,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return back()->with('success', 'Usuario desactivado correctamente.');
    }

    public function destroy(User $user)
    {
        if ($user->isSuperAdmin()) {
            return back()->with('error', 'No puedes eliminar a un Super Admin.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }
}
