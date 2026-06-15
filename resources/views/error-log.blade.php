@extends('layouts.app')

@section('title', 'Error Logs')

@section('header')
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-rose-600/20 border border-rose-500/30 flex items-center justify-center">
                <svg class="w-6 h-6 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white">Error Logs</h1>
                <p class="text-slate-400 text-sm mt-0.5">
                    System API error records &mdash;
                    <span class="text-rose-400 font-medium">{{ $error_logs->total() }} total</span>
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
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Message</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Line</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">File</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">IP Address</th>
                        <th class="px-4 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Method</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse ($error_logs as $error_log)
                    <tr class="hover:bg-slate-800/40 transition-colors">
                        <td class="px-4 py-3 text-slate-500 font-mono text-xs">{{ $error_log->id }}</td>
                        <td class="px-4 py-3">
                            <span class="text-indigo-400 text-xs font-mono truncate max-w-[180px] block" title="{{ $error_log->api_url }}">
                                {{ $error_log->api_url }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-300 text-xs">{{ $error_log->user }}</td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $error_log->user_id }}</td>
                        <td class="px-4 py-3">
                            <span class="text-slate-300 text-xs line-clamp-2 max-w-[200px] block" title="{{ $error_log->message }}">
                                {{ $error_log->message }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $code = $error_log->status_code;
                                $color = match(true) {
                                    $code >= 500 => 'bg-rose-500/20 text-rose-300 border-rose-500/30',
                                    $code >= 400 => 'bg-amber-500/20 text-amber-300 border-amber-500/30',
                                    $code >= 200 => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30',
                                    default => 'bg-slate-700 text-slate-400 border-slate-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded border text-xs font-mono font-semibold {{ $color }}">
                                {{ $code }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs font-mono">{{ $error_log->line }}</td>
                        <td class="px-4 py-3">
                            <span class="text-slate-400 text-xs font-mono truncate max-w-[140px] block" title="{{ $error_log->file }}">
                                {{ $error_log->file }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs font-mono">{{ $error_log->ip_address }}</td>
                        <td class="px-4 py-3">
                            @php
                                $method = strtoupper($error_log->request_method ?? '');
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
                        <td colspan="10" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="w-12 h-12 rounded-full bg-slate-800 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <p class="text-slate-500 text-sm">No error logs found</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($error_logs->hasPages())
        <div class="px-4 py-4 border-t border-slate-800 bg-slate-900/50">
            {{ $error_logs->links() }}
        </div>
        @endif
    </div>
@endsection
