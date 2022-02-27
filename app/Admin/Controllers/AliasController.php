<?php

namespace App\Admin\Controllers;

use App\Models\Alias;
use App\Models\Member;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class AliasController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Alias::with(['member']), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->column('member.nickname', __('昵称'));
            $grid->column('member.name', __('游戏ID'));
            $grid->column('name');
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Alias(), function (Show $show) {
            // $show->field('id');
            $show->field('member.nickname');
            $show->field('member.name');
            $show->field('name');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(Alias::with(['member']), function (Form $form) {
            #$form->display('id');
            $form->select('member_id')->options(Member::all()->pluck('name', 'id'))->required();
            $form->text('name')->required();
        
            // $form->display('created_at');
            // $form->display('updated_at');
        });
    }
}
