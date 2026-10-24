<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $users = User::with(['role', 'area'])
            ->orderBy('name')
            ->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $roles = Role::orderBy('name')->get();
        $areas = Area::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.create', compact('roles', 'areas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $isAdmin = Role::whereKey($request->input('role_id'))->value('name') === 'admin';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->whereIn('name', ['admin', 'operador', 'solicitante']),
            ],
            'area_id' => [
                Rule::prohibitedIf($isAdmin),
                Rule::requiredIf(! $isAdmin),
                'nullable',
                Rule::exists('areas', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $validated['area_id'] = $isAdmin ? null : $validated['area_id'];
        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'Usuário criado com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();
        $areas = Area::where('is_active', true)->orderBy('name')->get();

        return view('admin.users.edit', compact('roles', 'areas', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $isAdmin = Role::whereKey($request->input('role_id'))->value('name') === 'admin';

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . $user->id
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'role_id' => [
                'required',
                Rule::exists('roles', 'id')->whereIn('name', ['admin', 'operador', 'solicitante']),
            ],
            'area_id' => [
                Rule::prohibitedIf($isAdmin),
                Rule::requiredIf(! $isAdmin),
                'nullable',
                Rule::exists('areas', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role_id = $validated['role_id'];
        $user->area_id = $isAdmin ? null : $validated['area_id'];
        
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }
        $user->save();

        return redirect()->route('admin.users.index')->with('success', 'Usuário atualizado com sucesso.');
    }

    /**
     * Disable the specified user instead of deleting it.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->update(['is_active' => false]);

        return redirect()->route('admin.users.index')->with('success', 'Usuário desativado com sucesso.');
    }
}
