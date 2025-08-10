<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_fillable_attributes(): void
    {
        $fillable = ['name', 'email', 'password', 'role', 'force_password_change'];
        $user = new User();
        
        $this->assertEquals($fillable, $user->getFillable());
    }

    public function test_user_has_hidden_attributes(): void
    {
        $hidden = ['password', 'remember_token'];
        $user = new User();
        
        $this->assertEquals($hidden, $user->getHidden());
    }

    public function test_user_casts_role_to_enum(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        
        $this->assertInstanceOf(UserRole::class, $user->role);
        $this->assertEquals(UserRole::ADMIN, $user->role);
    }

    public function test_user_casts_force_password_change_to_boolean(): void
    {
        $user = User::factory()->create(['force_password_change' => 1]);
        
        $this->assertIsBool($user->force_password_change);
        $this->assertTrue($user->force_password_change);
    }

    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $this->assertTrue($user->isAdmin());
    }

    public function test_is_admin_returns_false_for_sales_role(): void
    {
        $user = User::factory()->create(['role' => UserRole::SALES]);
        
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_sales_returns_true_for_sales_role(): void
    {
        $user = User::factory()->create(['role' => UserRole::SALES]);
        
        $this->assertTrue($user->isSales());
    }

    public function test_is_sales_returns_false_for_admin_role(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $this->assertFalse($user->isSales());
    }

    public function test_admin_can_access_settings_and_users(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $this->assertTrue($user->canAccess('settings'));
        $this->assertTrue($user->canAccess('users'));
    }

    public function test_sales_cannot_access_settings_and_users(): void
    {
        $user = User::factory()->create(['role' => UserRole::SALES]);
        
        $this->assertFalse($user->canAccess('settings'));
        $this->assertFalse($user->canAccess('users'));
    }

    public function test_both_roles_can_access_content_modules(): void
    {
        $adminUser = User::factory()->create(['role' => UserRole::ADMIN]);
        $salesUser = User::factory()->create(['role' => UserRole::SALES]);
        
        $contentModules = [
            'divisions', 'products', 'technologies', 'machines',
            'media', 'milestones', 'clients', 'contact_messages'
        ];
        
        foreach ($contentModules as $module) {
            $this->assertTrue($adminUser->canAccess($module));
            $this->assertTrue($salesUser->canAccess($module));
        }
    }

    public function test_cannot_access_unknown_resource(): void
    {
        $user = User::factory()->create(['role' => UserRole::ADMIN]);
        
        $this->assertFalse($user->canAccess('unknown_resource'));
    }

    public function test_user_has_uploaded_media_relationship(): void
    {
        $user = User::factory()->create();
        
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->uploadedMedia());
        $this->assertEquals(Media::class, $user->uploadedMedia()->getRelated()::class);
        $this->assertEquals('uploaded_by', $user->uploadedMedia()->getForeignKeyName());
    }
}