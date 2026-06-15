@extends('layouts.app')

@section('title', 'User Activity Logs')

@section('header')
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">User Activity Logs</h1>
                <p class="text-slate-400 text-sm mt-0.5">
                    Audit trail of all user actions &mdash;
                    <span class="text-emerald-400 font-medium">{{ $activity_logs->total() }} total</span>
                </p>
            </div>
        </div>
        <a href="{{ env('APP_URL') }}" class="flex items-center gap-2 text-slate-400 hover:text-white text-sm transition-colors px-3 py-2 rounded-lg hover:bg-slate-800 border border-slate-700">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back
        </a>
    </div>
@endsection

@section('content')
    <div class="rounded-xl border border-slate-800 bg-slate-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b border-slate-800 bg-slate-800/50">
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">#ID</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">API URL</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">User ID</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Activity</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">IP Address</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Method</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($activity_logs as $activity_log)
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $activity_log->id }}</td>
                        <td class="px-4 py-3">
                            <span class="text-indigo-400 text-xs font-mono truncate max-w-[180px] block" title="{{ $activity_log->api_url }}">
                                {{ $activity_log->api_url }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-600/30 border border-indigo-500/30 flex items-center justify-center shrink-0">
                                    <span class="text-indigo-300 text-xs font-semibold">
                                        {{ strtoupper(substr($activity_log->user ?? '?', 0, 1)) }}
                                    </span>
                                </div>
                                <span class="text-slate-300 text-xs">{{ $activity_log->user }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs font-mono">{{ $activity_log->user_id }}</td>
                        <td class="px-4 py-3">
                            <span class="text-slate-300 text-xs line-clamp-2 max-w-[200px] block" title="{{ $activity_log->activity }}">
                                {{ $activity_log->activity }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs font-mono">{{ $activity_log->ip_address }}</td>
                        <td class="px-4 py-3">
                            @php
                                $method = strtoupper($activity_log->request_method ?? '');
                                $methodColor = match($method) {
                                    'GET' => 'bg-sky-500/20 text-sky-300 border-sky-500/30',
                                    'POST' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                    'PUT', 'PATCH' => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                    'DELETE' => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                    default => 'bg-slate-700 text-slate-400 border-slate-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded border text-xs font-mono font-semibold {{ $methodColor }}">
                                {{ $method ?: 'N/A' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                </div>
                                <p class="text-slate-500 text-sm">No activity logs found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activity_logs->hasPages())
        <div class="px-4 py-4 border-t border-slate-800 bg-slate-900/50">
            {{ $activity_logs->links() }}
        </div>
        @endif
    </div>
@endsection
