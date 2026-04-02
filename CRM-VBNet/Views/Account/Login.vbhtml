@* =============================================
   Login.vbhtml - Página de inicio de sesión
   Vista independiente sin sidebar
   ============================================= *@
@Code
    Layout = Nothing
End Code

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión - CRM</title>
    <link rel="stylesheet" href="~/Content/css/site.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <script src="https://unpkg.com/feather-icons"></script>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">
                    <i data-feather="hexagon"></i>
                </div>
                <h1>CRM Platform</h1>
                <p>Ingrese sus credenciales para continuar</p>
            </div>

            @If ViewBag.Error IsNot Nothing Then
                @<div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <i data-feather="alert-circle"></i>
                    <span>@ViewBag.Error</span>
                </div>
            End If

            @Using Html.BeginForm("Login", "Account", FormMethod.Post, New With {.class = "login-form"})
                @Html.AntiForgeryToken()

                @<div class="form-group">
                    <label for="Username">
                        <i data-feather="user"></i>
                        Usuario o Email
                    </label>
                    <input type="text" id="Username" name="Username" class="form-control"
                           placeholder="Ingrese su usuario o email" required autofocus />
                </div>

                @<div class="form-group">
                    <label for="Password">
                        <i data-feather="lock"></i>
                        Contraseña
                    </label>
                    <div class="password-wrapper">
                        <input type="password" id="Password" name="Password" class="form-control"
                               placeholder="Ingrese su contraseña" required />
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i data-feather="eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                @<div class="form-group checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="RememberMe" value="true" />
                        <span>Recordarme</span>
                    </label>
                </div>

                @<button type="submit" class="btn btn-primary btn-block">
                    <i data-feather="log-in"></i>
                    Iniciar Sesión
                </button>
            End Using
        </div>

        <div class="login-footer">
            <p>Usuario por defecto: <strong>admin</strong> / <strong>Admin123!</strong></p>
        </div>
    </div>

    <script>
        feather.replace();

        function togglePassword() {
            var input = document.getElementById('Password');
            var icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('data-feather', 'eye-off');
            } else {
                input.type = 'password';
                icon.setAttribute('data-feather', 'eye');
            }
            feather.replace();
        }
    </script>
</body>
</html>
