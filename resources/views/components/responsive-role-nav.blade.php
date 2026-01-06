@php
    $role = auth()->user()?->role;
    $user = auth()->user();
    $isEvaluator = $user?->isEvaluator() ?? false;
    $dashboardRoutes = [
        'student' => 'student.dashboard',
        'supervisor' => 'supervisor.dashboard',
        'admin' => 'admin.dashboard',
    ];
    $dashboardRoute = $dashboardRoutes[$role] ?? 'dashboard';
@endphp

@if ($role === 'student')
    <x-responsive-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)">
        Dashboard
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('projects.index')" :active="request()->routeIs('projects.*')">
        My Projects
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('supervisors.directory')" :active="request()->routeIs('supervisors.directory')">
        Supervisors
    </x-responsive-nav-link>

@elseif ($role === 'supervisor')
    <x-responsive-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)">
        Dashboard
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('supervisor.projects')" :active="request()->routeIs('supervisor.projects')">
        Assigned Projects
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('supervisor.history')" :active="request()->routeIs('supervisor.history')">
        History
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('supervisor.profile.edit')" :active="request()->routeIs('supervisor.profile.edit')">
        My Profile
    </x-responsive-nav-link>

    @if ($isEvaluator)
    <x-responsive-nav-link :href="route('member.sessions.index')" :active="request()->routeIs('member.sessions.index')">
        My Sessions (as evaluator)
    </x-responsive-nav-link>
    @endif
        

@elseif ($role === 'admin')
    <x-nav-link :href="route($dashboardRoute)" :active="request()->routeIs($dashboardRoute)">
        Dashboard
    </x-nav-link>

    <x-responsive-nav-link :href="route('admin.phases.index')" :active="request()->routeIs('admin.phases.*')">
        Phases
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('admin.projects.index')" :active="request()->routeIs('admin.projects.*')">
        Projects
    </x-responsive-nav-link>

    <x-responsive-nav-link :href="route('admin.scope-reviews.index')" :active="request()->routeIs('admin.scope-reviews.*')">
        Scope Reviews
    </x-responsive-nav-link>

    <x-nav-link :href="route('admin.templates.index')" :active="request()->routeIs('admin.templates.*')">
        Templates
    </x-nav-link>

    <x-nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
        Users
    </x-nav-link>

    <x-nav-link :href="route('admin.evaluators.index')" :active="request()->routeIs('admin.evaluators.*')">
        Evaluators
    </x-nav-link>

    <x-nav-link :href="route('admin.committees.index')" :active="request()->routeIs('admin.committees.*')">
        Committees
    </x-nav-link>

    <x-nav-link :href="route('admin.defence-sessions.index')" :active="request()->routeIs('admin.defence-sessions.*')">
        Defence Sessions
    </x-nav-link>
@endif