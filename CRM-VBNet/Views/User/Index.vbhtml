@* =============================================
   User/Index.vbhtml - Lista de usuarios (CRUD)
   Muestra tabla con todos los usuarios
   Botones condicionados por permisos
   ============================================= *@
@ModelType List(Of CRM_VBNet.Models.User)
@Code
    ViewBag.Title = "Usuarios"
    ViewBag.ActivePage = "Users"
    Layout = "~/Views/Shared/_Layout.vbhtml"
End Code

<div class="page-header">
    <div>
        <h2>Gestión de Usuarios</h2>
        <p class="text-muted">Administra los usuarios de la plataforma</p>
    </div>
    @If CBool(ViewBag.CanWrite) Then
        @<a href="@Url.Action("Create", "User")" class="btn btn-primary">
            <i data-feather="user-plus"></i>
            Nuevo Usuario
        </a>
    End If
</div>

<div class="card">
    <div class="card-body no-padding">
        @If Model IsNot Nothing AndAlso Model.Count > 0 Then
            @<div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @For Each user In Model
                            @<tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar small">
                                            @If Not String.IsNullOrEmpty(user.PhotoPath) Then
                                                @<img src="@user.PhotoUrl" alt="" />
                                            Else
                                                @<div class="avatar-placeholder small">
                                                    @user.FirstName.Substring(0, 1).ToUpper()
                                                </div>
                                            End If
                                        </div>
                                        <div>
                                            <div class="fw-500">@user.FullName</div>
                                            <div class="text-muted text-sm">@@@user.Username</div>
                                        </div>
                                    </div>
                                </td>
                                <td>@user.Email</td>
                                <td>
                                    <span class="badge badge-role">@user.Role.Name</span>
                                </td>
                                <td>
                                    <span class="badge @If user.IsActive Then @:badge-active
                                                        Else @:badge-inactive
                                    End If">
                                        @If user.IsActive Then @:Activo Else @:Inactivo
                                    End If
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="@Url.Action("Details", "User", New With {.id = user.Id})"
                                           class="btn btn-sm btn-icon" title="Ver detalle">
                                            <i data-feather="eye"></i>
                                        </a>
                                        @If CBool(ViewBag.CanWrite) Then
                                            @<a href="@Url.Action("Edit", "User", New With {.id = user.Id})"
                                               class="btn btn-sm btn-icon btn-edit" title="Editar">
                                                <i data-feather="edit-2"></i>
                                            </a>
                                        End If
                                        @If CBool(ViewBag.CanDelete) Then
                                            @Using Html.BeginForm("Delete", "User", New With {.id = user.Id}, FormMethod.Post, New With {.style = "display:inline"})
                                                @Html.AntiForgeryToken()
                                                @<button type="submit" class="btn btn-sm btn-icon btn-delete"
                                                         title="Desactivar"
                                                         onclick="return confirm('¿Está seguro de desactivar este usuario?')">
                                                    <i data-feather="trash-2"></i>
                                                </button>
                                            End Using
                                        End If
                                    </div>
                                </td>
                            </tr>
                        Next
                    </tbody>
                </table>
            </div>
        Else
            @<div class="empty-state">
                <i data-feather="users"></i>
                <h3>Sin usuarios</h3>
                <p>No se encontraron usuarios registrados.</p>
            </div>
        End If
    </div>
</div>

@Section Scripts
    <script>feather.replace();</script>
End Section
