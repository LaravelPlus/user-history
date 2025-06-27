@extends('user-history::layouts.app')

@section('content')
<div id="statsPage" v-cloak>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Activity Statistics</h1>
            <p class="mt-2 text-gray-600">Comprehensive analytics and insights</p>
        </div>

        <!-- Stats Overview -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Activities</dt>
                                <dd class="text-lg font-medium text-gray-900">@{{ formatNumber(stats.total_activities) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Unique Users</dt>
                                <dd class="text-lg font-medium text-gray-900">@{{ formatNumber(stats.unique_users) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Today</dt>
                                <dd class="text-lg font-medium text-gray-900">@{{ formatNumber(stats.today_activities) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">This Week</dt>
                                <dd class="text-lg font-medium text-gray-900">@{{ formatNumber(stats.this_week_activities) }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Activities by Date -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Activities by Date (Last 30 Days)</h3>
                <div class="space-y-3">
                    <div v-for="day in activitiesByDate" :key="day.date" class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">@{{ formatDay(day.date) }}</span>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="h-2 bg-blue-600 rounded-full" 
                                     :style="{ width: (day.count / Math.max(...activitiesByDate.map(d => d.count))) * 100 + '%' }"></div>
                            </div>
                            <span class="text-sm text-gray-500 w-8 text-right">@{{ day.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activities by Action Type -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Activities by Type</h3>
                <div class="space-y-3">
                    <div v-for="action in activitiesByAction" :key="action.action" class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-3" :class="getActionColor(action.action)"></div>
                            <span class="text-sm font-medium text-gray-900">@{{ action.action }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="h-2 rounded-full" 
                                     :class="getActionColor(action.action)"
                                     :style="{ width: (action.count / Math.max(...activitiesByAction.map(a => a.count))) * 100 + '%' }"></div>
                            </div>
                            <span class="text-sm text-gray-500 w-12 text-right">@{{ action.count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Users Section -->
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Most Active Users</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div v-for="user in topUsers" :key="user.user_id" 
                     @click="viewUserProfile(user.user_id)"
                     class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors duration-150">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900">@{{ user.user_name || 'Unknown User' }}</p>
                            <p class="text-xs text-blue-600">@{{ user.count }} activities</p>
                        </div>
                        <div class="text-right">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            stats: @json($stats ?? []),
            activitiesByDate: @json($activitiesByDate ?? []),
            activitiesByAction: @json($activitiesByAction ?? []),
            topUsers: @json($topUsers ?? [])
        }
    },
    methods: {
        formatNumber(num) {
            return new Intl.NumberFormat().format(num || 0);
        },
        
        formatDay(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        },
        
        viewUserProfile(userId) {
            window.location.href = `{{ route('user-history.users') }}/${userId}`;
        },
        
        getActionColor(action) {
            const colors = {
                'created': 'bg-green-500',
                'updated': 'bg-blue-500',
                'deleted': 'bg-red-500',
                'login': 'bg-purple-500',
                'logout': 'bg-gray-500'
            };
            return colors[action] || 'bg-gray-500';
        }
    }
}).mount('#statsPage');
</script>
@endsection 