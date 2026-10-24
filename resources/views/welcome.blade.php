<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>NEX — Connect. Share. Discover.</title>

    <meta
        name="description"
        content="NEX is a modern social platform where you can connect, share and discover."
    >

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    @vite(['resources/css/nex-home.css', 'resources/js/nex-home.js'])
</head>

<body>

    <!-- =========================
         HEADER
    ========================== -->

    <header id="header">

        <div class="container nav">

            <a href="{{ url('/') }}" class="logo">

                <div class="logo-mark">
                    <span>N</span>
                </div>

                <span class="logo-text">NEX</span>

            </a>


            <nav class="nav-links" id="navLinks">

                <a href="#home">Home</a>

                <a href="#features">Features</a>

                <a href="#about">About</a>

                @auth
                    <a href="{{ url('/dashboard') }}">
                        Feed
                    </a>
                @endauth

            </nav>


            <div class="nav-actions">

                @auth

                    <a
                        href="{{ url('/dashboard') }}"
                        class="btn btn-primary"
                    >
                        Open NEX
                    </a>

                @else

                    @if (Route::has('login'))

                        <a
                            href="{{ route('login') }}"
                            class="btn btn-login"
                        >
                            Log in
                        </a>

                    @endif


                    @if (Route::has('register'))

                        <a
                            href="{{ route('register') }}"
                            class="btn btn-primary"
                        >
                            Create account
                        </a>

                    @endif

                @endauth


                <button
                    class="mobile-menu"
                    id="mobileMenu"
                    type="button"
                    aria-label="Open menu"
                >
                    ☰
                </button>

            </div>

        </div>

    </header>


    <!-- =========================
         MAIN
    ========================== -->

    <main>

        <!-- HERO -->

        <section class="hero" id="home">

            <div class="container hero-grid">

                <div class="hero-content">

                    <div class="eyebrow">

                        <span class="status-dot"></span>

                        The next social experience

                    </div>


                    <h1>

                        Connect.

                        <span class="gradient-text">
                            Share.
                        </span>

                        Discover.

                    </h1>


                    <p class="hero-description">

                        Welcome to NEX — a modern social space built for
                        people, ideas, moments, and conversations that matter.
                        Follow people you care about and build your own
                        community.

                    </p>


                    <div class="hero-actions">

                        @guest

                            @if (Route::has('register'))

                                <a
                                    href="{{ route('register') }}"
                                    class="btn btn-primary hero-button"
                                >
                                    Join NEX

                                    <span>→</span>
                                </a>

                            @endif


                            @if (Route::has('login'))

                                <a
                                    href="{{ route('login') }}"
                                    class="watch-link"
                                >

                                    <span class="play">
                                        ▶
                                    </span>

                                    Sign in

                                </a>

                            @endif

                        @else

                            <a
                                href="{{ url('/dashboard') }}"
                                class="btn btn-primary hero-button"
                            >
                                Go to your feed

                                <span>→</span>
                            </a>

                        @endguest

                    </div>

                </div>


                <!-- SOCIAL FEED PREVIEW -->

                <div class="preview-area">

                    <div class="glow"></div>


                    <!-- FLOATING CARD -->

                    <div class="float-card float-card-one">

                        <div class="float-icon">
                            ✦
                        </div>

                        <div>

                            <strong>
                                New connection
                            </strong>

                            <small>
                                Someone followed you
                            </small>

                        </div>

                    </div>


                    <!-- FEED WINDOW -->

                    <div class="feed-window">

                        <div class="window-top">

                            <span class="window-dot"></span>
                            <span class="window-dot"></span>
                            <span class="window-dot"></span>

                            <span class="window-title">
                                nex.app / home
                            </span>

                        </div>


                        <div class="fake-feed">

                            <!-- POST -->

                            <article class="post">

                                <div class="post-header">

                                    <div class="avatar">
                                        CJ
                                    </div>

                                    <div>

                                        <div class="user-name">
                                            Christopher
                                        </div>

                                        <div class="post-time">
                                            Just now · NEX
                                        </div>

                                    </div>

                                    <div class="post-menu">
                                        •••
                                    </div>

                                </div>


                                <p class="post-text">

                                    Building something new.
                                    Sometimes the best ideas start
                                    with a simple thought. ✨

                                </p>


                                <div class="post-actions">

                                    <button
                                        type="button"
                                        class="post-action like-button"
                                    >
                                        ♡

                                        <span class="like-count">
                                            128
                                        </span>
                                    </button>


                                    <button
                                        type="button"
                                        class="post-action"
                                    >
                                        ◯ 24
                                    </button>


                                    <button
                                        type="button"
                                        class="post-action"
                                    >
                                        ↗ Share
                                    </button>

                                </div>

                            </article>


                            <!-- POST WITH MEDIA -->

                            <article class="post">

                                <div class="post-header">

                                    <div class="avatar avatar-green">
                                        NX
                                    </div>

                                    <div>

                                        <div class="user-name">
                                            NEX Community
                                        </div>

                                        <div class="post-time">
                                            18 min ago
                                        </div>

                                    </div>

                                    <div class="post-menu">
                                        •••
                                    </div>

                                </div>


                                <p class="post-text">

                                    Your feed is where your world
                                    comes together.

                                </p>


                                <div class="post-image">

                                    <span>NEX</span>

                                </div>


                                <div class="post-actions">

                                    <button
                                        type="button"
                                        class="post-action like-button"
                                    >
                                        ♡

                                        <span class="like-count">
                                            342
                                        </span>
                                    </button>


                                    <button
                                        type="button"
                                        class="post-action"
                                    >
                                        ◯ 51
                                    </button>


                                    <button
                                        type="button"
                                        class="post-action"
                                    >
                                        ↗
                                    </button>

                                </div>

                            </article>

                        </div>

                    </div>


                    <!-- FLOATING CARD -->

                    <div class="float-card float-card-two">

                        <div class="float-icon">
                            ♡
                        </div>

                        <div>

                            <strong>
                                People are talking
                            </strong>

                            <small>
                                Join the conversation
                            </small>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =========================
             STATS
        ========================== -->

        <section class="stats">

            <div class="container">

                <div class="stats-grid">

                    <div class="stat">

                        <div class="stat-number">
                            {{ number_format($connectionCount) }}
                        </div>

                        <div class="stat-label">
                            Connections
                        </div>

                    </div>


                    <div class="stat">

                        <div class="stat-number">
                            {{ number_format($postCount) }}
                        </div>

                        <div class="stat-label">
                            Posts shared
                        </div>

                    </div>

                    <div class="stat">

                        <div class="stat-number">
                            24/7
                        </div>

                        <div class="stat-label">
                            Always connected
                        </div>

                    </div>


                    <div class="stat">

                        <div class="stat-number">
                            ∞
                        </div>

                        <div class="stat-label">
                            Possibilities
                        </div>

                    </div>

                </div>

            </div>

        </section>


        <!-- =========================
             FEATURES
        ========================== -->

        <section
            class="section"
            id="features"
        >

            <div class="container">

                <div class="section-heading">

                    <span>
                        Why NEX
                    </span>

                    <h2>

                        Your social world,

                        <span class="gradient-text">
                            your way.
                        </span>

                    </h2>

                    <p>

                        NEX brings the essential parts of social networking
                        together in one clean, modern experience.

                    </p>

                </div>


                <div class="features">

                    <article class="feature">

                        <div class="feature-icon">
                            ✦
                        </div>

                        <h3>
                            Share your moments
                        </h3>

                        <p>

                            Create posts with text, images, and videos.
                            Share your thoughts and moments with the people
                            who matter to you.

                        </p>

                    </article>


                    <article class="feature">

                        <div class="feature-icon">
                            ◎
                        </div>

                        <h3>
                            Build connections
                        </h3>

                        <p>

                            Follow people you are interested in and discover
                            new accounts through your social network.

                        </p>

                    </article>


                    <article class="feature">

                        <div class="feature-icon">
                            ♡
                        </div>

                        <h3>
                            Engage naturally
                        </h3>

                        <p>

                            Like posts, join conversations, and interact with
                            content directly from your personalized feed.

                        </p>

                    </article>


                    <article class="feature">

                        <div class="feature-icon">
                            ◈
                        </div>

                        <h3>
                            Your profile
                        </h3>

                        <p>

                            Customize your profile with a picture, bio, and
                            status so people can learn more about you.

                        </p>

                    </article>


                    <article class="feature">

                        <div class="feature-icon">
                            ↗
                        </div>

                        <h3>
                            Discover people
                        </h3>

                        <p>

                            Explore other users and find communities,
                            creators, friends, and new conversations.

                        </p>

                    </article>


                    <article class="feature">

                        <div class="feature-icon">
                            ⚡
                        </div>

                        <h3>
                            Simple & responsive
                        </h3>

                        <p>

                            NEX is designed to feel natural on desktop,
                            tablet, and mobile devices.

                        </p>

                    </article>

                </div>

            </div>

        </section>


        <!-- =========================
             ABOUT
        ========================== -->

        <section
            class="section"
            id="about"
        >

            <div class="container">

                <div class="section-heading">

                    <span>
                        About NEX
                    </span>

                    <h2>

                        A place to stay

                        <span class="gradient-text">
                            connected.
                        </span>

                    </h2>

                    <p>

                        NEX is designed around a simple idea:
                        social networking should be easy to use,
                        visually modern, and centered around people.

                    </p>

                </div>

            </div>

        </section>


        <!-- =========================
             CTA
        ========================== -->

        <section class="cta">

            <div class="container">

                <div class="cta-box">

                    <h2>
                        Ready to join NEX?
                    </h2>

                    <p>

                        Create your account and start building your
                        social world today.

                    </p>


                    @guest

                        @if (Route::has('register'))

                            <a
                                href="{{ route('register') }}"
                                class="btn btn-primary"
                            >
                                Create your account

                                <span>→</span>
                            </a>

                        @endif

                    @else

                        <a
                            href="{{ url('/dashboard') }}"
                            class="btn btn-primary"
                        >
                            Enter NEX

                            <span>→</span>
                        </a>

                    @endguest

                </div>

            </div>

        </section>

    </main>


    <!-- =========================
         FOOTER
    ========================== -->

    <footer>

        <div class="container footer-inner">

            <div class="footer-copy">

                © {{ date('Y') }} NEX.
                Connect. Share. Discover.

            </div>


            <div class="footer-links">

                <a href="#home">
                    Home
                </a>

                <a href="#features">
                    Features
                </a>

                <a href="#about">
                    About
                </a>

                @guest

                    @if (Route::has('login'))

                        <a href="{{ route('login') }}">
                            Login
                        </a>

                    @endif

                @endguest

            </div>

        </div>

    </footer>

</body>
</html>
