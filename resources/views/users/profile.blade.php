@extends('user-history::layouts.app')

@section('content')
<div id="userProfile" v-cloak>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- User Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <a href="{{ route('user-history.users') }}" class="mr-4 text-gray-400 hover:text-gray-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">@{{ user.name }}</h1>
                        <p class="text-gray-600">@{{ user.email }}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Member since</p>
                    <p class="text-lg font-medium text-gray-900">@{{ formatDate(user.created_at) }}</p>
                </div>
            </div>
        </div>

        <!-- User Stats Cards -->
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
                                <dd class="text-lg font-medium text-gray-900">@{{ formatNumber(userStats.total_activities) }}</dd>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">This Week</dt>
                                <dd class="text-lg font-medium text-gray-900">@{{ formatNumber(userStats.this_week_activities) }}</dd>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Last Activity</dt>
                                <dd class="text-lg font-medium text-gray-900">@{{ formatRelativeTime(userStats.last_activity) }}</dd>
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Most Active Day</dt>
                                <dd class="text-lg font-medium text-gray-900">@{{ userStats.most_active_day || 'N/A' }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Activity by Action Type -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Activity by Type</h3>
                <div class="space-y-3">
                    <div v-for="action in actionStats" :key="action.action" class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-4 h-4 rounded-full mr-3" :class="getActionColor(action.action)"></div>
                            <span class="text-sm font-medium text-gray-900">@{{ action.action }}</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-32 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="h-2 rounded-full" 
                                     :class="getActionColor(action.action)"
                                     :style="{ width: (action.percentage) + '%' }"></div>
                            </div>
                            <span class="text-sm text-gray-500 w-12 text-right">@{{ action.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Timeline (Last 7 Days)</h3>
                <div class="space-y-3">
                    <div v-for="day in timelineData" :key="day.date" class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">@{{ formatDay(day.date) }}</span>
                        <div class="flex items-center">
                            <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                                <div class="h-2 bg-blue-600 rounded-full" 
                                     :style="{ width: (day.percentage) + '%' }"></div>
                            </div>
                            <span class="text-sm text-gray-500 w-8 text-right">@{{ day.count }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <h3 class="text-lg font-medium text-gray-900">User Activities</h3>
                
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <!-- Action Filter -->
                    <select v-model="selectedAction" @change="filterActivities" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">All Actions</option>
                        <option v-for="action in availableActions" :key="action" :value="action">@{{ action }}</option>
                    </select>
                    
                    <!-- Date Range Filter -->
                    <select v-model="selectedDateRange" @change="filterActivities" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last year</option>
                    </select>
                    
                    <!-- Search -->
                    <div class="relative">
                        <input 
                            v-model="searchQuery" 
                            @input="filterActivities"
                            type="text" 
                            placeholder="Search activities..."
                            class="border border-gray-300 rounded-md pl-8 pr-3 py-2 text-sm w-full sm:w-64"
                        >
                        <div class="absolute inset-y-0 left-0 pl-2 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activities List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Activity History</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Detailed activity log for this user</p>
                    </div>
                    <div class="text-sm text-gray-500">
                        Showing @{{ filteredActivities.length }} of @{{ totalActivities }} activities
                    </div>
                </div>
            </div>
            
            <ul class="divide-y divide-gray-200">
                <li v-for="activity in paginatedActivities" :key="activity.id" 
                    class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors duration-150">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                              :d="getActionIcon(activity.action)"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="flex items-center">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
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
                            <p class="text-xs text-gray-400">@{{ formatDate(activity.created_at) }}</p>
                        </div>
                    </div>
                    
                    <!-- Activity Details (Expandable) -->
                    <div v-if="activity.properties && Object.keys(activity.properties).length > 0" class="mt-3 pl-14">
                        <button @click="toggleActivityDetails(activity.id)" 
                                class="text-xs text-blue-600 hover:text-blue-800">
                            @{{ expandedActivities.includes(activity.id) ? 'Hide' : 'Show' }} details
                        </button>
                        <div v-if="expandedActivities.includes(activity.id)" class="mt-2 p-3 bg-gray-50 rounded-lg">
                            <pre class="text-xs text-gray-700 whitespace-pre-wrap">@{{ JSON.stringify(activity.properties, null, 2) }}</pre>
                        </div>
                    </div>
                </li>
            </ul>
            
            <!-- Pagination -->
            <div v-if="totalPages > 1" class="px-4 py-3 border-t border-gray-200 sm:px-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1 flex justify-between sm:hidden">
                        <button @click="previousPage" :disabled="currentPage === 1" 
                                class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                            Previous
                        </button>
                        <button @click="nextPage" :disabled="currentPage === totalPages" 
                                class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50">
                            Next
                        </button>
                    </div>
                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing page <span class="font-medium">@{{ currentPage }}</span> of <span class="font-medium">@{{ totalPages }}</span>
                            </p>
                        </div>
                        <div>
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <button @click="previousPage" :disabled="currentPage === 1" 
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                    Previous
                                </button>
                                <button @click="nextPage" :disabled="currentPage === totalPages" 
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50">
                                    Next
                                </button>
                            </nav>
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
            user: @json($user ?? []),
            userStats: @json($userStats ?? []),
            activities: @json($activities ?? []),
            actionStats: @json($actionStats ?? []),
            timelineData: @json($timelineData ?? []),
            selectedAction: '',
            selectedDateRange: '30',
            searchQuery: '',
            currentPage: 1,
            itemsPerPage: 20,
            expandedActivities: [],
            availableActions: []
        }
    },
    computed: {
        filteredActivities() {
            let filtered = this.activities;
            
            if (this.selectedAction) {
                filtered = filtered.filter(activity => activity.action === this.selectedAction);
            }
            
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(activity => 
                    activity.description.toLowerCase().includes(query) ||
                    activity.action.toLowerCase().includes(query) ||
                    (activity.subject_name && activity.subject_name.toLowerCase().includes(query))
                );
            }
            
            return filtered;
        },
        
        totalActivities() {
            return this.activities.length;
        },
        
        totalPages() {
            return Math.ceil(this.filteredActivities.length / this.itemsPerPage);
        },
        
        paginatedActivities() {
            const start = (this.currentPage - 1) * this.itemsPerPage;
            const end = start + this.itemsPerPage;
            return this.filteredActivities.slice(start, end);
        }
    },
    mounted() {
        this.availableActions = [...new Set(this.activities.map(a => a.action))];
    },
    methods: {
        filterActivities() {
            this.currentPage = 1;
        },
        
        previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
            }
        },
        
        nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
            }
        },
        
        toggleActivityDetails(activityId) {
            const index = this.expandedActivities.indexOf(activityId);
            if (index > -1) {
                this.expandedActivities.splice(index, 1);
            } else {
                this.expandedActivities.push(activityId);
            }
        },
        
        formatNumber(num) {
            return new Intl.NumberFormat().format(num || 0);
        },
        
        formatDate(dateString) {
            return new Date(dateString).toLocaleDateString();
        },
        
        formatRelativeTime(dateString) {
            if (!dateString) return 'Never';
            
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d ago`;
            return `${Math.floor(diffInSeconds / 2592000)}mo ago`;
        },
        
        formatDay(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
        },
        
        getActionBadgeClass(action) {
            const classes = {
                'created': 'bg-green-100 text-green-800',
                'updated': 'bg-blue-100 text-blue-800',
                'deleted': 'bg-red-100 text-red-800',
                'login': 'bg-purple-100 text-purple-800',
                'logout': 'bg-gray-100 text-gray-800'
            };
            return classes[action] || 'bg-gray-100 text-gray-800';
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
        },
        
        getActionIcon(action) {
            const icons = {
                'created': 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                'updated': 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                'deleted': 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                'login': 'M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1',
                'logout': 'M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1'
            };
            return icons[action] || 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
        },
        
        getBrowserInfo(userAgent) {
            if (!userAgent) return 'Unknown';
            
            // Simple browser detection
            if (userAgent.includes('Chrome')) return 'Chrome';
            if (userAgent.includes('Firefox')) return 'Firefox';
            if (userAgent.includes('Safari')) return 'Safari';
            if (userAgent.includes('Edge')) return 'Edge';
            if (userAgent.includes('MSIE') || userAgent.includes('Trident/')) return 'Internet Explorer';
            
            return 'Other';
        }
    }
}).mount('#userProfile');
</script>
@endsection 