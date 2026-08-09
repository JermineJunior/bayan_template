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
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        Department::create(['number' => 1, 'name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['departments.view']);

        $this->get(route('departments.index'))
            ->assertOk()
            ->assertSee('قسم النقل')
            ->assertSee('إدارة النقل');
    }

    public function test_user_with_basic_data_create_can_create_a_department(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $this->actingUser(['departments.view', 'departments.create']);

        $this->post(route('departments.store'), [
            'number' => 1,
            'name' => 'قسم النقل',
            'management_id' => $management->id,
        ])->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'number' => 1,
            'name' => 'قسم النقل',
            'management_id' => $management->id,
        ]);
    }

    public function test_department_number_is_required_and_unique(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        Department::create(['number' => 1, 'name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['departments.view', 'departments.create']);

        $this->post(route('departments.store'), [
            'number' => '',
            'name' => 'قسم جديد',
            'management_id' => $management->id,
        ])->assertSessionHasErrors('number');

        $this->post(route('departments.store'), [
            'number' => 1,
            'name' => 'قسم جديد',
            'management_id' => $management->id,
        ])->assertSessionHasErrors('number');

        $this->assertSame(1, Department::count());
    }

    public function test_department_name_is_required(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $this->actingUser(['departments.view', 'departments.create']);

        $this->post(route('departments.store'), [
            'number' => 1,
            'name' => '',
            'management_id' => $management->id,
        ])->assertSessionHasErrors('name');

        $this->assertSame(0, Department::count());
    }

    public function test_department_management_is_required_and_must_exist(): void
    {
        $this->actingUser(['departments.view', 'departments.create']);

        $this->post(route('departments.store'), [
            'number' => 1,
            'name' => 'قسم النقل',
            'management_id' => 999,
        ])->assertSessionHasErrors('management_id');

        $this->assertSame(0, Department::count());
    }

    public function test_user_with_basic_data_edit_can_update_a_department(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $other = Management::create(['number' => 2, 'name' => 'إدارة الصيانة']);
        $department = Department::create(['number' => 1, 'name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['departments.view', 'departments.edit']);

        $this->put(route('departments.update', $department), [
            'number' => 1,
            'name' => 'قسم الصيانة',
            'management_id' => $other->id,
        ])->assertRedirect(route('departments.index'));

        $this->assertDatabaseHas('departments', [
            'id' => $department->id,
            'name' => 'قسم الصيانة',
            'management_id' => $other->id,
        ]);
    }

    public function test_department_number_can_stay_the_same_when_updating(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $department = Department::create(['number' => 1, 'name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['departments.view', 'departments.edit']);

        $this->put(route('departments.update', $department), [
            'number' => 1,
            'name' => 'قسم النقل',
            'management_id' => $management->id,
        ])->assertRedirect(route('departments.index'));
    }

    public function test_user_with_basic_data_delete_can_delete_a_department(): void
    {
        $management = Management::create(['number' => 1, 'name' => 'إدارة النقل']);
        $department = Department::create(['number' => 1, 'name' => 'قسم النقل', 'management_id' => $management->id]);
        $this->actingUser(['departments.view', 'departments.delete']);

        $this->delete(route('departments.destroy', $department))
            ->assertRedirect(route('departments.index'));

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
    }

    public function test_user_without_basic_data_create_cannot_store_a_department(): void
    {
        $this->actingUser(['departments.view']);

        $this->post(route('departments.store'), [
            'number' => 1,
            'name' => 'Sneaky',
            'management_id' => 1,
        ])->assertForbidden();
    }
}
