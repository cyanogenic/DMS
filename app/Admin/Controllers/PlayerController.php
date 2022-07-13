<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\BatchRestore;
use App\Admin\Actions\Grid\Restore;
use App\Admin\Utils\ContextMenuWash;
use App\Models\Player;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class PlayerController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        ContextMenuWash::wash();

        return Grid::make(Player::with(['accounts']), function (Grid $grid) {
            // 默认按照DKP逆序排列
            $grid->model()->orderBy('dkp', 'desc');

            // 回收站
            if (request('_scope_') == 'trashed') { $grid->model()->onlyTrashed(); }

            // 按玩家名/游戏账号名/游戏曾用名查询
            $grid->quickSearch('name', 'accounts.name', 'accounts.former_names.name');

            // 工具栏
            $grid->tools(function (Grid\Tools $tools) {
                // 回收站按钮
                $tools->append(function () {
                    $href = request('_scope_') ? admin_url('players') : admin_url('players?_scope_=trashed');
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

            // 行操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 回收站还原
                if (request('_scope_') == 'trashed') { $actions->append(new Restore(Player::class)); }
            });

            // 批量操作
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                // 回收站批量还原
                if (request('_scope_') == 'trashed') { $batch->add(new BatchRestore(Player::class)); }
            });

            // 回收站内的数据不允许编辑
            if (request('_scope_') == 'trashed') { $grid->disableEditButton(); }

            $grid->column('name');
            if (request('_scope_') != 'trashed') {
                $grid->column('accounts')->display(function ($accounts) {
                    $names = array();
                    foreach ($accounts as $account) {
                        if ($account->trashed()) { $names[] = '<span class="badge" style="background:#cccccc">'. $account->name .' </span>'; }
                        else { $names[] = '<span class="badge" style="background:#586cb1">'. $account->name .' </span>'; }
                    }
                    return implode(' ', $names);
                });
            }
            $grid->column('dkp')->sortable();
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();
            if (request('_scope_') == 'trashed') { $grid->deleted_at()->sortable(); }
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
        return Show::make($id, Player::with(['accounts']), function (Show $show) {
            $show->field('name');
            $show->field('accounts')->pluck('name')->badge();
            $show->field('dkp');

            $show->field('created_at');
            $show->field('updated_at');
            // 回收站内的数据不允许编辑
            if ($show->model()->trashed()) {
                $show->field('deleted_at');
                $show->disableEditButton();
            }
            
            $show->relation('event', function ($event) {
                $a = 0;
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
        return Form::make(new Player(), function (Form $form) {
            $id = $form->getKey();

            $form->text('name')
                ->rules("unique:players,name,$id")
                ->required();

            if ($form->isEditing()) {
                $form->hidden('dkp');
                $form->hidden('dkp_comment');
                $form->radio('dkp_action')
                    ->when('>', 0, function (Form $form) { 
                        $form->number('dkp_action_point')->default(1, true)
                            ->rules('exclude_if:dkp_action,false|integer|gte:1')
                            ->setLabelClass(['asterisk']);
                        $form->text('dkp_comment_temp', admin_trans('player.fields.dkp_comment'))
                            ->rules('required_if:dkp_action,1,2', ['required_if' => '修改DKP时必须填写修改说明'])
                            ->setLabelClass(['asterisk']);
                    })
                    ->options([
                        0   => admin_trans('player.options.dkp_action.none'),
                        1   => admin_trans('player.options.dkp_action.plus'),
                        2   => admin_trans('player.options.dkp_action.minus'),
                    ])
                    ->help('当前' . ($form->model()->name) . '的DKP: ' . $form->model()->dkp)
                    ->default('0', true)->required();
            }

            $form->saving(function (Form $form) {
                if ($form->isEditing()) {
                    if ($form->dkp_action) {
                        if ($form->dkp_action == 1) {
                            // 加分
                            $form->dkp = $form->model()->dkp + $form->dkp_action_point;
                        } else {
                            // 减分
                            if ($form->dkp_action_point > $form->model()->dkp) {
                                // 防越界
                                return $form->response()->error('扣除的DKP分数大于' . $form->name . '当前的DKP');
                            }
                            $form->dkp = $form->model()->dkp - $form->dkp_action_point;
                        }
                        // 修改说明
                        $form->dkp_comment = $form->dkp_comment_temp;
                    }
                }
                // 释放临时字段
                $form->deleteInput(['dkp_action', 'dkp_action_point', 'dkp_comment_temp']);
            });

            $form->deleting(function (Form $form) {
                foreach (explode(',', $form->getKey()) as $player_id) {
                    $player = Player::withTrashed()->with('accounts')->find($player_id);
                    // 软删除的玩家不判断名下是否有账号
                    if (! $player->trashed()) {
                        if ($player->accounts->count()) {
                            return $form->response()->warning('玩家 ' . $player['name'] . ' 名下有绑定的账号,暂不允许删除')->refresh();
                        }
                    }
                }
            });
        });
    }
}
