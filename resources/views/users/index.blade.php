@extends('user-history::layouts.app')

@section('content')
<div id="usersIndex" v-cloak>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Page Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">User Profiles</h1>
                    <p class="mt-2 text-gray-600">Browse and analyze user activity profiles</p>
                </div>
                
                <!-- Search Bar -->
                <div class="mt-4 sm:mt-0 sm:ml-4">
                    <div class="relative">
                        <input 
                            v-model="searchQuery" 
                            @input="performSearch"
                            type="text" 
                            placeholder="Search users by name or email..."
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
                    <!-- Activity Level Filter -->
                    <select v-model="selectedActivityLevel" @change="filterUsers" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">All Activity Levels</option>
                        <option value="high">High Activity (50+ actions)</option>
                        <option value="medium">Medium Activity (10-49 actions)</option>
                        <option value="low">Low Activity (1-9 actions)</option>
                        <option value="inactive">Inactive (0 actions)</option>
                    </select>
                    
                    <!-- Date Range Filter -->
                    <select v-model="selectedDateRange" @change="filterUsers" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                        <option value="365">Last year</option>
                    </select>
                    
                    <!-- Sort By -->
                    <select v-model="sortBy" @change="filterUsers" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="activity_count">Most Active</option>
                        <option value="name">Name A-Z</option>
                        <option value="email">Email A-Z</option>
                        <option value="last_activity">Last Activity</option>
                        <option value="created_at">Registration Date</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Users Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <div v-for="user in paginatedUsers" :key="user.id" 
                 @click="viewUserProfile(user.id)"
                 class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow duration-200 cursor-pointer">
                <div class="p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h3 class="text-lg font-medium text-gray-900 truncate">@{{ user.name }}</h3>
                            <p class="text-sm text-gray-500 truncate">@{{ user.email }}</p>
                        </div>
                    </div>
                    
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Activities:</span>
                            <span class="font-medium text-gray-900">@{{ formatNumber(user.activity_count) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Last Activity:</span>
                            <span class="font-medium text-gray-900">@{{ formatRelativeTime(user.last_activity) }}</span>
                        </div>
                        
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Member Since:</span>
                            <span class="font-medium text-gray-900">@{{ formatDate(user.created_at) }}</span>
                        </div>
                    </div>
                    
                    <!-- Activity Level Badge -->
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                              :class="getActivityLevelBadgeClass(user.activity_count)">
                            @{{ getActivityLevel(user.activity_count) }}
                        </span>
                    </div>
                    
                    <!-- Activity Chart Preview -->
                    <div class="mt-4">
                        <div class="flex space-x-1">
                            <div v-for="day in user.weekly_activity" :key="day.date" 
                                 class="flex-1 bg-gray-200 rounded"
                                 :style="{ height: (day.count / Math.max(...user.weekly_activity.map(d => d.count))) * 20 + 'px' }">
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Weekly activity</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="filteredUsers.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No users found</h3>
            <p class="mt-1 text-sm text-gray-500">Try adjusting your search or filter criteria.</p>
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-8 flex items-center justify-between">
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
                        Showing <span class="font-medium">@{{ startIndex + 1 }}</span> to <span class="font-medium">@{{ endIndex }}</span> of <span class="font-medium">@{{ filteredUsers.length }}</span> users
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

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            users: @json($users ?? []),
            searchQuery: '',
            selectedActivityLevel: '',
            selectedDateRange: '30',
            sortBy: 'activity_count',
            currentPage: 1,
            itemsPerPage: 12
        }
    },
    computed: {
        filteredUsers() {
            let filtered = this.users;
            
            // Search filter
            if (this.searchQuery) {
                const query = this.searchQuery.toLowerCase();
                filtered = filtered.filter(user => 
                    user.name.toLowerCase().includes(query) ||
                    user.email.toLowerCase().includes(query)
                );
            }
            
            // Activity level filter
            if (this.selectedActivityLevel) {
                filtered = filtered.filter(user => {
                    const count = user.activity_count;
                    switch (this.selectedActivityLevel) {
                        case 'high': return count >= 50;
                        case 'medium': return count >= 10 && count < 50;
                        case 'low': return count >= 1 && count < 10;
                        case 'inactive': return count === 0;
                        default: return true;
                    }
                });
            }
            
            // Sort
            filtered.sort((a, b) => {
                switch (this.sortBy) {
                    case 'activity_count':
                        return b.activity_count - a.activity_count;
                    case 'name':
                        return a.name.localeCompare(b.name);
                    case 'email':
                        return a.email.localeCompare(b.email);
                    case 'last_activity':
                        return new Date(b.last_activity) - new Date(a.last_activity);
                    case 'created_at':
                        return new Date(b.created_at) - new Date(a.created_at);
                    default:
                        return 0;
                }
            });
            
            return filtered;
        },
        
        totalPages() {
            return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
        },
        
        startIndex() {
            return (this.currentPage - 1) * this.itemsPerPage;
        },
        
        endIndex() {
            return Math.min(this.startIndex + this.itemsPerPage, this.filteredUsers.length);
        },
        
        paginatedUsers() {
            return this.filteredUsers.slice(this.startIndex, this.endIndex);
        }
    },
    methods: {
        performSearch() {
            this.currentPage = 1;
        },
        
        filterUsers() {
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
        
        getActivityLevel(count) {
            if (count >= 50) return 'High Activity';
            if (count >= 10) return 'Medium Activity';
            if (count >= 1) return 'Low Activity';
            return 'Inactive';
        },
        
        getActivityLevelBadgeClass(count) {
            if (count >= 50) return 'bg-green-100 text-green-800';
            if (count >= 10) return 'bg-blue-100 text-blue-800';
            if (count >= 1) return 'bg-yellow-100 text-yellow-800';
            return 'bg-gray-100 text-gray-800';
        }
    }
}).mount('#usersIndex');
</script>
@endsection 