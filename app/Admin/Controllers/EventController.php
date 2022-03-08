<?php

namespace App\Admin\Controllers;

use App\Admin\Renderable\MemberTable;
use App\Models\Event;
use App\Models\Member;
use App\Models\Scoring;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;

class EventController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Event::with(['scoring', 'member']), function (Grid $grid) {
            $grid->model()->orderBy('time', 'desc');

            $grid->column('time')->sortable()->display(function ($time) {
                return date("Y-m-d H:i", strtotime($time));
            });;
            $grid->column('scoring.name', __('计分项'))->width('10%');
            $grid->column('point');
            $grid->column('member')->width('40%')->pluck('name')->badge();
            $grid->column('comment')->width('20%');
            
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
        $model = Event::with('scoring');
        return Show::make($id, $model, function (Show $show) {
            $show->field('time')->as(function ($time) {
                return date("Y-m-d H:i", strtotime($time));
            });
            $show->field('scoring.name', __('计分项'));
            $show->field('point');
            $show->field('comment');

            $show->field('created_at');
            $show->field('updated_at');

            $show->relation('member', function ($model) {
                $grid = new Grid(new Member);

                $grid->model()->join('event_member', function ($join) use ($model) {
                    $join->on('event_member.member_id', 'id')
                        ->where('event_id', '=', $model->id);
                });
            
                $grid->nickname;
                $grid->name;
                
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
        return Form::make(Event::with(['scoring', 'member']), function (Form $form) {
            $form->datetime('time')->format('YYYY-MM-DD HH:mm')
                ->default(date("Y-m-d H:i:s"))->required();
            // 同时取name和point
            $form->select('scoring_id', __('计分项'))->options(Scoring::all()->pluck('name', 'id'))->required();
            $form->radio('custom_point')
                ->when(1, function (Form $form) { $form->number('point'); })
                ->options([ 0 => '否', 1 => '是' ])
                ->default(0);

            $form->multipleSelectTable('member', __('参与玩家'))
                ->title('参与玩家')
                ->from(MemberTable::make())
                ->model(Member::class, 'id', 'name')
                ->required()
                ->customFormat(function ($v) {
                    if (!$v) return [];
                    // 这一步非常重要，需要把数据库中查出来的二维数组转化成一维数组
                    return array_column($v, 'id');
                });
            $form->text('comment');
        
            // 提交时填充point字段
            $form->saving(function (Form $form) {
                if (!$form->custom_point) {
                    $form->point = DB::table('scorings')->where('id', $form->scoring_id)->value('point');
                }
                // 不提交自定义分值的开关
                $form->deleteInput('custom_point');

                // TODO 快看,是脑瘫代码
                // 修改操作前先减
                $a = $form->getKey();
                DB::update(
                    'UPDATE members SET dkp = dkp - (SELECT point FROM `events` WHERE id = ?) WHERE id IN (SELECT member_id from event_member WHERE event_id = ?)',
                    [$form->getKey(), $form->getKey()]
                );
            });

            // result未使用
            $form->saved(function (Form $form, $result) {
                $newId = $form->getKey();
                // 新增或修改后重新加DKP
                DB::update(
                    'UPDATE members SET dkp = dkp + (SELECT point FROM `events` WHERE id = ?) WHERE id IN (SELECT member_id from event_member WHERE event_id = ?)',
                    [$newId, $newId]
                );
            });

            // 删除后重新计算DKP
            $form->deleting(function (Form $form) {
                // 获取待删除行数据，这里获取的是一个二维数组
                $data = $form->model()->toArray();
                //
                foreach ($data as $key => $value) {
                    DB::update(
                        'UPDATE members SET dkp = dkp - ? WHERE id IN (SELECT member_id from event_member WHERE event_id = ?)',
                        [$value["point"], $value["id"]]
                    );
                }
            });
        });
    }
}
