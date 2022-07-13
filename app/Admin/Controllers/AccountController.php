<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Grid\BatchRestore;
use App\Admin\Actions\Grid\Restore;
use App\Admin\Actions\Member\NameReset;
use App\Admin\Utils\ContextMenuWash;
use App\Models\Account;
use App\Models\FormerName;
use App\Models\Player;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class AccountController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        ContextMenuWash::wash();

        return Grid::make(Account::with(['player', 'former_names']), function (Grid $grid) {
            // 回收站
            if (request('_scope_') == 'trashed') { $grid->model()->onlyTrashed(); }

            // 按玩家名/游戏账号名/游戏曾用名查询
            $grid->quickSearch('name', 'player.name', 'former_names.name');

            // 工具栏
            $grid->tools(function (Grid\Tools $tools) {
                // 回收站按钮
                $tools->append(function () {
                    $href = request('_scope_') ? admin_url('accounts') : admin_url('accounts?_scope_=trashed');
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
                // 改回曾用名
                if (request('_scope_') != 'trashed') { $actions->append(new NameReset()); }

                // 回收站还原
                if (request('_scope_') == 'trashed') { $actions->append(new Restore(Account::class)); }
            });

            // 批量操作
            $grid->batchActions(function (Grid\Tools\BatchActions $batch) {
                // 回收站批量还原
                if (request('_scope_') == 'trashed') { $batch->add(new BatchRestore(Account::class)); }
            });

            // 回收站内的数据不允许编辑
            if (request('_scope_') == 'trashed') { $grid->disableEditButton(); }

            $grid->column('name');
            $grid->column('player.name')->display(function ($name) {
                if ($this->player) {
                    if ($this->player->trashed()) {
                        return "<span style='color:gray'>$name(在回收站中)</span>";
                    } else {
                        return $name;
                    }
                }
            });
            $grid->column('former_names')->pluck('name')->badge();
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
        return Show::make($id, Account::with(['player', 'former_names']), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('player.name');
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
        return Form::make(Account::with(['player', 'former_names']), function (Form $form) {
            if ($form->isCreating()) {
                $form->text('name')->required()->rules('unique:accounts,name|unique:former_names,name');
                $form->select('player_id')->options(Player::all()->pluck('name', 'id'))->required();
            } elseif ($form->isEditing()) {
                $form->row(function (Form\Row $form) {
                    $form->width(6)->text('name')->required()->rules('unique:accounts,name,' . $form->getKey() . '|unique:former_names,name,' . $form->getKey() . ',account_id');
                    $form->width(3)->switch('set_former_name', '生成曾用名')->default(1, true);
                });
                $form->row(function (Form\Row $form) {
                    $form->width(6)->select('player_id')->options(Player::all()->pluck('name', 'id'))->required();
                });
            }

            $form->saving(function (Form $form) {
                // 生成曾用名
                if ($form->set_former_name && $form->model()->name != $form->name) {
                    FormerName::create([
                        'account_id'    => $form->getKey(),
                        'name'          => $form->model()->name,
                    ]);
                }
                $form->deleteInput(['set_former_name']);
            });
        });
    }
}
