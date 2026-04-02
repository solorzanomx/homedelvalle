' =============================================
' SettingsController.vb
' Panel de configuración general del sitio
' Solo accesible para Administradores
' =============================================
Imports System.Web.Mvc
Imports System.IO
Imports CRM_VBNet.Models
Imports CRM_VBNet.Helpers

Namespace CRM_VBNet.Controllers

    Public Class SettingsController
        Inherits Controller

        Private ReadOnly _db As DatabaseContext

        Public Sub New()
            _db = New DatabaseContext(System.Configuration.ConfigurationManager.ConnectionStrings("CRMConnection").ConnectionString)
        End Sub

        Protected Overrides Sub OnActionExecuting(filterContext As ActionExecutingContext)
            If Not SessionHelper.IsAuthenticated() Then
                filterContext.Result = RedirectToAction("Login", "Account")
                Return
            End If

            ' Solo administradores pueden acceder a configuración
            If Not PermissionHelper.IsAdmin() Then
                TempData("Error") = "Solo los administradores pueden acceder a la configuración."
                filterContext.Result = RedirectToAction("Index", "Home")
                Return
            End If

            MyBase.OnActionExecuting(filterContext)
        End Sub

        ' GET: /Settings
        Function Index() As ActionResult
            Dim model = _db.GetSiteSettings()
            ViewBag.SiteSettings = model
            Return View(model)
        End Function

        ' POST: /Settings/Update
        <HttpPost>
        <ValidateAntiForgeryToken>
        Function Update(model As SiteSettingsViewModel) As ActionResult
            Dim userId = SessionHelper.GetCurrentUserId()

            Try
                _db.UpdateSiteSetting("SiteName", model.SiteName, userId)
                _db.UpdateSiteSetting("PrimaryColor", model.PrimaryColor, userId)
                _db.UpdateSiteSetting("CompanyEmail", model.CompanyEmail, userId)
                _db.UpdateSiteSetting("CompanyPhone", model.CompanyPhone, userId)

                _db.LogAction(userId, "UPDATE", "SiteSettings", Nothing, "Configuración del sitio actualizada")

                TempData("Success") = "Configuración actualizada exitosamente."
            Catch ex As Exception
                TempData("Error") = "Error al guardar: " & ex.Message
            End Try

            Return RedirectToAction("Index")
        End Function

        ' POST: /Settings/UploadLogo
        <HttpPost>
        <ValidateAntiForgeryToken>
        Function UploadLogo(logo As HttpPostedFileBase) As ActionResult
            Dim userId = SessionHelper.GetCurrentUserId()

            If logo Is Nothing OrElse logo.ContentLength = 0 Then
                TempData("Error") = "Seleccione una imagen para el logotipo."
                Return RedirectToAction("Index")
            End If

            Dim allowedExtensions = {".jpg", ".jpeg", ".png", ".gif", ".svg", ".webp"}
            Dim extension = Path.GetExtension(logo.FileName).ToLower()

            If Not allowedExtensions.Contains(extension) Then
                TempData("Error") = "Solo se permiten imágenes (JPG, PNG, GIF, SVG, WebP)."
                Return RedirectToAction("Index")
            End If

            If logo.ContentLength > 2 * 1024 * 1024 Then
                TempData("Error") = "El logotipo no debe superar 2MB."
                Return RedirectToAction("Index")
            End If

            Try
                Dim fileName = $"logo_{DateTime.Now:yyyyMMddHHmmss}{extension}"
                Dim uploadDir = Server.MapPath("~/Uploads/Logos/")

                If Not Directory.Exists(uploadDir) Then
                    Directory.CreateDirectory(uploadDir)
                End If

                ' Eliminar logo anterior
                Dim currentSettings = _db.GetSiteSettings()
                If Not String.IsNullOrEmpty(currentSettings.LogoPath) Then
                    Dim oldPath = Server.MapPath("~" & currentSettings.LogoPath)
                    If System.IO.File.Exists(oldPath) Then
                        System.IO.File.Delete(oldPath)
                    End If
                End If

                Dim filePath = Path.Combine(uploadDir, fileName)
                logo.SaveAs(filePath)

                Dim relativePath = $"/Uploads/Logos/{fileName}"
                _db.UpdateSiteSetting("LogoPath", relativePath, userId)

                _db.LogAction(userId, "UPDATE", "SiteSettings", Nothing, "Logotipo actualizado")

                TempData("Success") = "Logotipo actualizado exitosamente."
            Catch ex As Exception
                TempData("Error") = "Error al subir logotipo: " & ex.Message
            End Try

            Return RedirectToAction("Index")
        End Function

    End Class

End Namespace
