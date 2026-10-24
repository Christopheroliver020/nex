<nav x-data="{ userOpen: false, mobileOpen: false }" class="nex-nav">
    @php($unreadNotificationCount = Auth::user()->unreadNotifications()->count())
    @php($unreadMessageCount = Auth::user()->messagesReceived()->whereNull('read_at')->count())

    <div class="nex-nav-inner">
        <a href="{{ route('newsfeed') }}" class="nex-nav-brand">
            <span class="nex-nav-logo">N</span><span class="nex-nav-name">NEX</span>
        </a>

        <div class="nex-nav-links">
            <a href="{{ route('newsfeed') }}" class="nex-nav-link {{ request()->routeIs('newsfeed') ? 'active' : '' }}"><span class="nex-nav-link-icon">@include('partials.icon', ['icon' => 'home'])</span><span>Newsfeed</span></a>
            <a href="{{ route('reels.index') }}" class="nex-nav-link {{ request()->routeIs('reels.*') ? 'active' : '' }}"><span class="nex-nav-link-icon">@include('partials.icon', ['icon' => 'reels'])</span><span>Reels</span></a>
            <div class="nex-search nex-search-desktop" data-search-url="{{ route('users.search') }}">
                <span class="nex-search-icon" aria-hidden="true">@include('partials.icon', ['icon' => 'search'])</span>
                <input class="nex-search-input" type="search" placeholder="Find people" aria-label="Search people" autocomplete="off" aria-autocomplete="list" aria-expanded="false">
                <div class="nex-search-results" role="listbox" aria-label="People"></div>
            </div>
            <a href="{{ route('notifications.index') }}" class="nex-nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"><span class="nex-nav-link-icon">@include('partials.icon', ['icon' => 'bell'])</span><span>Notifications</span>@if($unreadNotificationCount)<b class="nex-nav-count">{{ $unreadNotificationCount }}</b>@endif</a>
            <a href="{{ route('messages.index') }}" class="nex-nav-link {{ request()->routeIs('messages.*') ? 'active' : '' }}"><span class="nex-nav-link-icon">@include('partials.icon', ['icon' => 'messages'])</span><span>Messages</span></a>
        </div>

        <div class="nex-nav-right">
            <a href="#create-post" class="nex-create-post-btn"><span>@include('partials.icon', ['icon' => 'plus'])</span><span>Create</span></a>
            <div class="nex-user-menu">
                <button type="button" class="nex-user-button" @click="userOpen = !userOpen" :aria-expanded="userOpen">
                    <span class="nex-user-avatar">@if(Auth::user()->profile_photo)<img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="">@else{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}@endif</span>
                    <span class="nex-user-name">{{ Auth::user()->name }}</span>
                    <span class="nex-user-arrow" :class="{ 'rotate': userOpen }">▾</span>
                </button>
                <div x-show="userOpen" x-transition @click.outside="userOpen = false" @keydown.escape.window="userOpen = false" class="nex-user-dropdown" style="display:none">
                    <div class="nex-dropdown-user">
                        <div class="nex-dropdown-avatar">@if(Auth::user()->profile_photo)<img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="">@else{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}@endif</div>
                        <div><strong>{{ Auth::user()->name }}</strong><span>{{ Auth::user()->email }}</span></div>
                    </div>
                    <div class="nex-dropdown-divider"></div>
                    <a href="{{ route('profiles.show', Auth::user()) }}" class="nex-dropdown-link"><span>@include('partials.icon', ['icon' => 'user'])</span><span>My Profile</span></a>
                    <a href="{{ route('setting.settings') }}" class="nex-dropdown-link"><span>@include('partials.icon', ['icon' => 'settings'])</span><span>Settings</span></a>
                    @if (Auth::user()->is_admin)<a href="{{ route('admin.reports.index') }}" class="nex-dropdown-link"><span>@include('partials.icon', ['icon' => 'shield'])</span><span>NEX moderation</span></a>@endif
                    <div class="nex-dropdown-divider"></div>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="nex-dropdown-link nex-logout"><span>@include('partials.icon', ['icon' => 'logout'])</span><span>Log Out</span></button></form>
                </div>
            </div>
            <button type="button" class="nex-mobile-menu-button" @click="mobileOpen = !mobileOpen" :aria-expanded="mobileOpen" aria-label="Toggle navigation menu"><span></span><span></span><span></span></button>
        </div>
    </div>

    <div class="nex-mobile-menu" x-show="mobileOpen" x-transition @click.outside="mobileOpen = false" @keydown.escape.window="mobileOpen = false" style="display:none">
        <a href="{{ route('profiles.show', Auth::user()) }}" class="nex-mobile-link"><span>@include('partials.icon', ['icon' => 'user'])</span>My Profile</a>
        <a href="{{ route('setting.settings') }}" class="nex-mobile-link"><span>@include('partials.icon', ['icon' => 'settings'])</span>Settings</a>
        @if (Auth::user()->is_admin)<a href="{{ route('admin.reports.index') }}" class="nex-mobile-link"><span>@include('partials.icon', ['icon' => 'shield'])</span>NEX moderation</a>@endif
        <div class="nex-mobile-divider"></div>
        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="nex-mobile-link nex-logout"><span>@include('partials.icon', ['icon' => 'logout'])</span>Log Out</button></form>
    </div>
</nav>

<nav class="nex-mobile-dock" aria-label="Main navigation">
    <a href="{{ route('newsfeed') }}" class="nex-dock-link {{ request()->routeIs('newsfeed') ? 'active' : '' }}" @if(request()->routeIs('newsfeed')) aria-current="page" @endif>
        <span class="nex-dock-icon">@include('partials.icon', ['icon' => 'home'])</span><span>Newsfeed</span>
    </a>
    <a href="{{ route('reels.index') }}" class="nex-dock-link {{ request()->routeIs('reels.*') ? 'active' : '' }}" @if(request()->routeIs('reels.*')) aria-current="page" @endif>
        <span class="nex-dock-icon">@include('partials.icon', ['icon' => 'reels'])</span><span>Reels</span>
    </a>
    <div class="nex-search nex-search-bottom" data-search-url="{{ route('users.search') }}">
        <span class="nex-search-icon" aria-hidden="true">@include('partials.icon', ['icon' => 'search'])</span>
        <input class="nex-search-input" type="search" placeholder="Search" aria-label="Search people" autocomplete="off" aria-autocomplete="list" aria-expanded="false">
        <div class="nex-search-results" role="listbox" aria-label="People"></div>
    </div>
    <a href="{{ route('notifications.index') }}" class="nex-dock-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}" @if(request()->routeIs('notifications.*')) aria-current="page" @endif>
        <span class="nex-dock-icon">@include('partials.icon', ['icon' => 'bell'])@if($unreadNotificationCount)<b class="nex-dock-badge">{{ $unreadNotificationCount > 99 ? '99+' : $unreadNotificationCount }}</b>@endif</span><span>Notifications</span>
    </a>
    <a href="{{ route('messages.index') }}" class="nex-dock-link {{ request()->routeIs('messages.*') ? 'active' : '' }}" @if(request()->routeIs('messages.*')) aria-current="page" @endif>
        <span class="nex-dock-icon">@include('partials.icon', ['icon' => 'messages'])@if($unreadMessageCount)<b class="nex-dock-badge">{{ $unreadMessageCount > 99 ? '99+' : $unreadMessageCount }}</b>@endif</span><span>Messages</span>
    </a>
</nav>
