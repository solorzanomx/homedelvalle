@extends('layouts.app-sidebar')

@section('title', 'Create Email Template')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Create Email Template</h1>
        <p class="text-gray-600 mt-2">Create a new custom email template with dynamic placeholders</p>
    </div>

    <form method="POST" action="{{ route('admin.custom-templates.store') }}" class="bg-white rounded-lg shadow p-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Form Fields -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('name') ? 'border-red-500' : '' }}" placeholder="e.g., Newsletter Febrero">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="3" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional description of the template...">{{ old('description') }}</textarea>
                    @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Type -->
                <div>
                    <label for="template_type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select id="template_type" name="template_type" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="custom" {{ old('template_type') === 'custom' ? 'selected' : '' }}>Custom</option>
                        <option value="marketing" {{ old('template_type') === 'marketing' ? 'selected' : '' }}>Marketing</option>
                        <option value="newsletter" {{ old('template_type') === 'newsletter' ? 'selected' : '' }}>Newsletter</option>
                        <option value="promotional" {{ old('template_type') === 'promotional' ? 'selected' : '' }}>Promotional</option>
                    </select>
                    @error('template_type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Subject -->
                <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700">
                        Email Subject
                        <span class="text-gray-500 font-normal text-xs ml-2">(supports @{{placeholders}})</span>
                    </label>
                    <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $errors->has('subject') ? 'border-red-500' : '' }}" placeholder="e.g., Hola @@{{nombre}}, tenemos una oferta para ti">
                    @error('subject') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Preview Text -->
                <div>
                    <label for="preview_text" class="block text-sm font-medium text-gray-700">Preview Text</label>
                    <input type="text" id="preview_text" name="preview_text" value="{{ old('preview_text') }}" maxlength="150" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Short preview for email client (max 150 chars)">
                    @error('preview_text') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- HTML Body (TinyMCE) -->
                <div>
                    <label for="html_body" class="block text-sm font-medium text-gray-700">Email Body (HTML)</label>
                    <textarea id="html_body" name="html_body" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('html_body') }}</textarea>
                    @error('html_body') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select id="status" name="status" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Publish Immediately</option>
                    </select>
                    @error('status') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <!-- Right Column: Preview & Helpers -->
            <div class="lg:col-span-1">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Available Placeholders</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center gap-2">
                            <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono">@{{nombre}}</code>
                            <span class="text-gray-600">Recipient name</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono">@{{email}}</code>
                            <span class="text-gray-600">Email address</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono">@{{colonia}}</code>
                            <span class="text-gray-600">Neighborhood</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono">@{{precio}}</code>
                            <span class="text-gray-600">Price</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono">@{{fecha}}</code>
                            <span class="text-gray-600">Date</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-8 flex gap-4 border-t border-gray-200 pt-6">
            <button type="submit" name="action" value="save" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                Save as Draft
            </button>
            <button type="submit" name="action" value="publish" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                Publish
            </button>
            <a href="{{ route('admin.custom-templates.index') }}" class="px-6 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 font-medium">
                Cancel
            </a>
        </div>
    </form>
</div>

<!-- Handle status via form action -->
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const statusField = document.getElementById('status');
    const submitButton = e.submitter;
    if (submitButton && submitButton.name === 'action' && submitButton.value === 'publish') {
        statusField.value = 'published';
    }
});

// Simple HTML editor with syntax highlighting
const htmlBody = document.getElementById('html_body');
htmlBody.style.fontFamily = 'monospace';
htmlBody.style.fontSize = '12px';
</script>
@endsection
