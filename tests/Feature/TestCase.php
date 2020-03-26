<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function loginWithPermission()
    {
        $user = factory(User::class)->create();
        $this->apiLoginUsing($user);
        return $user;
    }

    abstract protected function validParams($overrides = []);
}
