<?php

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Provider;
use App\Models\ProviderBarcode;
use App\Models\Scan;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogsController extends Controller
{
    protected array $knownSubjects = [
        Client::class => 'Client',
        Provider::class => 'Provider',
        ProviderBarcode::class => 'Provider Barcode',
        Zone::class => 'Zone',
        User::class => 'User',
        Scan::class => 'Scan',
    ];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', ActivityLog::class);

        $query = ActivityLog::query()->with('causer')->latest();

        if ($logName = $request->string('log_name')->toString()) {
            $query->where('log_name', $logName);
        }

        if ($subjectType = $request->string('subject_type')->toString()) {
            $query->where('subject_type', $subjectType);
        }

        if ($causer = $request->integer('causer_id')) {
            $query->where('causer_id', $causer);
        }

        if ($event = $request->string('event')->toString()) {
            $query->where('event', $event);
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->date('to'));
        }

        $logs = $query->paginate(20)->withQueryString();

        $logNames = ActivityLog::query()->distinct()->pluck('log_name');
        $causers = User::query()->orderBy('name')->get(['id', 'name']);
        $subjectTypes = collect($this->knownSubjects);

        return view('Admin.Activity.index', [
            'logs' => $logs,
            'logNames' => $logNames,
            'subjectTypes' => $subjectTypes,
            'causers' => $causers,
            'knownSubjects' => $this->knownSubjects,
            'filters' => $request->only(['log_name', 'subject_type', 'causer_id', 'event', 'from', 'to']),
        ]);
    }
}
