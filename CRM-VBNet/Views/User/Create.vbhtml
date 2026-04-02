@* =============================================
   User/Create.vbhtml - Formulario para crear usuario
   ============================================= *@
@ModelType CRM_VBNet.Models.UserViewModel
@Code
    ViewBag.Title = "Nuevo Usuario"
    ViewBag.ActivePage = "Users"
    Layout = "~/Views/Shared/_Layout.vbhtml"
    Dim roles = TryCast(ViewBag.Roles, List(Of CRM_VBNet.Models.Role))
End Code

<div class="page-header">
    <div>
        <h2>Crear Usuario</h2>
        <p class="text-muted">Registrar un nuevo usuario en el sistema</p>
    </div>
    <a href="@Url.Action("Index", "User")" class="btn btn-outline">
        <i data-feather="arrow-left"></i>
        Volver
    </a>
</div>

<div class="card form-card">
    <div class="card-body">
        @If ViewBag.Error IsNot Nothing Then
            @<div class="alert alert-error">
                <i data-feather="alert-circle"></i>
                <span>@ViewBag.Error</span>
            </div>
        End If

        @Using Html.BeginForm("Create", "User", FormMethod.Post)
            @Html.AntiForgeryToken()

            @<div class="form-grid">
                <div class="form-group">
                    <label for="Username">Usuario <span class="required">*</span></label>
                    <input type="text" id="Username" name="Username" class="form-control"
                           value="@Model.Username" required />
                </div>

                <div class="form-group">
                    <label for="Email">Email <span class="required">*</span></label>
                    <input type="email" id="Email" name="Email" class="form-control"
                           value="@Model.Email" required />
                </div>

                <div class="form-group">
                    <label for="Password">Contraseña <span class="required">*</span></label>
                    <input type="password" id="Password" name="Password" class="form-control"
                           required minlength="6" />
                </div>

                <div class="form-group">
                    <label for="RoleId">Rol <span class="required">*</span></label>
                    <select id="RoleId" name="RoleId" class="form-control" required>
                        <option value="">Seleccione un rol...</option>
                        @If roles IsNot Nothing Then
                            @For Each role In roles
                                @<option value="@role.Id">@role.Name - @role.Description</option>
                            Next
                        End If
                    </select>
                </div>

                <div class="form-group">
                    <label for="FirstName">Nombre <span class="required">*</span></label>
                    <input type="text" id="FirstName" name="FirstName" class="form-control"
                           value="@Model.FirstName" required />
                </div>

                <div class="form-group">
                    <label for="LastName">Apellido <span class="required">*</span></label>
                    <input type="text" id="LastName" name="LastName" class="form-control"
                           value="@Model.LastName" required />
                </div>

                <div class="form-group">
                    <label for="Phone">Teléfono</label>
                    <input type="tel" id="Phone" name="Phone" class="form-control"
                           value="@Model.Phone" />
                </div>
            </div>

            @<div class="form-actions">
                <a href="@Url.Action("Index", "User")" class="btn btn-outline">Cancelar</a>
                <button type="submit" class="btn btn-primary">
                    <i data-feather="save"></i>
                    Crear Usuario
                </button>
            </div>
        End Using
    </div>
</div>

@Section Scripts
    <script>feather.replace();</script>
End Section
