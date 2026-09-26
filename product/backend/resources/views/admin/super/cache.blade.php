@extends('admin.layout')

@section('title', 'System Cache - Super Admin')

@section('content')
    <div class="header">
        <div>
            <div class="eyebrow">Platform Administration</div>
            <h1>System Cache</h1>
            <p class="help">Clear Laravel caches after deploying configuration, routes, views, or application changes.</p>
        </div>
    </div>

    <div class="card" style="max-width: 920px;">
        <div style="display: grid; gap: 14px;">
            @foreach ([
                ['key' => 'application', 'title' => 'Application cache', 'description' => 'Clears cached application data created through Laravel cache stores.'],
                ['key' => 'config', 'title' => 'Configuration cache', 'description' => 'Reloads values from the production .env file on the next request.'],
                ['key' => 'route', 'title' => 'Route cache', 'description' => 'Clears the cached list of application routes.'],
                ['key' => 'view', 'title' => 'Compiled view cache', 'description' => 'Removes compiled Blade templates so they are rebuilt automatically.'],
                ['key' => 'event', 'title' => 'Event cache', 'description' => 'Clears the cached event and listener discovery manifest.'],
            ] as $cache)
                <div style="display: flex; justify-content: space-between; align-items: center; gap: 18px; padding: 16px; border: 1px solid var(--line); border-radius: 10px; flex-wrap: wrap;">
                    <div>
                        <h2 style="font-size: 16px;">{{ $cache['title'] }}</h2>
                        <p class="help" style="margin: 4px 0 0; font-size: 13px;">{{ $cache['description'] }}</p>
                    </div>
                    <form method="POST" action="{{ route('admin.super.cache.clear') }}" style="margin: 0;">
                        @csrf
                        <input type="hidden" name="cache" value="{{ $cache['key'] }}">
                        <button class="button" type="submit" style="width: auto; white-space: nowrap;">Clear cache</button>
                    </form>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 22px; padding-top: 22px; border-top: 1px solid var(--line); display: flex; justify-content: space-between; align-items: center; gap: 18px; flex-wrap: wrap;">
            <div>
                <h2 style="font-size: 17px;">Clear all Laravel caches</h2>
                <p class="help" style="margin: 4px 0 0; font-size: 13px;">Runs Laravel's optimize:clear command for a complete deployment reset.</p>
            </div>
            <form method="POST" action="{{ route('admin.super.cache.clear') }}" style="margin: 0;">
                @csrf
                <input type="hidden" name="cache" value="all">
                <button class="button" type="submit" style="width: auto; background: var(--agent-orange);">Clear all caches</button>
            </form>
        </div>
    </div>
@endsection
