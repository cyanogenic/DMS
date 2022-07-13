<?php

namespace App\Admin\Controllers;

use App\Admin\Utils\ContextMenuWash;
use App\Models\Scoring;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class ScoringController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        ContextMenuWash::wash();

        return Grid::make(new Scoring(), function (Grid $grid) {
            // TODO 删除关联了Event的Scoring时的自定义报错
            $grid->disableDeleteButton();

            $grid->column('name');
            $grid->column('point')->sortable();
            $grid->column('comment');
            $grid->column('created_at');
            $grid->column('updated_at');
        
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
        return Show::make($id, new Scoring(), function (Show $show) {
            $show->panel()->tools(function ($tools) {
                // TODO 删除关联了Event的Scoring时的自定义报错
                $tools->disableDelete();
            });

            $show->field('name');
            $show->field('point');
            $show->field('comment');
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
        return Form::make(new Scoring(), function (Form $form) {
            // TODO 删除关联了Event的Scoring时的自定义报错
            $form->disableDeleteButton();
            
            $form->text('name')->required();
            $form->number('point')->required();
            $form->text('comment');
        });
    }
}
