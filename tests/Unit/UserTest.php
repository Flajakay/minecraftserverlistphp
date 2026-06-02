<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    /**
     * Test that the default admin user seeded by schema.sql is retrievable.
     */
    public function testCanFindSeededAdminUser()
    {
        $admin = User::find(1);
        
        $this->assertNotNull($admin);
        $this->assertEquals('admin', $admin->username);
        $this->assertEquals('admin@admin.com', $admin->email);
        $this->assertEquals(2, $admin->type); // admin type is 2
        $this->assertEquals(1, $admin->active);
    }

    /**
     * Test creating a new user and finding them by username/email.
     */
    public function testCanCreateAndFindUser()
    {
        $userData = [
            'username' => 'testuser',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'test@example.com',
            'name' => 'Test User',
            'type' => 0,
            'active' => 1
        ];

        $userId = User::create($userData);
        
        $this->assertGreaterThan(0, $userId);

        // Find by ID
        $user = User::find($userId);
        $this->assertNotNull($user);
        $this->assertEquals('testuser', $user->username);
        $this->assertEquals('test@example.com', $user->email);
        $this->assertEquals('Test User', $user->name);

        // Find by Username
        $userByUsername = User::findByUsername('testuser');
        $this->assertNotNull($userByUsername);
        $this->assertEquals($userId, $userByUsername->id);

        // Find by Email
        $userByEmail = User::findByEmail('test@example.com');
        $this->assertNotNull($userByEmail);
        $this->assertEquals($userId, $userByEmail->id);
    }

    /**
     * Test updating a user's details.
     */
    public function testCanUpdateUser()
    {
        $userData = [
            'username' => 'updateuser',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'update@example.com',
            'name' => 'Update User',
            'type' => 0,
            'active' => 0
        ];

        $userId = User::create($userData);

        // Verify initial active status is inactive (0)
        $this->assertFalse(User::isActive('updateuser'));

        // Update active status and name
        $updated = User::update($userId, [
            'active' => 1,
            'name' => 'Updated Name'
        ]);

        $this->assertTrue($updated->rowCount() > 0);

        // Re-fetch and assert changes
        $user = User::find($userId);
        $this->assertEquals('Updated Name', $user->name);
        $this->assertTrue(User::isActive('updateuser'));
    }

    /**
     * Test deleting a user.
     */
    public function testCanDeleteUser()
    {
        $userData = [
            'username' => 'deleteuser',
            'password' => password_hash('secret123', PASSWORD_ARGON2ID),
            'email' => 'delete@example.com',
            'name' => 'Delete User',
            'type' => 0,
            'active' => 1
        ];

        $userId = User::create($userData);
        $this->assertNotNull(User::find($userId));

        // Delete user
        User::delete($userId);

        // Assert user is gone
        $this->assertFalse(User::find($userId));
        $this->assertFalse(User::findByUsername('deleteuser'));
    }
}
