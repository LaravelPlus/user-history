@extends('user-history::layouts.app')

@section('content')
<div id="activitiesIndex" v-cloak>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Activity Log</h1>
                    <p class="mt-2 text-gray-600">Browse and filter all user activities</p>
                </div>
                
                <!-- Search Bar -->
                <div class="mt-4 sm:mt-0 sm:ml-4">
                    <div class="relative">
                        <input 
                            v-model="searchQuery" 
                            @input="performSearch"
                            type="text" 
                            placeholder="Search activities, users, or actions..."
                            class="w-full sm:w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white shadow rounded-lg p-6 mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <h3 class="text-lg font-medium text-gray-900">Filters</h3>
                
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                    <!-- User Filter -->
                    <select v-model="selectedUser" @change="filterActivities" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">All Users</option>
                        <option v-for="user in availableUsers" :key="user.id" :value="user.id">@{{ user.name }}</option>
                    </select>
                    
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
                        <option value="all">All time</option>
                    </select>
                    
                    <!-- Sort By -->
                    <select v-model="sortBy" @change="filterActivities" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="created_at">Newest First</option>
                        <option value="created_at_asc">Oldest First</option>
                        <option value="user_name">User Name</option>
                        <option value="action">Action Type</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Activities List -->
        <div class="bg-white shadow overflow-hidden sm:rounded-md">
            <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900">All Activities</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">Complete activity log with detailed information</p>
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
                                    <p class="text-sm font-medium text-gray-900 cursor-pointer hover:text-blue-600"
                                       @click="viewUserProfile(activity.user_id)">
                                        @{{ activity.user_name || 'Unknown User' }}
                                    </p>
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
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
            
            <!-- Empty State -->
            <div v-if="filteredActivities.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No activities found</h3>
                <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
            </div>
            
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
                                Showing <span class="font-medium">@{{ startIndex + 1 }}</span> to <span class="font-medium">@{{ endIndex }}</span> of <span class="font-medium">@{{ filteredActivities.length }}</span> activities
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
            activities: @json($activitiesData ?? []),
            searchQuery: '',
            selectedUser: '',
            selectedAction: '',
            selectedDateRange: '30',
            sortBy: 'created_at',
            currentPage: 1,
            itemsPerPage: 20,
            expandedActivities: [],
            availableUsers: [],
            availableActions: []
        }
    },
    computed: {
        filteredActivities() {
            let filtered = this.activities;
            
            // Search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(activity => 
                    activity.description.toLowerCase().includes(query) ||
                    activity.action.toLowerCase().includes(query) ||
                    (activity.user_name && activity.user_name.toLowerCase().includes(query)) ||
                    (activity.subject_name && activity.subject_name.toLowerCase().includes(query))
                );
            }
            
            // User filter
            if (this.selectedUser) {
                filtered = filtered.filter(activity => activity.user_id == this.selectedUser);
            }
            
            // Action filter
            if (this.selectedAction) {
                filtered = filtered.filter(activity => activity.action === this.selectedAction);
            }
            
            // Date range filter
            if (this.selectedDateRange && this.selectedDateRange !== 'all') {
                const daysAgo = parseInt(this.selectedDateRange);
                const cutoffDate = new Date();
                cutoffDate.setDate(cutoffDate.getDate() - daysAgo);
                
                filtered = filtered.filter(activity => {
                    const activityDate = new Date(activity.created_at);
                    return activityDate >= cutoffDate;
                });
            }
            
            // Sort
            filtered.sort((a, b) => {
                switch (this.sortBy) {
                    case 'created_at':
                        return new Date(b.created_at) - new Date(a.created_at);
                    case 'created_at_asc':
                        return new Date(a.created_at) - new Date(b.created_at);
                    case 'user_name':
                        return (a.user_name || '').localeCompare(b.user_name || '');
                    case 'action':
                        return a.action.localeCompare(b.action);
                    default:
                        return 0;
                }
            });
            
            return filtered;
        },
        
        totalActivities() {
            return this.activities.length;
        },
        
        totalPages() {
            return Math.ceil(this.filteredActivities.length / this.itemsPerPage);
        },
        
        startIndex() {
            return (this.currentPage - 1) * this.itemsPerPage;
        },
        
        endIndex() {
            return Math.min(this.startIndex + this.itemsPerPage, this.filteredActivities.length);
        },
        
        paginatedActivities() {
            return this.filteredActivities.slice(this.startIndex, this.endIndex);
        }
    },
    mounted() {
        this.availableUsers = [...new Set(this.activities.map(a => ({ id: a.user_id, name: a.user_name })))];
        this.availableActions = [...new Set(this.activities.map(a => a.action))];
    },
    methods: {
        performSearch() {
            this.currentPage = 1;
        },
        
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
        
        viewUserProfile(userId) {
            window.location.href = `{{ route('user-history.users') }}/${userId}`;
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
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);
            
            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
            if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)}d ago`;
            return `${Math.floor(diffInSeconds / 2592000)}mo ago`;
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
}).mount('#activitiesIndex');
</script>
@endsection 