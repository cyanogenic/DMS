<?php

namespace Database\Seeders;

use Dcat\Admin\Models\Menu;
use Dcat\Admin\Models\Permission;
use Dcat\Admin\Models\Role;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Menu Seeder
         */
        // Player
        Menu::create([
            'parent_id' => 0,
            'order'     => 0,
            'title'     => 'Players',
            'icon'      => '',
            'uri'       => 'players',
            'extension' => '',
            'show'      => '1',
        ]);
        // Account
        Menu::create([
            'parent_id' => 0,
            'order'     => 0,
            'title'     => 'Accounts',
            'icon'      => '',
            'uri'       => 'accounts',
            'extension' => '',
            'show'      => '1',
        ]);
        // Scoring
        Menu::create([
            'parent_id' => 0,
            'order'     => 0,
            'title'     => 'Scorings',
            'icon'      => 'fa-list-ul',
            'uri'       => 'scorings',
            'extension' => '',
            'show'      => '1',
        ]);
        // Event
        Menu::create([
            'parent_id' => 0,
            'order'     => 0,
            'title'     => 'Events',
            'icon'      => 'fa-gamepad',
            'uri'       => 'events',
            'extension' => '',
            'show'      => '1',
        ]);

        /**
         * Permission Seeder
         */
        // DKP-Management
        $dkp_management = Permission::create([
            'name'          => 'DKP管理',
            'slug'          => 'dkp-management',
        ]);

        // Player
        Permission::create([
            'parent_id'     => $dkp_management->id,
            'name'          => '玩家管理',
            'slug'          => 'players',
            'http_path'     => '/players*',
        ]);

        // Account
        Permission::create([
            'parent_id'     => $dkp_management->id,
            'name'          => '游戏账号管理',
            'slug'          => 'accounts',
            'http_path'     => '/accounts*',
        ]);

        // Scoring
        Permission::create([
            'parent_id'     => $dkp_management->id,
            'name'          => '计分项管理',
            'slug'          => 'scorings',
            'http_path'     => '/scorings*',
        ]);

        // Event
        Permission::create([
            'parent_id'     => $dkp_management->id,
            'name'          => '活动记录管理',
            'slug'          => 'events',
            'http_path'     => '/events*',
        ]);

        /**
         * Role Seeder
         */
        // DKP Manager
        Role::create([
            'name'  => 'DKP管理员',
            'slug'  => 'dkp-manager',
        ]);
    }
}
