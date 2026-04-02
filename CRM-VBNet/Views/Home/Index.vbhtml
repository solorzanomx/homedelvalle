@* =============================================
   Dashboard - Index.vbhtml
   Panel principal con estadísticas y accesos rápidos
   ============================================= *@
@Code
    ViewBag.Title = "Dashboard"
    ViewBag.ActivePage = "Dashboard"
    Layout = "~/Views/Shared/_Layout.vbhtml"
    Dim user = TryCast(ViewBag.User, CRM_VBNet.Models.User)
End Code

<div class="dashboard">
    @* Saludo *@
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>Bienvenido, @If user IsNot Nothing Then @user.FirstName End If</h2>
            <p>Aquí tienes un resumen de tu plataforma CRM.</p>
        </div>
        <div class="welcome-date">
            <i data-feather="calendar"></i>
            <span>@DateTime.Now.ToString("dddd, dd MMMM yyyy")</span>
        </div>
    </div>

    @* Tarjetas de estadísticas *@
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i data-feather="users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">@ViewBag.TotalUsers</span>
                <span class="stat-label">Usuarios Totales</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i data-feather="user-check"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">@ViewBag.ActiveUsers</span>
                <span class="stat-label">Usuarios Activos</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-purple">
                <i data-feather="briefcase"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">0</span>
                <span class="stat-label">Contactos</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-orange">
                <i data-feather="trending-up"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value">0</span>
                <span class="stat-label">Oportunidades</span>
            </div>
        </div>
    </div>

    @* Sección de contenido *@
    <div class="dashboard-grid">
        @* Usuarios recientes *@
        <div class="card">
            <div class="card-header">
                <h3>Usuarios Recientes</h3>
                @If CRM_VBNet.Helpers.PermissionHelper.CanRead() Then
                    @<a href="@Url.Action("Index", "User")" class="btn btn-sm btn-outline">Ver todos</a>
                End If
            </div>
            <div class="card-body">
                @Code
                    Dim recentUsers = TryCast(ViewBag.RecentUsers, List(Of CRM_VBNet.Models.User))
                End Code
                @If recentUsers IsNot Nothing AndAlso recentUsers.Count > 0 Then
                    @<div class="user-list">
                        @For Each u In recentUsers
                            @<div class="user-list-item">
                                <div class="user-avatar small">
                                    @If Not String.IsNullOrEmpty(u.PhotoPath) Then
                                        @<img src="@u.PhotoUrl" alt="" />
                                    Else
                                        @<div class="avatar-placeholder small">@u.FirstName.Substring(0, 1).ToUpper()</div>
                                    End If
                                </div>
                                <div class="user-list-info">
                                    <span class="name">@u.FullName</span>
                                    <span class="email">@u.Email</span>
                                </div>
                                <span class="badge @If u.IsActive Then @:badge-active
                                                    Else @:badge-inactive
                                End If">
                                    @If u.IsActive Then @:Activo Else @:Inactivo
                                End If
                                </span>
                            </div>
                        Next
                    </div>
                Else
                    @<p class="text-muted">No hay usuarios registrados aún.</p>
                End If
            </div>
        </div>

        @* Accesos rápidos *@
        <div class="card">
            <div class="card-header">
                <h3>Accesos Rápidos</h3>
            </div>
            <div class="card-body">
                <div class="quick-actions">
                    @If CRM_VBNet.Helpers.PermissionHelper.CanWrite() Then
                        @<a href="@Url.Action("Create", "User")" class="quick-action-btn">
                            <i data-feather="user-plus"></i>
                            <span>Nuevo Usuario</span>
                        </a>
                    End If
                    <a href="@Url.Action("Index", "Profile")" class="quick-action-btn">
                        <i data-feather="edit-3"></i>
                        <span>Mi Perfil</span>
                    </a>
                    @If CRM_VBNet.Helpers.PermissionHelper.IsAdmin() Then
                        @<a href="@Url.Action("Index", "Settings")" class="quick-action-btn">
                            <i data-feather="settings"></i>
                            <span>Configuración</span>
                        </a>
                    End If
                </div>
            </div>
        </div>
    </div>
</div>

@Section Scripts
    <script>feather.replace();</script>
End Section
