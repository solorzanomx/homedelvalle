@extends('layouts.app-sidebar')

@section('title', 'Edit Email Template')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $template->name }}</h1>
        <div class="flex items-center gap-4 mt-2">
            @if($template->isDraft())
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">Draft</span>
            @elseif($template->isPublished())
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">Published</span>
            @else
                <span class="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">Archived</span>
            @endif
            <span class="text-gray-600 text-sm">Created by {{ $template->creator->name ?? 'Unknown' }} on {{ $template->created_at->format('M d, Y') }}</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2">
            <!-- Template Editor Form -->
            <form method="POST" action="{{ route('admin.custom-templates.update', $template) }}" class="bg-white rounded-lg shadow p-6 mb-8">
                @csrf
                @method('PUT')

                <div class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Template Name</label>
                        <input type="text" id="name" name="name" value="{{ old('name', $template->name) }}" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea id="description" name="description" rows="3" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description', $template->description) }}</textarea>
                        @error('description') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Type -->
                    <div>
                        <label for="template_type" class="block text-sm font-medium text-gray-700">Type</label>
                        <select id="template_type" name="template_type" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="custom" {{ old('template_type', $template->template_type) === 'custom' ? 'selected' : '' }}>Custom</option>
                            <option value="marketing" {{ old('template_type', $template->template_type) === 'marketing' ? 'selected' : '' }}>Marketing</option>
                            <option value="newsletter" {{ old('template_type', $template->template_type) === 'newsletter' ? 'selected' : '' }}>Newsletter</option>
                            <option value="promotional" {{ old('template_type', $template->template_type) === 'promotional' ? 'selected' : '' }}>Promotional</option>
                        </select>
                        @error('template_type') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Subject -->
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700">
                        Email Subject
                        <span class="text-gray-500 font-normal text-xs ml-2">(supports @{{placeholders}})</span>
                    </label>
                        <input type="text" id="subject" name="subject" value="{{ old('subject', $template->subject) }}" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('subject') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Preview Text -->
                    <div>
                        <label for="preview_text" class="block text-sm font-medium text-gray-700">Preview Text</label>
                        <input type="text" id="preview_text" name="preview_text" value="{{ old('preview_text', $template->preview_text) }}" maxlength="150" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('preview_text') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- HTML Body (TinyMCE) -->
                    <div>
                        <label for="html_body" class="block text-sm font-medium text-gray-700">Email Body (HTML)</label>
                        <textarea id="html_body" name="html_body" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('html_body', $template->html_body) }}</textarea>
                        @error('html_body') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                        <select id="status" name="status" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="draft" {{ old('status', $template->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $template->status) === 'published' ? 'selected' : '' }}>Published</option>
                            <option value="archived" {{ old('status', $template->status) === 'archived' ? 'selected' : '' }}>Archived</option>
                        </select>
                        @error('status') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="mt-8 flex gap-4 border-t border-gray-200 pt-6">
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.custom-templates.index') }}" class="px-6 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 font-medium">
                        Back
                    </a>
                </div>
            </form>

            <!-- Test Email Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Send Test Email</h3>
                <form method="POST" action="{{ route('admin.custom-templates.test', $template) }}" class="space-y-4">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="test_email" class="block text-sm font-medium text-gray-700">Email Address</label>
                            <input type="email" id="test_email" name="test_email" value="{{ old('test_email', auth()->user()->email) }}" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @error('test_email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="dataset" class="block text-sm font-medium text-gray-700">Sample Data</label>
                            <select name="dataset" id="dataset" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="generic">Generic</option>
                                <option value="seller">Seller</option>
                                <option value="buyer">Buyer</option>
                                <option value="developer">Developer</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        Send Test Email
                    </button>
                </form>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- Available Placeholders -->
            <div class="bg-gray-50 rounded-lg p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Available Placeholders</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex items-center gap-2">
                        <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono text-xs">@{{nombre}}</code>
                        <span class="text-gray-600">Name</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono text-xs">@{{email}}</code>
                        <span class="text-gray-600">Email</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono text-xs">@{{colonia}}</code>
                        <span class="text-gray-600">Area</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono text-xs">@{{precio}}</code>
                        <span class="text-gray-600">Price</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <code class="bg-white px-2 py-1 rounded text-blue-600 font-mono text-xs">@{{fecha}}</code>
                        <span class="text-gray-600">Date</span>
                    </div>
                </div>
            </div>

            <!-- Assignments Summary -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Assignments</h3>
                <p class="text-sm text-gray-600 mb-4">This template is assigned to <strong>{{ $assignments->count() }}</strong> trigger{{ $assignments->count() !== 1 ? 's' : '' }}</p>

                @if($assignments->count() > 0)
                    <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                        @foreach($assignments as $assignment)
                            <div class="flex items-center justify-between text-sm p-3 bg-gray-50 rounded">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $assignment->trigger_name }}</p>
                                    <p class="text-gray-600 text-xs">{{ ucfirst(str_replace('_', ' ', $assignment->trigger_type)) }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <form method="PATCH" action="{{ route('admin.custom-templates.assignments.toggle', [$template, $assignment]) }}" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs {{ $assignment->is_active ? 'text-green-600 hover:text-green-900' : 'text-gray-600 hover:text-gray-900' }}">
                                            {{ $assignment->is_active ? 'Active' : 'Inactive' }}
                                        </button>
                                    </form>
                                    <form method="DELETE" action="{{ route('admin.custom-templates.assignments.destroy', [$template, $assignment]) }}" class="inline" onsubmit="return confirm('Remove assignment?')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-xs">Remove</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-gray-500 mb-4">No assignments yet</p>
                @endif

                <button type="button" onclick="openAssignmentModal()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                    + Add Assignment
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Modal -->
<div id="assignmentModal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assign to Event</h3>

        <form method="POST" action="{{ route('admin.custom-templates.assignments.store', $template) }}" class="space-y-4">
            @csrf

            <div>
                <label for="trigger_type" class="block text-sm font-medium text-gray-700">Event Type</label>
                <select id="trigger_type" name="trigger_type" required onchange="updateTriggerOptions()" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select event type...</option>
                    <option value="event">Event</option>
                    <option value="form_submission">Form Submission</option>
                    <option value="user_action">User Action</option>
                </select>
            </div>

            <div>
                <label for="trigger_name" class="block text-sm font-medium text-gray-700">Event Name</label>
                <select id="trigger_name" name="trigger_name" required class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Select an event...</option>
                </select>
            </div>

            <div class="flex gap-4 pt-4">
                <button type="submit" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                    Assign
                </button>
                <button type="button" onclick="closeAssignmentModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-900 rounded-lg hover:bg-gray-300 font-medium">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const htmlBody = document.getElementById('html_body');
htmlBody.style.fontFamily = 'monospace';
htmlBody.style.fontSize = '12px';

function openAssignmentModal() {
    document.getElementById('assignmentModal').classList.remove('hidden');
}

function closeAssignmentModal() {
    document.getElementById('assignmentModal').classList.add('hidden');
}

function updateTriggerOptions() {
    const triggerType = document.getElementById('trigger_type').value;
    const triggerName = document.getElementById('trigger_name');
    const triggers = {
        'event': {
            'FormSubmitted': 'Formulario enviado',
            'UserCreated': 'Usuario creado',
            'UserActivated': 'Usuario activado',
            'LeadAssigned': 'Lead asignado',
        },
        'form_submission': {
            'seller_valuation': 'Solicitud de valuación',
            'buyer_search': 'Búsqueda de comprador',
            'contact_form': 'Formulario de contacto',
            'developer_brief': 'Briefing de desarrollador',
        },
        'user_action': {
            'first_login': 'Primer acceso',
            'profile_updated': 'Perfil actualizado',
            'password_changed': 'Contraseña cambiada',
        },
    };

    triggerName.innerHTML = '<option value="">Select an event...</option>';
    if (triggers[triggerType]) {
        Object.entries(triggers[triggerType]).forEach(([key, label]) => {
            const option = document.createElement('option');
            option.value = key;
            option.textContent = label;
            triggerName.appendChild(option);
        });
    }
}

// Close modal when clicking outside
document.getElementById('assignmentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignmentModal();
    }
});
</script>
@endsection
