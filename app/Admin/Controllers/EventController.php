<?php

namespace App\Admin\Controllers;

use App\Admin\Actions\Event\UploadScreenShot;
use App\Admin\Renderable\AccountTable;
use App\Admin\Utils\ContextMenuWash;
use App\Models\Account;
use App\Models\Event;
use App\Models\OCRResult;
use App\Models\Player;
use App\Models\Scoring;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Modal;

class EventController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        ContextMenuWash::wash();

        return Grid::make(Event::with(['scoring', 'accounts']), function (Grid $grid) {            
            // 默认按活动时间逆序排列
            $grid->model()->orderBy('time', 'desc');
            
            // 工具栏
            $grid->tools(function (Grid\Tools $tools) {
                // 弹窗新增
                $tools->append(function () {
                    Form::dialog('创建活动记录')
                        ->click('.create-form')     // 绑定点击按钮
                        ->url('events/create')      // 表单页面链接，此参数会被按钮中的 “data-url” 属性替换
                        ->width('50%')
                        ->success('Dcat.reload()'); // 新增成功后刷新页面
                    return '
                        <button class="btn btn-primary btn-mini btn-outline">
                            <i class="feather icon-plus-square"></i>
                            <span class="create-form">在弹窗中新增</span>
                        </button>
                    ';
                });
                
                // OCR
                $tools->append(function () {
                    return Modal::make()
                        ->xl()
                        ->title('使用截图创建')
                        ->body(UploadScreenShot::make())
                        ->button('
                            <button class="btn btn-primary btn-mini btn-outline">
                                <i class="feather icon-scissors"></i> <span>使用截图创建</span>
                            </button>
                        ');
                });
            });

            // 行操作
            $grid->actions(function (Grid\Displayers\Actions $actions) {
                // 复制按钮
                $actions->append('<a href="' . admin_url('events/create?template=') . $this->getKey() . '"><i class="feather icon-copy"></i> 复制</a>');
            });
            
            $grid->column('time')->sortable()->display(function ($time) { return date("Y-m-d H:i", strtotime($time)); });
            $grid->column('scoring.name')->width('10%');
            $grid->column('point');
            $grid->column('accounts')->display(function ($accounts) {
                $names = array();
                foreach ($accounts as $account) {
                    if ($account->trashed()) { $names[] = '<span class="badge" style="background:#cccccc">'. $account->name .' </span>'; }
                    else { $names[] = '<span class="badge" style="background:#586cb1">'. $account->name .' </span>'; }
                }
                return implode(' ', $names);
            });
            $grid->column('comment')->width('15%');
            // TODO 从Audit表中读取创建数据的管理员
            $grid->column('updated_at')->sortable();
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
        $model = Event::with(['scoring', 'accounts']);
        return Show::make($id, $model, function (Show $show) {
            $show->field('time')->as(function ($time) {
                return date("Y-m-d H:i", strtotime($time));
            });
            $show->field('scoring.name');
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
        return Form::make(Event::with(['scoring', 'accounts', 'players']), function (Form $form) {
            // 获取要复制的行的ID
            $template = Event::find(request('template'));
            // 获取OCR的ID
            $ocr = OCRResult::find(request('ocr'));

            $form->datetime('time')->format('YYYY-MM-DD HH:mm')
                ->default(date("Y-m-d H:i:s"))->required();
            // 同时取name和point
            $form->select('scoring_id')->options(Scoring::all()->pluck('name', 'id'))
                ->required()
                ->default($template ? $template->scoring_id : null);
            $form->radio('custom_point')
                ->when(1, function (Form $form) { $form->number('point'); })
                ->options([ 0 => '否', 1 => '是' ])
                ->default(0, true);
            
            if ($form->isCreating()) {
                $form->multipleSelectTable('accounts')
                    ->dialogWidth('75%')
                    ->from(AccountTable::make())
                    ->model(Account::class, 'id', 'name')
                    ->required()
                    ->customFormat(function ($v) {
                        if (!$v) return [];
                        // 这一步非常重要，需要把数据库中查出来的二维数组转化成一维数组
                        return array_column($v, 'id');
                    })
                    // FIX
                    ->default(function () use ($ocr, $template){
                        if ($ocr) {
                            return $ocr->res;
                        } elseif ($template) {
                            return array_column($template->accounts->toarray(), 'id');
                        }
                    });
                $form->hidden('players');
            } elseif ($form->isEditing()) {
                // 编辑时不允许修改参与账号
                $form->display('accounts')->with(function ($accounts) {
                    $names = array();
                    foreach ($accounts as $account) {
                        if ($account['deleted_at']) { $names[] = '<span class="badge" style="background:#cccccc">' . $account['name'] . ' </span>'; }
                        else { $names[] = '<span class="badge" style="background:#586cb1">' . $account['name'] . ' </span>'; }
                    }
                    return implode(' ', $names);
                });
            }

            $form->text('comment');

            $point_adjustment = 0;
        
            $form->saving(function (Form $form) use (&$point_adjustment) {
                // 提交时填充point字段
                if (!$form->custom_point) { $form->point = Scoring::find($form->scoring_id)->point; }

                if ($form->isCreating()) {
                    // 绑定Event与Player
                    foreach (explode(',', $form->accounts) as $account_id) {
                        $players_id[] = Account::find($account_id)->player->id;
                    }
                    $form->players = $players_id;
                } elseif ($form->isEditing()) {
                    $point_adjustment = $form->point - $form->model()->point;
                }

                // 不提交自定义分值的开关
                $form->deleteInput('custom_point');
            });

            $form->saved(function (Form $form) use (&$point_adjustment) {
                // 获取Model
                $event = Event::find($form->getKey());

                // 变更对应玩家的DKP
                if ($form->isCreating()) {
                    foreach ($event->players as $player) {
                        $player->dkp += $event->point;
                        $player->dkp_comment = '系统自动调整-活动记录新增';
                        $player->save();
                    }
                } elseif ($form->isEditing()) {
                    foreach ($event->players as $player) {
                        $player->dkp += $point_adjustment;
                        $player->dkp_comment = '系统自动调整-活动记录修改';
                        $player->save();
                    }
                }
            });

            $form->deleted(function (Form $form, $result) {
                if ($result) {
                    // TODO 假Model
                    foreach ($form->model()->toArray() as $event) {
                        foreach (array_column($event['players'], 'id') as $player_id) {
                            // 回收站里的也别想跑
                            $player = Player::withTrashed()->find($player_id);
                            $player->dkp -= $event['point'];
                            $player->dkp_comment = '系统自动调整-活动记录删除';
                            $player->save();
                        }
                    }
                }
            });
        });
    }
}
