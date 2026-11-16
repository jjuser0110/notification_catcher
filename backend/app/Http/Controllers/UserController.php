<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\RateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    private const ROLE_LABELS = [
        User::ROLE_ADMIN => 'Admins',
        User::ROLE_STAFF => 'Staff',
        User::ROLE_CUSTOMER => 'Customers',
        User::ROLE_MERCHANT => 'Merchants',
    ];

    /** List users of one role, scoped to what the viewer may see. */
    public function index(Request $request, string $role): View
    {
        abort_unless(array_key_exists($role, self::ROLE_LABELS), 404);
        $viewer = $request->user();
        abort_unless($this->canViewRole($viewer, $role), 403);

        $query = User::where('role', $role)->with('parent')->latest('id');

        // Customers only see their own subtree; staff-side see everyone.
        if (! $viewer->isStaffSide()) {
            $query->whereIn('id', $viewer->descendantIds() ?: [0]);
        }

        if ($search = $request->query('q')) {
            $query->where(fn ($w) => $w->where('username', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%"));
        }

        $users = $query->paginate(25)->withQueryString();

        return view('users.index', [
            'users' => $users,
            'role' => $role,
            'title' => self::ROLE_LABELS[$role],
            'canCreate' => $viewer->canCreateRole($role),
            'hasRate' => in_array($role, [User::ROLE_CUSTOMER, User::ROLE_MERCHANT], true),
        ]);
    }

    public function create(Request $request, string $role): View
    {
        $viewer = $request->user();
        abort_unless($viewer->canCreateRole($role), 403);

        return view('users.create', $this->formData($viewer, $role));
    }

    public function store(Request $request, string $role): RedirectResponse
    {
        $viewer = $request->user();
        abort_unless($viewer->canCreateRole($role), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')],
            'password' => ['required', 'string', 'min:6'],
            'quota' => ['nullable', 'numeric', 'min:0'],
        ]);

        $attributes = [
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => Hash::make($data['password']),
            'role' => $role,
            'parent_id' => $viewer->id,
            'is_active' => true,
            'quota' => $data['quota'] ?? 0,
        ];

        // Rates apply to customer & merchant only.
        if (in_array($role, [User::ROLE_CUSTOMER, User::ROLE_MERCHANT], true)) {
            [$payin, $payout] = $this->validateRates($request, $viewer);
            $attributes['payin_rate'] = $payin;
            $attributes['payout_rate'] = $payout;
        }

        // Merchants get API credentials automatically.
        if ($role === User::ROLE_MERCHANT) {
            $attributes['api_token'] = Str::random(60);
            $attributes['api_key'] = 'sk_'.Str::random(48);
        }

        User::create($attributes);

        return redirect()
            ->route('users.index', $role)
            ->with('ok', ucfirst($role).' "'.$data['username'].'" created.');
    }

    public function edit(Request $request, User $user): View
    {
        $viewer = $request->user();
        $this->assertCanManage($viewer, $user);

        return view('users.edit', array_merge(
            $this->formData($viewer, $user->role),
            ['user' => $user],
        ));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $viewer = $request->user();
        $this->assertCanManage($viewer, $user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'quota' => ['nullable', 'numeric', 'min:0'],
        ]);

        $user->name = $data['name'];
        $user->quota = $data['quota'] ?? $user->quota;

        if ($user->hasRate()) {
            // Validate against the creator of this user (its parent), falling back to viewer.
            $creator = $user->parent ?: $viewer;
            [$payin, $payout] = $this->validateRates($request, $creator);
            $user->payin_rate = $payin;
            $user->payout_rate = $payout;
        }

        $user->save();

        return redirect()
            ->route('users.index', $user->role)
            ->with('ok', 'Saved changes to "'.$user->username.'".');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $viewer = $request->user();
        $this->assertCanManage($viewer, $user);

        $data = $request->validate([
            'password' => ['required', 'string', 'min:6'],
        ]);

        $user->forceFill(['password' => Hash::make($data['password'])])->save();

        return back()->with('ok', 'Password reset for "'.$user->username.'".');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        $viewer = $request->user();
        $this->assertCanManage($viewer, $user);
        abort_if($user->id === $viewer->id, 403, 'You cannot disable your own account.');

        $user->forceFill(['is_active' => ! $user->is_active])->save();

        return back()->with('ok', ($user->is_active ? 'Enabled' : 'Disabled').' "'.$user->username.'".');
    }

    // -- helpers -------------------------------------------------------------

    private function canViewRole(User $viewer, string $role): bool
    {
        return match ($role) {
            User::ROLE_ADMIN => $viewer->isSuperadmin(),
            User::ROLE_STAFF => $viewer->isSuperadmin() || $viewer->isAdmin(),
            User::ROLE_CUSTOMER, User::ROLE_MERCHANT => $viewer->isStaffSide() || $viewer->isCustomer(),
            default => false,
        };
    }

    /** Staff-side manage everyone; a customer manages only its own subtree. */
    private function assertCanManage(User $viewer, User $target): void
    {
        if ($viewer->isStaffSide()) {
            // Staff-side can manage anyone except they shouldn't touch a superadmin unless they are one.
            abort_if($target->isSuperadmin() && ! $viewer->isSuperadmin(), 403);
            return;
        }
        abort_unless(in_array($target->id, $viewer->descendantIds(), true), 403);
    }

    private function formData(User $viewer, string $role): array
    {
        [$payinMin, $payinMax] = RateService::payinRange($viewer);
        [$payoutMin, $payoutMax] = RateService::payoutRange($viewer);

        return [
            'role' => $role,
            'roleLabel' => ucfirst($role),
            'hasRate' => in_array($role, [User::ROLE_CUSTOMER, User::ROLE_MERCHANT], true),
            'payinMin' => $payinMin,
            'payinMax' => $payinMax,
            'payoutMin' => $payoutMin,
            'payoutMax' => $payoutMax,
            'payinDefault' => RateService::payinDefault($viewer),
            'payoutDefault' => RateService::payoutDefault($viewer),
        ];
    }

    /** @return array{0: float, 1: float} [payin_rate, payout_rate] */
    private function validateRates(Request $request, User $creator): array
    {
        [$payinMin, $payinMax] = RateService::payinRange($creator);
        [$payoutMin, $payoutMax] = RateService::payoutRange($creator);

        $data = $request->validate([
            'payin_rate' => ['required', 'numeric'],
            'payout_rate' => ['required', 'numeric'],
        ]);

        $payin = round((float) $data['payin_rate'], 2);
        $payout = round((float) $data['payout_rate'], 2);

        if ($payin < $payinMin || $payin > $payinMax) {
            throw ValidationException::withMessages([
                'payin_rate' => "Payin rate must be between {$payinMin}% and {$payinMax}%.",
            ]);
        }
        if ($payout < $payoutMin || $payout > $payoutMax) {
            throw ValidationException::withMessages([
                'payout_rate' => "Payout rate must be between {$payoutMin}% and {$payoutMax}%.",
            ]);
        }

        return [$payin, $payout];
    }
}
