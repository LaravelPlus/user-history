@extends('user-history::layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Page Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
        <p class="mt-2 text-gray-600">Configure user history tracking preferences</p>
    </div>

    <!-- Settings Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form method="POST" action="{{ route('user-history.settings.update') }}">
                @csrf
                
                <div class="space-y-6">
                    <!-- Enable Tracking -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="enabled" value="1" 
                                   {{ config('user-history.enabled', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-900">Enable user activity tracking</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Track all user activities across the application</p>
                    </div>

                    <!-- Track IP Address -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="track_ip" value="1" 
                                   {{ config('user-history.track_ip', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-900">Track IP addresses</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Store IP addresses with activities for security monitoring</p>
                    </div>

                    <!-- Track User Agent -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="track_user_agent" value="1" 
                                   {{ config('user-history.track_user_agent', true) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-primary-600 shadow-sm focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-900">Track user agents</span>
                        </label>
                        <p class="mt-1 text-sm text-gray-500">Store browser and device information</p>
                    </div>

                    <!-- Retention Period -->
                    <div>
                        <label for="retention_days" class="block text-sm font-medium text-gray-700">Activity Retention Period (days)</label>
                        <input type="number" name="retention_days" id="retention_days" 
                               value="{{ config('user-history.retention_days', 365) }}"
                               min="1" max="3650"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <p class="mt-1 text-sm text-gray-500">How long to keep activity records (1-3650 days)</p>
                    </div>

                    <!-- Items Per Page -->
                    <div>
                        <label for="per_page" class="block text-sm font-medium text-gray-700">Items per page</label>
                        <input type="number" name="per_page" id="per_page" 
                               value="{{ config('user-history.per_page', 15) }}"
                               min="5" max="100"
                               class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <p class="mt-1 text-sm text-gray-500">Number of activities to show per page in lists</p>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6">
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cleanup Section -->
    <div class="mt-8 bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Data Management</h3>
            
            <div class="space-y-4">
                <div>
                    <h4 class="text-sm font-medium text-gray-700">Cleanup Old Activities</h4>
                    <p class="text-sm text-gray-500">Remove activities older than the retention period</p>
                    <form method="POST" action="{{ route('user-history.cleanup') }}" class="mt-2">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Run Cleanup
                        </button>
                    </form>
                </div>

                <div>
                    <h4 class="text-sm font-medium text-gray-700">Export Data</h4>
                    <p class="text-sm text-gray-500">Download activity data for backup or analysis</p>
                    <div class="mt-2 space-x-2">
                        <a href="{{ route('user-history.reports.export') }}?format=csv" 
                           class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Export CSV
                        </a>
                        <a href="{{ route('user-history.reports.export') }}?format=json" 
                           class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            Export JSON
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 