' =============================================
' UserController.vb
' CRUD completo de usuarios con validación de permisos
' Ejemplo claro de cómo se aplican los permisos en cada acción
' =============================================
Imports System.Web.Mvc
Imports CRM_VBNet.Models
Imports CRM_VBNet.Helpers

Namespace CRM_VBNet.Controllers

    Public Class UserController
        Inherits Controller

        Private ReadOnly _db As DatabaseContext

        Public Sub New()
            _db = New DatabaseContext(System.Configuration.ConfigurationManager.ConnectionStrings("CRMConnection").ConnectionString)
        End Sub

        ''' <summary>
        ''' Filtro que verifica autenticación antes de cada acción
        ''' </summary>
        Protected Overrides Sub OnActionExecuting(filterContext As ActionExecutingContext)
            If Not SessionHelper.IsAuthenticated() Then
                filterContext.Result = RedirectToAction("Login", "Account")
                Return
            End If
            MyBase.OnActionExecuting(filterContext)
        End Sub

        ' ===========================================================
        ' READ - Listar usuarios
        ' Permiso requerido: CanRead
        ' ===========================================================
        ' GET: /User/Index
        Function Index() As ActionResult
            ' *** VALIDACIÓN DE PERMISOS ***
            If Not PermissionHelper.CanRead() Then
                TempData("Error") = "No tiene permiso para ver usuarios."
                Return RedirectToAction("Index", "Home")
            End If

            Dim users = _db.GetAllUsers()
            ViewBag.CanWrite = PermissionHelper.CanWrite()
            ViewBag.CanDelete = PermissionHelper.CanDelete()
            ViewBag.SiteSettings = _db.GetSiteSettings()
            Return View(users)
        End Function

        ' ===========================================================
        ' READ - Ver detalle de un usuario
        ' Permiso requerido: CanRead
        ' ===========================================================
        ' GET: /User/Details/5
        Function Details(id As Integer) As ActionResult
            If Not PermissionHelper.CanRead() Then
                TempData("Error") = "No tiene permiso para ver esta información."
                Return RedirectToAction("Index", "Home")
            End If

            Dim user = _db.GetUserById(id)
            If user Is Nothing Then
                TempData("Error") = "Usuario no encontrado."
                Return RedirectToAction("Index")
            End If

            ViewBag.SiteSettings = _db.GetSiteSettings()
            Return View(user)
        End Function

        ' ===========================================================
        ' CREATE - Formulario para crear usuario
        ' Permiso requerido: CanWrite
        ' ===========================================================
        ' GET: /User/Create
        Function Create() As ActionResult
            ' *** VALIDACIÓN DE PERMISOS ***
            If Not PermissionHelper.CanWrite() Then
                TempData("Error") = "No tiene permiso para crear usuarios."
                Return RedirectToAction("Index")
            End If

            ViewBag.Roles = _db.GetAllRoles()
            ViewBag.SiteSettings = _db.GetSiteSettings()
            Return View(New UserViewModel())
        End Function

        ' POST: /User/Create
        <HttpPost>
        <ValidateAntiForgeryToken>
        Function Create(model As UserViewModel) As ActionResult
            ' *** VALIDACIÓN DE PERMISOS ***
            If Not PermissionHelper.CanWrite() Then
                TempData("Error") = "No tiene permiso para crear usuarios."
                Return RedirectToAction("Index")
            End If

            If String.IsNullOrEmpty(model.Username) OrElse String.IsNullOrEmpty(model.Email) OrElse
               String.IsNullOrEmpty(model.Password) Then
                ViewBag.Error = "Todos los campos obligatorios deben completarse."
                ViewBag.Roles = _db.GetAllRoles()
                ViewBag.SiteSettings = _db.GetSiteSettings()
                Return View(model)
            End If

            Try
                Dim user As New User() With {
                    .Username = model.Username,
                    .Email = model.Email,
                    .FirstName = model.FirstName,
                    .LastName = model.LastName,
                    .Phone = model.Phone,
                    .RoleId = model.RoleId
                }

                Dim newId = _db.CreateUser(user, model.Password)

                ' Log de auditoría
                _db.LogAction(SessionHelper.GetCurrentUserId(), "CREATE", "Users", newId,
                             $"Usuario creado: {model.Username}")

                TempData("Success") = "Usuario creado exitosamente."
                Return RedirectToAction("Index")
            Catch ex As Exception
                ViewBag.Error = "Error al crear usuario: " & ex.Message
                ViewBag.Roles = _db.GetAllRoles()
                ViewBag.SiteSettings = _db.GetSiteSettings()
                Return View(model)
            End Try
        End Function

        ' ===========================================================
        ' UPDATE - Formulario para editar usuario
        ' Permiso requerido: CanWrite
        ' ===========================================================
        ' GET: /User/Edit/5
        Function Edit(id As Integer) As ActionResult
            ' *** VALIDACIÓN DE PERMISOS ***
            If Not PermissionHelper.CanWrite() Then
                TempData("Error") = "No tiene permiso para modificar usuarios."
                Return RedirectToAction("Index")
            End If

            Dim user = _db.GetUserById(id)
            If user Is Nothing Then
                TempData("Error") = "Usuario no encontrado."
                Return RedirectToAction("Index")
            End If

            Dim model As New UserViewModel() With {
                .Id = user.Id,
                .Username = user.Username,
                .Email = user.Email,
                .FirstName = user.FirstName,
                .LastName = user.LastName,
                .Phone = user.Phone,
                .RoleId = user.RoleId,
                .IsActive = user.IsActive
            }

            ViewBag.Roles = _db.GetAllRoles()
            ViewBag.SiteSettings = _db.GetSiteSettings()
            Return View(model)
        End Function

        ' POST: /User/Edit/5
        <HttpPost>
        <ValidateAntiForgeryToken>
        Function Edit(model As UserViewModel) As ActionResult
            ' *** VALIDACIÓN DE PERMISOS ***
            If Not PermissionHelper.CanWrite() Then
                TempData("Error") = "No tiene permiso para modificar usuarios."
                Return RedirectToAction("Index")
            End If

            Try
                Dim user As New User() With {
                    .Id = model.Id,
                    .Email = model.Email,
                    .FirstName = model.FirstName,
                    .LastName = model.LastName,
                    .Phone = model.Phone,
                    .RoleId = model.RoleId,
                    .IsActive = model.IsActive
                }

                _db.UpdateUser(user)

                ' Log de auditoría
                _db.LogAction(SessionHelper.GetCurrentUserId(), "UPDATE", "Users", model.Id,
                             $"Usuario actualizado: {model.Username}")

                TempData("Success") = "Usuario actualizado exitosamente."
                Return RedirectToAction("Index")
            Catch ex As Exception
                ViewBag.Error = "Error al actualizar: " & ex.Message
                ViewBag.Roles = _db.GetAllRoles()
                ViewBag.SiteSettings = _db.GetSiteSettings()
                Return View(model)
            End Try
        End Function

        ' ===========================================================
        ' DELETE - Eliminar (desactivar) usuario
        ' Permiso requerido: CanDelete
        ' ===========================================================
        ' POST: /User/Delete/5
        <HttpPost>
        <ValidateAntiForgeryToken>
        Function Delete(id As Integer) As ActionResult
            ' *** VALIDACIÓN DE PERMISOS ***
            If Not PermissionHelper.CanDelete() Then
                TempData("Error") = "No tiene permiso para eliminar usuarios."
                Return RedirectToAction("Index")
            End If

            Try
                ' No permitir eliminarse a sí mismo
                If id = SessionHelper.GetCurrentUserId() Then
                    TempData("Error") = "No puede desactivar su propia cuenta."
                    Return RedirectToAction("Index")
                End If

                _db.DeleteUser(id)

                ' Log de auditoría
                _db.LogAction(SessionHelper.GetCurrentUserId(), "DELETE", "Users", id,
                             "Usuario desactivado")

                TempData("Success") = "Usuario desactivado exitosamente."
            Catch ex As Exception
                TempData("Error") = "Error al eliminar: " & ex.Message
            End Try

            Return RedirectToAction("Index")
        End Function

    End Class

End Namespace
