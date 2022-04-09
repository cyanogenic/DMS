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
            $grid->column('alias')->pluck('name')->badge();
            $grid->column('updated_at');

            $grid->paginate(15);
            $grid->disableActions();
        });
    }
}