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
            $grid->quickSearch('name', 'nickname', 'alias.name');
            $grid->export()->titles([
                'nickname' => '昵称',
                'name' => '游戏ID',
                'dkp' => 'dkp',
            ]);

            $grid->nickname();
            $grid->name();
            $grid->alias()
                ->display('查看')
                ->modal(function ($modal){
                    $modal->title($this->name . '的曾用名');
                    return AliasTable::make();
            });
            $grid->dkp()->sortable();
            $grid->created_at()->sortable();
            $grid->updated_at()->sortable();
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
            $form->text('nickname')->required();
            $form->text('name')->required();
            $form->number('dkp');
            
            $form->saving(function (Form $form) {
                // 判断是否是修改操作
                if ($form->isEditing()) {
                    $name_coming = $form->name;
                    $id = $form->getKey();
                    $timestamp = date("Y-m-d H:i:s");
                    
                    //取当前名称
                    $name_current = DB::table('members')->where('id', $id)->value('name');
                    if ($name_coming != $name_current) {
                        DB::table('alias')->insert([
                            'member_id' => $id,
                            'name' => $name_current,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ]);
                    }
                }
            });
        });
    }
}
