<?php

namespace App\Admin\Renderable;

use App\Models\Member;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\LazyRenderable;

class MemberTable extends LazyRenderable
{
    public function grid(): Grid
    {
        return Grid::make(new Member(), function (Grid $grid) {
            // $grid->column('id', 'ID')->sortable();
            $grid->column('name');
            $grid->column('nickname');
            // $grid->column('created_at');
            $grid->column('updated_at');

            $grid->quickSearch(['name', 'nickname']);

            $grid->paginate(10);
            $grid->perPages([10, 20, 50, 100, 200]);
            $grid->disableActions();

            // $grid->filter(function (Grid\Filter $filter) {
            //     $filter->like('name')->width(4);
            //     $filter->like('nickname')->width(4);
            // });
        });
    }
}