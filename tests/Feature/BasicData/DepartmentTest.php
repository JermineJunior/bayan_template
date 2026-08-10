<?php

namespace Tests\Feature\BasicData;

use App\Models\Department;
use App\Models\Management;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the permission catalogue and return an authenticated user with
     * the given permissions.
     *
     * @param  array<int, string>  $permissions
     */
    private function actingUser(array $permissions = []): User
    {
        $this->seed(PermissionSeeder::class);

        $user = User::factory()->create();
        $user->givePermissionTo($permissions);

        $this->actingAs($user);

        return $user;
    }

    public function test_guest_is_redirected_to_login_when_accessing_departments(): void
    {
        $this->get(route('departments.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_without_permission_cannot_access_departments(): void
    {
        $this->actingUser();

        $this->get(route('departments.index'))->assertForbidden();
    }

    public function test_user_with_basic_data_view_can_view_the_department_index(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        Department::create(['name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['departments.view']);

        $this->get(route('departments.index'))
            ->assertOk()
            ->assertSee('قسم النقل')
            ->assertSee('إدارة النقل');
    }

    public function test_user_with_basic_data_create_can_create_a_department(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        $this->actingUser(['departments.view', 'departments.create']);

        $this->post(route('departments.store'), [
            'name' => 'قسم النقل',
            'status' => 'active',
            'management_id' => $management->id,
        ])->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'name' => 'قسم النقل',
            'status' => 'active',
            'management_id' => $management->id,
        ]);
    }

    public function test_department_status_is_required_and_must_be_valid(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        $this->actingUser(['departments.view', 'departments.create']);

        $this->post(route('departments.store'), [
            'name' => 'قسم جديد',
            'status' => '',
            'management_id' => $management->id,
        ])->assertSessionHasErrors('status');

        $this->post(route('departments.store'), [
            'name' => 'قسم جديد',
            'status' => 'bogus',
            'management_id' => $management->id,
        ])->assertSessionHasErrors('status');

        $this->assertSame(0, Department::count());
    }

    public function test_department_name_is_required(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        $this->actingUser(['departments.view', 'departments.create']);

        $this->post(route('departments.store'), [
            'name' => '',
            'status' => 'active',
            'management_id' => $management->id,
        ])->assertSessionHasErrors('name');

        $this->assertSame(0, Department::count());
    }

    public function test_department_management_is_required_and_must_exist(): void
    {
        $this->actingUser(['departments.view', 'departments.create']);

        $this->post(route('departments.store'), [
            'name' => 'قسم النقل',
            'status' => 'active',
            'management_id' => 999,
        ])->assertSessionHasErrors('management_id');

        $this->assertSame(0, Department::count());
    }

    public function test_department_status_defaults_to_active(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        $department = Department::create(['name' => 'قسم النقل', 'management_id' => $management->id]);

        $this->assertDatabaseHas('departments', ['name' => 'قسم النقل', 'status' => 'active']);
        $this->assertSame('active', $department->fresh()->status);
    }

    public function test_user_with_basic_data_edit_can_update_a_department(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        $other = Management::create(['name' => 'إدارة الصيانة']);
        $department = Department::create(['name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['departments.view', 'departments.edit']);

        $this->put(route('departments.update', $department), [
            'name' => 'قسم الصيانة',
            'status' => 'inactive',
            'management_id' => $other->id,
        ])->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'قسم الصيانة',
            'status' => 'inactive',
            'management_id' => $other->id,
        ]);
    }

    public function test_user_with_basic_data_delete_can_delete_a_department(): void
    {
        $management = Management::create(['name' => 'إدارة النقل']);
        $department = Department::create(['name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['departments.view', 'departments.delete']);

        $this->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_user_without_basic_data_create_cannot_store_a_department(): void
    {
        $this->actingUser(['departments.view']);

        $this->post(route('departments.store'), [
            'name' => 'Sneaky',
            'status' => 'active',
            'management_id' => 1,
        ])->assertForbidden();
    }
}
