@extends('layouts.app-sidebar')

@section('title', 'Email Templates Personalizados')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Email Templates</h1>
            <p class="text-gray-600 mt-2">Manage custom email templates for marketing and other purposes</p>
        </div>
        <a href="{{ route('admin.custom-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            New Template
        </a>
    </div>

    <!-- Filters -->
    <div class="mb-6 flex gap-4">
        <form method="GET" action="{{ route('admin.custom-templates.index') }}" class="flex gap-4 flex-1">
            <input type="text" name="search" placeholder="Search templates..." value="{{ request('search') }}" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

            <select name="status" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Status</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>

            <select name="type" onchange="this.form.submit()" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All Types</option>
                <option value="custom" {{ request('type') === 'custom' ? 'selected' : '' }}>Custom</option>
                <option value="marketing" {{ request('type') === 'marketing' ? 'selected' : '' }}>Marketing</option>
                <option value="newsletter" {{ request('type') === 'newsletter' ? 'selected' : '' }}>Newsletter</option>
                <option value="promotional" {{ request('type') === 'promotional' ? 'selected' : '' }}>Promotional</option>
            </select>
        </form>
    </div>

    <!-- Templates Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        @if($templates->count() > 0)
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Type</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Assignments</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Created By</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($templates as $template)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm text-gray-900 font-medium">{{ $template->name }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    {{ ucfirst($template->template_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($template->isDraft())
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">Draft</span>
                                @elseif($template->isPublished())
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">Published</span>
                                @else
                                    <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">Archived</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $template->assignments->count() }} assignments
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $template->creator->name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.custom-templates.edit', $template) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                                    <button onclick="openPreview({{ $template->id }})" class="text-gray-600 hover:text-gray-900">Preview</button>
                                    <a href="{{ route('admin.custom-templates.clone', $template) }}" class="text-green-600 hover:text-green-900" onclick="return confirm('Clone this template?')">Clone</a>
                                    <form method="POST" action="{{ route('admin.custom-templates.destroy', $template) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $templates->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center">
                <p class="text-gray-500">No templates found. Create one to get started.</p>
            </div>
        @endif
    </div>
</div>
@endsection
