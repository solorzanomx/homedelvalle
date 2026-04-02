@* =============================================
   User/Details.vbhtml - Ver detalle de un usuario
   ============================================= *@
@ModelType CRM_VBNet.Models.User
@Code
    ViewBag.Title = "Detalle de Usuario"
    ViewBag.ActivePage = "Users"
    Layout = "~/Views/Shared/_Layout.vbhtml"
End Code

<div class="page-header">
    <div>
        <h2>Detalle de Usuario</h2>
    </div>
    <a href="@Url.Action("Index", "User")" class="btn btn-outline">
        <i data-feather="arrow-left"></i>
        Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="detail-header">
            <div class="user-avatar large">
                @If Not String.IsNullOrEmpty(Model.PhotoPath) Then
                    @<img src="@Model.PhotoUrl" alt="" />
                Else
                    @<div class="avatar-placeholder large">
                        @Model.FirstName.Substring(0, 1).ToUpper()@Model.LastName.Substring(0, 1).ToUpper()
                    </div>
                End If
            </div>
            <div>
                <h3>@Model.FullName</h3>
                <p class="text-muted">@@@Model.Username &middot; @Model.Role.Name</p>
            </div>
        </div>

        <div class="detail-grid">
            <div class="detail-item">
                <label>Email</label>
                <span>@Model.Email</span>
            </div>
            <div class="detail-item">
                <label>Teléfono</label>
                <span>@If Not String.IsNullOrEmpty(Model.Phone) Then @Model.Phone Else @:—
                End If</span>
            </div>
            <div class="detail-item">
                <label>WhatsApp</label>
                <span>@If Not String.IsNullOrEmpty(Model.WhatsApp) Then @Model.WhatsApp Else @:—
                End If</span>
            </div>
            <div class="detail-item">
                <label>Dirección</label>
                <span>@If Not String.IsNullOrEmpty(Model.Address) Then @Model.Address Else @:—
                End If</span>
            </div>
            <div class="detail-item">
                <label>Estado</label>
                <span class="badge @If Model.IsActive Then @:badge-active Else @:badge-inactive End If">
                    @If Model.IsActive Then @:Activo Else @:Inactivo End If
                </span>
            </div>
            <div class="detail-item">
                <label>Permisos del Rol</label>
                <div class="permission-badges">
                    @If Model.Role.CanRead Then @<span class="badge badge-perm">Lectura</span> End If
                    @If Model.Role.CanWrite Then @<span class="badge badge-perm">Escritura</span> End If
                    @If Model.Role.CanDelete Then @<span class="badge badge-perm">Eliminación</span> End If
                </div>
            </div>
        </div>
    </div>
</div>

@Section Scripts
    <script>feather.replace();</script>
End Section
