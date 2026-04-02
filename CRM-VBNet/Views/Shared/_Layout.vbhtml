@* =============================================
   _Layout.vbhtml
   Layout principal con sidebar CRM estilo moderno
   El sidebar es fijo a la izquierda
   El contenido se desplaza a la derecha
   ============================================= *@
@Code
    Dim currentUser = CRM_VBNet.Helpers.SessionHelper.GetCurrentUser()
    Dim currentRole = CRM_VBNet.Helpers.SessionHelper.GetCurrentRole()
    Dim siteSettings = TryCast(ViewBag.SiteSettings, CRM_VBNet.Models.SiteSettingsViewModel)
    Dim siteName = If(siteSettings IsNot Nothing AndAlso Not String.IsNullOrEmpty(siteSettings.SiteName), siteSettings.SiteName, "CRM Platform")
    Dim logoPath = If(siteSettings IsNot Nothing AndAlso Not String.IsNullOrEmpty(siteSettings.LogoPath), siteSettings.LogoPath, "")
    Dim primaryColor = If(siteSettings IsNot Nothing AndAlso Not String.IsNullOrEmpty(siteSettings.PrimaryColor), siteSettings.PrimaryColor, "#4f46e5")
End Code

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>@ViewBag.Title - @siteName</title>
    <link rel="stylesheet" href="~/Content/css/site.css" />
    @* Google Fonts - Inter para un look moderno *@
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    @* Iconos Feather vía CDN *@
    <script src="https://unpkg.com/feather-icons"></script>
    <style>
        :root {
            --primary-color: @primaryColor;
            --primary-hover: color-mix(in srgb, @primaryColor 85%, black);
        }
    </style>
</head>
<body>
    <div class="app-container">
        @* ========== SIDEBAR ========== *@
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                @If Not String.IsNullOrEmpty(logoPath) Then
                    @<img src="@logoPath" alt="Logo" class="sidebar-logo" />
                Else
                    @<div class="sidebar-brand">
                        <div class="brand-icon">
                            <i data-feather="hexagon"></i>
                        </div>
                        <span class="brand-text">@siteName</span>
                    </div>
                End If
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">PRINCIPAL</span>
                    <a href="@Url.Action("Index", "Home")" class="nav-item @If ViewBag.ActivePage = "Dashboard" Then @:active
End If">
                        <i data-feather="grid"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="@Url.Action("Index", "User")" class="nav-item @If ViewBag.ActivePage = "Users" Then @:active
End If">
                        <i data-feather="users"></i>
                        <span>Usuarios</span>
                    </a>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">CRM</span>
                    <a href="#" class="nav-item">
                        <i data-feather="briefcase"></i>
                        <span>Contactos</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i data-feather="trending-up"></i>
                        <span>Oportunidades</span>
                    </a>
                    <a href="#" class="nav-item">
                        <i data-feather="file-text"></i>
                        <span>Reportes</span>
                    </a>
                </div>

                @If CRM_VBNet.Helpers.PermissionHelper.IsAdmin() Then
                    @<div class="nav-section">
                        <span class="nav-section-title">ADMINISTRACIÓN</span>
                        <a href="@Url.Action("Index", "Settings")" class="nav-item @If ViewBag.ActivePage = "Settings" Then @:active
End If">
                            <i data-feather="settings"></i>
                            <span>Configuración</span>
                        </a>
                    </div>
                End If
            </nav>

            @* Perfil del usuario en la parte inferior del sidebar *@
            <div class="sidebar-footer">
                <a href="@Url.Action("Index", "Profile")" class="user-card">
                    <div class="user-avatar">
                        @If currentUser IsNot Nothing AndAlso Not String.IsNullOrEmpty(currentUser.PhotoPath) Then
                            @<img src="@currentUser.PhotoUrl" alt="Avatar" />
                        Else
                            @<div class="avatar-placeholder">
                                @If currentUser IsNot Nothing Then
                                    @currentUser.FirstName.Substring(0, 1).ToUpper()@currentUser.LastName.Substring(0, 1).ToUpper()
                                End If
                            </div>
                        End If
                    </div>
                    <div class="user-info">
                        <span class="user-name">@If currentUser IsNot Nothing Then @currentUser.FullName End If</span>
                        <span class="user-role">@If currentRole IsNot Nothing Then @currentRole.Name End If</span>
                    </div>
                </a>
                <a href="@Url.Action("Logout", "Account")" class="logout-btn" title="Cerrar sesión">
                    <i data-feather="log-out"></i>
                </a>
            </div>
        </aside>

        @* ========== CONTENIDO PRINCIPAL ========== *@
        <main class="main-content">
            @* Barra superior móvil *@
            <div class="topbar">
                <button class="sidebar-toggle" onclick="toggleSidebar()">
                    <i data-feather="menu"></i>
                </button>
                <h1 class="page-title">@ViewBag.Title</h1>
                <div class="topbar-actions">
                    <a href="@Url.Action("Index", "Profile")" class="topbar-avatar">
                        @If currentUser IsNot Nothing AndAlso Not String.IsNullOrEmpty(currentUser.PhotoPath) Then
                            @<img src="@currentUser.PhotoUrl" alt="Avatar" />
                        Else
                            @<span>@If currentUser IsNot Nothing Then @currentUser.FirstName.Substring(0, 1).ToUpper() End If</span>
                        End If
                    </a>
                </div>
            </div>

            @* Mensajes de alerta *@
            @If TempData("Success") IsNot Nothing Then
                @<div class="alert alert-success" id="alertSuccess">
                    <i data-feather="check-circle"></i>
                    <span>@TempData("Success")</span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            End If

            @If TempData("Error") IsNot Nothing Then
                @<div class="alert alert-error" id="alertError">
                    <i data-feather="alert-circle"></i>
                    <span>@TempData("Error")</span>
                    <button class="alert-close" onclick="this.parentElement.remove()">×</button>
                </div>
            End If

            <div class="content-body">
                @RenderBody()
            </div>
        </main>
    </div>

    @* Overlay para sidebar móvil *@
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <script src="~/Scripts/site.js"></script>
    <script>
        // Inicializar iconos Feather
        feather.replace();

        // Auto-ocultar alertas después de 5 segundos
        setTimeout(function () {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function (alert) {
                alert.style.opacity = '0';
                setTimeout(function () { alert.remove(); }, 300);
            });
        }, 5000);
    </script>
    @If IsSectionDefined("Scripts") Then
        @RenderSection("Scripts")
    End If
</body>
</html>
