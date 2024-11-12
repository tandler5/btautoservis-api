<?php

declare(strict_types=1);

namespace App\GraphQL\Mutations;

use App\Models\Customer;
use App\Models\LinkedSocialAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use MikeMcLin\WpPassword\Facades\WpPassword;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as ProviderUser;

final readonly class Login
{
    /** @param  array{}  $args */
    public function __invoke(null $_, array $args)
    {

        try {
            $accessToken = $args['token'];
            $provider = $args['provider'];
            $providerUser = Socialite::driver($provider)->userFromToken($accessToken);

        } catch (Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ]);
        }

        if (filled($providerUser)) {
            $user = $this->findOrCreate($providerUser, $provider);
        } else {
            $user = $providerUser;
        }
        auth()->login($user);
        if (auth()->check()) {
            return [
               'token' => auth()->user()->createToken('login-token')->plainTextToken,
               'user' => $user,
            ];
        } else {
            return $this->error(
                message: 'Failed to Login try again',
                code: 401
            );
        }
    }

    protected function findOrCreate(ProviderUser $providerUser, string $provider): Customer
    {
        $linkedSocialAccount = LinkedSocialAccount::query()->where('provider_name', $provider)
            ->where('provider_id', $providerUser->getId())
            ->first();

        if ($linkedSocialAccount) {
            return $linkedSocialAccount->customer;
        } else {
            $user = null;

            if ($email = $providerUser->getEmail()) {
                $user = Customer::query()->where('email', $email)->first();
            }

            if (!$user) {
                $user = Customer::query()->create([
                    'name' => $providerUser->getName(),
                    'email' => $providerUser->getEmail(),
                ]);
                $user->markEmailAsVerified();
            }

            $user->linkedSocialAccounts()->create([
                'provider_id' => $providerUser->getId(),
                'provider_name' => $provider,
            ]);

            return $user;


        }
    }
}
