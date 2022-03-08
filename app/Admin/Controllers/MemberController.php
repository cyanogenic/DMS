<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\AliasTable;
use App\Models\Alias;
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
                'innercity' => '上次内城',
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
            $grid->innercity()->sortable();
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
            $show->field('innercity');

            $show->field('created_at');
            $show->field('updated_at');

            $show->relation('alias', function ($model) {
                $grid = new Grid(new Alias);

                $grid->model()->where('member_id', $model->id);

                $grid->name;
                $grid->updated_at;
                
                $grid->disableActions();
                $grid->disableRefreshButton();
                $grid->disableCreateButton();
                $grid->disableRowSelector();
                
                return $grid;
            });
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
            $form->text('nickname')->rules(
                function (Form $form) { if (!$id = $form->model()->id) { return 'unique:members,nickname'; } },
                [ 'unique' => '该昵称已存在' ]
            );
            $form->text('name')->required()->rules(
                function (Form $form) { if (!$id = $form->model()->id) { return 'unique:members,name'; } },
                [ 'unique' => '该ID已存在' ]
            );

            $form->number('dkp');
            $form->date('innercity');
            
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
