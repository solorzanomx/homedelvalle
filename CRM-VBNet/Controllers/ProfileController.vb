' =============================================
' ProfileController.vb
' Perfil del usuario autenticado
' Permite editar datos personales y foto de perfil
' =============================================
Imports System.Web.Mvc
Imports System.IO
Imports CRM_VBNet.Models
Imports CRM_VBNet.Helpers

Namespace CRM_VBNet.Controllers

    Public Class ProfileController
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
            MyBase.OnActionExecuting(filterContext)
        End Sub

        ' GET: /Profile
        Function Index() As ActionResult
            Dim userId = SessionHelper.GetCurrentUserId()
            Dim user = _db.GetUserById(userId)

            Dim model As New ProfileViewModel() With {
                .Id = user.Id,
                .FirstName = user.FirstName,
                .LastName = user.LastName,
                .Email = user.Email,
                .Phone = user.Phone,
                .WhatsApp = user.WhatsApp,
                .Address = user.Address,
                .PhotoPath = user.PhotoUrl
            }

            ViewBag.User = user
            ViewBag.SiteSettings = _db.GetSiteSettings()
            Return View(model)
        End Function

        ' POST: /Profile/Update
        <HttpPost>
        <ValidateAntiForgeryToken>
        Function Update(model As ProfileViewModel) As ActionResult
            model.Id = SessionHelper.GetCurrentUserId()

            If String.IsNullOrEmpty(model.FirstName) OrElse String.IsNullOrEmpty(model.Email) Then
                ViewBag.Error = "Nombre y Email son obligatorios."
                ViewBag.SiteSettings = _db.GetSiteSettings()
                Return View("Index", model)
            End If

            Try
                _db.UpdateProfile(model)

                ' Refrescar datos en sesión
                Dim updatedUser = _db.GetUserById(model.Id)
                SessionHelper.RefreshUser(updatedUser)

                ' Auditoría
                _db.LogAction(model.Id, "UPDATE", "Users", model.Id, "Perfil actualizado")

                TempData("Success") = "Perfil actualizado exitosamente."
            Catch ex As Exception
                TempData("Error") = "Error al actualizar perfil: " & ex.Message
            End Try

            Return RedirectToAction("Index")
        End Function

        ' ===========================================================
        ' FOTO DE PERFIL
        ' Sube la imagen, la guarda en /Uploads/Profiles/
        ' y actualiza la ruta en la base de datos
        ' ===========================================================
        ' POST: /Profile/UploadPhoto
        <HttpPost>
        <ValidateAntiForgeryToken>
        Function UploadPhoto(photo As HttpPostedFileBase) As ActionResult
            Dim userId = SessionHelper.GetCurrentUserId()

            If photo Is Nothing OrElse photo.ContentLength = 0 Then
                TempData("Error") = "Seleccione una imagen."
                Return RedirectToAction("Index")
            End If

            ' Validar que sea una imagen
            Dim allowedExtensions = {".jpg", ".jpeg", ".png", ".gif", ".webp"}
            Dim extension = Path.GetExtension(photo.FileName).ToLower()

            If Not allowedExtensions.Contains(extension) Then
                TempData("Error") = "Solo se permiten imágenes (JPG, PNG, GIF, WebP)."
                Return RedirectToAction("Index")
            End If

            ' Validar tamaño (máx 5MB)
            If photo.ContentLength > 5 * 1024 * 1024 Then
                TempData("Error") = "La imagen no debe superar 5MB."
                Return RedirectToAction("Index")
            End If

            Try
                ' Generar nombre único para evitar colisiones
                Dim fileName = $"profile_{userId}_{DateTime.Now:yyyyMMddHHmmss}{extension}"
                Dim uploadDir = Server.MapPath("~/Uploads/Profiles/")

                ' Crear directorio si no existe
                If Not Directory.Exists(uploadDir) Then
                    Directory.CreateDirectory(uploadDir)
                End If

                ' Eliminar foto anterior si existe
                Dim currentUser = _db.GetUserById(userId)
                If Not String.IsNullOrEmpty(currentUser.PhotoPath) Then
                    Dim oldPath = Server.MapPath("~" & currentUser.PhotoPath)
                    If System.IO.File.Exists(oldPath) Then
                        System.IO.File.Delete(oldPath)
                    End If
                End If

                ' Guardar nueva foto
                Dim filePath = Path.Combine(uploadDir, fileName)
                photo.SaveAs(filePath)

                ' Actualizar ruta en BD
                Dim relativePath = $"/Uploads/Profiles/{fileName}"
                _db.UpdateUserPhoto(userId, relativePath)

                ' Refrescar sesión
                Dim updatedUser = _db.GetUserById(userId)
                SessionHelper.RefreshUser(updatedUser)

                ' Auditoría
                _db.LogAction(userId, "UPDATE", "Users", userId, "Foto de perfil actualizada")

                TempData("Success") = "Foto de perfil actualizada."
            Catch ex As Exception
                TempData("Error") = "Error al subir la imagen: " & ex.Message
            End Try

            Return RedirectToAction("Index")
        End Function

        ' POST: /Profile/ChangePassword
        <HttpPost>
        <ValidateAntiForgeryToken>
        Function ChangePassword(currentPassword As String, newPassword As String, confirmPassword As String) As ActionResult
            Dim userId = SessionHelper.GetCurrentUserId()
            Dim user = SessionHelper.GetCurrentUser()

            If String.IsNullOrEmpty(newPassword) OrElse newPassword.Length < 6 Then
                TempData("Error") = "La nueva contraseña debe tener al menos 6 caracteres."
                Return RedirectToAction("Index")
            End If

            If newPassword <> confirmPassword Then
                TempData("Error") = "Las contraseñas no coinciden."
                Return RedirectToAction("Index")
            End If

            ' Verificar contraseña actual
            Dim authenticated = _db.Authenticate(user.Username, currentPassword)
            If authenticated Is Nothing Then
                TempData("Error") = "La contraseña actual es incorrecta."
                Return RedirectToAction("Index")
            End If

            Try
                _db.ChangePassword(userId, newPassword)
                _db.LogAction(userId, "UPDATE", "Users", userId, "Contraseña cambiada")
                TempData("Success") = "Contraseña actualizada exitosamente."
            Catch ex As Exception
                TempData("Error") = "Error al cambiar contraseña: " & ex.Message
            End Try

            Return RedirectToAction("Index")
        End Function

    End Class

End Namespace
