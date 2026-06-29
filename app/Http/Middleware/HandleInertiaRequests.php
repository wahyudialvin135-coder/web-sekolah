<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $notifications = [];
        $notificationCount = 0;

        if ($user && !$user->isOperator()) {
            // Admin and Pimpinan can see notifications
            $schools = \App\Models\School::with('latestAccreditation')->get();
            $notifications = $schools->filter(function($s) {
                return in_array($s->monitoring_status, ['Habis dalam 6 Bulan', 'Habis dalam 12 Bulan', 'Kadaluarsa']);
            })->map(function($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'status' => $s->monitoring_status,
                    'color' => $s->monitoring_status_color,
                    'expiry_date' => $s->latestAccreditation?->expiry_date?->format('d-m-Y') ?? '-',
                ];
            })->values()->take(5); // Show top 5 in dropdown

            $notificationCount = $schools->filter(function($s) {
                return in_array($s->monitoring_status, ['Habis dalam 6 Bulan', 'Habis dalam 12 Bulan', 'Kadaluarsa']);
            })->count();
        } elseif ($user && $user->isOperator() && $user->school_id) {
            // Operator sees notifications for their own school
            $school = \App\Models\School::with('latestAccreditation')->find($user->school_id);
            if ($school && in_array($school->monitoring_status, ['Habis dalam 6 Bulan', 'Habis dalam 12 Bulan', 'Kadaluarsa'])) {
                $notifications[] = [
                    'id' => $school->id,
                    'name' => $school->name,
                    'status' => $school->monitoring_status,
                    'color' => $school->monitoring_status_color,
                    'expiry_date' => $school->latestAccreditation?->expiry_date?->format('d-m-Y') ?? '-',
                ];
                $notificationCount = 1;
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'school_id' => $user->school_id,
                    'is_admin' => $user->isAdmin(),
                    'is_pimpinan' => $user->isPimpinan(),
                    'is_operator' => $user->isOperator(),
                ] : null,
            ],
            'globalNotifications' => $notifications,
            'globalNotificationCount' => $notificationCount,
        ];
    }
}
