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
            // $grid->column('id')->sortable();
            $grid->column('time');
            $grid->column('scoring.name', __('计分项'));
            $grid->column('member')->pluck('name')->badge();
            $grid->column('comment');
            // $grid->column('created_at');
            $grid->column('updated_at')->sortable();
        
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
        
            });

            //TODO 想不到查看/编辑的方案，先屏蔽按钮
            $grid->disableViewButton();
            $grid->disableEditButton();
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
        return Show::make($id, new Event(), function (Show $show) {
            // $show->field('id');
            $show->field('time');
            $show->field('member');
            $show->field('comment');
            // $show->field('created_at');
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
        return Form::make(Event::with(['scoring', 'member']), function (Form $form) {
            // $form->display('id');
            $form->datetime('time')->default(date("Y-m-d H:i:s"))->required();
            $form->select('scoring_id', __('计分项'))->options(Scoring::all()->pluck('name', 'id'))->required();
            $form->multipleSelectTable('member', __('参与玩家'))
                ->title('参与玩家')
                // ->max(4)
                ->from(MemberTable::make())
                ->model(Member::class, 'id', 'name')
                ->required();
            $form->text('comment');
        
            // $form->display('created_at');
            // $form->display('updated_at');

            //修改本身还有BUG,先不考虑修改的情况
            $form->saved(function (Form $form, $result) {
                $newId = $form->getKey();
                $affected = DB::update(
                    'UPDATE members SET dkp = dkp + (SELECT point FROM scorings WHERE id IN (SELECT scoring_id FROM `events` WHERE id = ?))  WHERE id IN (SELECT member_id from event_member WHERE event_id = ?)',
                    [$newId, $newId]
                );
                if (! $affected) {
                    return $form->error('DKP计算失败');
                }
            });
            
            $form->deleting(function (Form $form) {
                // 获取待删除行数据，这里获取的是一个二维数组
                $data = $form->model()->toArray();
                foreach ($data as $key => $value) {
                    $affected = DB::update(
                        'UPDATE members SET dkp = dkp - (SELECT point FROM scorings WHERE id = ?)  WHERE id IN (SELECT member_id from event_member WHERE event_id = ?)',
                        [$value["scoring_id"], $value["id"]]
                    );
                    if (! $affected) {
                        return $form->error('DKP计算失败');
                    }
                }
            });
        });
    }
}
