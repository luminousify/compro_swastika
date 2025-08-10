<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use Tests\TestCase;

class UserRoleEnumTest extends TestCase
{
    public function test_user_role_has_correct_values(): void
    {
        $this->assertEquals('admin', UserRole::ADMIN->value);
        $this->assertEquals('sales', UserRole::SALES->value);
    }

    public function test_user_role_labels(): void
    {
        $this->assertEquals('Admin', UserRole::ADMIN->label());
        $this->assertEquals('Sales', UserRole::SALES->label());
    }

    public function test_admin_can_access_settings(): void
    {
        $this->assertTrue(UserRole::ADMIN->canAccessSettings());
        $this->assertFalse(UserRole::SALES->canAccessSettings());
    }

    public function test_admin_can_manage_users(): void
    {
        $this->assertTrue(UserRole::ADMIN->canManageUsers());
        $this->assertFalse(UserRole::SALES->canManageUsers());
    }

    public function test_enum_can_be_created_from_string(): void
    {
        $adminRole = UserRole::from('admin');
        $salesRole = UserRole::from('sales');
        
        $this->assertEquals(UserRole::ADMIN, $adminRole);
        $this->assertEquals(UserRole::SALES, $salesRole);
    }

    public function test_enum_cases_returns_all_values(): void
    {
        $cases = UserRole::cases();
        
        $this->assertCount(2, $cases);
        $this->assertContains(UserRole::ADMIN, $cases);
        $this->assertContains(UserRole::SALES, $cases);
    }
}