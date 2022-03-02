<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\AliasTable;
use App\Models\Member;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Illuminate\Support\Facades\DB;

class MemberController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Member::with(['alias']), function (Grid $grid) {
            // $grid->column('id')->sortable();
            $grid->nickname();
            $grid->name();
            $grid->alias()
                ->display('查看')
                ->modal(function ($modal){
                    $modal->title($this->name . '的曾用名');
                    return AliasTable::make();
            });
            $grid->dkp();
            $grid->created_at();
            $grid->updated_at()->sortable();
        
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
        return Show::make($id, new Member(), function (Show $show) {
            // $show->field('id');
            $show->field('nickname');
            $show->field('name');
            $show->field('dkp');
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
        return Form::make(new Member(), function (Form $form) {
            // $form->display('id');
            $form->text('nickname')->required();
            $form->text('name')->required();
            $form->number('dkp');
        
            // $form->display('created_at');
            // $form->display('updated_at');
            
            $form->saving(function (Form $form) {
                // 判断是否是新增操作
                if ($form->isEditing()) {
                    $id = $form->getKey();
                    $timestamp = date("Y-m-d H:i:s");
                    //取当前名称插入到曾用名中
                    $current_name = DB::table('members')->where('id', $id)->value('name');
                        DB::table('alias')->insert([
                        'member_id' => $id,
                        'name' => $current_name,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
                }
            });
        });
    }
}
