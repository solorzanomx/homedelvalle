@* =============================================
   Settings/Index.vbhtml - Configuración del sitio
   Solo accesible por administradores
   ============================================= *@
@ModelType CRM_VBNet.Models.SiteSettingsViewModel
@Code
    ViewBag.Title = "Configuración"
    ViewBag.ActivePage = "Settings"
    Layout = "~/Views/Shared/_Layout.vbhtml"
End Code

<div class="page-header">
    <div>
        <h2>Configuración del Sitio</h2>
        <p class="text-muted">Personaliza la apariencia y datos de la plataforma</p>
    </div>
</div>

<div class="settings-grid">
    @* ========== LOGOTIPO ========== *@
    <div class="card">
        <div class="card-header">
            <h3>Logotipo</h3>
        </div>
        <div class="card-body text-center">
            <div class="logo-preview">
                @If Not String.IsNullOrEmpty(Model.LogoPath) Then
                    @<img src="@Model.LogoPath" alt="Logo actual" id="logoPreview" />
                Else
                    @<div class="logo-placeholder" id="logoPlaceholder">
                        <i data-feather="image"></i>
                        <span>Sin logotipo</span>
                    </div>
                End If
            </div>

            @Using Html.BeginForm("UploadLogo", "Settings", FormMethod.Post,
                New With {.enctype = "multipart/form-data", .id = "logoForm"})
                @Html.AntiForgeryToken()
                @<div class="upload-area">
                    <input type="file" id="logoInput" name="logo" accept="image/*"
                           onchange="previewLogo(this)" />
                    <label for="logoInput" class="btn btn-outline">
                        <i data-feather="upload"></i>
                        @If Not String.IsNullOrEmpty(Model.LogoPath) Then @:Cambiar Logo Else @:Subir Logo
                        End If
                    </label>
                    <button type="submit" class="btn btn-primary" id="saveLogo" style="display:none">
                        <i data-feather="save"></i> Guardar Logo
                    </button>
                </div>
            End Using
        </div>
    </div>

    @* ========== INFORMACIÓN GENERAL ========== *@
    <div class="card">
        <div class="card-header">
            <h3>Información General</h3>
        </div>
        <div class="card-body">
            @Using Html.BeginForm("Update", "Settings", FormMethod.Post)
                @Html.AntiForgeryToken()

                @<div class="form-stack">
                    <div class="form-group">
                        <label for="SiteName">
                            <i data-feather="type"></i> Nombre del Sitio
                        </label>
                        <input type="text" id="SiteName" name="SiteName" class="form-control"
                               value="@Model.SiteName" placeholder="Nombre de tu empresa o CRM" />
                        <small class="form-hint">Este nombre se mostrará en el sidebar y la pestaña del navegador.</small>
                    </div>

                    <div class="form-group">
                        <label for="PrimaryColor">
                            <i data-feather="droplet"></i> Color Primario
                        </label>
                        <div class="color-picker-group">
                            <input type="color" id="PrimaryColor" name="PrimaryColor"
                                   value="@Model.PrimaryColor" class="color-picker" />
                            <input type="text" class="form-control" value="@Model.PrimaryColor"
                                   id="colorText" onchange="document.getElementById('PrimaryColor').value=this.value" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="CompanyEmail">
                            <i data-feather="mail"></i> Email de la Empresa
                        </label>
                        <input type="email" id="CompanyEmail" name="CompanyEmail" class="form-control"
                               value="@Model.CompanyEmail" />
                    </div>

                    <div class="form-group">
                        <label for="CompanyPhone">
                            <i data-feather="phone"></i> Teléfono de la Empresa
                        </label>
                        <input type="tel" id="CompanyPhone" name="CompanyPhone" class="form-control"
                               value="@Model.CompanyPhone" />
                    </div>
                </div>

                @<div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save"></i>
                        Guardar Configuración
                    </button>
                </div>
            End Using
        </div>
    </div>
</div>

@Section Scripts
    <script>
        feather.replace();

        // Sync del color picker con el campo de texto
        document.getElementById('PrimaryColor').addEventListener('input', function () {
            document.getElementById('colorText').value = this.value;
        });

        // Preview del logo
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var preview = document.getElementById('logoPreview');
                    var placeholder = document.getElementById('logoPlaceholder');

                    if (placeholder) placeholder.style.display = 'none';

                    if (preview) {
                        preview.src = e.target.result;
                    } else {
                        var img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Logo preview';
                        img.id = 'logoPreview';
                        document.querySelector('.logo-preview').appendChild(img);
                    }

                    document.getElementById('saveLogo').style.display = 'inline-flex';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
End Section
