# Laravel Performance Optimization Guide

## Current Optimizations Applied

### ✅ Dashboard Caching (Biggest Win)

The `/api/teacher/dashboard` endpoint runs **~55+ queries** in the original version. Now it's **cached for 60 seconds** using Laravel's `Cache::remember()`. This means:
- **Before**: 55+ queries per page load
- **After**: 1 cache read + 0 DB queries (when cache is warm)
- Cache is invalidated automatically after 60 seconds
- Each teacher gets their own cache key

### ✅ Chart Queries Optimized

Instead of running **24 individual count queries** for the poll chart (12 months × 2 models), it now runs just **2 GROUP BY queries**.

Before the change:
```
For each of 12 months → Poll::whereBetween()->count()    ← 12 queries
For each of 12 months → Quiz::whereBetween()->count()    ← 12 queries
For each of 7 days → Vote::whereHas(Poll)->count()       ← 7 queries
For each of 7 days → QuizSubmission::whereHas(Quiz)->count() ← 7 queries
Total: 38 queries for charts alone
```

After the change:
```
SELECT DATE_FORMAT(created_at, '%Y-%m'), COUNT(*) FROM polls GROUP BY month  ← 1 query
SELECT DATE_FORMAT(created_at, '%Y-%m'), COUNT(*) FROM quizzes GROUP BY month ← 1 query
SELECT DATE(votes.created_at), COUNT(*) ... GROUP BY day                       ← 1 query
SELECT DATE(submissions.created_at), COUNT(*) ... GROUP BY day                ← 1 query
Total: 4 queries for charts
```

### ✅ N+1 Query Fixed

The `getRecentActivities()` method previously called `$poll->votes()->count()` inside a `foreach` loop — this is the classic N+1 problem. Now uses `withCount('votes')` to eager-load the count in a single query.

### ✅ Database Indexes Added (via migration)

Indexes added on every frequently-queried column:

| Table | Indexed Columns | Benefit |
|-------|----------------|---------|
| `polls` | `teacher_id`, `status`, `room_code`, `created_at` | Dashboard, filtering, join-by-code |
| `votes` | `poll_id`, `student_id`, `option_id`, `created_at` | Vote counting, student lookup |
| `quizzes` | `teacher_id`, `status`, `created_at` | Dashboard, filtering |
| `questions` | `quiz_id` | Quiz question loading |
| `quiz_submissions` | `quiz_id`, `student_name`, `created_at` | Rankings, reports |
| `game_sessions` | `teacher_id`, `join_code`, `status`, `created_at` | Join game, dashboard |
| `game_answers` | `game_session_id`, `user_id` | Leaderboard, scoring |
| `wheels` | `user_id`, `share_token` | User's wheels, sharing |
| `user_profiles` | `user_id`, `school_id` | Profile lookups |
| Spatie tables | `model_id` | Permission checks |

### ✅ JOINs Replaced Sub-queries

The `whereHas()` calls (which generate correlated sub-queries) were replaced with explicit `join()` calls. This is significantly faster, especially on large datasets.

**Before**: `Vote::whereHas('poll', fn($q) => $q->where('teacher_id', $id))->count()`
**After**: `Vote::query()->join('polls', ...)->where('polls.teacher_id', $id)->count()`

### ✅ Lazy Loading Prevention

In `AppServiceProvider`, we've enabled `Model::preventLazyLoading()` in non-production environments. This will throw an exception if any code triggers an N+1 query via lazy-loaded relationships, so you can catch and fix them during development.

---

## ⚡ Recommended Production Configuration

### 1. Use a Real Cache Driver ⭐ (HIGH IMPACT)

The default `CACHE_STORE=database` stores cache in MySQL — the **slowest** option. 

**For single-server**: Switch to file cache immediately:
```
CACHE_STORE=file
```

**For multi-server / better performance**: Install Redis:
```bash
# Install PhpRedis extension, then:
CACHE_STORE=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. Enable OPcache ⭐ (HIGH IMPACT)

Add this to your `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=60
opcache.fast_shutdown=1
```

### 3. Run Laravel Optimizations

```bash
# Cache config, routes, events (do this on every deployment)
php artisan optimize

# Or individually:
php artisan config:cache    # Merge config files into one
php artisan route:cache     # Cache route registration
php artisan event:cache     # Cache event listeners
php artisan view:cache      # Pre-compile Blade templates
```

### 4. Switch Session Driver

Database sessions are slow. Use one of these:
```
SESSION_DRIVER=file       # Good for single-server
SESSION_DRIVER=redis      # Best for multi-server
SESSION_DRIVER=cookie     # Fastest (but 4KB size limit)
```

### 5. Queue Driver

For background jobs (report exports, notifications):
```
QUEUE_CONNECTION=redis     # Fastest
# Or for single-server:
QUEUE_CONNECTION=database  # Current — adequate for small scale
```

---

## 📊 Performance Benchmarking

Test the dashboard before/after optimizations:

```bash
# Install Laravel Debugbar for development
composer require barryvdh/laravel-debugbar --dev

# Or use manual query logging:
php artisan tinker --execute '
    DB::enableQueryLog();
    App\Models\Poll::where("teacher_id", "some-uuid")->count();
    dump(DB::getQueryLog());
'
```

---

## 🔍 Monitoring

For production, monitor:
1. **Query count** — Should be < 20 per page load
2. **Cache hit ratio** — High = good
3. **Slow queries** — Enable MySQL slow query log
4. **PHP-FPM status** — Check `pm.status_path`
