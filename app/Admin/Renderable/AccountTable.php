<?php

namespace App\Admin\Renderable;

use App\Models\Account;
use Dcat\Admin\Admin;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;

class AccountTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(Account::with(['player', 'former_names']), function (Grid $grid) {
            Admin::translation('account');
            
            $grid->quickSearch('name', 'player.name', 'former_names.name');

            $grid->column('name');
            $grid->column('player.name');
            $grid->column('former_names')->pluck('name')->badge();
            $grid->column('updated_at');

            $grid->paginate(15);
            $grid->disableActions();
        });
    }
}