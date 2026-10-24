<?php

use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\Admin\ModerationController;
use App\Http\Controllers\AccountSuspensionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MyDayController;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureUserIsNexAdmin;
use App\Models\User;
use App\Models\Post;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $connectionCount = DB::table('follows')->count();
    $postCount = Post::count();

    return view('welcome', compact('connectionCount', 'postCount'));
});

Route::middleware('auth')->group(function () {
    Route::get('/account/suspended', [AccountSuspensionController::class, 'show'])->name('account.suspended');
    Route::post('/account/suspended/checkout/{provider}', [AccountSuspensionController::class, 'checkout'])
        ->middleware('throttle:6,1')->name('account.suspended.checkout');
    Route::get('/account/suspended/paypal/return', [AccountSuspensionController::class, 'paypalReturn'])
        ->name('account.suspended.paypal.return');
    Route::get('/account/suspended/paypal/cancel', fn () => redirect()->route('account.suspended')->with('checkout_error', 'PayPal checkout was cancelled.'))
        ->name('account.suspended.paypal.cancel');
});

Route::post('/webhooks/paymongo/account-recovery', [AccountSuspensionController::class, 'paymongoWebhook'])
    ->middleware('throttle:60,1')->name('webhooks.paymongo.account-recovery');

Route::middleware(['auth', 'verified', EnsureAccountIsActive::class])->group(function () {

    Route::prefix('admin')->name('admin.')->middleware(EnsureUserIsNexAdmin::class)->group(function () {
        Route::get('/reports', [ModerationController::class, 'reports'])->name('reports.index');
        Route::patch('/reports/{postReport}', [ModerationController::class, 'resolveReport'])->name('reports.resolve');
        Route::get('/accounts', [ModerationController::class, 'accounts'])->name('accounts.index');
        Route::post('/accounts/{user}/ban', [ModerationController::class, 'ban'])->name('accounts.ban');
        Route::patch('/accounts/{user}/ban', [ModerationController::class, 'changeBan'])->name('accounts.ban.update');
        Route::delete('/accounts/{user}/ban', [ModerationController::class, 'restore'])->name('accounts.restore');
    });

    /*
    |--------------------------------------------------------------------------
    | Newsfeed
    |--------------------------------------------------------------------------
    */

    Route::get('/newsfeed', [PostController::class, 'index'])
        ->name('newsfeed');

    Route::get('/reels', [PostController::class, 'reels'])->name('reels.index');

    Route::get('/myday', [MyDayController::class, 'index'])->name('myday.index');
    Route::post('/myday', [MyDayController::class, 'store'])->middleware('throttle:10,1')->name('myday.store');
    Route::post('/myday/highlights', [MyDayController::class, 'feature'])->name('myday.highlights.store');
    Route::post('/myday/highlights/{highlight}/stories', [MyDayController::class, 'addToFeature'])->name('myday.highlights.stories.store');
    Route::delete('/myday/highlights/{highlight}', [MyDayController::class, 'deleteHighlight'])->name('myday.highlights.destroy');
    Route::get('/myday/{story}', [MyDayController::class, 'show'])->name('myday.show');
    Route::get('/myday/{story}/media/{kind}', [MyDayController::class, 'media'])->name('myday.media');
    Route::delete('/myday/{story}', [MyDayController::class, 'destroy'])->name('myday.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{user}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{user}', [MessageController::class, 'store'])->middleware('throttle:30,1')->name('messages.store');

    Route::get('/search/users', function (\Illuminate\Http\Request $request) {
        $query = trim((string) $request->query('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json([]);
        }

        $users = User::query()
            ->where('id', '!=', $request->user()->id)
            ->where(function ($builder) use ($query) {
                $builder->where('name', 'like', "%{$query}%")
                    ->orWhere('username', 'like', "%{$query}%");
            })
            ->where('is_permanently_banned', false)
            ->where(function ($builder) {
                $builder->whereNull('banned_until')->orWhere('banned_until', '<=', now());
            })
            ->orderBy('name')
            ->limit(8)
            ->get(['id', 'name', 'username', 'profile_photo']);

        return response()->json($users->map(fn (User $user) => [
            'name' => $user->name,
            'username' => $user->username,
            'photo' => $user->profile_photo ? asset('storage/' . $user->profile_photo) : null,
            'url' => route('profiles.show', $user),
        ]));
    })->middleware('throttle:60,1')->name('users.search');

    /*
    |--------------------------------------------------------------------------
    | Posts
    |--------------------------------------------------------------------------
    */

    Route::post('/newsfeed', [PostController::class, 'store'])
        ->name('newsfeed.store');

    Route::post('/users/{user}/follow', [FollowController::class, 'store'])->name('users.follow');
    Route::delete('/users/{user}/follow', [FollowController::class, 'destroy'])->name('users.unfollow');
    Route::post('/users/{user}/report', [PostController::class, 'reportUser'])
        ->middleware('throttle:10,1')->name('users.report');

    Route::patch('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::post('/posts/{post}/report', [PostController::class, 'report'])
        ->middleware('throttle:10,1')->name('posts.report');
    Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');

    Route::delete('/posts/{post}', [PostController::class, 'destroy'])
        ->name('posts.destroy');

    /*
    |--------------------------------------------------------------------------
    | Post Reactions
    |--------------------------------------------------------------------------
    */

    Route::post('/posts/{post}/react', [ReactionController::class, 'post'])
        ->name('posts.react');

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    */

    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])
        ->name('comments.store');

    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])
        ->name('comments.destroy');

    /*
    |--------------------------------------------------------------------------
    | Comment Reactions
    |--------------------------------------------------------------------------
    */

    Route::post('/comments/{comment}/react', [ReactionController::class, 'comment'])
        ->name('comments.react');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::get('/users/{user}', [ProfileController::class, 'show'])
        ->name('profiles.show');

    Route::get('/users/{user}/media/{type}', [ProfileController::class, 'media'])
        ->name('profiles.media');

    Route::get('/settings', function () {
        $user = request()->user();

        return view('setting.settings', [
            'user' => $user,
            'postCount' => $user->posts()->count(),
            'followersCount' => $user->followers()->count(),
            'followingCount' => $user->following()->count(),
        ]);
    })->name('setting.settings');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/settings/profile', [ProfileController::class, 'updateSettings'])
        ->name('settings.profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__ . '/auth.php';
