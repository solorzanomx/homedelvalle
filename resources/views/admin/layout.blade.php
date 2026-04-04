<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Administrativo')</title>
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Instrument Sans', system-ui, sans-serif;
            background: #f5f5f5;
            color: #333;
        }

        .admin-container {
            display: flex;
            height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #3B82C4 0%, #1E3A5F 100%);
            color: white;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .sidebar-user {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            padding: 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid white;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.3s;
        }

        .user-avatar:hover {
            transform: scale(1.05);
        }

        .user-info {
            text-align: center;
            color: white;
        }

        .user-name {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-role {
            font-size: 12px;
            opacity: 0.8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
        }

        .nav-item {
            display: block;
            padding: 12px 20px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
            font-size: 14px;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: white;
        }

        .nav-divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 15px 0;
        }

        .sidebar-footer {
            padding: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .logout-btn {
            width: 100%;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .header-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }

        .content {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
        }

        /* Cards */
        .card {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }

        /* Table */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .table thead {
            background: #f9f9f9;
        }

        .table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            font-size: 12px;
            color: #666;
            border-bottom: 2px solid #e0e0e0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e0e0e0;
        }

        .table tbody tr:hover {
            background: #f5f5f5;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 6px 12px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .btn-view {
            background: #3B82C4;
            color: white;
        }

        .btn-view:hover {
            background: #1E3A5F;
        }

        .btn-edit {
            background: #16a34a;
            color: white;
        }

        .btn-edit:hover {
            background: #15803d;
        }

        .btn-delete {
            background: #dc2626;
            color: white;
        }

        .btn-delete:hover {
            background: #b91c1c;
        }

        .btn-disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-family: inherit;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: #3B82C4;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 100px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #3B82C4 0%, #1E3A5F 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        /* Alert Messages */
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* Avatar Section */
        .avatar-upload {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            padding: 30px;
            background: #f9f9f9;
            border-radius: 8px;
            border: 2px dashed #e0e0e0;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 30px;
        }

        .avatar-upload:hover {
            border-color: #3B82C4;
            background: #f5f5f5;
        }

        .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #3B82C4;
        }

        .avatar-upload-text {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }

        .avatar-upload-text strong {
            color: #333;
        }

        .avatar-upload-text small {
            color: #999;
            font-size: 12px;
        }

        #avatarInput {
            display: none;
        }

        /* Pagination */
        .pagination {
            display: flex;
            gap: 5px;
            margin-top: 20px;
            justify-content: center;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            text-decoration: none;
            color: #3B82C4;
            font-size: 14px;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #3B82C4;
            color: white;
        }

        .pagination .active {
            background: #3B82C4;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                max-height: 200px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .table {
                font-size: 12px;
            }

            .table th,
            .table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">🏢 Homedelvalle</div>
            </div>

            <div class="sidebar-user">
                @if(auth()->user()->avatar_path)
                    <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="Avatar" class="user-avatar" id="sidebarAvatar" data-avatar-img onclick="document.getElementById('avatarInput').click()">
                @else
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.2); border: 3px solid white; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 40px;" id="sidebarAvatar" data-avatar-placeholder onclick="document.getElementById('avatarInput').click()">
                        👤
                    </div>
                @endif
                <div class="user-info">
                    <div class="user-name">{{ auth()->user()->name }} {{ auth()->user()->last_name }}</div>
                    <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
                </div>
            </div>

            <input type="file" id="avatarInput" accept="image/*" style="display:none;" onchange="openCropper(this.files[0])">

            <nav class="sidebar-nav">
                <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">📊 Dashboard</a>
                <a href="{{ route('admin.users.index') }}" class="nav-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">👥 Usuarios</a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.users.permissions') }}" class="nav-item {{ request()->routeIs('admin.users.permissions') ? 'active' : '' }}">🔐 Permisos</a>
                @endif
                <a href="{{ route('admin.brokers') }}" class="nav-item {{ request()->routeIs('admin.brokers') ? 'active' : '' }}">🏠 Brokers</a>
                <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->routeIs('admin.settings') ? 'active' : '' }}">⚙️ Configuración</a>
            </nav>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">Cerrar Sesión</button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="header-title">@yield('title', 'Panel Administrativo')</h1>
            </div>

            <div class="content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if(session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <x-avatar-cropper :upload-url="route('admin.users.avatar', auth()->user())" />
</body>
</html>
