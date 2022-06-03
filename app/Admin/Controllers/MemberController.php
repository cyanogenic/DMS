<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Member\BatchRestore;
use App\Admin\Actions\Member\NameReset;
use App\Admin\Actions\Member\Restore;
use App\Admin\Renderable\AliasTable;
use App\Models\Alias;
use App\Models\Event;
use App\Models\Member;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

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
            $grid->model()->orderBy('dkp', 'desc');

            // 回收站
            if (request('_scope_') == 'trashed') { $grid->model()->onlyTrashed(); }

            $grid->quickSearch('name', 'nickname', 'alias.name');

            $grid->tools(function (Grid\Tools $tools) {
                $tools->append(function () {
                    $href = request('_scope_') ? admin_url('members') : admin_url('members?_scope_=trashed');
                    $icon = request('_scope_') ? 'icon-check-circle' : 'icon-circle';
                    return '
                    <a href="' . $href . '">
                        <button class="btn btn-primary btn-mini btn-outline" style="margin-right:3px">
                            <i class="feather ' . $icon . '"></i>
                            <span class="d-none d-sm-inline">回收站</span>
                        </button>
                    </a>
                    ';
                });
            });
            
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
                    if ($this->nickname) { $modal->title($this->nickname . "的曾用名"); }
                    else { $modal->title($this->name . "的曾用名"); }
                    return AliasTable::make();
            });
            $grid->dkp()->sortable();
            $grid->innercity()->sortable();
            $grid->created_at()->sortable();
            $grid->updated_at()->sortable();
            if (request('_scope_') == 'trashed') { $grid->deleted_at()->sortable(); }

            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 改回曾用名
                $actions->append(new NameReset());
                // 回收站还原
                if (request('_scope_') == 'trashed') { $actions->append(new Restore(Member::class)); }
            });

            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                // 回收站批量还原
                if (request('_scope_') == 'trashed') { $batch->add(new BatchRestore(Member::class)); }
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
                $grid->model()->orderBy('updated_at', 'desc');

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

            if ($form->isCreating()) {
                $form->number('dkp');
            }
            else {
                $form->radio('dkp_type')
                    ->when(0, function (Form $form) {
                        $form->number('dkp');
                    })
                    ->when(1, function (Form $form) { 
                        $form->number('dkp_minus')
                            ->help(($form->model()->nickname ?? $form->model()->name) . '当前的DKP: ' . $form->model()->dkp);
                    })
                    ->options([
                        0 => __('member.fields.dkp_set'),
                        1 => __('member.fields.dkp_minus'),
                    ])
                    ->default('0', true);
            }

            $form->date('innercity');
            
            $form->saving(function (Form $form) {
                if ($form->isEditing()) {
                    // DKP扣除
                    if ($form->dkp_minus > $form->model()->dkp) {
                        // 中断后续逻辑
                        return $form->response()->error('扣除的DKP大于' . ($form->model()->nickname ?? $form->model()->name) . '当前的DKP');
                    }
                    if ($form->dkp_type) {
                        $form->dkp = $form->model()->dkp - $form->dkp_minus;
                    }
                    $form->deleteInput(['dkp_type', 'dkp_minus']);
                    // 添加曾用名
                    if ($form->name != $form->model()->name) {
                        Alias::create([
                            'member_id' => $form->getKey(),
                            'name' => $form->model()->name,
                        ]);
                    }
                }
            });
        });
    }
}
