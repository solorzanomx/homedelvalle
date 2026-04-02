' =============================================
' AccountController.vb
' Maneja login, logout y autenticación
' =============================================
Imports System.Web.Mvc
Imports CRM_VBNet.Models
Imports CRM_VBNet.Helpers

Namespace CRM_VBNet.Controllers

    Public Class AccountController
        Inherits Controller

        Private ReadOnly _db As DatabaseContext

        Public Sub New()
            _db = New DatabaseContext(System.Configuration.ConfigurationManager.ConnectionStrings("CRMConnection").ConnectionString)
        End Sub

        ' GET: /Account/Login
        <AllowAnonymous>
        Function Login() As ActionResult
            ' Si ya está autenticado, redirigir al dashboard
            If SessionHelper.IsAuthenticated() Then
                Return RedirectToAction("Index", "Home")
            End If
            Return View()
        End Function

        ' POST: /Account/Login
        <HttpPost>
        <AllowAnonymous>
        <ValidateAntiForgeryToken>
        Function Login(model As LoginViewModel) As ActionResult
            If String.IsNullOrEmpty(model.Username) OrElse String.IsNullOrEmpty(model.Password) Then
                ViewBag.Error = "Ingrese usuario y contraseña."
                Return View(model)
            End If

            Dim user = _db.Authenticate(model.Username, model.Password)

            If user Is Nothing Then
                ViewBag.Error = "Usuario o contraseña incorrectos."
                Return View(model)
            End If

            ' Iniciar sesión
            SessionHelper.Login(user)

            ' Log de auditoría
            _db.LogAction(user.Id, "LOGIN", "Users", user.Id, "Inicio de sesión exitoso")

            Return RedirectToAction("Index", "Home")
        End Function

        ' GET: /Account/Logout
        Function Logout() As ActionResult
            Dim userId = SessionHelper.GetCurrentUserId()
            If userId > 0 Then
                _db.LogAction(userId, "LOGOUT", "Users", userId, "Cierre de sesión")
            End If

            SessionHelper.Logout()
            Return RedirectToAction("Login")
        End Function

    End Class

End Namespace
