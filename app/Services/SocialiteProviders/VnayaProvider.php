<?php

namespace App\Services\SocialiteProviders;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\InvalidStateException;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Illuminate\Support\Arr;

class VnayaProvider extends AbstractProvider implements ProviderInterface
{
    protected $base_uri = 'https://ops1.vnaya.com';

    /**
     * @return
     */
    public function setBaseURI($base_uri)
    {
        $this->base_uri = env('VNAYA_SERVER_URL', $base_uri);

        return $this;
    }

    /**
     * Returns
     */
    public function getBaseURI()
    {
        return $this->base_uri;
    }

    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return null;
    }

    protected function getTokenUrl()
    {
        return null;
    }

    public function user() {
        if ($this->user) {
            return $this->user;
        }

        $access_token = \Route::current()->parameter('access_token');
        $user = $this->getUserByToken($access_token);
        return $this->userInstance(['access_token' => $access_token], $user);
    }


    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()
            ->post(
                "{$this->base_uri}/en/api/get-users-detail",
                $this->getRequestOptions($token)
            );

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => Arr::get($user, 'UserID'),
            'email' => Arr::get($user, 'Email'),
            'name' => trim(Arr::get($user, 'First Name').' '.Arr::get($user, 'Last Name')),
            'first_name' => Arr::get($user, 'First Name'),
            'last_name' => Arr::get($user, 'Last Name'),
            'timezone' => Arr::get($user, 'Timezone'),
            'mobile' => Arr::get($user, 'Phone Number'),
            'source' => Arr::get($user, 'Source'),
            'student_grade' => Arr::get($user, 'Grades'),
            'school_batch' => Arr::get($user, 'Batch'),
            'gender' => Arr::get($user, 'Gender'),
            'date_of_birth' => Arr::get($user, 'Dob'),
            'parent_mobile' => Arr::get($user, 'Parent Mobile'),
            'parent_name' => Arr::get($user, 'Parent Name'),
            'profile_image' => Arr::get($user, 'Profile Image'),
            'address' => Arr::get($user, 'Address'),
        ]);
    }

    /**
     * Get the default options for an HTTP request.
     *
     * @param string $token
     * @return array
     */
    protected function getRequestOptions($token)
    {
        return [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer $token",
            ],
        ];
    }
}
