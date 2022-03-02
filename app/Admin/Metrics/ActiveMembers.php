<?php

namespace App\Admin\Metrics;

use Carbon\Carbon;
use Dcat\Admin\Widgets\Metrics\Line;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActiveMembers extends Line
{
    /**
     * 初始化卡片内容
     *
     * @return void
     */
    protected function init()
    {
        parent::init();

        $this->title('活跃玩家数');
        $this->dropdown([
            '7' => '7天内',
            '30' => '30天内',
        ]);
    }

    public function activeMember($days, $rate)
    {
        $start = Carbon::today()->subDay($days * ($rate + 1))->toDateTimeString();
        $end = Carbon::today()->subDay($days * $rate -1)->toDateTimeString();

        return DB::table('event_member')
            ->join('events', 'events.id', '=', 'event_member.event_id')
            ->whereBetween('events.time', [$start, $end])
            ->select('member_id')->distinct()->get()->count();
    }

    /**
     * 处理请求
     *
     * @param Request $request
     *
     * @return mixed|void
     */
    public function handle(Request $request)
    {

        $members = array();
        for ($i=0; $i < 7; $i++) { 
            $members[] = $this->activeMember($request->get('option') ?? 7, $i);
        }

        $this->withContent($members[0]);
        // 图表数据
        $this->withChart(array_reverse($members));
    }

    /**
     * 设置图表数据.
     *
     * @param array $data
     *
     * @return $this
     */
    public function withChart(array $data)
    {
        return $this->chart([
            'series' => [
                [
                    'name' => $this->title,
                    'data' => $data,
                ],
            ],
        ]);
    }

    /**
     * 设置卡片内容.
     *
     * @param string $content
     *
     * @return $this
     */
    public function withContent($content)
    {
        return $this->content(
            <<<HTML
<div class="d-flex justify-content-between align-items-center mt-1" style="margin-bottom: 2px">
    <h2 class="ml-1 font-lg-1">{$content}</h2>
    <span class="mb-0 mr-1 text-80">{$this->title}</span>
</div>
HTML
        );
    }
}
