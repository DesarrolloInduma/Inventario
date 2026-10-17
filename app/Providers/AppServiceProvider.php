<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $like = function (string $boolean) {
            return function (string $column, string $value) use ($boolean) {
                if (DB::connection()->getDriverName() === 'pgsql') {
                    $wrapped = $this->getGrammar()->wrap($column);

                    return $this->whereRaw(
                        "unaccent(lower({$wrapped}::text)) ILIKE unaccent(lower(?::text))",
                        [$value],
                        $boolean,
                    );
                }

                return $this->where($column, 'like', $value, $boolean);
            };
        };

        foreach ([EloquentBuilder::class, QueryBuilder::class] as $builder) {
            $builder::macro('whereTextLike', $like('and'));
            $builder::macro('orWhereTextLike', $like('or'));
        }

        if (
            request()->server->get('HTTP_X_FORWARDED_PROTO') === 'https' ||
            request()->isSecure() ||
            str_contains(request()->header('host') ?? '', 'devtunnels.ms') ||
            str_contains(request()->header('host') ?? '', 'ngrok')
        ) {
            URL::forceScheme('https');
        }
    }
}
