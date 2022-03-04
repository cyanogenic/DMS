<?php

namespace App\Admin\Renderable;

use App\Models\Member;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;

class MemberTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(Member::with(['alias']), function (Grid $grid) {
            $grid->quickSearch('name', 'nickname', 'alias.name');

            $grid->column('name');
            $grid->column('nickname');
            // $grid->column('created_at');
            $grid->column('updated_at');

            $grid->paginate(10);
            $grid->perPages([10, 20, 50, 100, 200]);
            $grid->disableActions();
        });
    }
}