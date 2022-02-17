# Socialiter for Laravel

![socialiter-masthead](https://user-images.githubusercontent.com/1791050/66761837-a9378980-ee59-11e9-9ddf-0293e0eb344b.png)

## Supporting This Package

This is an MIT-licensed open source project with its ongoing development made possible by the support of the community. If you'd like to support this, and our other packages, please consider sponsoring us via the button above.

We thank the following sponsors for their generosity, please take a moment to check them out:

- [LIX](https://lix-it.com)

## Table of Contents

-   [Requirements](#Requirements)
-   [Installation](#Installation)
-   [Implementation](#Implementation)

<a name="Requirements"></a>

## Requirements

-   PHP 7.2+
-   Laravel 7.x
-   Socialite 4.2+

<a name="Installation"></a>

## Installation

1. Install the composer package:

    ```sh
    composer require genealabs/laravel-socialiter
    ```

2. Add the social credentials table:

    ```sh
    php artisan migrate
    ```

    To prevent automatic migrations from running (for example if you have a different migration setup, like multi-tenancy, etc.), add the following entry to your app's service provider:

    ```php
    <?php

    namespace App\Providers;

    use GeneaLabs\LaravelSocialiter\Socialiter;
    use Illuminate\Support\ServiceProvider;

    class AppServiceProvider extends ServiceProvider
    {
        public function register()
        {
            //
        }

        public function boot()
        {
            Socialiter::ignoreMigrations();
        }
    }
    ```

    And then publish the migration files and manipulate them as needed:

    ```sh
    php artisan vendor:publish --provider="GeneaLabs\LaravelSocialiter\Providers\ServiceProvider" --tag=migrations
    ```

3. Update the user model:

    ```
    use GeneaLabs\LaravelSocialiter\Traits\SocialCredentials;
    
    class User extends Authenticatable {
    
        use SocialCredentials;
    
        ...
    }
    ```

<a name="Implementation"></a>

## Implementation

The following is an example controller implementation using the "Sign in with
Apple" driver:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GeneaLabs\LaravelSocialiter\Socialiter;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class SignInWithAppleController extends Controller
{
    public function redirectToProvider() : RedirectResponse
    {
        // use Socialite, as before
        return Socialite::driver("sign-in-with-apple")
            ->scopes(["name", "email"])
            ->redirect();
    }

    public function handleProviderCallback()
    {
        // but handle the callback using Socialiter
        $user = (new Socialiter)
            ->driver("sign-in-with-apple")
            ->login();

        // or you can use the facade:
        $user = Socialiter::driver("sign-in-with-apple")
            ->login();

        // or you can use the app binding:
        $user = app("socialiter")
            ->driver("sign-in-with-apple")
            ->login();
    }
}
```

---

## Commitment to Quality

During package development I try as best as possible to embrace good design and development practices, to help ensure that this package is as good as it can
be. My checklist for package development includes:

-   ✅ Achieve as close to 100% code coverage as possible using unit tests.
-   ✅ Eliminate any issues identified by SensioLabs Insight and Scrutinizer.
-   ✅ Be fully PSR1, PSR2, and PSR4 compliant.
-   ✅ Include comprehensive documentation in README.md.
-   ✅ Provide an up-to-date CHANGELOG.md which adheres to the format outlined
    at <http://keepachangelog.com>.
-   ✅ Have no PHPMD or PHPCS warnings throughout all code.

## Contributing

Please observe and respect all aspects of the included [Code of Conduct](https://github.com/GeneaLabs/laravel-sign-in-with-apple/blob/master/CODE_OF_CONDUCT.md).

### Reporting Issues

When reporting issues, please fill out the included template as completely as
possible. Incomplete issues may be ignored or closed if there is not enough
information included to be actionable.

### Submitting Pull Requests

Please review the [Contribution Guidelines](https://github.com/GeneaLabs/laravel-sign-in-with-apple/blob/master/CONTRIBUTING.md). Only PRs that meet all criterium will be accepted.

## If you ❤️ open-source software, give the repos you use a ⭐️.

We have included the awesome `symfony/thanks` composer package as a dev dependency. Let your OS package maintainers know you appreciate them by starring the packages you use. Simply run `composer thanks` after installing this package. (And not to worry, since it's a dev-dependency it won't be installed in your live environment.)
