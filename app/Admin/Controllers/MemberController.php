<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\ResetMemberName;
use App\Admin\Renderable\AliasTable;
use App\Models\Alias;
use App\Models\Event;
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

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                $actions->append(new ResetMemberName());
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
            $show->field('nickname');
            $show->field('name');
            $show->field('dkp');
            $show->field('innercity');

            $show->field('created_at');
            $show->field('updated_at');

            $show->relation('events', function ($model) {
                $grid = Grid::make(Event::with(['scoring']));
            
                $grid->model()->join('event_member', function ($join) use ($model) {
                    $join->on('event_member.event_id', 'id')
                        ->where('member_id', '=', $model->id);
                });

                $grid->model()->orderBy('time', 'desc');
                $grid->column('time', __('时间'))->sortable()->display(function ($time) {
                    return date("Y-m-d H:i", strtotime($time));
                });;
                $grid->column('scoring.name', __('计分项'));
                $grid->column('point', __('分值'));
                $grid->column('comment', __('说明'));

                $grid->disableActions();
                $grid->disableRefreshButton();
                $grid->disableCreateButton();
                $grid->disableRowSelector();

                return $grid;
            });

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
            $id = $form->model()->id;
            $form->text('nickname')
                ->rules("nullable|unique:members,nickname,$id", [ 'unique' => '该昵称已存在' ]);
            $form->text('name')->required()
                // 不允许修改为其它人的ID和曾用名
                ->rules("unique:members,name,$id|unique:alias,name,$id,member_id", [ 'unique' => '该ID已存在' ]);

            $form->number('dkp');
            $form->date('innercity');
            
            $form->saving(function (Form $form) {
                // 判断是否是修改操作
                if ($form->isEditing()) {
                    $name_coming = $form->name;
                    $id = $form->getKey();
                    $timestamp = date("Y-m-d H:i:s");
                    
                    // 取当前名称
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
