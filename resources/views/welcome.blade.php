@extends('layouts.app')

@section('title', 'Dashboard')

@section('header')
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center">
            <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
            </svg>
        </div>
        <div>
            <h1 class="text-2xl font-bold text-white">Control Panel</h1>
            <p class="text-slate-400 text-sm mt-0.5">Manage your Property Management System</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- API Documentation --}}
        <a href="{{ env('APP_URL') }}/api/documentation#/" target="_blank"
           class="group relative flex items-start gap-4 p-5 rounded-xl bg-slate-900 border border-slate-800
                  hover:border-indigo-500/50 hover:bg-slate-800/80 transition-all duration-200 cursor-pointer">
            <div class="w-10 h-10 rounded-lg bg-indigo-600/20 border border-indigo-500/30 flex items-center justify-center shrink-0 group-hover:bg-indigo-600/30 transition-colors">
                <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-white group-hover:text-indigo-300 transition-colors">API Documentation</h3>
                <p class="text-slate-500 text-xs mt-0.5">View Swagger API docs</p>
            </div>
            <svg class="w-4 h-4 text-slate-600 group-hover:text-indigo-400 group-hover:translate-x-0.5 transition-all mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
            </svg>
        </a>

        {{-- Swagger Refresh --}}
        <a href="{{ env('APP_URL') }}/swagger-refresh"
           class="group relative flex items-start gap-4 p-5 rounded-xl bg-slate-900 border border-slate-800
                  hover:border-sky-500/50 hover:bg-slate-800/80 transition-all duration-200 cursor-pointer">
            <div class="w-10 h-10 rounded-lg bg-sky-600/20 border border-sky-500/30 flex items-center justify-center shrink-0 group-hover:bg-sky-600/30 transition-colors">
                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-white group-hover:text-sky-300 transition-colors">Swagger Refresh</h3>
                <p class="text-slate-500 text-xs mt-0.5">Regenerate API documentation</p>
            </div>
            <svg class="w-4 h-4 text-slate-600 group-hover:text-sky-400 group-hover:translate-x-0.5 transition-all mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>

        {{-- Migrate --}}
        <a href="{{ env('APP_URL') }}/migrate"
           class="group relative flex items-start gap-4 p-5 rounded-xl bg-slate-900 border border-slate-800
                  hover:border-amber-500/50 hover:bg-slate-800/80 transition-all duration-200 cursor-pointer">
            <div class="w-10 h-10 rounded-lg bg-amber-600/20 border border-amber-500/30 flex items-center justify-center shrink-0 group-hover:bg-amber-600/30 transition-colors">
                <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-white group-hover:text-amber-300 transition-colors">Run Migration</h3>
                <p class="text-slate-500 text-xs mt-0.5">Execute database migrations</p>
            </div>
            <svg class="w-4 h-4 text-slate-600 group-hover:text-amber-400 group-hover:translate-x-0.5 transition-all mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>

        {{-- Role Refresh --}}
        <a href="{{ env('APP_URL') }}/roleRefresh"
           class="group relative flex items-start gap-4 p-5 rounded-xl bg-slate-900 border border-slate-800
                  hover:border-rose-500/50 hover:bg-slate-800/80 transition-all duration-200 cursor-pointer">
            <div class="w-10 h-10 rounded-lg bg-rose-600/20 border border-rose-500/30 flex items-center justify-center shrink-0 group-hover:bg-rose-600/30 transition-colors">
                <svg class="w-5 h-5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-white group-hover:text-rose-300 transition-colors">Role Refresh</h3>
                <p class="text-slate-500 text-xs mt-0.5">Sync roles and permissions</p>
            </div>
            <svg class="w-4 h-4 text-slate-600 group-hover:text-rose-400 group-hover:translate-x-0.5 transition-all mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>

        {{-- Activity Logs --}}
        <a href="{{ env('APP_URL') }}/error-log"
           class="group relative flex items-start gap-4 p-5 rounded-xl bg-slate-900 border border-slate-800
                  hover:border-emerald-500/50 hover:bg-slate-800/80 transition-all duration-200 cursor-pointer">
            <div class="w-10 h-10 rounded-lg bg-emerald-600/20 border border-emerald-500/30 flex items-center justify-center shrink-0 group-hover:bg-emerald-600/30 transition-colors">
                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-sm font-semibold text-white group-hover:text-emerald-300 transition-colors">Activity Logs</h3>
                <p class="text-slate-500 text-xs mt-0.5">View system error & activity logs</p>
            </div>
            <svg class="w-4 h-4 text-slate-600 group-hover:text-emerald-400 group-hover:translate-x-0.5 transition-all mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </a>

    </div>
@endsection
