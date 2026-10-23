<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\AdminAccountRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class AdminAccountController extends Controller
{
    public function __construct(private readonly ActivityLogger $logger) {}

    public function index(): Response
    {
        Gate::authorize('viewAny', User::class);

        return Inertia::render('admin-accounts/index', [
            'accounts' => User::query()->admins()->orderBy('name')->orderBy('id')->get()->map(fn (User $u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'created_at' => $u->created_at?->toIso8601String(),
            ])->all(),
        ]);
    }

    public function store(AdminAccountRequest $request): RedirectResponse
    {
        $user = new User($request->safe()->only(['name', 'email', 'password']));
        $user->role = Role::Admin;
        $user->email_verified_at = now();
        $user->save();

        $this->logger->log('account.created', $user, $user->email);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.account.created', ['name' => $user->name])]);

        return back();
    }

    public function update(AdminAccountRequest $request, User $user): RedirectResponse
    {
        $user->update($request->safe()->only(['name', 'email']));

        $this->logger->log('account.updated', $user, $user->email);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.account.updated', ['name' => $user->name])]);

        return back();
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        Gate::authorize('resetPassword', $user);

        $data = $request->validate(['password' => ['required', 'string', Password::defaults()]]);
        $user->update(['password' => $data['password']]);

        $this->logger->log('account.password_reset', $user, $user->email);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.account.password_reset', ['name' => $user->name])]);

        return back();
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('delete', $user);

        $user->delete();

        $this->logger->log('account.deleted', $user, $user->email);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('fuel.account.deleted', ['name' => $user->name])]);

        return back();
    }
}
