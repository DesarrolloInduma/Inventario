<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthSeederTest extends TestCase
{
    public function test_seed_creates_admin_with_hashed_password()
    {
        $this->seed();

        $user = User::where('email', 'admin@inventario.local')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->activo);
        $this->assertTrue(Hash::check('admin123', $user->password));
    }
}
