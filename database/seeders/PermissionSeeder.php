<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions and assign them to roles
        Permission::create(['name' => 'products:create', 'description' => 'Allows creating new products in the system']);
        Permission::create(['name' => 'products:update', 'description' => 'Allows updating existing product details']);
        Permission::create(['name' => 'products:view.any', 'description' => 'Allows viewing all products in the system']);
        Permission::create(['name' => 'products:delete', 'description' => 'Allows deleting products from the system']);
        Permission::create(['name' => 'products:restore', 'description' => 'Allows restoring deleted products']);

        Permission::create(['name' => 'users:view.any', 'description' => 'Allows viewing all users in the system']);
        Permission::create(['name' => 'users:view.own', 'description' => 'Allows viewing own user profile']);
        Permission::create(['name' => 'users:create', 'description' => 'Allows creating new user accounts']);
        Permission::create(['name' => 'users:update.any', 'description' => 'Allows updating details of any user']);
        Permission::create(['name' => 'users:update.own', 'description' => 'Allows updating own user profile']);
        Permission::create(['name' => 'users:delete.any', 'description' => 'Allows deleting any user account']);
        Permission::create(['name' => 'users:delete.own', 'description' => 'Allows deleting own user account']);
        Permission::create(['name' => 'users:restore', 'description' => 'Allows restoring deleted user accounts']);

        Permission::create(['name' => 'orders:view.any', 'description' => 'Allows viewing all orders in the system']);
        Permission::create(['name' => 'orders:view.own', 'description' => 'Allows viewing own orders']);
        Permission::create(['name' => 'orders:view.assigned', 'description' => 'Allows viewing orders assigned to the user']);
        Permission::create(['name' => 'orders:create', 'description' => 'Allows creating new orders']);
        Permission::create(['name' => 'orders:update.any', 'description' => 'Allows updating any order']);
        Permission::create(['name' => 'orders:update.own', 'description' => 'Allows updating own orders']);
        Permission::create(['name' => 'orders:delete.any', 'description' => 'Allows deleting any order']);
        Permission::create(['name' => 'orders:delete.own', 'description' => 'Allows deleting own orders']);
        Permission::create(['name' => 'orders:restore', 'description' => 'Allows restoring deleted orders']);

        Permission::create(['name' => 'categories:view.any', 'description' => 'Allows viewing all product categories']);
        Permission::create(['name' => 'categories:create', 'description' => 'Allows creating new product categories']);
        Permission::create(['name' => 'categories:update', 'description' => 'Allows updating existing product categories']);
        Permission::create(['name' => 'categories:delete', 'description' => 'Allows deleting product categories']);
        Permission::create(['name' => 'categories:restore', 'description' => 'Allows restoring deleted product categories']);

        Permission::create(['name' => 'roles:view', 'description' => 'Allows viewing all roles in the system']);
        Permission::create(['name' => 'roles:create', 'description' => 'Allows creating new roles']);
        Permission::create(['name' => 'roles:update', 'description' => 'Allows updating existing roles']);
        Permission::create(['name' => 'roles:delete', 'description' => 'Allows deleting roles']);

        Permission::create(['name' => 'permissions:view', 'description' => 'Allows viewing all permissions in the system']);
        Permission::create(['name' => 'permissions:create', 'description' => 'Allows creating new permissions']);
        Permission::create(['name' => 'permissions:update', 'description' => 'Allows updating existing permissions']);
        Permission::create(['name' => 'permissions:delete', 'description' => 'Allows deleting permissions']);

        $delivery_man_permissions = [
            'orders:view.assigned',
            'products:view.any',
            'users:view.any',
            'users:view.own',
            'users:update.own',
            'categories:view.any',
        ];

        $user_permissions = [
            'orders:view.own',
            'products:view.any',
            'users:view.any',
            'users:view.own',
            'users:update.own',
            'categories:view.any',
        ];

        $admin_permissions = [
            'products:create',
            'products:update',
            'products:view.any',
            'products:delete',
            'products:restore',
            'users:view.any',
            'users:view.own',
            'users:create',
            'users:update.any',
            'users:update.own',
            'users:delete.any',
            'users:delete.own',
            'users:restore',
            'orders:view.any',
            'orders:view.own',
            'orders:view.assigned',
            'orders:create',
            'orders:update.any',
            'orders:update.own',
            'orders:delete.any',
            'orders:delete.own',
            'orders:restore',
            'categories:view.any',
            'categories:create',
            'categories:update',
            'categories:delete',
            'categories:restore',
            'roles:view',
            'roles:create',
            'roles:update',
            'roles:delete',
            'permissions:view',
            'permissions:create',
            'permissions:update',
            'permissions:delete'
        ];

        // Create roles and assign existing permissions
        $admin_role = Role::findOrCreate('admin');

        $admin_role->givePermissionTo($admin_permissions);

        $userRole = Role::findOrCreate('user');
        $userRole->givePermissionTo($user_permissions);

        $delivery_role = Role::findOrCreate('delivery');
        $delivery_role->givePermissionTo($delivery_man_permissions);

        // Create admin users
        $admin_user = User::create([
            "fullname" => "John Doe",
            "email" => "holonouemmanuel0@gmail.com",
            "password" => bcrypt("password"),
            "phone" => "22996424245",
        ]);

        $admin_user->assignRole($admin_role);

        $delivery_man_user = User::create([
            "fullname" =>"John Delivery",
            "email" => "manelhln00@gmail.com",
            "password" => bcrypt("password"),
            "phone" => "1234567890",
        ]);

        $delivery_man_user->assignRole($delivery_role);

        $user = User::create([
            "fullname" =>"Jane Doe",
            "email" => "emmanuelholonou.pro@gmail.com",
            "password" => bcrypt("password"),
            "phone" => "0987654321",
        ]);

        $user->assignRole($userRole);
    }
}
