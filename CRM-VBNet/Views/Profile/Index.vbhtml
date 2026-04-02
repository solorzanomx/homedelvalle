@* =============================================
   Profile/Index.vbhtml - Perfil del usuario actual
   Incluye edición de datos y foto de perfil circular
   con click-to-upload estilo Facebook
   ============================================= *@
@ModelType CRM_VBNet.Models.ProfileViewModel
@Code
    ViewBag.Title = "Mi Perfil"
    ViewBag.ActivePage = "Profile"
    Layout = "~/Views/Shared/_Layout.vbhtml"
    Dim user = TryCast(ViewBag.User, CRM_VBNet.Models.User)
End Code

<div class="profile-page">
    @* ========== SECCIÓN DE FOTO DE PERFIL ========== *@
    <div class="card profile-photo-card">
        <div class="card-body text-center">
            <div class="profile-photo-wrapper">
                @* Avatar circular con overlay al hacer hover *@
                <div class="profile-avatar" id="profileAvatar" onclick="document.getElementById('photoInput').click()">
                    @If Not String.IsNullOrEmpty(Model.PhotoPath) AndAlso Model.PhotoPath <> "/Content/images/default-avatar.png" Then
                        @<img src="@Model.PhotoPath" alt="Foto de perfil" id="avatarImage" />
                    Else
                        @<div class="avatar-placeholder xlarge" id="avatarPlaceholder">
                            @If user IsNot Nothing Then
                                @user.FirstName.Substring(0, 1).ToUpper()@user.LastName.Substring(0, 1).ToUpper()
                            End If
                        </div>
                    End If
                    @* Overlay que aparece al hacer hover (estilo Facebook) *@
                    <div class="photo-overlay">
                        <i data-feather="camera"></i>
                        <span>Cambiar foto</span>
                    </div>
                </div>
            </div>

            <h3 class="profile-name">@If user IsNot Nothing Then @user.FullName End If</h3>
            <p class="text-muted">@If user IsNot Nothing Then @user.Role.Name End If</p>

            @* Formulario oculto para subir foto *@
            @Using Html.BeginForm("UploadPhoto", "Profile", FormMethod.Post,
                New With {.enctype = "multipart/form-data", .id = "photoForm"})
                @Html.AntiForgeryToken()
                @<input type="file" id="photoInput" name="photo" accept="image/*"
                        style="display:none" onchange="previewAndSubmit(this)" />
            End Using
        </div>
    </div>

    @* ========== FORMULARIO DE DATOS PERSONALES ========== *@
    <div class="card">
        <div class="card-header">
            <h3>Información Personal</h3>
        </div>
        <div class="card-body">
            @If ViewBag.Error IsNot Nothing Then
                @<div class="alert alert-error">
                    <i data-feather="alert-circle"></i>
                    <span>@ViewBag.Error</span>
                </div>
            End If

            @Using Html.BeginForm("Update", "Profile", FormMethod.Post)
                @Html.AntiForgeryToken()

                @<div class="form-grid">
                    <div class="form-group">
                        <label for="FirstName">
                            <i data-feather="user"></i> Nombre <span class="required">*</span>
                        </label>
                        <input type="text" id="FirstName" name="FirstName" class="form-control"
                               value="@Model.FirstName" required />
                    </div>

                    <div class="form-group">
                        <label for="LastName">
                            <i data-feather="user"></i> Apellido
                        </label>
                        <input type="text" id="LastName" name="LastName" class="form-control"
                               value="@Model.LastName" />
                    </div>

                    <div class="form-group">
                        <label for="Email">
                            <i data-feather="mail"></i> Email <span class="required">*</span>
                        </label>
                        <input type="email" id="Email" name="Email" class="form-control"
                               value="@Model.Email" required />
                    </div>

                    <div class="form-group">
                        <label for="Phone">
                            <i data-feather="phone"></i> Teléfono
                        </label>
                        <input type="tel" id="Phone" name="Phone" class="form-control"
                               value="@Model.Phone" />
                    </div>

                    <div class="form-group">
                        <label for="WhatsApp">
                            <i data-feather="message-circle"></i> WhatsApp
                        </label>
                        <input type="tel" id="WhatsApp" name="WhatsApp" class="form-control"
                               value="@Model.WhatsApp" placeholder="+1 234 567 8900" />
                    </div>

                    <div class="form-group full-width">
                        <label for="Address">
                            <i data-feather="map-pin"></i> Dirección
                        </label>
                        <textarea id="Address" name="Address" class="form-control" rows="2">@Model.Address</textarea>
                    </div>
                </div>

                @<div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Guardar Cambios
                    </button>
                </div>
            End Using
        </div>
    </div>

    @* ========== CAMBIAR CONTRASEÑA ========== *@
    <div class="card">
        <div class="card-header">
            <h3>Cambiar Contraseña</h3>
        </div>
        <div class="card-body">
            @Using Html.BeginForm("ChangePassword", "Profile", FormMethod.Post)
                @Html.AntiForgeryToken()

                @<div class="form-grid">
                    <div class="form-group">
                        <label for="currentPassword">Contraseña Actual</label>
                        <input type="password" id="currentPassword" name="currentPassword"
                               class="form-control" required />
                    </div>
                    <div class="form-group">
                        <label for="newPassword">Nueva Contraseña</label>
                        <input type="password" id="newPassword" name="newPassword"
                               class="form-control" required minlength="6" />
                    </div>
                    <div class="form-group">
                        <label for="confirmPassword">Confirmar Contraseña</label>
                        <input type="password" id="confirmPassword" name="confirmPassword"
                               class="form-control" required minlength="6" />
                    </div>
                </div>

                @<div class="form-actions">
                    <button type="submit" class="btn btn-outline">
                        <i data-feather="lock"></i>
                        Cambiar Contraseña
                    </button>
                </div>
            End Using
        </div>
    </div>
</div>

@Section Scripts
    <script>
        feather.replace();

        // Preview de imagen y envío automático del formulario
        function previewAndSubmit(input) {
            if (input.files && input.files[0]) {
                var file = input.files[0];

                // Validar tipo
                if (!file.type.match('image.*')) {
                    alert('Solo se permiten imágenes.');
                    return;
                }

                // Validar tamaño (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('La imagen no debe superar 5MB.');
                    return;
                }

                // Preview inmediato
                var reader = new FileReader();
                reader.onload = function (e) {
                    var avatar = document.getElementById('profileAvatar');
                    var existingImg = document.getElementById('avatarImage');
                    var placeholder = document.getElementById('avatarPlaceholder');

                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }

                    if (existingImg) {
                        existingImg.src = e.target.result;
                    } else {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Foto de perfil';
                        img.id = 'avatarImage';
                        avatar.insertBefore(img, avatar.firstChild);
                    }
                };
                reader.readAsDataURL(file);

                // Enviar formulario
                document.getElementById('photoForm').submit();
            }
        }
    </script>
End Section
