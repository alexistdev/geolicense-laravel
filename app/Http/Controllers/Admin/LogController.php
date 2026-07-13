<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class LogController extends Controller
{
    /**
     * System > Log System. Only the 500 most recent entries are ever shown,
     * paginated. Logs are read-only — there is intentionally no delete action.
     */
    public function index(Request $request)
    {
        $level = $request->query('level');
        $keyword = $request->query('filter');
        $levels = ['INFO', 'WARNING', 'ERROR', 'CRITICAL', 'DEBUG'];

        $base = SystemLog::query()
            ->when($level, fn ($q) => $q->where('level', $level))
            ->when($keyword, fn ($q) => $q->where(fn ($w) => $w
                ->where('description', 'like', '%'.$keyword.'%')
                ->orWhere('action', 'like', '%'.$keyword.'%')
                ->orWhere('causer', 'like', '%'.$keyword.'%')));

        // Cap the visible set to the latest 500 matching entries, then paginate.
        $recentIds = (clone $base)->latest()->limit(500)->pluck('id');

        $logs = SystemLog::query()
            ->with('user')
            ->whereIn('id', $recentIds)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.logs.index', compact('logs', 'levels', 'level', 'keyword'));
    }
}
