@extends('user-history::layouts.app')

@section('content')
<div id="advancedDashboard" v-cloak>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with Real-time Indicator -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Advanced Dashboard</h1>
                <p class="text-gray-600">Live real-time user activity monitoring and analytics</p>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                    <span class="text-sm font-medium text-green-600">Live</span>
                </div>
                <div class="text-sm text-gray-500">
                    Last updated: <span class="font-medium">@{{ formatLastUpdate() }}</span>
                </div>
            </div>
        </div>

        <!-- Search Results with Advanced Display -->
        <div v-if="searchResults.length > 0" class="mb-8 fade-in">
            <div class="bg-white shadow-lg rounded-xl border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Search Results (@{{ searchResults.length }})</h3>
                        <button @click="clearSearch" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div v-for="result in searchResults" :key="result.id" 
                             class="flex items-center justify-between p-4 bg-gradient-to-r from-gray-50 to-white rounded-lg border border-gray-100 hover:shadow-md transition-all duration-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-semibold text-gray-900 cursor-pointer hover:text-blue-600 transition-colors duration-200"
                                       @click="viewUserProfile(result.user_id)">
                                        @{{ result.user_name }}
                                    </p>
                                    <p class="text-sm text-gray-600">@{{ result.description }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium shadow-sm"
                                      :class="getActionBadgeClass(result.action)">
                                    @{{ result.action }}
                                </span>
                                <p class="text-xs text-gray-400 mt-1">@{{ formatRelativeTime(result.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Stats Cards with Animations -->
        <div v-if="stats" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Activities -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total Activities</p>
                        <p class="text-2xl font-bold text-gray-900">@{{ stats.total_activities || 0 }}</p>
                        <p class="text-sm" :class="stats.total_growth >= 0 ? 'text-green-600' : 'text-red-600'">
                            <span v-if="stats.total_growth >= 0">+</span>@{{ stats.total_growth || 0 }}% from last week
                        </p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Active Users</p>
                        <p class="text-2xl font-bold text-gray-900">@{{ stats.active_users || 0 }}</p>
                        <p class="text-sm text-gray-500">@{{ stats.active_today || 0 }} active today</p>
                    </div>
                    <div class="p-3 bg-green-50 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Today's Activities -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Today's Activities</p>
                        <p class="text-2xl font-bold text-gray-900">@{{ stats.today_activities || 0 }}</p>
                        <p class="text-sm text-gray-500">@{{ stats.hourly_average || 0 }}/hour average</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-full">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- This Week -->
            <div class="bg-white rounded-lg shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">This Week</p>
                        <p class="text-2xl font-bold text-gray-900">@{{ stats.this_week || 0 }}</p>
                        <p class="text-sm" :class="stats.week_growth >= 0 ? 'text-green-600' : 'text-red-600'">
                            <span v-if="stats.week_growth >= 0">+</span>@{{ stats.week_growth || 0 }}% vs last week
                        </p>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-full">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Activity Timeline Chart -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Activity Timeline</h3>
                        <div class="flex space-x-2">
                            <button @click="updateChartPeriod('7')" 
                                    :class="chartPeriod === '7' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-700'"
                                    class="px-3 py-1 rounded-md text-sm font-medium transition-colors duration-200">
                                7D
                            </button>
                            <button @click="updateChartPeriod('30')" 
                                    :class="chartPeriod === '30' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-700'"
                                    class="px-3 py-1 rounded-md text-sm font-medium transition-colors duration-200">
                                30D
                            </button>
                            <button @click="updateChartPeriod('90')" 
                                    :class="chartPeriod === '90' ? 'bg-primary-100 text-primary-700' : 'bg-gray-100 text-gray-700'"
                                    class="px-3 py-1 rounded-md text-sm font-medium transition-colors duration-200">
                                90D
                            </button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <canvas id="activityChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Activity Distribution Chart -->
            <div class="bg-white shadow-lg rounded-xl border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Activity Distribution</h3>
                </div>
                <div class="p-6">
                    <canvas id="distributionChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Users with Advanced Analytics -->
        <div v-if="topUsers.length > 0" class="mb-8">
            <div class="bg-white shadow-lg rounded-xl border border-gray-100">
                <div class="px-6 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Most Active Users</h3>
                        <a href="{{ route('user-history.users') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                            View All Users →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div v-for="(user, index) in topUsers" :key="user.id" 
                             @click="viewUserProfile(user.id)"
                             class="group p-6 border border-gray-200 rounded-xl hover:shadow-lg hover:border-primary-200 cursor-pointer transition-all duration-300 fade-in">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="relative">
                                        <div class="h-12 w-12 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                            </svg>
                                        </div>
                                        <div v-if="index < 3" class="absolute -top-1 -right-1 h-6 w-6 rounded-full bg-yellow-400 flex items-center justify-center">
                                            <span class="text-xs font-bold text-white">@{{ index + 1 }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="ml-4 flex-1">
                                    <h4 class="text-sm font-semibold text-gray-900 group-hover:text-primary-600 transition-colors duration-200">
                                        @{{ user.name }}
                                    </h4>
                                    <p class="text-sm text-gray-500">@{{ user.email }}</p>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <span class="text-xs font-medium text-blue-600">@{{ user.activity_count }} activities</span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-1">
                                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-1 rounded-full transition-all duration-300"
                                                 :style="{ width: (user.activity_count / Math.max(...topUsers.map(u => u.activity_count))) * 100 + '%' }"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right opacity-0 group-hover:opacity-100 transition-opacity duration-200">
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

        <!-- Recent Activities with Advanced Features -->
        <div v-if="recentActivitiesData.length > 0" class="bg-white shadow-lg overflow-hidden rounded-xl border border-gray-100 mb-8">
            <div class="px-6 py-5 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activities</h3>
                        <p class="text-sm text-gray-500">Live activity feed with real-time updates</p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <button @click="toggleAutoRefresh" 
                                :class="autoRefresh ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                                class="px-3 py-1 rounded-md text-sm font-medium transition-colors duration-200">
                            <span v-if="autoRefresh">Auto-refresh ON</span>
                            <span v-else>Auto-refresh OFF</span>
                        </button>
                        <button @click="refreshActivities" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                            Refresh
                        </button>
                    </div>
                </div>
            </div>
            <div class="max-h-96 overflow-y-auto custom-scrollbar">
                <ul class="divide-y divide-gray-200">
                    <li v-for="activity in recentActivitiesData" :key="activity.id" 
                        class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150 fade-in">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="h-10 w-10 rounded-lg bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                  :d="getActionIcon(activity.action)"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="flex items-center">
                                        <p class="text-sm font-medium text-gray-900 cursor-pointer hover:text-blue-600 transition-colors duration-200"
                                           @click="viewUserProfile(activity.user_id)">
                                            @{{ activity.user_name || 'Unknown User' }}
                                        </p>
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium shadow-sm"
                                              :class="getActionBadgeClass(activity.action)">
                                            @{{ activity.action }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-900 mt-1">
                                        @{{ activity.description }}
                                        <span v-if="activity.subject_name" class="text-blue-600">• @{{ activity.subject_name }}</span>
                                    </p>
                                    <div class="mt-1 text-xs text-gray-500">
                                        <span v-if="activity.ip_address">IP: @{{ activity.ip_address }}</span>
                                        <span v-if="activity.user_agent" class="ml-2">Browser: @{{ getBrowserInfo(activity.user_agent) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">@{{ formatRelativeTime(activity.created_at) }}</p>
                                <p class="text-xs text-gray-400">@{{ formatDateTime(activity.created_at) }}</p>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Quick Actions with Advanced Features -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <a href="{{ route('user-history.activities') }}" class="group bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-blue-600 transition-colors duration-200">View All Activities</h3>
                            <p class="text-sm text-gray-500">Browse complete activity history</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('user-history.users') }}" class="group bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-green-600 transition-colors duration-200">User Profiles</h3>
                            <p class="text-sm text-gray-500">Deep dive into user activities</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('user-history.stats') }}" class="group bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-r from-purple-500 to-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-purple-600 transition-colors duration-200">Detailed Statistics</h3>
                            <p class="text-sm text-gray-500">View comprehensive analytics</p>
                        </div>
                    </div>
                </div>
            </a>

            <a href="{{ route('user-history.reports.export') }}" class="group bg-white overflow-hidden shadow-lg rounded-xl border border-gray-100 hover:shadow-xl transition-all duration-300">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-xl bg-gradient-to-r from-yellow-500 to-yellow-600 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900 group-hover:text-yellow-600 transition-colors duration-200">Export Data</h3>
                            <p class="text-sm text-gray-500">Download activity reports</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            activities: [],
            stats: {
                total_activities: 0,
                total_growth: 0,
                active_users: 0,
                active_today: 0,
                today_activities: 0,
                hourly_average: 0,
                this_week: 0,
                week_growth: 0
            },
            loading: true,
            searchQuery: '',
            selectedAction: '',
            selectedUser: '',
            dateFrom: '',
            dateTo: '',
            chartPeriod: 7,
            activityChart: null,
            distributionChart: null,
            refreshInterval: null,
            users: [],
            searchResults: [],
            searchTimeout: null,
            lastUpdate: new Date(),
            autoRefresh: true,
            recentActivitiesData: [],
            topUsers: []
        }
    },
    async mounted() {
        console.log('Dashboard mounted, loading initial data...');
        
        // Load initial data
        await Promise.all([
            this.loadActivities(),
            this.loadStats(),
            this.loadUsers()
        ]);
        
        // Load charts after data is available
        await this.loadChartData();
        
        // Start real-time updates
        this.startRealTimeUpdates();
        
        // Start fallback polling
        this.startAutoRefresh();
        
        console.log('Dashboard initialization complete');
    },
    beforeUnmount() {
        // Clean up intervals
        if (this.realTimeInterval) {
            clearInterval(this.realTimeInterval);
        }
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    },
    methods: {
        async loadChartData() {
            await Promise.all([
                this.loadTimelineChart(),
                this.loadDistributionChart()
            ]);
        },

        async loadTimelineChart() {
            try {
                const response = await fetch(`{{ route('user-history.api.timeline-chart') }}?days=${this.chartPeriod}`);
                const data = await response.json();
                
                if (this.activityChart) {
                    this.activityChart.destroy();
                }
                
                const timelineCtx = document.getElementById('activityChart');
                if (timelineCtx) {
                    this.activityChart = new Chart(timelineCtx, {
                        type: 'line',
                        data: {
                            labels: data.map(item => this.formatChartDate(item.date)),
                            datasets: [{
                                label: 'Activities',
                                data: data.map(item => item.count),
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                borderWidth: 2,
                                fill: true,
                                tension: 0.4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    grid: {
                                        color: 'rgba(0, 0, 0, 0.05)'
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading timeline chart:', error);
            }
        },

        async loadDistributionChart() {
            try {
                const response = await fetch(`{{ route('user-history.api.distribution-chart') }}?days=${this.chartPeriod}`);
                const data = await response.json();
                
                if (this.distributionChart) {
                    this.distributionChart.destroy();
                }
                
                const distributionCtx = document.getElementById('distributionChart');
                if (distributionCtx) {
                    this.distributionChart = new Chart(distributionCtx, {
                        type: 'doughnut',
                        data: {
                            labels: data.map(item => item.action),
                            datasets: [{
                                data: data.map(item => item.count),
                                backgroundColor: [
                                    'rgba(34, 197, 94, 0.8)',
                                    'rgba(59, 130, 246, 0.8)',
                                    'rgba(239, 68, 68, 0.8)',
                                    'rgba(147, 51, 234, 0.8)',
                                    'rgba(107, 114, 128, 0.8)',
                                    'rgba(251, 146, 60, 0.8)',
                                    'rgba(16, 185, 129, 0.8)'
                                ],
                                borderWidth: 0
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            } catch (error) {
                console.error('Error loading distribution chart:', error);
            }
        },

        formatChartDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        },

        performSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                if (this.searchQuery.length >= 2) {
                    this.fetchSearchResults();
                } else {
                    this.searchResults = [];
                }
            }, 300);
        },
        
        async fetchSearchResults() {
            try {
                const response = await fetch(`{{ route('user-history.api.search') }}?q=${encodeURIComponent(this.searchQuery)}`);
                const data = await response.json();
                this.searchResults = data.data || [];
            } catch (error) {
                console.error('Search error:', error);
            }
        },

        clearSearch() {
            this.searchQuery = '';
            this.searchResults = [];
        },
        
        viewUserProfile(userId) {
            window.location.href = `{{ route('user-history.users') }}/${userId}`;
        },
        
        refreshActivities() {
            this.lastUpdate = new Date();
            // In a real app, this would fetch fresh data
            // For now, we'll just update the timestamp
        },

        toggleAutoRefresh() {
            this.autoRefresh = !this.autoRefresh;
            if (this.autoRefresh) {
                this.startAutoRefresh();
            } else {
                this.stopAutoRefresh();
            }
        },

        startAutoRefresh() {
            // Fallback polling mechanism for charts (less frequent)
            this.refreshInterval = setInterval(async () => {
                try {
                    if (!document.hidden) {
                        await this.loadChartData();
                    }
                } catch (error) {
                    console.error('Chart refresh error:', error);
                }
            }, 30000); // Refresh charts every 30 seconds
        },

        stopAutoRefresh() {
            if (this.refreshInterval) {
                clearInterval(this.refreshInterval);
                this.refreshInterval = null;
            }
        },

        refreshData() {
            this.refreshActivities();
            // Add a visual feedback
            const button = event.target.closest('button');
            button.classList.add('animate-spin');
            setTimeout(() => {
                button.classList.remove('animate-spin');
            }, 1000);
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },

        formatDateTime(dateString) {
            return new Date(dateString).toLocaleString();
        },
        
        formatRelativeTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            return `${Math.floor(diffInSeconds / 86400)}d ago`;
        },
        
        getActionBadgeClass(action) {
            const classes = {
                'created': 'bg-green-100 text-green-800 border-green-200',
                'updated': 'bg-blue-100 text-blue-800 border-blue-200',
                'deleted': 'bg-red-100 text-red-800 border-red-200',
                'login': 'bg-purple-100 text-purple-800 border-purple-200',
                'logout': 'bg-gray-100 text-gray-800 border-gray-200'
            };
            return classes[action] || 'bg-gray-100 text-gray-800 border-gray-200';
        },
        
        getActionIcon(action) {
            const icons = {
                'created': 'fas fa-plus',
                'updated': 'fas fa-edit',
                'deleted': 'fas fa-trash',
                'login': 'fas fa-sign-in-alt'
            };
            
            return icons[action.toLowerCase()] || 'fas fa-circle';
        },

        getBrowserInfo(userAgent) {
            if (!userAgent) return 'Unknown';
            
            if (userAgent.includes('Chrome')) return 'Chrome';
            if (userAgent.includes('Firefox')) return 'Firefox';
            if (userAgent.includes('Safari')) return 'Safari';
            if (userAgent.includes('Edge')) return 'Edge';
            if (userAgent.includes('MSIE') || userAgent.includes('Trident/')) return 'Internet Explorer';
            
            return 'Other';
        },

        updateChartPeriod(period) {
            this.chartPeriod = period;
            this.loadChartData();
        },

        getActionColor(action) {
            const colors = {
                'created': 'bg-green-100 text-green-800',
                'updated': 'bg-blue-100 text-blue-800',
                'deleted': 'bg-red-100 text-red-800',
                'viewed': 'bg-purple-100 text-purple-800',
                'login': 'bg-yellow-100 text-yellow-800'
            };
            
            return colors[action.toLowerCase()] || 'bg-gray-100 text-gray-800';
        },

        getActionLabel(action) {
            const labels = {
                'created': 'Created',
                'updated': 'Updated',
                'deleted': 'Deleted',
                'viewed': 'Viewed',
                'login': 'Login'
            };
            
            return labels[action.toLowerCase()] || 'Other';
        },

        formatLastUpdate() {
            if (!this.lastUpdate) return 'Never';
            return this.lastUpdate.toLocaleTimeString();
        },

        async loadActivities() {
            try {
                console.log('Loading activities...');
                const response = await fetch('{{ route('user-history.api.activities') }}');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                console.log('Activities loaded:', data);
                this.activities = data.activities || [];
                this.loading = false;
            } catch (error) {
                console.error('Error loading activities:', error);
                this.activities = [];
                this.loading = false;
            }
        },

        async loadStats() {
            try {
                console.log('Loading stats...');
                const response = await fetch('{{ route('user-history.api.stats') }}');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                console.log('Stats loaded:', data);
                this.stats = data;
            } catch (error) {
                console.error('Error loading stats:', error);
                this.stats = {
                    total_activities: 0,
                    total_growth: 0,
                    active_users: 0,
                    active_today: 0,
                    today_activities: 0,
                    hourly_average: 0,
                    this_week: 0,
                    week_growth: 0
                };
            }
        },

        async loadUsers() {
            try {
                console.log('Loading users...');
                const response = await fetch('{{ route('user-history.users') }}');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                console.log('Users loaded:', data);
                this.users = data.users || [];
            } catch (error) {
                console.error('Error loading users:', error);
                this.users = [];
            }
        },

        startRealTimeUpdates() {
            // Simple polling mechanism for real-time updates
            this.realTimeInterval = setInterval(async () => {
                try {
                    // Only update if the page is visible
                    if (!document.hidden) {
                        await Promise.all([
                            this.loadStats(),
                            this.loadActivities()
                        ]);
                        this.lastUpdate = new Date();
                    }
                } catch (error) {
                    console.error('Real-time update error:', error);
                }
            }, 3000); // Update every 3 seconds
        },
    }
}).mount('#advancedDashboard');
</script>
@endsection 